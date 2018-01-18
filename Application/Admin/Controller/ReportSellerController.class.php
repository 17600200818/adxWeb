<?php

namespace Admin\Controller;
use Admin\Model\Report\ReportCommonModel;
use Think\Controller;
use Org\Util\Rbac;
class ReportSellerController extends BaseController {

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
            $groupBy = I('groupBy') ? I('groupBy') : 'id';


            $param = [
                'reportType' => 'sellerSummary',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
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
                'sellerid' => 'total',
                'reportdate' => 'total',
                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
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

            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 81);
        $colNames = ['日期', '卖方'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
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

        $startTime = date('Y-m-d');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
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
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'sellerSummary',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
        ];
        $this->exportReport($param, '卖方总表');
    }

    public function media() {
        if (I('infoType')) {
            $function = I('infoType');
            $sellerId = I('sellerId');
            $mediaId = I('mediaId');
            $id = $mediaId ? $mediaId : $sellerId;
            $this->ajaxReturn($this->$function($id));
        }

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
            $mediaId = I('mediaId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'sellerMedia',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
                'mediaId' => $mediaId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            $msg = $this->getList($param);
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'sellerid' => 'total',
                'mediaid' => 'total',
                'reportdate' => 'total',
                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
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
                $total['mediaid'] = '-';
            }else if($groupBy == 'mediaId') {
                $total['mediaid'] = '-';
            }
            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            $this->ajaxReturn($result);
        }

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 82);
        $colNames = ['日期', '卖方', '媒体'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'mediaid', 'index'=>'mediaid', 'width'=>100, 'editable' => true, 'sortable' => false],
        ];

        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
          if (strstr($v['remark'], 'seller')) {
                $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'green', 'sortable' => false];
            }else{
                if ( (strstr($v['remark'], 'spend') && !strstr($v['remark'], 'buyer')) || strstr($v['remark'], 'play') || strstr($v['remark'], 'idok')) {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'blue', 'sortable' => false];
                }else {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'sortable' => false];
                }
            } 
        }
        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }

    public function exportMediaReport() {
        $startTime = I('startTime') ? date('Y/m/d',strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d',strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $mediaId = I('mediaId') ? I('mediaId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'sellerMedia',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'mediaId' => $mediaId,
        ];
        $this->exportReport($param, '卖方媒体报表');
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
            $mediaId = I('mediaId');
            $placeId = I('placeId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'sellerPlace',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
                'mediaId' => $mediaId,
                'placeId' => $placeId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            $msg = $this->getList($param);
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'sellerid' => 'total',
                'mediaid' => 'total',
                'placeid' => 'total',
                'reportdate' => 'total',
                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
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
        $colNames = ['日期', '卖方', '媒体', '广告位'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'mediaid', 'index'=>'mediaid', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'placeid', 'index'=>'placeid', 'width'=>100, 'editable' => true, 'sortable' => false],
        ];

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 83);
        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
           if (strstr($v['remark'], 'seller')) {
                $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'green', 'sortable' => false];
            }else{
                if ( (strstr($v['remark'], 'spend') && !strstr($v['remark'], 'buyer')) || strstr($v['remark'], 'play') || strstr($v['remark'], 'idok')) {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'classes' => 'blue', 'sortable' => false];
                }else {
                    $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'sortable' => false];
                }
            } 
        }

        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);

        $seller = D('Seller')->field('id, company')->where('parentId = 0')->order('status asc')->select();
        // $medias = D('Media')->field('id, name')->select();
        // $places = D('Place')->field('id, name')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        // $this->assign('medias', $medias);
        // $this->assign('places', $places);
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

        $sellerId = I('sellerId') ? I('sellerId') : '';
        $mediaId = I('mediaId') ? I('mediaId') : '';
        $placeId = I('placeId') ? I('placeId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'sellerPlace',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'mediaId' => $mediaId,
            'placeId' => $placeId,
        ];

        $this->exportReport($param, '卖方广告位报表');
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

        if (isset($search['sellerId'])) {
            $param['sellerId'] = $search['sellerId'];
        }
        if (isset($search['mediaId'])) {
            $param['mediaId'] = $search['mediaId'];
        }
        if (isset($search['placeId'])) {
            $param['placeId'] = $search['placeId'];
        }
         if (isset($search['w'])) {
            $param['w'] = $search['w'];
        }
         if (isset($search['h'])) {
            $param['h'] = $search['h'];
        }

        $parentIdArr = [
            'sellerSummary' => 81,
            'sellerMedia' => 82,
            'sellerPlace' => 83,
            'sellerSize' => 347,
        ];
        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], $parentIdArr[$reportType]);

        $msg = $this->getList($param);
        $totalArr = $msg['data']['total'];
        $data = $msg['data']['list'];

        $title = ['reportdate' => '日期', 'sellerid' => '卖方', 'mediaid' => '媒体', 'placeid' => '广告位','h'=>'高度','w'=>'宽度'];

        $total = [
            'reportdate' => 'total',
            'sellerid' => 'total',
            'mediaid' => 'total',
            'placeid' => 'total',
             'h' => 'total',
            'w' => 'total',
        ];
        
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
                default:
                    $total[$v['remark']] = $totalArr[$v['remark']] ? $totalArr[$v['remark']] : 0;
                    break;
            }
            $fieldArr[$v['remark']] = $v['name'];
        }
        if($reportType != 'sellerSize'){
            unset($title['w']);
            unset($total['w']);
            unset($title['h']);
            unset($total['h']);
        }

        if ($reportType == 'sellerSummary') {
            unset($title['mediaid']);
            unset($total['mediaid']);
            unset($title['placeid']);
            unset($total['placeid']);
        }elseif ($reportType == 'sellerMedia') {
            unset($title['placeid']);
            unset($total['placeid']);
        }elseif ($reportType == 'sellerSize') {
            unset($title['placeid']);
            unset($total['placeid']);
             unset($title['mediaid']);
            unset($total['mediaid']);
           
           
                 
             
        }

        if ($groupBy == 'id') {
            $total['sellerid'] = '-';
            if ($reportType == 'sellerMedia' || $reportType == 'sellerPlace') {
                $total['mediaid'] = '-';
                if ($reportType == 'sellerPlace') {
                    $total['placeid'] = '-';
                }
            }
        }else if($groupBy == 'reportDate') {
            if ( $reportType == 'sellerSummary' || $reportType == 'sellerMedia' || $reportType == 'sellerPlace') {
                unset($total['sellerid']);
                unset($title['sellerid']);
                if ($reportType == 'sellerMedia' || $reportType == 'sellerPlace') {
                    unset($total['mediaid']);
                    unset($title['mediaid']);
                    if ($reportType == 'sellerPlace') {
                        unset($total['placeid']);
                        unset($title['placeid']);
                    }
                }
            }
        }else if($groupBy == 'sellerId') {
            if ( $reportType == 'sellerSummary' || $reportType == 'sellerMedia' || $reportType == 'sellerPlace') {
                unset($total['reportdate']);
                unset($title['reportdate']);
                if ($reportType == 'sellerMedia' || $reportType == 'sellerPlace') {
                    unset($total['mediaid']);
                    unset($title['mediaid']);
                    if ($reportType == 'sellerPlace') {
                        unset($total['placeid']);
                        unset($title['placeid']);
                    }
                }
            }
        }else if($groupBy == 'mediaId') {
            $total['mediaid'] = '-';
            unset($total['reportdate']);
            unset($title['reportdate']);
            if ($reportType == 'sellerPlace') {
                unset($total['placeid']);
                unset($title['placeid']);
            }
        }else if($groupBy == 'placeId') {
            $total['mediaid'] = '-';
            $total['placeid'] = '-';
            unset($total['reportdate']);
            unset($title['reportdate']);
        }
