<?php

namespace Admin\Controller;
use Admin\Model\Report\ReportCommonModel;
use Think\Controller;
use Org\Util\Rbac;
class ReportOperationController extends BaseController {

    public function index() {
        $this->display();
    }

    public function summary() {
        if(!empty($_POST)){
            $page = $_POST['page'] != '' ? $_POST['page'] : 1;
            $rows = $_POST['rows'] != '' ? $_POST['rows']-1 : 1;
            $startTime = I('startTime') ? strtotime(I('startTime')) : '';
            $endTime = I('endTime') ? strtotime(I('endTime')) : '';
            if ($startTime && $endTime && $startTime <= $endTime) {
                $startTime = date('Y/m/d', $startTime);
                $endTime = date('Y/m/d', $endTime);
            }elseif($endTime) {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
            }else {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
                $endTime = date('Y/m/d');
            }
            $sellerId = I('sellerId');
            $buyerId = I('buyerId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'operationSummary',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
                'buyerId' => $buyerId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            if (I('sidx')) {
                $order = I('sidx').' '.I('sord');
                if ($order) {
                    $param['orderBy'] = $order;
                }
            }
            $msg = $this->getList($param);
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'buyerid' => 'total',
                'sellerid' => 'total',
                'reportdate' => 'total',
//                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'view' => '-',
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
                'bidok' => $totalArr['bidok'] ? $totalArr['bidok'] : 0,
                'play' => $totalArr['play'] ? $totalArr['play'] : 0,
                'cpm' => $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : '0.00',
                'click' => $totalArr['click'] ? $totalArr['click'] : 0,
                'cpc' => $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : '0.00',
                'spend' => $totalArr['spend'] ? number_format($totalArr['spend'], 2, '.', ',') : '0.00',
                'sellerplay' => $totalArr['sellerplay'] ? $totalArr['sellerplay'] : 0,
                'sellercpm' => $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : '0.00',
                'sellerclick' => $totalArr['sellerclick'] ? $totalArr['sellerclick'] : 0,
                'sellercpc' => $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : '0.00',
                'sellerspend' => $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : '0.00',
                'buyerspend' => $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : '0.00',
            ];
            if ($groupBy != 'sellerId') {
                $total['sellerid'] = '-';
            }
            if ($groupBy != 'buyerId') {
                $total['buyerid'] = '-';
            }

            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 87);
        $colNames = ['日期', '买方', '卖方'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'buyerid', 'index'=>'buyerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
        ];
        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
            if (strstr($v['remark'], 'seller')) {
                $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'green', 'sorttype' => 'int'];
            }else{
                if ( (strstr($v['remark'], 'spend') && !strstr($v['remark'], 'buyer')) || strstr($v['remark'], 'play') || strstr($v['remark'], 'idok')) {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'blue', 'sorttype' => 'int'];
                }else {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'sorttype' => 'int'];
                }
            }
        }

        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();
        $buyer = D('Buyer')->field('id, company')->order('status asc')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('buyer', $buyer);
        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }

    public function exportSummaryReport() {
        $startTime = I('startTime') ? date('Y/m/d', strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d', strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $buyerId = I('buyerId') ? I('buyerId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'operationSummary',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'buyerId' => $buyerId,
            'buyerId' => $buyerId,
        ];
        $this->exportReport($param, '运营总表');
    }

    public function place() {
        if (I('infoType')) {
            $function = I('infoType');
            $sellerId = I('sellerId');
            $mediaId = I('mediaId');
            $id = $mediaId ? $mediaId : $sellerId;
            $this->ajaxReturn($this->$function($id));
        }

        if(IS_POST){
            $page = $_POST['page'] != '' ? $_POST['page'] : 1;
            $rows = $_POST['rows'] != '' ? $_POST['rows']-1 : 1;
            $startTime = I('startTime') ? strtotime(I('startTime')) : '';
            $endTime = I('endTime') ? strtotime(I('endTime')) : '';
            if ($startTime && $endTime && $startTime <= $endTime) {
                $startTime = date('Y/m/d', $startTime);
                $endTime = date('Y/m/d', $endTime);
            }elseif($endTime) {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
            }else {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
                $endTime = date('Y/m/d');
            }
            $sellerId = I('sellerId');
            $buyerId = I('buyerId');
            $mediaId = I('mediaId');
            $placeId = I('placeId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';
            $dateType = I('dateType');

            $param = [
                'reportType' => 'operationPlace',
                'reportDate' => $startTime.'-'.$endTime,
                'dateType' => $dateType,
                'groupBy' => $groupBy,
                'buyerId' => $buyerId,
                'sellerId' => $sellerId,
                'mediaId' => $mediaId,
                'placeId' => $placeId,
                'page' => $page,
                'pageSize' => $rows,
            ];
              if (I('sidx')) {
                $order = I('sidx').' '.I('sord');
                if ($order) {
                    $param['orderBy'] = $order;
                }
            }
            $msg = $this->getList($param);
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'buyerid' => 'total',
                'sellerid' => 'total',
                'mediaid' => 'total',
                'placeid' => 'total',
                'reportdate' => 'total',
                'hour' => '-',
//                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'view' => '-',
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
                'bidok' => $totalArr['bidok'] ? $totalArr['bidok'] : 0,
                'play' => $totalArr['play'] ? $totalArr['play'] : 0,
                'cpm' => $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : '0.00',
                'click' => $totalArr['click'] ? $totalArr['click'] : 0,
                'cpc' => $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : '0.00',
                'spend' => $totalArr['spend'] ? number_format($totalArr['spend'], 2, '.', ',') : '0.00',
                'sellerplay' => $totalArr['sellerplay'] ? $totalArr['sellerplay'] : 0,
                'sellercpm' => $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : '0.00',
                'sellerclick' => $totalArr['sellerclick'] ? $totalArr['sellerclick'] : 0,
                'sellercpc' => $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : '0.00',
                'sellerspend' => $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : '0.00',
                'buyerspend' => $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : '0.00',
            ];
            if ($groupBy == 'id') {
                $total['sellerid'] = '-';
                $total['buyerid'] = '-';
                $total['mediaid'] = '-';
                $total['placeid'] = '-';
            }else if($groupBy == 'mediaId') {
                $total['mediaid'] = '-';
            }else if($groupBy == 'placeId') {
                $total['mediaid'] = '-';
                $total['placeid'] = '-';
            }
            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }
        $colNames = ['日期', '小时', '买方', '卖方', '媒体', '广告位'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'hour', 'index'=>'hour', 'width'=>100, 'editable' => true,'sorttype' => 'int','hidden' => true],
            ['name'=>'buyerid', 'index'=>'buyerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'mediaid', 'index'=>'mediaid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'placeid', 'index'=>'placeid', 'width'=>100, 'editable' => true,'sorttype' => 'int'],
        ];

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 88);
        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
            $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true,'sorttype' => 'int'];
        }

        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();
        $buyer = D('Buyer')->field('id, company')->order('status asc')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('buyer', $buyer);

        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }

    public function exportPlaceReport() {
        $startTime = I('startTime') ? date('Y/m/d',strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d',strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }

        $buyerId = I('buyerId') ? I('buyerId') : '';
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $mediaId = I('mediaId') ? I('mediaId') : '';
        $placeId = I('placeId') ? I('placeId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';
        $dateType = I('dateType');

        $param = [
            'reportType' => 'operationPlace',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'buyerId' => $buyerId,
            'mediaId' => $mediaId,
            'placeId' => $placeId,
            'dateType' => $dateType,
        ];

        $this->exportReport($param, '运营广告位报表');
    }

    public function exportFailureReport() {
        $startTime = I('startTime') ? date('Y/m/d',strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d',strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }

        $buyerId = I('buyerId') ? I('buyerId') : '';
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $mediaId = I('mediaId') ? I('mediaId') : '';
        $placeId = I('placeId') ? I('placeId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'operationFailure',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'buyerId' => $buyerId,
            'mediaId' => $mediaId,
            'placeId' => $placeId,
            'page' => 1,
            'pageSize' => 99999999,
        ];

        $msg = $this->getList($param);
        $data = $msg['data']['list'];

        $title = ['reportdate' => '日期', 'buyerid' => '买方', 'creativeid' => '素材', 'sellerid' => '卖方', 'mediaid' => '媒体', 'place' => '广告位', 'placeid' => '广告位ID', '错误id', '错误总数', '错误描述'];

        if($groupBy == 'reportDate') {
                unset($title['sellerid']);
                unset($title['buyerid']);
                unset($title['creativeid']);
                unset($title['mediaid']);
                unset($title['place']);
                unset($title['placeid']);
        }else if($groupBy == 'sellerId') {
                unset($title['reportdate']);
                unset($title['buyerid']);
                unset($title['creativeid']);
                unset($title['mediaid']);
                unset($title['place']);
                unset($title['placeid']);
        }else if($groupBy == 'buyerId') {
            unset($title['reportdate']);
            unset($title['sellerid']);
            unset($title['creativeid']);
            unset($title['place']);
            unset($title['placeid']);
            unset($title['mediaid']);
        }else if($groupBy == 'mediaId') {
            unset($title['reportdate']);
            unset($title['buyerid']);
            unset($title['creativeid']);
            unset($title['place']);
            unset($title['placeid']);
        }else if($groupBy == 'placeId') {
            unset($title['reportdate']);
            unset($title['buyerid']);
            unset($title['creativeid']);
        }else if($groupBy == 'creativeId') {
            unset($title['reportdate']);
            unset($title['seller']);
            unset($title['placeid']);
            unset($title['place']);
            unset($title['mediaid']);
        }else if($groupBy == 'errorId') {
            unset($title['reportdate']);
            unset($title['buyerid']);
            unset($title['sellerid']);
            unset($title['creativeid']);
            unset($title['mediaid']);
            unset($title['place']);
            unset($title['placeid']);
        }

        $resultData = [];
        foreach ($data as $key => $value) {
            $resultData[$key]['reportdate'] = isset($value['reportdate']) ? $value['reportdate'] : '';
            $resultData[$key]['buyerid'] = isset($value['buyerid']) ? $value['buyerid'] : '';
            $resultData[$key]['creativeid'] = isset($value['creativeid']) ? $value['creativeid'] : '';
            $resultData[$key]['sellerid'] = isset($value['sellerid']) ? $value['sellerid'] : '';
            $resultData[$key]['mediaid'] = isset($value['mediaid']) ? $value['mediaid'] : '';
            $resultData[$key]['place'] = isset($value['place']) ? $value['place'] : '';
            $resultData[$key]['placeid'] = isset($value['placeid']) ? $value['placeid'] : '';
            $resultData[$key]['errorid'] = isset($value['errorid']) ? $value['errorid'] : '';
            $resultData[$key]['errortotal'] = isset($value['errortotal']) ? $value['errortotal'] : '';
            $resultData[$key]['errordes'] = isset($value['errordes']) ? $value['errordes'] : '';
            if($groupBy == 'reportDate') {
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['creativeid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'sellerId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['creativeid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'buyerId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['creativeid']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
                unset($resultData[$key]['mediaid']);
            }else if($groupBy == 'mediaId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['creativeid']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'placeId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['creativeid']);
            }else if($groupBy == 'creativeId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['seller']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
                unset($resultData[$key]['mediaid']);
            }else if($groupBy == 'errorId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['creativeid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['place']);
                unset($resultData[$key]['placeid']);
            }
        }

        exportExcel($resultData, $title, '失败明细');
    }

    public function exportPmpReport()
    {
        $startTime = I('startTime') ? date('Y/m/d',strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d',strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }

        $buyerId = I('buyerId') ? I('buyerId') : '';
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'operationPmp',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'buyerId' => $buyerId,
        ];

        $this->exportReport($param, 'PMP报表');
    }

    public function pmp() {
        if(IS_POST){
            $page = $_POST['page'] != '' ? $_POST['page'] : 1;
            $rows = $_POST['rows'] != '' ? $_POST['rows']-1 : 1;
            $startTime = I('startTime') ? strtotime(I('startTime')) : '';
            $endTime = I('endTime') ? strtotime(I('endTime')) : '';
            if ($startTime && $endTime && $startTime <= $endTime) {
                $startTime = date('Y/m/d', $startTime);
                $endTime = date('Y/m/d', $endTime);
            }elseif($endTime) {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
            }else {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
                $endTime = date('Y/m/d');
            }
            $sellerId = I('sellerId');
            $buyerId = I('buyerId');
            $dealId = I('dealId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'operationPmp',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'buyerId' => $buyerId,
                'sellerId' => $sellerId,
                'dealId' => $dealId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            if (I('sidx')) {
                $order = I('sidx').' '.I('sord');
                if ($order) {
                    $param['orderBy'] = $order;
                }
            }
            $msg = $this->getList($param);
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'buyerid' => 'total',
                'sellerid' => 'total',
                'dealid' => 'total',
                'reportdate' => 'total',
                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
                'winbid' => $totalArr['winbid'] ? $totalArr['winbid'] : 0,
                'play' => $totalArr['play'] ? $totalArr['play'] : 0,
                'cpm' => $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : 0.00,
                'click' => $totalArr['click'] ? $totalArr['click'] : 0,
                'cpc' => $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : 0.00,
                'spend' => $totalArr['spend'] ? $totalArr['spend'] : '0.00',
                'sellerplay' => $totalArr['sellerplay'] ? $totalArr['sellerplay'] : 0,
                'sellercpm' => $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : 0.00,
                'sellerclick' => $totalArr['sellerclick'] ? $totalArr['sellerclick'] : 0,
                'sellercpc' => $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : 0.00,
                'sellercost' => $totalArr['sellercost'] ? number_format($totalArr['sellercost'], 2, '.', ',') : 0.00,
                'buyerspend' => $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : 0.00,
                'sellerspend' => $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : 0.00,
            ];
            if ($groupBy == 'id') {
                $total['sellerid'] = '-';
                $total['buyerid'] = '-';
                $total['dealid'] = '-';
            }else if($groupBy == 'mediaId') {
                $total['mediaid'] = '-';
            }else if($groupBy == 'placeId') {
                $total['mediaid'] = '-';
                $total['placeid'] = '-';
            }
            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }
        $colNames = ['日期', '买方', '卖方', '交易'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'buyerid', 'index'=>'buyerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'dealid', 'index'=>'dealid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
        ];

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 90);
        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
            $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true,'sorttype' => 'int'];
        }

        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();
        $buyer = D('Buyer')->field('id, company')->order('status asc')->select();

//        $startTime = date('Y-m-01');
        $startTime = date('Y-m-d');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('buyer', $buyer);

        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }

    public function failure() {
        if (I('infoType')) {
            $function = I('infoType');
            $sellerId = I('sellerId');
            $mediaId = I('mediaId');
            $id = $mediaId ? $mediaId : $sellerId;
            $this->ajaxReturn($this->$function($id));
        }

        if(IS_POST){
            $page = $_POST['page'] != '' ? $_POST['page'] : 1;
            $rows = $_POST['rows'] != '' ? $_POST['rows'] : 1;
            $startTime = I('startTime') ? strtotime(I('startTime')) : '';
            $endTime = I('endTime') ? strtotime(I('endTime')) : '';
            if ($startTime && $endTime && $startTime <= $endTime) {
                $startTime = date('Y/m/d', $startTime);
                $endTime = date('Y/m/d', $endTime);
            }elseif($endTime) {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
            }else {
//                $startTime = date('Y/m/1');
                $startTime = date('Y/m/d');
                $endTime = date('Y/m/d');
            }
            $sellerId = I('sellerId');
            $buyerId = I('buyerId');
            $creativeId = I('creativeId');
            $mediaId = I('mediaId');
            $placeId = I('placeId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            if ($groupBy != 'id' && $groupBy != 'errorId') {
                $groupBy .= ',errorId';
            }

            $param = [
                'reportType' => 'operationFailure',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'buyerId' => $buyerId,
                'creativeId' => $creativeId,
                'sellerId' => $sellerId,
                'mediaId' => $mediaId,
                'placeId' => $placeId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            if (I('sidx')) {
                $order = I('sidx').' '.I('sord');
                if ($order) {
                    $param['orderBy'] = $order;
                }
            }
            $msg = $this->getList($param);
            $data = $msg['data']['list'];

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }
        $colNames = ['日期', '买方', '素材ID', '卖方', '媒体', '广告位', '广告位ID', '错误ID', '错误总数', '错误描述'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>80, 'editable' => true, 'sortable' => false],
            ['name'=>'buyerid', 'index'=>'buyerid', 'width'=>150, 'editable' => true, 'sortable' => false],
            ['name'=>'creativeid', 'index'=>'creativeid', 'width'=>70, 'editable' => true, 'sortable' => false],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>80, 'editable' => true, 'sortable' => false],
            ['name'=>'mediaid', 'index'=>'mediaid', 'width'=>170, 'editable' => true, 'sortable' => false],
            ['name'=>'place', 'index'=>'place', 'width'=>170, 'editable' => true, 'sortable' => false],
            ['name'=>'placeid', 'index'=>'placeid', 'width'=>70, 'editable' => true, 'sortable' => false],
            ['name'=>'errorid', 'index'=>'errorid', 'width'=>70, 'editable' => true, 'sortable' => false],
            ['name'=>'errortotal', 'index'=>'errortotal', 'width'=>70, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'errordes', 'index'=>'errordes', 'width'=>180, 'editable' => true, 'sortable' => false],
        ];

        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();
        $buyer = D('Buyer')->field('id, company')->order('status asc')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('buyer', $buyer);

        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }

    public function getList($param = []) {
        $obj = new ReportCommonModel($param);
        $msg=$obj->getData();
        return $msg;
    }

    public function getMedias($sellerId) {
        $where = 1;
        if ($sellerId != '') {
            $where .= " and sellerId = {$sellerId}";
        }

        $medias = D('Media')->where($where)->select();
        $places = D('Place')->where($where)->select();
        $result = [
            'medias' => $medias,
            'places' => $places
        ];
        return $result;
    }

    public function getPlaces($mediaId) {
        $where = 1;
        if ($mediaId != '') {
            $where .= " and mediaId = {$mediaId}";
        }

        $medias = D('Place')->where($where)->select();
        return $medias;
    }

    public function exportReport($search, $excelName) {
        $reportType = $search['reportType'];
        $groupBy = $search['groupBy'] ? $search['groupBy'] : 'id';
        $param = [
            'reportType' => $reportType,
            'reportDate' => $search['reportDate'],
            'groupBy' => $groupBy,
            'page' => 1,
            'pageSize' => 99999999,
        ];

        if (isset($search['dateType'])) {
            $param['dateType'] = $search['dateType'];
        }

        if (isset($search['buyerId'])) {
            $param['buyerId'] = $search['buyerId'];
        }
        if (isset($search['sellerId'])) {
            $param['sellerId'] = $search['sellerId'];
        }
        if (isset($search['mediaId'])) {
            $param['mediaId'] = $search['mediaId'];
        }
        if (isset($search['placeId'])) {
            $param['placeId'] = $search['placeId'];
        }

        $parentIdArr = [
            'operationSummary' => 87,
            'operationPlace' => 88,
            'operationPmp' => 90,
        ];
        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], $parentIdArr[$reportType]);

        $msg = $this->getList($param);
        $totalArr = $msg['data']['total'];
        $data = $msg['data']['list'];

        $dateTitle = ['reportdate' => '日期', 'hour' => '小时'];
        $dateTotal = ['reportdate' => 'total', 'hour' => '-'];

        $title = ['buyerid' => '买方', 'sellerid' => '卖方', 'mediaid' => '媒体', 'placeid' => '广告位', 'dealid' => '交易'];

        $total = [
            'reportdate' => 'total',
            'buyerid' => 'total',
            'sellerid' => 'total',
            'mediaid' => 'total',
            'placeid' => 'total',
            'dealid' => '-'
        ];

        if (!isset($search['dateType']) || $search['dateType'] != 'hour') {
            unset($dateTitle['hour']);
            unset($dateTotal['hour']);
        }
        $title = array_merge($dateTitle, $title);
        $total = array_merge($dateTotal, $total);

        $fieldArr = [];
        foreach ($fields as $k => $v) {
            $title[] = $v['name'];
            switch ($v['remark']) {
                case 'cpm':
                    $total[$v['remark']] = $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : 0;
                    break;
                case 'cpc':
                    $total[$v['remark']] = $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : 0;
                    break;
                case 'spend':
                    $total[$v['remark']] = $totalArr['spend'] ? number_format(($totalArr['spend']), 2, '.', ',') : 0;
                    break;
                case 'sellercpm':
                    $total[$v['remark']] = $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : 0;
                    break;
                case 'sellercpc':
                    $total[$v['remark']] = $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : 0;
                    break;
                case 'sellerspend':
                    $total[$v['remark']] = $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : 0;
                    break;
                case 'buyerspend':
                    $total[$v['remark']] = $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : 0;
                    break;
                case 'view':
                    $total[$v['remark']] = $totalArr['view'] ? $totalArr['view'] : '-';
                    break;
                default:
                    $total[$v['remark']] = $totalArr[$v['remark']] ? $totalArr[$v['remark']] : 0;
                    break;
            }
            $fieldArr[$v['remark']] = $v['name'];
        }

        if ($reportType == 'operationSummary' || $reportType == 'operationPmp' ) {
            unset($title['mediaid']);
            unset($total['mediaid']);
            unset($title['placeid']);
            unset($total['placeid']);
        }

        if ($reportType != 'operationPmp') {
            unset($title['dealid']);
            unset($total['dealid']);
        }

        if ($groupBy == 'id') {
            $total['sellerid'] = '-';
            $total['buyerid'] = '-';
            if ($reportType == 'operationPlace') {
                $total['mediaid'] = '-';
                $total['placeid'] = '-';
            }
        }else if($groupBy == 'reportDate') {
            if ( $reportType == 'operationSummary' || $reportType == 'operationPlace' || $reportType == 'operationPmp') {
                unset($title['dealid']);
                unset($total['dealid']);
                unset($total['sellerid']);
                unset($title['sellerid']);
                unset($total['buyerid']);
                unset($title['buyerid']);
                if ($reportType == 'operationPlace') {
                    unset($total['mediaid']);
                    unset($title['mediaid']);
                    unset($total['placeid']);
                    unset($title['placeid']);
                }
            }
        }else if($groupBy == 'sellerId') {
            if ( $reportType == 'operationSummary' || $reportType == 'operationPlace' || $reportType == 'operationPmp') {
                unset($title['dealid']);
                unset($total['dealid']);
                unset($total['reportdate']);
                unset($title['reportdate']);
                unset($title['hour']);
                unset($total['hour']);
                unset($total['buyerid']);
                unset($title['buyerid']);
                if ($reportType == 'operationPlace') {
                    unset($total['mediaid']);
                    unset($title['mediaid']);
                    unset($total['placeid']);
                    unset($title['placeid']);
                }
            }
        }else if($groupBy == 'buyerId') {
            unset($title['dealid']);
            unset($total['dealid']);
            unset($total['reportdate']);
            unset($title['reportdate']);
            unset($title['hour']);
            unset($total['hour']);
            unset($total['sellerid']);
            unset($title['sellerid']);
            if ($reportType == 'operationPlace') {
                unset($total['placeid']);
                unset($title['placeid']);
                unset($total['mediaid']);
                unset($title['mediaid']);
            }
        }else if($groupBy == 'mediaId') {
            $total['mediaid'] = '-';
            unset($total['reportdate']);
            unset($title['reportdate']);
            unset($title['hour']);
            unset($total['hour']);
            unset($total['buyerid']);
            unset($title['buyerid']);
            unset($total['placeid']);
            unset($title['placeid']);
        }else if($groupBy == 'placeId') {
            $total['mediaid'] = '-';
            $total['placeid'] = '-';
            unset($total['reportdate']);
            unset($title['reportdate']);
            unset($title['hour']);
            unset($total['hour']);
            unset($total['buyerid']);
            unset($title['buyerid']);
        }
        $resultData = [];
        foreach ($data as $key => $value) {
            $resultData[$key]['reportdate'] = isset($value['reportdate']) ? $value['reportdate'] : '';
            if (isset($search['dateType']) && $search['dateType'] == 'hour') {
                $resultData[$key]['hour'] = isset($value['hour']) ? $value['hour'] : '';
            }
            $resultData[$key]['buyerid'] = isset($value['buyerid']) ? $value['buyerid'] : '';
            $resultData[$key]['sellerid'] = isset($value['sellerid']) ? $value['sellerid'] : '';
            $resultData[$key]['dealid'] = isset($value['dealid']) ? $value['dealid'] : '';
            if ($reportType != 'operationPmp') {
                $resultData[$key]['mediaid'] = isset($value['mediaid']) ? $value['mediaid'] : '';
                $resultData[$key]['placeid'] = isset($value['placeid']) ? $value['placeid'] : '';
            }
            if ($reportType == 'operationSummary' || $reportType == 'operationPlace') {
                unset($resultData[$key]['dealid']);
            }
            foreach ($fieldArr as $k => $v) {
                if ($k == 'bid') {
                    $resultData[$key]['bid'] = isset($value['bid']) ? $value['bid'] : '';
                }else {
                    $resultData[$key][$k] = isset($value[$k]) ? $value[$k] : 0;
                }
            }

            if($groupBy == 'reportDate') {
                unset($resultData[$key]['dealid']);
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'sellerId') {
                unset($resultData[$key]['dealid']);
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['hour']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'buyerId') {
                unset($resultData[$key]['dealid']);
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['hour']);
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'mediaId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['hour']);
                unset($resultData[$key]['buyerid']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'placeId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['hour']);
                unset($resultData[$key]['buyerid']);
            }else if($groupBy == 'id') {
                if ($reportType == 'operationSummary') {
                    unset($resultData[$key]['placeid']);
                    unset($resultData[$key]['mediaid']);
                }
            }
        }
        array_unshift($resultData, $total);

        exportExcel($resultData, $title, $excelName);
    }
}
