<?php

namespace V1\Controller;

class AdvertiserController extends CommonController {

    public function add() {
        $Advertiser = D('Advertiser');
        $request = $this->request;
        foreach ($request['request'] as $k => $v) {
            if(!is_array($v)){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1002';
                $this->response['errors'][$k]['message'] = 'format error';
                continue;
            }
            if (!is_numeric($v['advertiserId'])){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1001';
                $this->response['errors'][$k]['message'] = '1001';
                continue;
            }
            if (!isset($v['advertiserName'])){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1001';
                $this->response['errors'][$k]['message'] = '1001';
                continue;
            }
            if (strlen($v['companyName']) > 100 || strlen($v['companyAdd']) > 100 || strlen($v['companyPostcode']) > 100 || strlen($v['companyFax']) > 100 || strlen($v['companyTel']) > 100 || strlen($v['websiteName']) > 100 || strlen($v['website']) > 100 || strlen($v['file']) > 100){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1002';
                $this->response['errors'][$k]['message'] = ''.json_encode($v);
                continue;
            }
            $data=array();
            $data['idBuyer'] = $this->dspId;
            $data['idBuyerAdvertiser'] = $v['advertiserId'];
            $data['name'] = $v['advertiserName'];
            if($v['companyAdd']!='')$data['address'] = $v['companyAdd'];
            if($v['companyPostcode']!='')$data['zip'] = $v['companyPostcode'];
            if($v['companyTel']!='')$data['tel'] = $v['companyTel'];
            if($v['websiteName']!='')$data['siteName'] = $v['websiteName'];
            if($v['website']!='')$data['domain'] = $v['website'];
//            if($v['file']!='')$data['file'] = $v['file'];
            $data['status'] = '1';
            $data['cuid'] = $this->dspId;
            $data['ctime'] = date("Y-m-d H:i:s", time());

            // 判断是否有重复
            $result = $Advertiser->where(array("idBuyer"=>$this->dspId,"idBuyerAdvertiser"=>$v['advertiserId']))->select();

            if ($result){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1004';
                $this->response['errors'][$k]['message'] = 'chongfu';
                continue;
            }

            $result = $Advertiser->add($data);
            // 新增广告主正确
            if ($result) {
                $is_success = true;
                $this->response['Advertiser'][] = $v['advertiserId'];
                // 记录日志
//                $msg=array();
//                $msg['uid'] = $this->dspId;
//                $msg['object'] = "advertiser";
//                $msg['objid'] = $result;
//                $msg['type'] = 'add';
//                $msg['ip'] = $_SERVER["REMOTE_ADDR"];
//                $msg['content'] = json_encode($data);
//                $this->History($msg);

                // 广告主录入失败
            } else {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1004';
                $this->response['errors'][$k]['message'] = '';
            }
        }
        // 如果没有成功的 则是全部失败
        if ($is_success !== true) {
            $this->response['status'] = 2;
        }
        // 如果没有失败的 则是全部成功
        if ($is_failed !== true) {
            $this->response['status'] = 0;
        }
        // 如果有成功的 有失败的，则是部分失败
        if ($is_success === true and $is_failed === true) {
            $this->response['status'] = 1;
        }
        $this->stop();
    }