//        var_dump(json_encode($data));die;
        $resultData = [];
        $arr = ['reportdate', 'sellerid', 'mediaid', 'placeid','h','w'];
        foreach ($data as $key => $value) {
            $resultData[$key]['reportdate'] = isset($value['reportdate']) ? $value['reportdate'] : '';
            $resultData[$key]['sellerid'] = isset($value['sellerid']) ? $value['sellerid'] : '';
            $resultData[$key]['mediaid'] = isset($value['mediaid']) ? $value['mediaid'] : '';
            $resultData[$key]['placeid'] = isset($value['placeid']) ? $value['placeid'] : '';
            $resultData[$key]['h'] = isset($value['h']) ? $value['h'] : '';
            $resultData[$key]['w'] = isset($value['w']) ? $value['w'] : '';
            foreach ($fieldArr as $k => $v) {
                if ($k == 'bid') {
                    $resultData[$key]['bid'] = isset($value['bid']) ? $value['bid'] : '';
                }else {
                    $resultData[$key][$k] = isset($value[$k]) ? $value[$k] : 0;
                }
            }
                if($reportType != 'sellerSize' ){
                  unset( $resultData[$key]['h']);
                  unset( $resultData[$key]['w']);
            }
            if($reportType == 'sellerSize' ){
                   unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            }
            if($groupBy == 'reportDate') {
                unset($resultData[$key]['sellerid']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            } else if($groupBy == 'sellerId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['mediaid']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'mediaId') {
                unset($resultData[$key]['reportdate']);
                unset($resultData[$key]['placeid']);
            }else if($groupBy == 'placeId') {
                unset($resultData[$key]['reportdate']);
            }else if($groupBy == 'id') {
                if ($reportType == 'sellerMedia' || $reportType == 'sellerSummary') {
                    unset($resultData[$key]['placeid']);
                    if ($reportType == 'sellerSummary') {
                        unset($resultData[$key]['mediaid']);
                    }
                }
            } 
            
            
        }
     
        array_unshift($resultData, $total);
//        var_dump($resultData);die;
        exportExcel($resultData, $title, $excelName);
    }
    public function size() {
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
            $groupBy =  'id';
           
              
            $param = [
                'reportType' => 'sellerSize',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
                'page' => $page,
                'pageSize' => $rows,
            ];
             if(I('w')) {
                $param['w'] =I('w');  
            }
            if(I('h')) {
                $param['h'] =I('h');  
            }
            if (I('sidx')) {
                $order = I('sidx').' '.I('sord');
                if ($order) {
                    $param['orderBy'] = $order;
                }
            }
             
            
            $msg = $this->getList($param);
//            var_dump(json_encode($msg));die;
            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];
            
            $total = [
                'sellerid' => '-',
                'reportdate' => 'total',
                'w' => '-',
                'h' => '-',
                'view' => $totalArr['view'] ? $totalArr['view'] : 0,
                'request' => $totalArr['request'] ? $totalArr['request'] : 0,
                'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
                'response' => $totalArr['response'] ? $totalArr['response'] : 0,
                'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
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
               
               if ($groupBy != 'id') {
                   foreach ($data as $k => $v) {
                       $data[$k]['sellerid']='-';
                       $data[$k]['reportdate']='-';
                       
                   }
            }
         
//            var_dump($data);
            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum']+1;
            
//            var_dump(json_encode($result));die;
            $this->ajaxReturn($result);
        }

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 347);
       
           
                 $colNames = ['日期', '卖方','宽度','高度'];
                  $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
             ['name'=>'w', 'index'=>'w', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
            ['name'=>'h', 'index'=>'h', 'width'=>100, 'editable' => true, 'sorttype' => 'int'],
        ];
//            }
//       
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

        $startTime = date('Y-m-d');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
        $this->assign('seller', $seller);
        $this->assign('colNames', $colNames);
        $this->assign('colModel', $colModel);
        $this->display();
    }
     public function exportPlaceSizeReport() {
        $startTime = I('startTime') ? date('Y/m/d', strtotime(I('startTime'))) : '';
        $endTime = I('endTime') ? date('Y/m/d', strtotime(I('endTime'))) : '';
        if (!$startTime || !$endTime) {
            return false;
        }
        $sellerId = I('sellerId') ? I('sellerId') : '';
        $groupBy = 'id';

        $param = [
            'reportType' => 'sellerSize',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
        ];
         if(I('w')) {
                $param['w'] =I('w');  
            }
            if(I('h')) {
                $param['h'] =I('h');  
            }
        $this->exportReport($param, '广告位尺寸报表');
    }
}
