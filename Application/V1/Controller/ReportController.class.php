<?php
namespace V1\Controller;
use Admin\Model\Report\ReportCommonModel;

class ReportController extends CommonController {

    public function consume(){
    	$this->datelimit();
    	$msg = $this->summary();
        $this->response['status'] = '0';
        $this->response['response'] = $msg['data'];
        $this->stop();
    }

    public function datelimit(){
        $request=$this->request;
        $str_startDate = date_create($this->request['startDate']);
        $str_endDate = date_create($this->request['endDate']);
        $interval = date_diff($str_startDate, $str_endDate);
        if ($interval->format('%R')!="+"){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '200';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if ($interval->format('%a') > 10){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '200';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        return true;
    }

    // DSP 财务数据
    public function summary() {
        $page = $_POST['page'] != '' ? $_POST['page'] : 1;
        $rows = $_POST['rows'] != '' ? $_POST['rows']-1 : 999999;
        $startTime = date('Y/m/d', strtotime($this->request['startDate']));
        $endTime = date('Y/m/d', strtotime($this->request['endDate']));

        $sellerId = $this->dspId;
        $groupBy = 'reportDate';

        $param = [
            'reportType' => 'sellerSummary',
            'reportDate' => $startTime.'-'.$endTime,
            'groupBy' => $groupBy,
            'sellerId' => $sellerId,
            'page' => $page,
            'pageSize' => $rows,
        ];
        $msg = $this->getList($param);

        $data = array();

        foreach ($msg['data']['list'] as $v) {
            $arr = [
                'showDate' => $v['reportdate'],
                'srchs' => $v['play'],
                'clks' => $v['click'],
                'cost' => $v['spend'],
                'ctr' => $v['click']/$v['play'],
                'acp' => $v['cpc'],
                'cpm' => $v['cpm'],
            ];
            $data['data'][] = $arr;
        }

        return $data;
    }

    public function getList($param = []) {
        $obj = new ReportCommonModel($param);
        $msg=$obj->getData();
        return $msg;
    }
}