    public function update() {
        // 更新记录
        $Advertiser = D('Advertiser');
        $request = $this->request;
        foreach ($request['request'] as $k => $v) {
            if(!is_array($v)){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1002';
                $this->response['errors'][$k]['message'] = 'format error';
                continue;
            }
            if (!is_numeric($v['advertiserId'])){
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1001';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }

            $data['idBuyerAdvertiser'] = $v['advertiserId'];
            $data['idBueyr'] = $this->dspId;


            if($v['advertiserName']!='')$data['name'] = $v['advertiserName'];
            if($v['companyAdd']!='')$data['address'] = $v['companyAdd'];
            if($v['companyPostcode']!='')$data['zip'] = $v['companyPostcode'];
            if($v['companyTel']!='')$data['tel'] = $v['companyTel'];
            if($v['websiteName']!='')$data['siteName'] = $v['websiteName'];
            if($v['website']!='')$data['domain'] = $v['website'];

            $result = $Advertiser->where($data)->find();
            $data['mtime'] = date("Y-m-d H:i:s", time());
            if(!$result){
                $data['status'] = 1;
                $data['muid'] = $this->dspId;
                $data['remark'] = '';
                $condition['idBuyer'] = $this->dspId;
                $condition['idBuyerAdvertiser'] = $v['advertiserId'];
                $result = $Advertiser->where($condition)->find();

                if (is_array($result)) {
                    $result = $Advertiser->where($condition)->save($data);
                    // 更新广告主正确
                    if ($result !== false) {
                        $is_success = true;
                        $this->response['Advertiser'][] = $v['advertiserId'];
                    } else {
                        // 更新广告主失败
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = '1004';
                        $this->response['errors'][$k]['message'] = '';
                    }
                }else{
                    // 没找到指定id的数据
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '1001';
                    $this->response['errors'][$k]['message'] = '1001-2';
                }

            }else{
                //广告素材内容不变化
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1004';
                $this->response['errors'][$k]['message'] = 'advertiser exist';
            }
        }
        // 如果没有成功的 则是全部失败
        if ($is_success !== true) {
            $this->response['status'] = 2;
        }
        // 如果没有失败的 则是全部成功
        if ($is_failed !== true) {
            $this->response['status'] = 0;
        }
        // 如果有成功的 有失败的，则是部分失败
        if ($is_success === true and $is_failed === true) {
            $this->response['status'] = 1;
        }
        $this->stop();
    }

    public function get() {
        // 获取id对应的审核状态和信息
        $Advertiser = D('Advertiser');
        $request = $this->request;
        if (!isset($request['advertiserIds']) || empty($request['advertiserIds'])) {
            $this->response['errors']['code'] = '1001';
            $this->response['errors']['message'] = '1001';
            $this->stop();
        }
        $str_advertiserIds = implode(',', $request['advertiserIds']);
        $result = $Advertiser->where(" idBuyer='" . $this->dspId . "' and idBuyerAdvertiser in (" . $str_advertiserIds . ")")->select();
        $this->response['Advertiser'] = array();
        foreach ($result as $k => $v) {
            $status = $this->getNewStatus($v['status']);
            $arr = array('advertiserId' => $v['idbuyeradvertiser'],
                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['Advertiser'][] = $arr;
        }
        $this->stop();
    }

    public function getall() {
        // 获取指定时间内创建的数据
        $Advertiser = D('Advertiser');
        $request = $this->request;
        $result = $Advertiser->where(" idBuyer='" . $this->dspId . "' and ctime >='" . $request['startDate'] . "' and ctime <= '" . date("Y-m-d", strtotime($request['endDate']) + 86400) . "' ")->count();
        if ($result > 100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '202';
            $this->response['errors']['message'] = '';
            $this->stop();
        }

        $result = $Advertiser->where(" idBuyer='" . $this->dspId . "' and ctime >='" . $request['startDate'] . "' and ctime <= '" . date("Y-m-d", strtotime($request['endDate']) + 86400) . "' ")->select();
        $this->response['Advertiser'] = array();
        foreach ($result as $k => $v) {

            $status = $this->getNewStatus($v['status']);
            $arr = array('advertiserId' => $v['idbuyeradvertiser'],
                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['Advertiser'][] = $arr;
        }
        $this->stop();
    }

    public function queryQualification() {
        // 获取id对应的审核状态和信息
        $Advertiser = D('Advertiser');
        $request = $this->request;
        $str_advertiserIds = implode(',', $request['advertiserIds']);
        $result = $Advertiser->where(" idBuyer='" . $this->dspId . "' and idBuyerAdvertiser in (" . $str_advertiserIds . ")")->count();
        if ($result > 100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '202';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        $result = $Advertiser->where(" idBuyer='" . $this->dspId . "' and idBuyerAdvertiser in (" . $str_advertiserIds . ")")->select();
        foreach ($result as $k => $v) {

            $status = $this->getNewStatus($v['status']);
            $arr = array('advertiserId' => $v['idbuyeradvertiser'],
                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['AdvertiserQualification'][] = $arr;
        }
        $this->stop();
    }

    public function getNewStatus($oldStatus) {
        switch ($oldStatus) {
            case 1:
                $status = 1;
                break;
            case 2:
                $status = 0;
                break;
            case 3:
                $status = 2;
                break;
            default:
                $status = $oldStatus;
        }

        return $status;
    }
}
