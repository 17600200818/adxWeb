<?php

namespace Seller\Controller;
use Seller\Model\Report\ReportCommonModel;
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
                $startTime = date('Y/m/1');
            }else {
                $startTime = date('Y/m/1');
                $endTime = date('Y/m/d');
            }
            $sellerId = $_SESSION["userInfo"]['id'];
            $groupBy = I('groupBy') ? I('groupBy') : 'id';

            $param = [
                'reportType' => 'sellerSummary',
                'reportDate' => $startTime.'-'.$endTime,
                'groupBy' => $groupBy,
                'sellerId' => $sellerId,
                'page' => $page,
                'pageSize' => $rows,
            ];
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
                'cpm' => $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : 0,
                'click' => $totalArr['click'] ? $totalArr['click'] : 0,
                'cpc' => $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : 0,
                'spend' => $totalArr['spend'] ? number_format($totalArr['spend'], 2, '.', ',') : 0,
                'sellerplay' => $totalArr['sellerplay'] ? $totalArr['sellerplay'] : 0,
                'sellercpm' => $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : 0,
                'sellerclick' => $totalArr['sellerclick'] ? $totalArr['sellerclick'] : 0,
                'sellercpc' => $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : 0,
                'sellerspend' => $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : 0,
                'buyerspend' => $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : 0,
                'clickrate' =>(sprintf("%.4f",($totalArr['sellerplay'] ? $totalArr['sellerclick']/$totalArr['sellerplay'] : '-'))*100).'%',
            ];
            if ($groupBy != 'sellerId') {
                $total['sellerid'] = '-';
            }
            foreach ($data as $k=>$v){
                    $data[$k]['clickrate']=(sprintf("%.4f",($data[$k]['sellerplay']?$data[$k]['sellerclick']/$data[$k]['sellerplay'] : '-'))*100).'%';
                }

            array_unshift($data, $total);

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = $msg['maxPage'];
            $result['records'] = $msg['totalNum'];
            
            $this->ajaxReturn($result);
        }

        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], 247);
        $colNames = ['日期'
//            , '卖方'
            ];
        $colModel = [
            ['name'=>'reportdate', 'index'=>'reportdate', 'width'=>100, 'editable' => true, 'sortable' => false],
//            ['name'=>'sellerid', 'index'=>'sellerid', 'width'=>100, 'editable' => true, 'sortable' => false],
        ];
        foreach ($fields as $k => $v){
            $colNames[] = $v['name'];
            $colModel[] = ['name'=>$v['remark'], 'index'=>$v['remark'], 'width'=>100, 'editable' => true, 'sortable' => false];
        }
        $colNames[]='点击率';
        $colModel[]=['name'=>'clickrate', 'index'=>'clickrate', 'width'=>100, 'editable' => true, 'sortable' => false];
        $colNames = json_encode($colNames, true);
        $colModel = json_encode($colModel, true);
        
        $seller = D('Seller')->field('id, company')->where('parentId = 0')->select();

        $startTime = date('Y-m-01');
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

   
            $param['sellerId'] =$_SESSION["userInfo"]['id'];
        
        if (isset($search['mediaId'])) {
            $param['mediaId'] = $search['mediaId'];
        }
        if (isset($search['placeId'])) {
            $param['placeId'] = $search['placeId'];
        }

        $parentIdArr = [
            'sellerSummary' => 247,
            
        ];
        $fields = D('PowerItem')->getShowField($_SESSION['userInfo']['idrole'], $parentIdArr[$reportType]);

        $msg = $this->getList($param);
        $totalArr = $msg['data']['total'];
        $data = $msg['data']['list'];

        $title = ['reportdate' => '日期', 'sellerid' => '卖方', 'mediaid' => '媒体', 'placeid' => '广告位'];

        $total = [
            'reportdate' => 'total',
            'sellerid' => 'total',
            'mediaid' => 'total',
            'placeid' => 'total',
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

//        $title = ['reportdate' => '日期', 'sellerid' => '卖方', 'mediaid' => '媒体', 'placeid' => '广告位',  '媒体请求数', '转发请求数', '转发成功数', '回复数', '参与竞价数', '曝光数', 'cpm', '点击数', 'cpc', '花费', '卖方曝光数', '卖方cpm', '卖方点击数', '卖方cpc', '卖方花费', '买方花费'];
//
//        $total = [
//            'reportdate' => 'total',
//            'sellerid' => 'total',
//            'mediaid' => 'total',
//            'placeid' => 'total',
//            'view' => $totalArr['view'] ? $totalArr['view'] : 0,
//            'request' => $totalArr['request'] ? $totalArr['request'] : 0,
//            'requestok' => $totalArr['requestok'] ? $totalArr['requestok'] : 0,
//            'response' => $totalArr['response'] ? $totalArr['response'] : 0,
//            'bid' => $totalArr['bid'] ? $totalArr['bid'] : 0,
//            'play' => $totalArr['play'] ? $totalArr['play'] : 0,
//            'cpm' => $totalArr['play'] ? number_format(($totalArr['spend'] / ($totalArr['play'] / 1000)), 2, '.', ',') : 0,
//            'click' => $totalArr['click'] ? $totalArr['click'] : 0,
//            'cpc' => $totalArr['click'] ? number_format(($totalArr['spend'] / $totalArr['click']), 2, '.', ',') : 0,
//            'spend' => $totalArr['spend'] ? number_format(($totalArr['spend']), 2, '.', ',') : 0,
//            'sellerplay' => $totalArr['sellerplay'] ? $totalArr['sellerplay'] : 0,
//            'sellercpm' => $totalArr['sellerplay'] ? number_format(($totalArr['sellerspend'] / ($totalArr['sellerplay'] / 1000)), 2, '.', ',') : 0,
//            'sellerclick' => $totalArr['sellerclick'] ? $totalArr['sellerclick'] : 0,
//            'sellercpc' => $totalArr['sellerclick'] ? number_format(($totalArr['sellerspend'] / $totalArr['sellerclick']), 2, '.', ',') : 0,
//            'sellerspend' => $totalArr['sellerspend'] ? number_format($totalArr['sellerspend'], 2, '.', ',') : 0,
//            'buyerspend' => $totalArr['buyerspend'] ? number_format($totalArr['buyerspend'], 2, '.', ',') : 0,
//        ];

        if ($reportType == 'sellerSummary') {
            unset($title['mediaid']);
            unset($total['mediaid']);
            unset($title['placeid']);
            unset($total['placeid']);
        }elseif ($reportType == 'sellerMedia') {
            unset($title['placeid']);
            unset($total['placeid']);
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
        $resultData = [];
        $arr = ['reportdate', 'sellerid', 'mediaid', 'placeid'];
        foreach ($data as $key => $value) {
            $resultData[$key]['reportdate'] = isset($value['reportdate']) ? $value['reportdate'] : '';
            $resultData[$key]['sellerid'] = isset($value['sellerid']) ? $value['sellerid'] : '';
            $resultData[$key]['mediaid'] = isset($value['mediaid']) ? $value['mediaid'] : '';
            $resultData[$key]['placeid'] = isset($value['placeid']) ? $value['placeid'] : '';

            foreach ($fieldArr as $k => $v) {
                if ($k == 'bid') {
                    $resultData[$key]['bid'] = isset($value['bid']) ? $value['bid'] : '';
                }else {
                    $resultData[$key][$k] = isset($value[$k]) ? $value[$k] : 0;
                }
            }

//            if(isset($fieldArr['view']))
//                $resultData[$key]['view'] = isset($value['view']) ? $value['view'] : 0;
//            if(isset($fieldArr['request']))
//                $resultData[$key]['request'] = isset($value['request']) ? $value['request'] : 0;
//            if(isset($fieldArr['requestok']))
//                $resultData[$key]['requestok'] = isset($value['requestok']) ? $value['requestok'] : 0;
//            if(isset($fieldArr['response']))
//                $resultData[$key]['response'] = isset($value['response']) ? $value['response'] : 0;
//            if(isset($fieldArr['bid']))
//                $resultData[$key]['bid'] = isset($value['bid']) ? $value['bid'] : '';
//            if(isset($fieldArr['play']))
//                $resultData[$key]['play'] = isset($value['play']) ? $value['play'] : 0;
//            if(isset($fieldArr['cpm']))
//                $resultData[$key]['cpm'] = isset($value['cpm']) ? $value['cpm'] : '0.00';
//            if(isset($fieldArr['click']))
//                $resultData[$key]['click'] = isset($value['click']) ? $value['click'] : 0;
//            if(isset($fieldArr['cpc']))
//                $resultData[$key]['cpc'] = isset($value['cpc']) ? $value['cpc'] : '0.00';
//            if(isset($fieldArr['spend']))
//                $resultData[$key]['spend'] = isset($value['spend']) ? $value['spend'] : '0.00';
//            if(isset($fieldArr['sellerplay']))
//                $resultData[$key]['sellerplay'] = isset($value['sellerplay']) ? $value['sellerplay'] : 0;
//            if(isset($fieldArr['sellercpm']))
//                $resultData[$key]['sellercpm'] = isset($value['sellercpm']) ? $value['sellercpm'] : '0.00';
//            if(isset($fieldArr['sellerclick']))
//                $resultData[$key]['sellerclick'] = isset($value['sellerclick']) ? $value['sellerclick'] : 0;
//            if(isset($fieldArr['sellercpc']))
//                $resultData[$key]['sellercpc'] = isset($value['sellercpc']) ? $value['sellercpc'] : '0.00';
//            if(isset($fieldArr['sellerspend']))
//                $resultData[$key]['sellerspend'] = isset($value['sellerspend']) ? $value['sellerspend'] : '0.00';
//            if(isset($fieldArr['buyerspend']))
//                $resultData[$key]['buyerspend'] = isset($value['buyerspend']) ? $value['buyerspend'] : '0.00';
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

        exportExcel($resultData, $title, $excelName);
    }
}
