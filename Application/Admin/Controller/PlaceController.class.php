<?php

namespace Admin\Controller;

use Common\Controller;
use Common\Controller\PageCus;
//use Vendor\phpexcel\PHPExcel;
/**
 * 广告位控制器
 */
class PlaceController extends BaseController {
    /**
     * 广告位列表
     */
    public function index(){
        $userInfo = $_SESSION["userInfo"]['id'];
        $result = array();
        if(IS_POST){
            //获取搜索条件
            $name_seller = I("name_seller");                //媒体账户
            $name_media  = I("name_media");                 //媒体名字
            $domain_media  = I("domain_media");             //媒体域名
            $id_place  = I("id_place");                     //广告位id
            $name_place  = I("name_place");   //广告位名称
            $width_place  = I("width_place");               //广告位宽度
            $height_place  = I("height_place");             //广告位高度
            $type_place  = I("type_place");                 //广告位类型（1：banner；2：video；3：native） 
            $os_type  = I("os_type");                 //系统类型
            $instl_place  = I("instl_place");               //展现形式（1:banner;2:video;3:背投;4:视频暂停;5:弹窗;6:视频悬浮;7:开屏;8:插屏;9:应用墙;10:信息流）
            $device_type_place  = I("device_type_place");   //设备类型
            $status_place  = I("status_place");             //广告位状态
              $sellerid     = I("sellerid");
            //分页使用参数
            $limit= I("rows");      //显示行数
            $sidx = I("sidx");      //排序name
            $sord = I("sord");      //排序方式
            
            
            //判断权限  是否是员工账号
            $user_role = D("user_role")->where("idUser = {$userInfo}")->find();
            
            if($user_role['idrole'] == 6){
                $userSeller = D("UserSeller")->where("idUser = {$userInfo}")->select();
                $temp_is_sellerID = "";
                foreach ($userSeller as $key => $val){
                    if($val['allow'] == 1){
                        $temp_is_sellerID .= $val['idseller'].",";
                    }
                }
                $temp_is_sellerID = substr($temp_is_sellerID,0,strlen($temp_is_sellerID)-1);
                $sql = "SELECT distinct p.id,p.* FROM `place` p , `media` m, `seller` s WHERE m.id=p.mediaid and (s.id=p.sellerId or s.id=p.sellerSonId) and s.id IN(".$temp_is_sellerID.")";
            }else{
                $sql = "SELECT distinct p.id,p.* FROM `place` p , `media` m, `seller` s WHERE m.id=p.mediaid and (s.id=p.sellerId or s.id=p.sellerSonId)";
            }
            $count=0;
            //拼接搜索sql
            if($name_seller !=""){
                $sql .= " and s.company like '%{$name_seller}%' ";
                $count++;
            }
            if($name_media !=""){
                $sql .= " and m.name like '%{$name_media}%'";
                $count++;
            }
            if($domain_media !=""){
                $sql .= " and m.domain like '%{$domain_media}%'";
                $count++;;
            }
            if($name_place !=""){
                $sql .= " and p.name like '%{$name_place}%'";
                $count++;;
            }
            if($id_place !=""){
                if(strlen($id_place)>=20){
                    $sql .= " and p.md5Id = {$id_place}";
                }else{
                    $sql .= " and p.id = {$id_place}";
                }
                
                $count++;;
            }
            if($width_place !=""){
                $sql .= " and p.width = {$width_place}";
                $count++;;
            }
            if($height_place !=""){
                $sql .= " and p.height = {$height_place}";
                $count++;;
            }
            if($type_place !=""){
                $sql .= " and p.placeType = {$type_place}";
                $count++;;
            }
             if($sellerid!=""){
                $sql .= " and p.sellerid  like '%{$sellerid}%'";
                $count++;;
            }
            if($os_type !=""){
                $sql .= " and p.osType = {$os_type}";
                $count++;;
            }
            if($instl_place !=""){
                $sql .= " and p.instl = {$instl_place}";
                $count++;;
            }
            if($device_type_place !=""){
                $sql .= " and p.deviceType = {$device_type_place}";
                $count++;;
            }
            if($status_place !=""){
                $sql .= " and p.status = {$status_place}";
                $count++;;
            }
            
            //排序
            if($sidx){
                $sql .=  " ORDER BY {$sidx} {$sord}";
            }else{
                $sql .=  " ORDER BY id desc";
            }
            
            //查询place总数
            if($count>0){
                $place_count = count(D("place")->query($sql));
            }else{
                $place_count = count(D("place")->query($sql));
            }
            //分页封装类
            $Page_project = new PageCus($place_count, $limit);
            //返回nowPage当前页   totalPages总页数
            $show_project = $Page_project->show();
            
            $sql .=  " LIMIT {$Page_project->firstRow},{$Page_project->listRows}";
            //查询广告位
            $places = D("place")->query($sql);
            
            if($places){
//                var_dump($places);die;
                $placetype_arr = C("PLACE_TYPE");
                $devicetype_arr = C("DEVICE_TYPE");
                $instl_arr = C("INSTL");
                foreach ($places as $key => $val){
                    $seller = D("seller")->where("id = '{$val["sellerid"]}'")->find();
                    $parent_seller = D("seller")->where("id = '{$val["sellersonid"]}'")->find();
                    $media = D("media")->where("id = '{$val["mediaid"]}'")->find();
                    
                    $result[$key]['id'] = $val['id'];
                    $result[$key]['sellername'] = $seller['company'];
                    $result[$key]['sellerid']=$val['sellerid'];
                    $result[$key]['agentname'] = $parent_seller['id']>0?$parent_seller['company']:"无";
                    $result[$key]['name'] = $val['name'];
                    $result[$key]['namemedia'] = $media['name'];
                    $result[$key]['mediaid'] = $val["mediaid"];    
                      $class1['1'] = 'badge badge-grey';
                    $class1['2'] = 'badge badge-info';
                    $class1['3'] = 'badge badge-warning';
                   
                    if(($val['devicetype']=='1')  or($val['devicetype']=='4')){
                        $val["ostype"]='0';
                    }
                    $result[$key]['placetype'] ='<span class="'.$class1[$val['placetype']].'">'. $placetype_arr[$val['placetype']].'</span>';                                      
                    $result[$key]['audittype'] = $val['audittype'] == "1" ? '<span class="label label-sm label-danger arrowed arrowed-right">先审后投</span>' : '<span class="label label-sm arrowed-in-right arrowed-in label-success">先投后审</span>';
                    
                    $result[$key]['size'] = $val['width']."*".$val['height'];
                    $result[$key]['bidfloor'] = $val['bidfloor'];
//                    $result[$key]['devicetype'] = $devicetype_arr[$val['devicetype']];
                     $class2['1'] = 'label label-sm label-inverse arrowed';
                    $class2['2'] = 'label label-sm label-purple arrowed';
                    $class2['3'] = 'label label-sm label-yellow arrowed';
                    $class2['4'] = 'label label-sm label-pink arrowed';

                    $val["ostype"]=='0'?$result[$key]['devicetype'] ='<span class="'.$class2[$val["devicetype"]].'">'. $devicetype_arr[$val['devicetype']].'</span>':$result[$key]['devicetype'] = '<span class="'.$class2[$val["devicetype"]].'">'.$devicetype_arr[$val['devicetype']].'-'.C('OS_TYPE'.'.'."$val[ostype]").'</span>';
                    $result[$key]['instl'] = $instl_arr[$val['instl']];
                    $result[$key]['status'] = $val['status'];
                    $result[$key]['saf']   = 1;
                    $result[$key]['num']   = $key+1;
                }
            }
            $result2['page']    = $show_project['nowPage'];
            $result2['total']   = $show_project['totalPages'];
            $result2['records'] = $place_count;
            $result2['data'] = $result;
            $this->ajaxReturn($result2);
        }else{
            $this->assign("instls",C("INSTL"));
        }
        $placeRoute = isAllow('placeRoute', 'index') ? 1 : 0;
        $isGetCode = isAllow('Place', 'getCode') ? 1 : 0;
        $isAllow = isAllow('Place', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('Place', 'setStatus') ? 1 : 0;
        $isAddAllow = isAllow('Place', 'add') ? 1 : 0;
        $isplaceImport = isAllow('Place', 'placeImport') ? 1 : 0;
        $isplaceExport = isAllow('Place', 'placeExport') ? 1 : 0;
        $this->assign('isplaceExport', $isplaceExport);
        $this->assign('isplaceImport', $isplaceImport);
        $this->assign('isGetCode', $isGetCode);
        $this->assign('isAddAllow', $isAddAllow);
        $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $this->assign('placeRoute', $placeRoute);
        $this->display();
    }

    /**
     * 广告位添加
     */
    public function add(){
       
        $i=1;  
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){ 
            $placeType = $_REQUEST['placeType'];
            $w = "";
            $h = "";
            if($_REQUEST['nativeAssets']!=NULL){
                $_REQUEST['nativeAssets']=  json_decode($_REQUEST['nativeAssets'],TRUE);
                  $_REQUEST['nativeAssets']['native']['layout']=intval($_REQUEST['nativeAssets']['native']['layout']);
                foreach ($_REQUEST['nativeAssets']['native']['assets'] as $key => $value) {
                    $_REQUEST['nativeAssets']['native']['assets'][$key]['id']=$i;
                    $i++;   
                  
                }
               $_REQUEST['nativeAssets']=  json_encode($_REQUEST['nativeAssets']);
             
            }
            if($placeType == "1"){
                $_REQUEST['width'] = $_REQUEST['width1'];
                $_REQUEST['height'] = $_REQUEST['height1'];
                $_REQUEST['fileExt'] = implode(",",$_REQUEST['fileExt1']);
                $_REQUEST['mimes'] = implode(",",$_REQUEST['mimes1']);
                $_REQUEST['instl'] = $_REQUEST['instl1'];
            }else if($placeType == "2"){
                $_REQUEST['width'] = $_REQUEST['width2'];
                $_REQUEST['height'] = $_REQUEST['height2'];
                 $_REQUEST['fileExt'] = implode(",",$_REQUEST['fileExt2']);
                $_REQUEST['mimes'] = implode(",",$_REQUEST['mimes2']);
                $_REQUEST['instl'] = $_REQUEST['instl2'];
            }
            if($placeType == "3"){
                $_REQUEST['instl'] = 10;
            }
            
            if($_REQUEST['bidfloor'] == ""){
                $_REQUEST['bidfloor'] = 0;
            }
            
            //获取地域
            $areabidfloor1 = $_REQUEST['areabidfloor1'][0];
            $areabidfloor2 = $_REQUEST['areabidfloor2'][0];
            if($areabidfloor1){
                $areabidfloor1 = explode(',',$areabidfloor1);
                $areabidfloor2 = explode(',',$areabidfloor2);
                $common = A("Common");
                if($areabidfloor1){
                    $areabidfloor1 = $common->getCityId($areabidfloor1,1);
                }
                $newAreabidfloor = array();
                for ($i = 0; $i < count($areabidfloor1); $i++) {
                    $newAreabidfloor[$i][$areabidfloor1[$i]] = $areabidfloor2[$i];
                }
                $_REQUEST['areabidfloor'] = json_encode($newAreabidfloor);
            }
            $t = date("Y-m-d H:i:s",time());
            $_REQUEST['cuid']  = $userInfo;
            $_REQUEST['muid']  = 0;
            $_REQUEST['ctime'] = $t;
            $_REQUEST['code'] =  htmlspecialchars($_REQUEST['code']);
            if(D("Place")->add($_REQUEST)){
                $this->redirect('/Place/index');
            }else{
                $this->error("广告位添加数据失败~");
            }
        }
        
        $user_role = D("user_role")->where("idUser = {$userInfo}")->find();
        
        if($user_role['idrole'] == 6){
            $userSeller = D("UserSeller")->where("idUser = {$userInfo}")->select();
            $temp_is_sellerID = "";
            foreach ($userSeller as $key => $val){
                if($val['allow'] == 1){
                    $temp_is_sellerID .= $val['idseller'].",";
                }
            }
            
            $map['id']  = array('in',$temp_is_sellerID);
            $map['status'] = 2;
            //1.查询所有媒体账号
            $sellers = D("seller")->where($map)->select();
            
            
            //2.查询所有媒体信息
            $medias = D("media")->where("status = 1")->select();
        }else{
            //1.查询所有媒体账号
            $sellers = D("seller")->where("status = 2")->select();
            
            //2.查询所有媒体信息
            $medias = D("media")->where("status = 1")->select();
        }
              
        $this->assign("native_assets_data_type",C("NATIVE_ASSETS_DATA_TYPE"));
        $this->assign("sellersJson",json_encode($sellers));
        $this->assign("bannerTypes",C("BANNER_MIME_TYPE"));
        $this->assign("fileExts",C("FILE_EXT"));
        $this->assign("videoType",C("VIDEO_MIME_TYPE"));
        $this->assign("instls",C("INSTL"));
        $this->assign("layouts",C("LAYOUT"));
        $this->assign("pos",C("POS"));
        $this->assign("medias",$medias);
        $this->assign("sellers",$sellers);
        $this->display();
    }
    
    
    public function edit(){
           $i=1;
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            if($_REQUEST['nativeAssets']!=NULL){
                $_REQUEST['nativeAssets']=  json_decode($_REQUEST['nativeAssets'],TRUE);
                  $_REQUEST['nativeAssets']['native']['layout']=intval($_REQUEST['nativeAssets']['native']['layout']);
                foreach ($_REQUEST['nativeAssets']['native']['assets'] as $key => $value) {
                    $_REQUEST['nativeAssets']['native']['assets'][$key]['id']=$i;
                    $i++;   
                  
                }
               $_REQUEST['nativeAssets']=  json_encode($_REQUEST['nativeAssets']);
             
            }
            $placeType = $_REQUEST['placeType'];
            $w = "";
            $h = "";
            if($placeType == "1"){
                $_REQUEST['width'] = $_REQUEST['width1'];
                $_REQUEST['height'] = $_REQUEST['height1'];
                $_REQUEST['fileExt'] = implode(",",$_REQUEST['fileExt1']);
                $_REQUEST['mimes'] = implode(",",$_REQUEST['mimes1']);
                $_REQUEST['instl'] = $_REQUEST['instl1'];
                $_REQUEST['allowAdm'] = $_REQUEST['allowAdm1'];
            }
            if($placeType == "2"){
                $_REQUEST['width'] = $_REQUEST['width2'];
                $_REQUEST['height'] = $_REQUEST['height2'];
                 $_REQUEST['fileExt'] = implode(",",$_REQUEST['fileExt2']);
                $_REQUEST['mimes'] = implode(",",$_REQUEST['mimes2']);
                $_REQUEST['instl'] = $_REQUEST['instl2'];
                $_REQUEST['allowAdm'] = $_REQUEST['allowAdm2'];
            }
            
            if($placeType == "3"){
                $_REQUEST['instl'] = 10;
            }
            
            if($_REQUEST['bidfloor'] == ""){
                $_REQUEST['bidfloor'] = 0;
            }
            
            //获取地域
            $areabidfloor1 = $_REQUEST['areabidfloor1'][0];
            $areabidfloor2 = $_REQUEST['areabidfloor2'][0];
            if($areabidfloor1){
                $areabidfloor1 = explode(',',$areabidfloor1);
                $areabidfloor2 = explode(',',$areabidfloor2);
                $common = A("Common");
                if($areabidfloor1){
                    $areabidfloor1 = $common->getCityId($areabidfloor1,1);
                }
                $newAreabidfloor = array();
                for ($i = 0; $i < count($areabidfloor1); $i++) {
                    $newAreabidfloor[$i][$areabidfloor1[$i]] = $areabidfloor2[$i];
//                     $newAreabidfloor[$i]['price'] = $areabidfloor2[$i];
                }
                $_REQUEST['areabidfloor'] = json_encode($newAreabidfloor);
            }
            $t = date("Y-m-d H:i:s",time());
            $_REQUEST['muid']  = $userInfo;
            $_REQUEST['mtime'] = $t;
           
            $_REQUEST['code'] =  htmlspecialchars($_REQUEST['code']);
           
           if($_REQUEST['deviceType']==1){
                $_REQUEST['osType']=0;
            }
            D("Place")->where("id = {$_REQUEST['id']}")->save($_REQUEST);
            $this->redirect('/Place/index');
        }else{
            $id = $_GET['id']==""?0:$_GET['id'];
            if($id){
                //查询当前媒体信息
                $palce = D("place")->where("id = {$id}")->find();
                if($palce['nativeassets']){
                    
                    $nativeassets=json_decode($palce['nativeassets'],true);
                    $layout= $nativeassets['native']['layout'];
                }
              
                //修改状态
                if(I('type') == "2"){
                    $msg = array();
                    $palce['status'] = $palce['status']=="1"?"2":"1";
                    $result = D("place")->where("id = {$id}")->save(array("status"=>$palce['status']));
                    if($result>0 || $result ===0) $msg['status'] = "200";
                    $this->ajaxReturn($msg);
                }
                //查询媒体信息是否有代理记录
                if($palce['sellersonid'] > 0 ){
                    $agent_seller = D("seller")->where("id = {$palce['sellersonid']}")->find();
                }else{
                    $agent_seller = D("seller")->where("id = {$palce['sellerid']}")->find();
                }
                
                if($agent_seller['parentid'] != 0){
                    $pSeller = D("seller")->where("id = {$agent_seller['parentid']}")->find();
                    if($pSeller){
                        $palce['parent_company'] = $pSeller['company'];
                        $palce['parent_parent_id'] = $pSeller['id'];
                    }
                }
                $media = D("media")->where("id = {$palce['mediaid']}")->find();
                
                
                
                //获取地域name
                $areabidfloor = $palce['areabidfloor'];
                $common = A("Common");
                $tempRegion = array();
                if($areabidfloor){
                    $areabidfloor = json_decode($areabidfloor,true);
                    foreach($areabidfloor as $key => $region){
                        $temp_key = implode("",array_keys($areabidfloor[$key]));
                        unset($areabidfloor[$key]);
                        $areabidfloor[$key]['price'] = $region[$temp_key];
                        $tempRegion[] = $temp_key;
                    }
                    $r = $common->getCityId($tempRegion,2);
                    foreach($r as $key => $region){
                        $areabidfloor[$key]['region'] = $r[$key]==null?"其他":$r[$key];
                    }
                    
                    $palce['areabidfloor'] = json_encode($areabidfloor);
                }
            }
        }
        //1.查询所有媒体账号
        $sellers = D("seller")->where("status = 2")->select();
        //2.查询所有媒体信息
        $medias = D("media")->where("status = 1")->select();
//        var_dump($palce['sellerid']);die;
        $fileExtsnum=array('a'=>'1','b'=>'2','c'=>'3','d'=>'4','e'=>'5','f'=>'6');
        
