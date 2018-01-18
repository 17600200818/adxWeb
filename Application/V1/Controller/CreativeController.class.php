<?php
namespace V1\Controller;
class CreativeController extends CommonController
{

    public function add()
    {
        // 新增记录
        $Creative = D('Creative');
        $request = $this->request;
        foreach ($request['request'] as $k => $v) {
            $is_allow = false;
            if (!is_array($v)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1002';
                $this->response['errors'][$k]['message'] = 'format error';
                continue;
            }
            // 基本验证  monitorUrls
            if (count($v['monitorUrls']) > 3 || count($v['monitorUrls']) < 1) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2005';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['targetUrl'] == "" && empty($v['ClickTracking'])) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2006';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if (count($v['ClickTracking']) > 3) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2005';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['end_date'] == '') {
                $v['end_date'] = date("Y-m-d", (time() + 86400 * 90));
            } else {
                $v['end_date'] = $v['end_date'];
            }
            if (strtotime($v['end_date']) > (time() + 86400 * 180) || date('Y-m-d', strtotime($v['end_date'])) != $v['end_date'] || strtotime($v['end_date']) < (time() + 86400 * 7)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2028';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }

            $v['targetUrl'] = $this->fixUrls($v['targetUrl']);
            $v['landingPage'] = $this->fixUrls($v['landingPage']);
            $v['monitorUrls'] = $this->fixUrls($v['monitorUrls']);
            $v['ClickTracking'] = $this->fixUrls($v['ClickTracking']);
            //var_dump($v['ClickTracking']);
            if ($v['fileExtName'] == '') {
                $v['fileExtName'] = $this->getExt($v['creativeUrl']);
            } else {
                $v['fileExtName'] = $v['fileExtName'];
            }
            if ($this->clickUrls($v['targetUrl']) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->clickUrls($v['targetUrl']);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($this->checkUrls($v['landingPage'], 2048) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->checkUrls($v['landingPage'], 2048);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($this->clickUrls($v['ClickTracking']) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->clickUrls($v['ClickTracking']);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($this->checkUrls($v['monitorUrls']) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->checkUrls($v['monitorUrls']);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }

            $str_monitorUrls = json_encode($v['monitorUrls']);
            if (strpos($str_monitorUrls, "%%WINPRICE%%") > 0) {
            } else {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = "2009";
                $this->response['errors'][$k]['message'] = 'WINPRICE error ';
                continue;
            }

            // 这里就需要区分开，动态创意和静态创意
            if (!isset($v['Html']) || strlen($v['Html']) <= 10) {
                // 静态广告
                if ($v['fileExtName']) {
                    $str_fileExtName = $v['fileExtName'];
                } else {
                    $str_fileExtName = $this->getExt($v['creativeUrl']);
                }
                $fileExtArr = array_flip(C('FILE_EXT'));
                if (isset($fileExtArr[$str_fileExtName])) {
                    $str_fileType = $fileExtArr[$str_fileExtName];
                    $is_allow = true;
                }
//                if ($str_fileExtName == "jpg") {
//                    $str_fileType = "1";
//                    $is_allow = true;
//                }
//                if ($str_fileExtName == "gif") {
//                    $str_fileType = "1";
//                    $is_allow = true;
//                }
//                if ($str_fileExtName == "png") {
//                    $str_fileType = "1";
//                    $is_allow = true;
//                }
//                if ($str_fileExtName == "swf") {
//                    $str_fileType = "2";
//                    $is_allow = true;
//                }
//                if ($str_fileExtName == "flv") {
//                    $str_fileType = "4";
//                    $is_allow = true;
//                }
//                if ($str_fileExtName == "mp4") {
//                    $str_fileType = "4";
//                    $is_allow = true;
//                }
                if ($is_allow !== true) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '2000';
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }
                if (!is_numeric($v['creativeId'])) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '2002';
                    $this->response['errors'][$k]['message'] = 'creative id is not numberic';
                    continue;
                }
                if ($this->arr_tradeId[$v['creativeTradeId']] != 1) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '2003';
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }

                if ($v['width'] == "" && $v['height'] == "") {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '2004';
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }

                $data['filePath'] = $this->saveFile($v['creativeUrl']);
                if (is_numeric($data['filePath'])) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = $data['filePath'];
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }

                if ($this->checkUrls($v['creativeUrl']) !== true) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = $this->checkUrls($v['creativeUrl']);
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }
                $data['url'] = $v['creativeUrl'];
                $data['fileExt'] = $str_fileType;
                $data['fileSize'] = ceil(filesize($data['filePath']) / 1024);
                if (is_numeric($v['duration'])) $data['duration'] = $v['duration'];

            } else {
                // HTML格式广告
                $data['adCode'] = $v['Html'];
                $data['md5Id'] = md5($this->dspId . "_" . $v['creativeId']);
                $data['fileSize'] = ceil(strlen($v['Html']) / 1024);
                $data['fileExt'] = 7;
                if (isset($v['creativeUrl'])) {
                    $data['filePath'] = $this->saveFile($v['creativeUrl']);
                    $data['url'] = $v['creativeUrl'];
                }else {
                    $data['url'] = 'http://gts.rtbs.cn/Public/images/common/logo.png';
                }
            }
            // 验证广告主是否存在并已经通过审核
            $Advertiser = D('Advertiser');
            $result = $Advertiser->where(array("idBuyerAdvertiser" => $v['advertiserId'], "idBuyer" => $this->dspId, "status" => 2))->find();
            if (!$result) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1003';
                $this->response['errors'][$k]['message'] = 'Advertiser is Not Allow';
                continue;
            }

            $data['buyerCrid'] = $v['creativeId'];
            $data['clickUrl'] = $v['targetUrl'];

            $data['loadingPage'] = $v['landingPage'];
            $data['actionType'] = $v['interactiontype'];
            if ($data['interactiontype'] == "") $data['actionType'] = 2;

            $data['idBuyer'] = $this->dspId;
            $data['md5Id'] = md5($this->dspId . "_" . $v['creativeId']);
            $data['imptrackers'] = json_encode($v['monitorUrls']);
            if ($v['ClickTracking']) $data['clktrackers'] = json_encode($v['ClickTracking']);

            $data['height'] = $v['height'];
            $data['width'] = $v['width'];
            $data['category2'] = $v['creativeTradeId'];
            if ($data['category2']) {
                $data['category1'] = D('SysIndustryCategory')->getC1($data['category2']);
            }
            $data['buyerAdvertiserId'] = $v['advertiserId'];

            $data['expirationDate'] = $v['end_date'];

            $data['status'] = 1;
            $data['cuid'] = $this->dspId;
            $data['ctime'] = date("Y-m-d H:i:s", time());
            $data['advertiserId'] = D('Advertiser')->getId($this->dspId, $v['advertiserId']);

            $condition['idBuyer'] = $this->dspId;
            $condition['buyerCrid'] = $v['creativeId'];
            $result = $Creative->where("`buyerCrid` = '".$condition['buyerCrid']."' and `idBuyer` = ".$condition['idBuyer'])->find();
            if (is_array($result)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2002';
                $this->response['errors'][$k]['message'] = 'creative is exist';
                continue;
            }
            $result = $Creative->add($data);

            // 新增广告主正确
            if ($result) {
                $is_success = true;
                $this->response['Creative'][] = $v['creativeId'];
                // 记录日志
//                $msg['uid'] = $this->dspId;
//                $msg['object'] = "creative";
//                $msg['objid'] = $result;
//                $msg['type'] = 'add';
//                $msg['ip'] = $_SERVER["REMOTE_ADDR"];
//                $msg['content'] = json_encode($data);
//                $this->History($msg);

                // 创意录入失败
            } else {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1004' . $result;
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

    public function update()
    {
        // 更新记录
        $Creative = D('Creative');
        $request = $this->request;
        foreach ($request['request'] as $k => $v) {
            if (!is_array($v)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1002';
                $this->response['errors'][$k]['message'] = 'format error';
                continue;
            }
            if (!is_numeric($v['creativeId'])) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2002';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['creativeTradeId'] && $this->arr_tradeId[$v['creativeTradeId']] != 1) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2003';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['monitorUrls'] && (count($v['monitorUrls']) > 3 || count($v['monitorUrls']) < 1)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2005';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['ClickTracking '] && (count($v['ClickTracking']) > 3)) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '2005';
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            $v['targetUrl'] = $this->fixUrls($v['targetUrl']);
            $v['landingPage'] = $this->fixUrls($v['landingPage']);
            $v['monitorUrls'] = $this->fixUrls($v['monitorUrls']);
            $v['ClickTracking'] = $this->fixUrls($v['ClickTracking']);
            if ($v['targetUrl'] && $this->checkUrls($v['targetUrl']) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->checkUrls($v['targetUrl']);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['landingPage'] && $this->checkUrls($v['landingPage'], 2048) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->checkUrls($v['landingPage'], 2048);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['clickTracking'] && $this->clickUrls($v['clickTracking'], 2048) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->clickUrls($v['clickTracking'], 2048);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['monitorUrls'] && $this->checkUrls($v['monitorUrls']) !== true) {
                $is_failed = true;
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = $this->checkUrls($v['monitorUrls']);
                $this->response['errors'][$k]['message'] = '';
                continue;
            }
            if ($v['end_date']) {
                if (strtotime($v['end_date']) > (time() + 86400 * 180) || date('Y-m-d', strtotime($v['end_date'])) != $v['end_date'] || strtotime($v['end_date']) < (time() + 86400 * 7)) {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '2028';
                    $this->response['errors'][$k]['message'] = '';
                    continue;
                }
            }

            if ($v['monitorUrls']) {
                $str_monitorUrls = json_encode($v['monitorUrls']);
                if (strpos($str_monitorUrls, "%%WINPRICE%%") > 0) {

                } else {
                    $is_failed = true;
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = "2009";
                    $this->response['errors'][$k]['message'] = 'WINPRICE error';
                    continue;
                }
            }
            if (!isset($v['Html']) || strlen($v['Html']) <= 10) {
                // 静态创意修改
                if (strlen($v['creativeUrl']) > 7) {
//                    $data['type'] = 1;
                    $is_allow = false;
                    if ($v['fileExName'] == "") {
                        $str_fileExtName = $this->getExt($v['creativeUrl']);
                    } else {
                        $str_fileExtName = $this->$v['fileExName'];
                    }
                    if ($str_fileExtName == "jpg") {
                        $str_fileType = "1";
                        $is_allow = true;
                    }
                    if ($str_fileExtName == "gif") {
                        $str_fileType = "1";
                        $is_allow = true;
                    }
                    if ($str_fileExtName == "png") {
                        $str_fileType = "1";
                        $is_allow = true;
                    }
                    if ($str_fileExtName == "swf") {
                        $str_fileType = "2";
                        $is_allow = true;
                    }
                    if ($str_fileExtName == "flv") {
                        $str_fileType = "4";
                        $is_allow = true;
                    }
                    if ($str_fileExtName == "mp4") {
                        $str_fileType = "4";
                        $is_allow = true;
                    }
                    if ($is_allow !== true) {
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = '2000';
                        $this->response['errors'][$k]['message'] = '';
                        continue;
                    }
                    /*
                    if ($this->arr_size[$v['width']."x".$v['height']] != 1){
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = '2004';
                        $this->response['errors'][$k]['message'] = '';
                        continue;
                    }*/
                    $data['filePath'] = $this->saveFile($v['creativeUrl']);
                    if (is_numeric($data['filePath'])) {
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = $data['filePath'];
                        $this->response['errors'][$k]['message'] = '';
                        continue;
                    }
                    /*
                    if (in_array($str_fileExtName,array("png","gif","jpg"))){
                        $imginfo = getimagesize($data['fileUrl']);
                        if ($imginfo[0]."x".$imginfo[1] != $v['width']."x".$v['height']){
                            $is_failed = true;
                            $this->response['errors'][$k]['index'] = $k;
                            $this->response['errors'][$k]['code'] = "2017";
                            $this->response['errors'][$k]['message'] = '';
                            continue;
                        }
                    }*/
                    if ($this->checkUrls($v['creativeUrl']) !== true) {
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = $this->checkUrls($v['creativeUrl']);
                        $this->response['errors'][$k]['message'] = '';
                        continue;
                    }
                }
                // 赋值
                if ($v['creativeUrl']) $data['url'] = $v['creativeUrl'];
                if ($data['filePath']) $data['fileSize'] = ceil(filesize($data['filePath']) / 1024);

            } else {
                // 动态创意修改
                // 赋值
                if ($v['Html']) $data['adCode'] = $v['Html'];
                $data['fileExt'] = 7;
                if (isset($v['creativeUrl'])) {
                    $data['filePath'] = $this->saveFile($v['creativeUrl']);
                    $data['url'] = $v['creativeUrl'];
                }else {
                    $data['url'] = 'http://gts.rtbs.cn/Public/images/common/logo.png';
                }
            }


            $data['buyerCrid'] = $v['creativeId'];

            if ($v['targetUrl']) $data['clickUrl'] = $v['targetUrl'];
            if ($v['landingPage']) $data['loadingPage'] = $v['landingPage'];
            if ($v['end_date']) $data['expirationDate'] = $v['end_date'];
            if ($v['interactiontype']) {
                $data['actionType'] = $v['interactiontype'];
            }else {
                $data['actionType'] = 2;
            }
            if ($v['creativeUrl']) $data['fileExt'] = $str_fileType;

            if ($v['monitorUrls']) $data['imptrackers'] = json_encode($v['monitorUrls']);
            if ($v['ClickTracking']) $data['clktrackers'] = json_encode($v['ClickTracking']);
            if ($v['height']) $data['height'] = $v['height'];
            if ($v['width']) $data['width'] = $v['width'];
            if ($v['creativeTradeId']) {
                $data['category2'] = $v['creativeTradeId'];
                $data['category1'] = D('SysIndustryCategory')->getC1($data['category2']);
            }

            if ($v['duration']) $data['duration'] = $v['duration'];
            $fileUrl = $data['filePath'];
            unset($data['filePath']);
            $result = $Creative->where($data)->find();
            if (!$result) {
                if ($fileUrl) {
                    $data['filePath'] = $fileUrl;
                }
                $data['muid'] = $this->dspId;
                $data['mtime'] = date("Y-m-d H:i:s", time());
                $data['status'] = 1;
                $data['remark'] = '';
                $condition['idBuyer'] = $this->dspId;
                $condition['buyerCrid'] = $v['creativeId'];
                $result = $Creative->where($condition)->find();
                if (is_array($result)) {
                    $id = $result['id'];
                    $result = $Creative->where($condition)->save($data);
                    if ($result !== false) {
                        $editCreativeAudit = D('CreativeAudit')->where("crid = $id and status != 1")->save(['status' => 6]);
                    }
                    // 更新广告主正确
                    if ($result !== false) {
                        $is_success = true;
                        $this->response['Creative'][] = $v['creativeId'];

                        // 记录日志
//                        $result = $Creative->where($condition)->find();
//                        $msg['uid'] = $this->dspId;
//                        $msg['object'] = "creative";
//                        $msg['objid'] = $id;
//                        $msg['type'] = 'update';
//                        $msg['ip'] = $_SERVER["REMOTE_ADDR"];
//                        $msg['content'] = json_encode($data);
//                        $this->History($msg);

                        // 更新广告主失败
                    } else {
                        $is_failed = true;
                        $this->response['errors'][$k]['index'] = $k;
                        $this->response['errors'][$k]['code'] = '1004';
                        $this->response['errors'][$k]['message'] = '';
                    }

                } else {
                    // 没找到指定id的数据
                    $this->response['errors'][$k]['index'] = $k;
                    $this->response['errors'][$k]['code'] = '1001';
                    $this->response['errors'][$k]['message'] = '';
                }
            } else {
                $this->response['errors'][$k]['index'] = $k;
                $this->response['errors'][$k]['code'] = '1003';
                $this->response['errors'][$k]['message'] = 'creative exist';
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

    public function get()
    {
        // 获取id对应的审核状态和信息
        $Creative = D('Creative');
        $request = $this->request;
        $str_creativeIds = implode(',', $request['creativeIds']);
        $result = $Creative->where(" idBuyer='" . $this->dspId . "' and buyerCrid in (" . $str_creativeIds . ")")->select();

        $this->response['Creative'] = array();
        foreach ($result as $k => $v) {
            switch ($v['status']) {
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
                    $status = $v['status'];
            }
            $arr = array('creativeId' => $v['buyercrid'],

                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['Creative'][] = $arr;
        }
        $this->stop();

    }

    public function getall()
    {
        // 获取指定时间内创建的数据
        $Creative = D('Creative');
        $request = $this->request;
        $result = $Creative->where(" idBuyer='" . $this->dspId . "' and ctime >='" . $request['startDate'] . "' and ctime <= '" . date("Y-m-d", strtotime($request['endDate']) + 86400) . "' ")->count();
        if ($result > 100) {
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '202';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        $result = $Creative->where(" idBuyer='" . $this->dspId . "' and ctime >='" . $request['startDate'] . "' and ctime <= '" . date("Y-m-d", strtotime($request['endDate']) + 86400) . "' ")->select();
        $this->response['Creative'] = array();
        foreach ($result as $k => $v) {
            switch ($v['status']) {
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
                    $status = $v['status'];
            }
            $arr = array('creativeId' => $v['buyercrid'],
                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['Creative'][] = $arr;
        }
        $this->stop();

    }

    public function queryAuditState()
    {
        // 获取id对应的审核状态和信息
        $Creative = D('Creative');
        $request = $this->request;
        $str_creativeIds = implode("','", $request['creativeIds']);
        $result = $Creative->where(" idBuyer='" . $this->dspId . "' and buyerCrid in ('" . $str_creativeIds . "')")->count();
        if ($result > 100) {
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '202';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        $result = $Creative->where(" idBuyer='" . $this->dspId . "' and buyerCrid in ('" . $str_creativeIds . "')")->select();
        $this->response['CreativeAuditState'] = array();
        foreach ($result as $k => $v) {
            switch ($v['status']) {
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
                    $status = $v['status'];
            }
            $arr = array('creativeId' => $v['buyercrid'],
                'state' => $status,
                'refuseReason' => $v['remark']
            );
            $this->response['CreativeAuditState'][] = $arr;
        }
        $this->stop();

    }

    public function getFilesName($url)
    {
        // 创建目录
        $s_dir = C('CREATIVE_FILE_SAVEPATH') . date("Y/m/d/");
        if (is_dir($s_dir)) {
        } else {
            if ($this->mkdirs($s_dir)) {
            } else {
            }
        }
        // 定义文件名
        $s_filename = md5(uniqid(mt_rand(), true)) . "." . $this->getExt($url);
        return $s_dir . $s_filename;
    }

    public function mkdirs($dir)
    {
        if (!is_dir($dir)) {
            if (!$this->mkdirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0777)) {
                return false;
            }
        }
        return true;
    }

    // 获取文件扩展名
    public function getExt($url)
    {
        $arr = parse_url($url);

        $file = basename($arr['path']);
        $ext = explode(".", $file);
        return strtolower($ext[1]);
    }

    public function saveFile($url)
    {
//        if ($url == '') {
//            return false;
//        }
//        //文件保存路径
//        $ch = curl_init();
//        $timeout = 5;
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//        $img = curl_exec($ch);
//        curl_close($ch);
//        $size = strlen($img);
//        //error_log(date()." ".$url." ".strlen($img),3,"/home/system/apache/htdocs/v1/Application/Runtime/Logs/V1/".date("Ymd").".debug.log");
//        //文件大小
//        $filename = $this->getFilesName($url);
//        $fp2 = @fopen($filename, 'a');
//        fwrite($fp2, $img);
//        fclose($fp2);
//        $fileExtName = substr($filename, strlen($filename) - 3);
//        //if ($size > 150000) return "2013";
//        if ($size == 0) return "2015";
        return $url;
    }

    public function fixUrls($item)
    {
        if (is_array($item)) {
            foreach ($item as $k => $url) {
                $url = trim($url);
                $item[$k] = $url;
            }
        } else {
            $item = trim($item);
        }
        return $item;
    }

    public function checkUrls($item, $len = 1024)
    {
        if (is_array($item)) {
            foreach ($item as $url) {
                $i = $this->checkUrls($url, $len);
                if ($i !== true) return $i;
            }
        } else {
            if ($item == "") return "2006";
            if (substr($item, 0, 4) != "http") return "2007";
            if (strlen($item) > $len) return "2010";
        }
        return true;
    }

    public function clickUrls($item, $len = 1024)
    {
        if (is_array($item)) {
            foreach ($item as $url) {
                $i = $this->clickUrls($url, $len);
                if ($i !== true) return $i;
            }
        } else if ($item) {
            if (substr($item, 0, 4) != "http") return "2007";
            if (strlen($item) > $len) return "2010";
        }
        return true;
    }
}