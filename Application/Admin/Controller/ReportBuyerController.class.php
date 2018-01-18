<?php

namespace Admin\Controller;
use Admin\Model\Report\ReportCommonModel;
use Think\Controller;
use Org\Util\Rbac;
class ReportBuyerController extends BaseController {

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
            $buyerId = I('buyerId');
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'buyerSummary',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'buyerId' => $buyerId,
                'page' => $page,
                'pageSize' => $rows,
            ];
            $msg = $this->getList($param);

            $data = $msg['data']['list'];
            $totalArr = $msg['data']['total'];

            $total = [
                'buyerid' => 'total',
                'reportdate' => 'total',
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

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 85);
        $colNames = ['日期', '买方'];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sortable' => false],
            ['name'=>'buyerid', 'index'=>'buyerid', 'width'=>100, 'editable' => true, 'sortable' => false],
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

        $buyer = D('Buyer')->field('id, company')->order('status asc')->select();

        $startTime = date('Y-m-d');
//        $startTime = date('Y-m-01');
        $endTime = date('Y-m-d');
        $this->assign('endTime', $endTime);
        $this->assign('startTime', $startTime);
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
        $buyerId = I('buyerId') ? I('buyerId') : '';
        $groupBy = I('groupBy') ? I('groupBy') : 'id';

        $param = [
            'reportType' => 'buyerSummary',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'buyerId' => $buyerId,
        ];
        $this->exportReport($param, '买方总表');
    }

    public function getList($param = []) {
        $obj = new ReportCommonModel($param);
        $msg=$obj->getData();
        return $msg;
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

        if (isset($search['buyerId'])) {
            $param['buyerId'] = $search['buyerId'];
        }

        $parentIdArr = [
            'buyerSummary' => 85,
        ];
        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], $parentIdArr[$reportType]);

        $msg = $this->getList($param);
        $totalArr = $msg['data']['total'];
        $data = $msg['data']['list'];

        $title = ['reportdate' => '日期', 'buyerid' => '买方'];

        $total = [
            'reportdate' => 'total',
            'buyerid' => 'total',
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

        if($groupBy == 'reportDate') {
            unset($total['buyerid']);
            unset($title['buyerid']);
        }else if($groupBy == 'buyerId') {
            unset($total['reportdate']);
            unset($title['reportdate']);
        }
        $resultData = [];
        foreach ($data as $key => $value) {
            $resultData[$key]['reportdate'] = isset($value['reportdate']) ? $value['reportdate'] : '';
            $resultData[$key]['buyerid'] = isset($value['buyerid']) ? $value['buyerid'] : '';

            foreach ($fieldArr as $k => $v) {
                if ($k == 'bid') {
                    $resultData[$key]['bid'] = isset($value['bid']) ? $value['bid'] : '';
                }else {
                    $resultData[$key][$k] = isset($value[$k]) ? $value[$k] : 0;
                }
            }

            if($groupBy == 'reportDate') {
                unset($resultData[$key]['buyerid']);
            } else if($groupBy == 'buyerId') {
                unset($resultData[$key]['reportdate']);
            }
        }
        array_unshift($resultData, $total);

        exportExcel($resultData, $title, $excelName);
    }
}