        $this->assign('fileExtsnum',$fileExtsnum);
        $this->assign('gaintype',$palce['gaintype']);
        $this->assign("gainrate",$palce['gainrate']);
         $this->assign("sellerid",$palce['sellerid']);
        $this->assign("bannerTypes",C("BANNER_MIME_TYPE"));
        $this->assign("fileExts",C("FILE_EXT"));
        $this->assign("videoType",C("VIDEO_MIME_TYPE"));
        $this->assign("instls",C("INSTL"));
        $this->assign("pos",C("POS"));
        $this->assign("native_assets_data_type",C("NATIVE_ASSETS_DATA_TYPE"));
        $this->assign("layouts",C("LAYOUT"));
        $this->assign("layout",$layout);
        $this->assign("sellersJson",json_encode($sellers));
        $this->assign("edit_place",$palce);
        $this->assign("dfsdfsd","1");
        $this->assign("agent_seller",$agent_seller);
        $this->assign("medias",$medias);
        $this->assign("media",$media);
        $this->assign("sellers",$sellers);
        $this->display();
    }
    
    
    function remove_xss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    
        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
    
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }
    
        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
    
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(�{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }
    
    
    /**
     * 广告位导出
     */
    public function placeExport(){
        //获取搜索条件
        $name_seller = I("name_seller");                //媒体账户
        $name_media  = I("name_media");                 //媒体名字
        $domain_media  = I("domain_media");             //媒体域名
        $id_place  = I("id_place");                     //广告位id
        $name_place=I("name_place");//广告位名称
        $width_place  = I("width_place");               //广告位宽度
        $height_place  = I("height_place");             //广告位高度
        $type_place  = I("type_place");                 //广告位类型（1：banner；2：video；3：native）
        $instl_place  = I("instl_place");               //展现形式（1:banner;2:video;3:背投;4:视频暂停;5:弹窗;6:视频悬浮;7:开屏;8:插屏;9:应用墙;10:信息流）
        $device_type_place  = I("device_type_place");   //设备类型
        $status_place  = I("status_place");             //广告位状态
    
    
        $sql = "SELECT p.* FROM `place` p , `media` m, `seller` s WHERE m.id=p.mediaid AND s.id=p.sellerId";
        $count=0;
        //拼接搜索sql
        if($name_seller !=""){
            $sql .= " and s.company like '%{$name_seller}%' ";
            $count++;
        }
        if($name_media !=""){
            $sql .= " and m.name like '%{$name_media}%'";
            $count++;
        }
        if($domain_media !=""){
            $sql .= " and m.domain like '%{$domain_media}%'";
            $count++;;
        }
         if($name_place !=""){
            $sql .= " and p.name like '%{$name_place}%'";
            $count++;;
        }
        if($id_place !=""){
            $sql .= " and p.id = {$id_place}";
            $count++;;
        }
        if($width_place !=""){
            $sql .= " and p.width = {$width_place}";
            $count++;;
        }
        if($height_place !=""){
            $sql .= " and p.height = {$height_place}";
            $count++;;
        }
        if($type_place !=""){
            $sql .= " and p.placeType = {$type_place}";
            $count++;;
        }
        if($instl_place !=""){
            $sql .= " and p.instl = {$instl_place}";
            $count++;;
        }
        if($device_type_place !=""){
            $sql .= " and p.deviceType = {$device_type_place}";
            $count++;;
        }
        if($status_place !=""){
            $sql .= " and p.status = {$status_place}";
            $count++;;
        }
    
    
        $places = D("place")->query($sql);
    
    
        $filename = "广告位列表";
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.xls');
    
        $title = array(
            '媒体ID',
            '广告位ID',
            '广告位名称',
            '素材审核类型',
            '底价',
            '设备类型',
            '移动端操作系统',
            "屏幕位置",
            "广告类型",
            "展现形式",
            "宽度",
            "高度",
            "允许创意类型",
            "允许文件类型",
            "最大时长",
            "最小时长",
            "贴片位置",
            "播放类型",
            "打底代码",
            "状态",
            "原生广告的元素"
        );
        $data = array();
        foreach ($places as $k => $v){
            if($v['placetype'] == "1"){
                $v['mimes'] = C("BANNER_MIME_TYPE")[$v['mimes']];
            }
            if($v['placetype'] == "2"){
                $v['mimes'] = C("VIDEO_MIME_TYPE")[$v['mimes']];
            }
            
            if($v['devicetype'] == 1 || $v['devicetype'] == 4){
                $v['ostype'] = "无";
            }else{
                $v['ostype'] = C("OS_TYPE")[$v['ostype']];
            }
            $temparr = array(
                $v['mediaid'],
                $v['id'],
                $v['name'],
                $v['audittype']=="1"?"先审后投":"先投后审",
                $v['bidfloor'],
                C("DEVICE_TYPE")[$v['devicetype']],
                $v['ostype'],
                $v['screenlocation'],
                C("PLACE_TYPE")[$v['placetype']],
                C("INSTL")[$v['instl']],
                $v['width'],
                $v['height'],
                $v['mimes'],
                C("FILE_EXT")[$v['fileext']],
                $v['minduration'],
                $v['maxduration'],
                C("POS")[$v['pos']],
                C("LINEARITY")[$v['linearity']],
                $v['code'],
                $v['status']=="1"?"正常":"停用",
                $v['nativeAssets'],
            );
            $data[$k] = $temparr;
        }
         
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        echo '<html><head> <meta http-equiv="Content-type" content="text/html;charset=UTF-8" /><style id="Classeur1_16681_Styles">td{text-align:center;}</style></head>';
        echo '<body>';
        echo '<div id="Classeur1_16681" align=center x:publishsource="Excel">';
        echo '<table x:str border=1 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">';
        echo '<tr style="background:#686868;color:#fff;">';
        foreach ($title as $value) {
            echo "<td class=xl2216681 nowrap>".$value."</td>";
        }
        echo '</tr>';
        foreach ($data as $value) {
            echo '<tr>';
            foreach ($value as $k){
                echo "<td class=xl2216681 nowrap>".$k."</td>";
            }
            echo  "</tr>";
        }
        echo '</table>';
        echo '</body>';
        echo '</html>';
    }
    
    
    /**
     * 广告位导入
     */
    public function placeImport(){
        $suss=0; $failed=0; $chann=0;
    if (!empty($_FILES)) {
            $userInfo = $_SESSION["userInfo"]['id'];
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728 ;// 设置附件上传大小
            $upload->exts = array('xlsx','xls');// 设置附件上传类型
            $upload->rootPath = './Public/uploads/place/';
            // 上传文件
            $info = $upload->upload();
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $file_name=$upload->rootPath.$info["file_stu"]['savepath'].$info["file_stu"]["savename"];//地址等于更目录加上创建的子目录加上文件名
            }
            
            vendor("phpexcel.PHPExcel");
            
            //文件名为文件路径和文件名的拼接字符串
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//创建读取实例
            /*
             * log()//方法参数
             * $file_name excal文件的保存路径
             */
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');//加载文件
            $sheet = $objPHPExcel->getSheet(0);//取得sheet(0)表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            
            $t = date("Y-m-d H:i:s",time());
            $userInfo = $_SESSION["userInfo"]['id'];
            $mes = "";
            for($i=2;$i<=$highestRow;$i++)
            {
                $bidfloor = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                if(!is_float($bidfloor)){
                    $mes .= $i.",";
                    continue;
                }
                $mediaid = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                $media2=D('media')->where("sellerid= {$userInfo} and id = {$mediaid}")->find();
                $media = D("media")->where("id = {$mediaid} ")->find();
                if($media and $media2){
                    $data['mediaId']                =    $media['id'];
                    $data['sellerId']               =    $media['sellerid'];
                    $data['sellerSonId']            =    $media['sellersonid'];
                    $data['name']                   =    $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                    $data['auditType']              =    $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                    $data['bidfloor']               =    $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                    $data['deviceType']             =    $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
                    $data['osType']                 =    $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue()==""?0:$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
                    $data['screenLocation']         =    $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
                    $data['placeType']              =    $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
                    $data['instl']                  =    $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
                    $data['width']                  =    $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
                    $data['height']                 =    $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
                    $data['mimes']                  =    $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
                    $data['fileExt']                =    $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
                    $data['maxduration']            =    $objPHPExcel->getActiveSheet()->getCell("N".$i)->getValue();
                    $data['minduration']            =    $objPHPExcel->getActiveSheet()->getCell("O".$i)->getValue();
                    $data['pos']                    =    $objPHPExcel->getActiveSheet()->getCell("P".$i)->getValue();
                    $data['linearity']              =    $objPHPExcel->getActiveSheet()->getCell("Q".$i)->getValue();
                    $data['code']                   =    $objPHPExcel->getActiveSheet()->getCell("R".$i)->getValue();
                    $data['cuid']                   =    $userInfo;
                    $data['muid']                   =    $userInfo;
                    $data['ctime']                  =    $t;
                    $data['mtime']                  =    $t;
                    $data['mediaPlaceId']           =    $objPHPExcel->getActiveSheet()->getCell("S".$i)->getValue();
                    if(  $data['mediaPlaceId']!=null){
                        $changed=D('place')->where("mediaid = {$mediaid} and mediaplaceid= {$data['mediaPlaceId']}")->select();
                        if($changed!=null){
                            $res=D("place")->where("mediaid = {$mediaid} and mediaplaceid= {$data['mediaPlaceId']}")->save($data);
                            if($res) { $chann++;}else{$failed++;}
                                            continue;
                        }
                    }
                  
                  $res= D("place")->add($data);
              if($res) { $suss++;}else{$failed++;}
                   
              }else{
                  $failed++;
                    $mes .= $i.",";
                }
            }
            
            $this->assign("place_mes",$mes);
            $this->redirect('/Place/index',array("place_mes"=>$mes,"suss"=>$suss,"failed"=>$failed,"chann"=>$chann));
        }else
        {
            $this->error("请选择上传的文件");
        }
    }
    
     public function setStatus() {
       $id = $_GET['id']==""?0:$_GET['id'];
            if($id){
                //查询当前媒体信息
                $palce = D("place")->where("id = {$id}")->find();
                //修改状态
                if(I('type') == "2"){
                    $msg = array();
                    $palce['status'] = $palce['status']=="1"?"2":"1";
                    $result = D("place")->where("id = {$id}")->save(array("status"=>$palce['status']));
                    if($result>0 || $result ===0) $msg['status'] = "200";
                    $this->ajaxReturn($msg);
                }
    
}
}
public function checkMediaPlaceId() {

            $msg = array();
        $sellerId = $_GET['sellerId'] == "" ? 0 : $_GET['sellerId'];
        $MediaPlaceId = $_GET['MediaPlaceId'] == "" ? 0 : $_GET['MediaPlaceId'];
        $place = D("Place")->where('sellerId =' . $sellerId . ' and MediaPlaceId=' . $MediaPlaceId)->find();
       

        $place == null ? $msg['status'] = '1' : $msg['status'] = '0';
         if($_GET['id']){
            $result = D("Place")->where('sellerId =' . $sellerId . ' and MediaPlaceId=' . $MediaPlaceId.' and id='.$_GET['id'])->find();
            if($result){
                $msg['status']='1';
            }
        }
        
        $this->ajaxReturn($msg);
    }
    public function getCode(){
        $pid = $_GET['id'];
        $place = D("place")->where("id = {$pid}")->getField("id,width,height,name");
        $this->ajaxReturn($place);
    }
}