<?php

namespace V1\Controller;

use Think\Controller;

class CommonController extends Controller {

    //定义uid request response参数，系统共用
    public $dspId, $request, $response;
    public $arr_tradeId,$arr_size;

    public function __construct() {
        //获取DSPid token IP，做权限验证		
        $request = json_decode(file_get_contents('php://input', 'r'), true);
        $dspId = $request['authHeader']['dspId'];
        $token = $request['authHeader']['token'];
        if ($dspId == "") {
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '801';
            $this->response['errors']['message'] = '801';
            $this->stop();
        }
        if ($token == "") {
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '802';
            $this->response['errors']['message'] = '802';
            $this->stop();
        }

//        $User = D('User');
//        $cond['id'] = $dspId;

        $result = D('Buyer')->join('left join buyer_param on buyer.id=buyer_param.id')->field('buyer.id, buyer.status, buyer_param.token, buyer_param.ipList')->where("buyer.id = $dspId")->find();

        //echo $User->getLastSql();
        //print_r($result);
        //$result = $User->byIdList(array('where'=>"id='".$dspId."'"));
        //echo "sql=". $User->getLastSql();
        // 根据id能查询到数据
        if ($result) {
            // token正确
            //echo "token1=".$result['token']."\n";
            //echo "token2=".$token."\n";
           
            if ($result['token'] == $token) {
                // 账户被禁用
                if ($result['status'] == 4) {
                    $this->response['status'] = '2';
                    $this->response['errors']['code'] = '803';
                    $this->response['errors']['message'] = '803';
                    $this->stop();
                }

                $str_ip = $result['iplist'];
                $arr_ip = explode(',', $str_ip);
//                $int_anum = 1000;// api 每天调用次数
               // print_r($arr_ip);
                if (is_array($arr_ip)) {
                    $bool_ip = false;
                    // IP在列表中
                    foreach ($arr_ip as $v) {
                        if ($this->get_real_ip() == $v) {
                            $bool_ip = true;
                            break;
                        }
                    }
                    //$bool_ip=true;
                    // IP不在列表中
                    if ($bool_ip == false) {
                        $this->response['status'] = '2';
                        $this->response['errors']['code'] = '804';
                        $this->response['errors']['message'] = '804';
                        $this->stop();
                    }
                }else {
                    //未设置IP
                    $this->response['status'] = '2';
                    $this->response['errors']['code'] = '804';
                    $this->response['errors']['message'] = '804';
                    $this->stop();
                }
                //	echo "debug skip";
                $this->dspId = $dspId;
                $this->request = $request;
            } else {
                // token不正确
                $this->response['status'] = '2';
                $this->response['errors']['code'] = '803';
                $this->response['errors']['message'] = '803';
                $this->stop();
            }
        } else {
            // 查询不到DSPid
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '803';
            $this->response['errors']['message'] = '803';
            $this->stop();
        }
        // 判断接口调用次数
        $str_cfile = C('CREATIVE_FILE_SAVEPATH')."count/".date("Ymd").".".$this->dspId.".count";
        $fp = fopen ($str_cfile,"r");
        $i_count = fgets($fp);
        if ($i_count == "")$i_count = 0;
        /*
        if ($i_count > $int_anum){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '805';
            $this->response['errors']['message'] = '';
            $this->stop();
        }*/
        $i_count++;
        $fp = fopen ($str_cfile,"w");
		fwrite($fp, $i_count);
		fclose($fp);
        
        // 判断输入条件
        if ($request['startDate']!='' && !$this->isdate($request['startDate'])){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '200';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if ($request['endDate']!='' && !$this->isdate($request['endDate'])){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '200';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Advertiser" && ACTION_NAME == "add" &&  count($request['request'])>5){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Advertiser" && ACTION_NAME == "update" &&  count($request['request'])>5){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Advertiser" && ACTION_NAME == "get" &&  count($request['advertiserIds'])>100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Advertiser" && ACTION_NAME == "queryQualification" &&  count($request['advertiserIds'])>100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Creative" && ACTION_NAME == "add" &&  count($request['request'])>10){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Creative" && ACTION_NAME == "update" &&  count($request['request'])>10){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Creative" && ACTION_NAME == "get" &&  count($request['creativeIds'])>100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        if (CONTROLLER_NAME == "Creative" && ACTION_NAME == "queryAuditState" &&  count($request['creativeIds'])>100){
            $this->response['status'] = '2';
            $this->response['errors']['code'] = '102';
            $this->response['errors']['message'] = '';
            $this->stop();
        }
        
        // 载入基本数据，广告尺寸，广告行业id等
        $result = D('Place')->getSizeList();
        foreach ($result as $k => $v){
        	$this->arr_size[$v['width']."x".$v['height']] = 1;
        }
        $result = D('SysIndustryCategory')->select();
        foreach ($result as $k => $v){
        	$this->arr_tradeId[$v['c1']] = 1;
        	$this->arr_tradeId[$v['c2']] = 1;
        }
    }

    // 输出结果
    public function stop() {
		error_log(date("Y-m-d H:i:s")." ".$this->dspId." ".$this->get_real_ip()." /".MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME." request=" .json_encode($this->request)." response=".json_encode($this->response)."\n",3,$_SERVER['DOCUMENT_ROOT'].APP_PATH."./Runtime/Logs/V1/".date("Ymd").".log");
        echo json_encode($this->response);
        exit();
    }

//    public function history($msg) {
//        $History = D('History');
//		$str_sql = "CREATE TABLE IF NOT EXISTS `history_".date("Y_m")."` (
//  `id` int(11) unsigned NOT NULL  AUTO_INCREMENT COMMENT '操作记录id',
//  `uid` int(11) unsigned NOT NULL COMMENT '操作人',
//  `object` varchar(32) NOT NULL COMMENT '操作对象(表)',
//  `objid` varchar(100) NOT NULL COMMENT '操作对象id',
//  `ip` varchar(50) DEFAULT NULL COMMENT '执行的IP',
//  `type` enum('update','add','delete') NOT NULL COMMENT '操作类型(增加,修改,删除)',
//  `channel` enum('api','web','sys') NOT NULL COMMENT '操作渠道(api,web)',
//  `content` text NOT NULL COMMENT '操作内容,编码格式，基本内容为(字段名=字段值)',
//  `ctime` datetime NOT NULL COMMENT '操作时间',
//  PRIMARY KEY (`id`),
//  UNIQUE KEY `unique` (`id`),
//  KEY `history` (`id`,`uid`,`object`,`objid`,`type`,`ctime`)
//) ENGINE=MyISAM DEFAULT CHARSET=utf8;
//";
//		$result = $History->query($str_sql);
//        $data['uid'] = $msg['uid'];
//        $data['object'] = $msg['object'];
//        $data['objid'] = $msg['objid'];
//        $data['type'] = $msg['type'];
//        $data['ip'] = $msg['ip'];
//        $data['channel'] = 'api';
//        $data['content'] = $msg['content'];
//        $data['ctime'] = date("Y-m-d H:i:s", time());
//        $result = $History->table("history_".date("Y_m"))->add($data);
//    }
	
    
    public function isdate($str,$format="Y-m-d"){
        $strArr = explode("-",$str);
        if(empty($strArr)){
            return false;
        }
        foreach($strArr as $val){
            if(strlen($val)<2){
                $val="0".$val;
            }
            $newArr[]=$val;
        }
        $str =implode("-",$newArr);
        $unixTime=strtotime($str);
        $checkDate= date($format,$unixTime);
        if($checkDate==$str)
            return true;
        else
            return false;
    }

    function get_real_ip() {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }

}
