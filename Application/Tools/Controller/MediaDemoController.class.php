<?php
namespace Tools\Controller;
use Think\Controller;
class MediaDemoController extends Controller {
     public function index(){
         
         $this->display();
    }
    public function banner(){
        if(IS_POST){

        $p_id = I('p_id');

        $place = D('place');
        $cond['id'] = $p_id;
        $placeInfo = $place->where($cond)->find();
       
        if (!$placeInfo) {
            $result['status'] = 0;
            $result['msg'] = '广告位ID有误';
            $this->ajaxReturn($result);
        }
         $mediacond['id']=$placeInfo['meidaid'];
        $mediaInfo=D('media')->where()->find();
        if (!$mediaInfo) {
            $result['status'] = 0;
            $result['msg'] = '没有该媒体ID'.$placeInfo['meidaid'];
            $this->ajaxReturn($result);
        }
//        var_dump($_SERVER);
        $devicetype = ($placeInfo['devicetype'] == 1) ? 2 : (($placeInfo['devicetype'] == 2) ? 4 : (($placeInfo['devicetype'] == 3) ? 5 : 3));
//        $data['token'] = '5QRkJhkJTjknrtsMr6wAPbEiR5skWfQQ';        
//        $data['id'] = 'v1.2-test-1-t12-1396427099-0-779';
        $data['media_id'] = intval($placeInfo['mediaid']);
        $data['ts'] = time();
        $data['device']['dnt'] = FALSE;
        $data['device']['ip'] = (string)( $_SERVER["HTTP_X_REAL_IP"]?$_SERVER["HTTP_X_REAL_IP"]:$_SERVER["HTTP_X_FORWARDED_FOR"]);
        $data['device']['js'] = TRUE;
        $data['device']['devicetype'] = $devicetype;
        $data['device']['orientation'] = 1;
        $data['device']['connectiontype'] = 2;
        if($placeInfo['devicetype']==2 && $placeInfo['devicetype']==3){
            if($placeInfo['ostype']==1){
//                $data['device']['ua'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_2_1 like Mac OS X) AppleWebKit/602.4.6 (KHTML, like Gecko) Mobile/14D27';
                $data['device']['make'] = 'Apple';
                $data['device']['language'] = $_SERVER['HTTP_USER_AGENT'];
                $data['device']['model'] = 'iPhone';
                $data['device']['os'] = 'ios';
                $data['device']['osv'] = '10.3.1';
                $data['device']['w'] = 768;
                $data['device']['h'] = 1024;
                $data['device']['idfa'] = "D2A11E27-2CC8-4018-A5D7-4F0B85607922";
        $data['app']['storeurl'][] =(string)$mediaInfo['storeurl'];
         }else{
//                $data['device']['ua'] = 'Dalvik/1.6.0 (Linux; U; Android 4.4.2; 2014501 MIUI/V6.6.2.0.KHHCNCF)';
                $data['device']['make'] = 'Xiaomi';
                $data['device']['language'] = 'zh';
                $data['device']['model'] = '2014501';
                $data['device']['os'] = 'android';
                $data['device']['osv'] = '4.4.2';
                $data['device']['w'] = 720;
                $data['device']['h'] = 1280;
                $data['device']['imei'] = "865182022941710";
                $data['device']['androidId'] = "c38815b3f646273c";   
         }
        $data['app']['id'] = (string)$mediaInfo['id'];
        $data['app']['name'] = (string)$mediaInfo['name'];
        $data['app']['bundle'] =(string)$mediaInfo['domain'];
        
        }else{
//            $data['device']['ua'] = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
         $data['device']['os'] = 'Windows 7';
          isset($_COOKIE['id'])?1:$_COOKIE['id']=  md5(time().$placeInfo['id'].  rand(1, 10000));
            $data['user']['id'] = (string)$_COOKIE['id'];
                $data['site']['ref'] = (string)$_SERVER['HTTP_REFERER'] ;
        $data['site']['page'] = (string)('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                $data['site']['domain'] =(string) $_SERVER['HTTP_HOST'];
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])){
            $data['device']['ua'] = $_SERVER['HTTP_USER_AGENT'];  
        }
//        var_dump($_SERVER);
        $data['imp'][0]['id'] = $mediaInfo['id'];
        $data['imp'][0]['tagid'] = $p_id;
        $data['imp'][0]['bidfloor'] = intval($placeInfo['bidfloor']);
        
        $data['imp'][0]['banner']['w'] = intval($placeInfo['width']);
        $data['imp'][0]['banner']['h'] = intval($placeInfo['height']);
//        $data['imp'][0]['banner']['pos'] = 1;
        $mimes = explode(',', $placeInfo['mimes']);
        $mimes[0]?$data['imp'][0]['banner']['mimes'][]=intval($mimes[0]):1;
        $mimes[1]?$data['imp'][0]['banner']['mimes'][]=intval($mimes[1]):1;
        $mimes[2]?$data['imp'][0]['banner']['mimes'][]=intval($mimes[2]):1;
        $data['imp'][0]['instl'] = (int)rand(1, 8);
        $data['imp'][0]['allow_adm'] = TRUE;
        $data=  json_encode(($data));      
//        print_r($data);
                                        date_default_timezone_set('Asia/Shanghai');
                              ini_set('error_reporting',		'E_ALL');
                              ini_set('max_execution_time',	'3600');
                              ini_set("memory_limit",			"-1");
                              ini_set('display_errors',		'on');
                              ini_set('log_errors',			'on');
                              ini_set('error_log',		  '/home/php_errors.log');

                              //根目录
                              define('BASE_PATH', '/home/system/apache/htdocs/adxDaemon/src/seller/sendApiRequest/');
                              set_include_path(implode(PATH_SEPARATOR, array(BASE_PATH, BASE_PATH."/../../lib/", get_include_path())));
                              require_once("comm.php");
                              $host = 'ad.test.rtbs.cn';
                              $port = 80;
                              $path = '/v2/api';
                                      $postData = $data;
                              $url = sprintf("%s", $path);
                              $rtn =$this-> post($host, $port, $url, $postData);
                              $start = strpos($rtn, "\r\n\r\n");
                              $respBody = substr($rtn, $start+4);
                              $array = json_decode($respBody,TRUE);
        if($array['nbr']){
             $result['status'] = 0;
            $result['msg'] = $array['nbr'];
            $this->ajaxReturn($result);
        }      
        $result['status']='1';
        $result['src']=$array['seatbid']['0']['bid']['0']['iurl'];
        $result['h']=$array['seatbid']['0']['bid']['0']['h'];
        $result['w']=$array['seatbid']['0']['bid']['0']['w'];
        $result['clkurl']=$array['seatbid']['0']['bid']['0']['clkurl'];
        $result['imptrackers']=$array['seatbid']['0']['bid']['0']['imptrackers'];
        $result['clktrackers']=$array['seatbid']['0']['bid']['0']['clktrackers'];
        
//        var_dump($array);die;
        $this->ajaxReturn($result);
        }else{
         
        
        $this->display();
        
        }

    }
    public function video() {
        if(IS_POST){

        $p_id = I('p_id');

        $place = D('place');
        $cond['id'] = $p_id;
        $placeInfo = $place->where($cond)->find();
        if (!$placeInfo) {
            $result['status'] = 0;
            $result['msg'] = '广告位ID有误';
            $this->ajaxReturn($result);
        }
        $devicetype = ($placeInfo['devicetype'] == 1) ? 2 : (($placeInfo['devicetype'] == 2) ? 4 : (($placeInfo['devicetype'] == 3) ? 5 : 3));
        $data['id'] = 'v1.2-test-1-t12-1396427099-0-779';
//        $data['media_id'] = intval($placeInfo['mediaid']);
        $data['ts'] = time();
//        $data['token'] = '5QRkJhkJTjknrtsMr6wAPbEiR5skWfQQ';
        $data['device']['dnt'] = FALSE;
        $data['device']['ua'] = 'Mozilla/5.0 (Linux; Android 4.4.4; M351 Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36';        $data['device']['ip'] = '222.76.241.20';
        $data['device']['ip'] = '222.76.241.20';
        $data['device']['make'] = 'HUAWEI';
        $data['device']['model'] = 'H30-T00';
        $data['device']['os'] = 'Android';
        $data['device']['osv'] = '4.2.2';
        $data['device']['w'] = 1280;
        $data['device']['h'] = 720;
        $data['device']['js'] = TRUE;
        $data['device']['connectiontype'] = 2; 
        $data['device']['devicetype'] = 4;
        $data['device']['mac'] = '862845023710978';
        $data['device']['androidid'] = '4d8a6e6dc28ab592';
        $data['device']['brk'] = FALSE;
        $data['app']['id'] = 'app_id';
        $data['app']['name'] = '测试app';
        $data['app']['bundle'] = 'com.test.cn';
        $data['app']['cat'][] = 'IAB1';
        $data['app']['cat'][] = 'IAB2';
        $data['imp'][0]['id'] = 'adx_imp_1';
        $data['imp'][0]['tagid'] = $p_id;
        $data['imp'][0]['bidfloor'] = intval($placeInfo['bidfloor']);
        
        $data['imp'][0]['video']['w'] = intval($placeInfo['width']);
        $data['imp'][0]['video']['h'] = intval($placeInfo['height']);
        $data['imp'][0]['video']['linearity'] = intval($placeInfo['linearity']);
        $data['imp'][0]['video']['pos'] = 1;
        $mimes = explode(',', $placeInfo['mimes']);
        $mimes[0]?$data['imp'][0]['video']['mimes'][]=intval($mimes[0]):1;
        $mimes[1]?$data['imp'][0]['video']['mimes'][]=intval($mimes[1]):1;
        $mimes[2]?$data['imp'][0]['video']['mimes'][]=intval($mimes[2]):1;
        $data['imp'][0]['instl'] = 1;
        $data['imp'][0]['allow_adm'] = TRUE;
        $data=  json_encode(($data));      
                                        date_default_timezone_set('Asia/Shanghai');
                              ini_set('error_reporting',		'E_ALL');
                              ini_set('max_execution_time',	'3600');
                              ini_set("memory_limit",			"-1");
                              ini_set('display_errors',		'on');
                              ini_set('log_errors',			'on');
                              ini_set('error_log',		  '/home/php_errors.log');

                              //根目录
                              define('BASE_PATH', '/home/system/apache/htdocs/adxDaemon/src/seller/sendApiRequest/');
                              set_include_path(implode(PATH_SEPARATOR, array(BASE_PATH, BASE_PATH."/../../lib/", get_include_path())));
                              require_once("comm.php");
                              $host = 'ad.test.rtbs.cn';
                              $port = 80;
                              $path = '/v2/api';
                                      $postData = $data;
                              $url = sprintf("%s", $path);
                              $rtn =$this-> post($host, $port, $url, $postData);
                              $start = strpos($rtn, "\r\n\r\n");
                              $respBody = substr($rtn, $start+4);
                              $array = json_decode($respBody,TRUE);
        if($array['nbr']){
             $result['status'] = 0;
            $result['msg'] = $array['nbr'];
            $this->ajaxReturn($result);
        }      
        $result['status']='1';
        $result['src']=$array['seatbid']['0']['bid']['0']['iurl'];
        $result['h']=$array['seatbid']['0']['bid']['0']['h'];
        $result['w']=$array['seatbid']['0']['bid']['0']['w'];
        $result['clkurl']=$array['seatbid']['0']['bid']['0']['clkurl'];
        $result['imptrackers']=$array['seatbid']['0']['bid']['0']['imptrackers'];
        $result['clktrackers']=$array['seatbid']['0']['bid']['0']['clktrackers'];
        

        $this->ajaxReturn($result);
        }else{
         
        
        $this->display();
        
        }

    }
     public function native() {
        if(IS_POST){

        $p_id = I('p_id');

        $place = D('place');
        $cond['id'] = $p_id;
        $placeInfo = $place->where($cond)->find();
        if (!$placeInfo) {
            $result['status'] = 0;
            $result['msg'] = '广告位ID有误';
            $this->ajaxReturn($result);
        }
        $devicetype = ($placeInfo['devicetype'] == 1) ? 2 : (($placeInfo['devicetype'] == 2) ? 4 : (($placeInfo['devicetype'] == 3) ? 5 : 3));
        $data['id'] = 'v1.2-test-1-t12-1396427099-0-779';
//        $data['media_id'] = intval($placeInfo['mediaid']);
        $data['ts'] = time();
//        $data['token'] = '5QRkJhkJTjknrtsMr6wAPbEiR5skWfQQ';
        $data['device']['dnt'] = FALSE;
        $data['device']['ua'] = 'Mozilla/5.0 (Linux; Android 4.4.4; M351 Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Mobile Safari/537.36';        $data['device']['ip'] = '222.76.241.20';
        $data['device']['ip'] = '222.76.241.20';
        $data['device']['make'] = 'HUAWEI';
        $data['device']['model'] = 'H30-T00';
        $data['device']['os'] = 'Android';
        $data['device']['osv'] = '4.2.2';
        $data['device']['w'] = 1280;
        $data['device']['h'] = 720;
        $data['device']['js'] = TRUE;
        $data['device']['connectiontype'] = 2; 
        $data['device']['devicetype'] = 4;
        $data['device']['mac'] = '862845023710978';
        $data['device']['androidid'] = '4d8a6e6dc28ab592';
        $data['device']['brk'] = FALSE;
        $data['app']['id'] = 'app_id';
        $data['app']['name'] = '测试app';
        $data['app']['bundle'] = 'com.test.cn';
        $data['app']['cat'][] = 'IAB1';
        $data['app']['cat'][] = 'IAB2';
        $data['imp'][0]['id'] = 'adx_imp_1';
        $data['imp'][0]['tagid'] = $p_id;
        $data['imp'][0]['bidfloor'] = intval($placeInfo['bidfloor']);
        $placeInfo['nativeassets']=  json_decode($placeInfo['nativeassets']);
        $data['imp'][0]['native']['layout'] = intval($placeInfo['nativeassets']['native']['layout']);
        $data['imp'][0]['native']['assets'] = intval($placeInfo['nativeassets']['native']['assets']);
       
        $data['imp'][0]['instl'] = 1;
        $data['imp'][0]['allow_adm'] = TRUE;
        $data=  json_encode(($data));      
                                        date_default_timezone_set('Asia/Shanghai');
                              ini_set('error_reporting',		'E_ALL');
                              ini_set('max_execution_time',	'3600');
                              ini_set("memory_limit",			"-1");
                              ini_set('display_errors',		'on');
                              ini_set('log_errors',			'on');
                              ini_set('error_log',		  '/home/php_errors.log');

                              //根目录
                              define('BASE_PATH', '/home/system/apache/htdocs/adxDaemon/src/seller/sendApiRequest/');
                              set_include_path(implode(PATH_SEPARATOR, array(BASE_PATH, BASE_PATH."/../../lib/", get_include_path())));
                              require_once("comm.php");
                              $host = 'ad.test.rtbs.cn';
                              $port = 80;
                              $path = '/v2/api';
                                      $postData = $data;
                              $url = sprintf("%s", $path);
                              $rtn =$this-> post($host, $port, $url, $postData);
                              $start = strpos($rtn, "\r\n\r\n");
                              $respBody = substr($rtn, $start+4);
                              $array = json_decode($respBody,TRUE);
        if($array['nbr']){
             $result['status'] = 0;
            $result['msg'] = $array['nbr'];
            $this->ajaxReturn($result);
        }      
        $result['status']='1';
        $result['src']=$array['seatbid']['0']['bid']['0']['iurl'];
        $result['h']=$array['seatbid']['0']['bid']['0']['h'];
        $result['w']=$array['seatbid']['0']['bid']['0']['w'];
        $result['clkurl']=$array['seatbid']['0']['bid']['0']['clkurl'];
        $result['imptrackers']=$array['seatbid']['0']['bid']['0']['imptrackers'];
        $result['clktrackers']=$array['seatbid']['0']['bid']['0']['clktrackers'];
        

        $this->ajaxReturn($result);
        }else{
         
        
        $this->display();
        
        }

    }
    
            public function  post($host, $port, $path, $data){
                    $post="POST $path HTTP/1.1\r\nHost: $host\r\n";
                    $post.="Content-Type: application/json; charset=UTF-8\r\n";
                    $post.="User-Agent: Mozilla 4.0\r\nContent-length: ";
                    $post.=strlen($data)."\r\nConnection: keep-alive\r\n\r\n$data\r\n";
                    
                    $h=fsockopen($host, $port, $errno, $errstr);
                    fwrite($h,$post);
                    for($a=0,$r='';!$a;){
                        $b=fread($h,8192);
                        $r.=$b;
                        $a=(($b=='')?1:0);

                        $start = strpos($r, "204 No Content");
                        if($start > 0)
                            break;

                        $start = strpos($r, "Content-Length:");
                        if($start > 0){
                            $end = strpos($r, "\r\n", $start);
                            if($end > 0){
                                $len = strlen("Content-Length:");
                                $str = substr($r, $start+$len, $end-$start-$len);
                                $contentLength = intval($str);

                                $start = strpos($r, "\r\n\r\n", $end);
                                $total = strlen($r);
                                $bodyLen = $total-$start-4;

                                if($bodyLen >= $contentLength)
                                    break;
                            }
                        }
                    }
                    fclose($h);
                    return $r;
                }
}