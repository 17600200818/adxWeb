<?php

namespace Admin\Controller;
/**
 * 路由控制器
 */
class RouteController extends BaseController {
    /**
     * 媒体列表
     */
    public function index(){
        $result = array();
        if(IS_POST){
        }else{
            $idMedia = $_GET['id'];
            $routes = D("route")->where("idMedia = {$idMedia}")->select();
            foreach ($routes as $key => $value) {
                
                    //dsp
                    $dspids = $value['dspids'];
                    $dspids_arr = explode(",",$dspids);
                    $where['id'] = array("in",$dspids_arr);
                    $dsp_buyers = D("buyer")->where($where)->select();
                    
                    //adn
                    $adnids = $value['adnids'];
                    $adnidss_arr = explode(",",$adnids);
                    $where2['id'] = array("in",$adnidss_arr);
                    $adn_buyers = D("buyer")->where($where2)->select();
                    
                    
                    $dsp_company = "";
                    $adn_company = "";
                    foreach ($dsp_buyers as $val2){
                        $dsp_company  =  $dsp_company."".$val2['company'].",";
                    }
                    foreach ($adn_buyers as $val2){
                        $adn_company  =  $adn_company."".$val2['company'].",";
                    }
                    
                    if($value['dspids'] == "-1"){
                        $routes[$key]['dsp_company'] = "全部";
                    }else{
                        $routes[$key]['dsp_company'] = $dsp_company==""?"无":$dsp_company;
                    }
                    
                    if($value['adnids'] == "-1"){
                        $routes[$key]['adn_company'] = "全部";
                    }else{
                        $routes[$key]['adn_company'] = $adn_company==""?"无":$adn_company;
                    }
                    $routes[$key]['num'] = $key+1;
            }
            $this->assign("routes",json_encode($routes));
            $this->assign("id",$idMedia);
        }
        $this->display();
    }
    
    
    /**
     *  路由添加
     */
    public function add(){
        $userInfo = $_SESSION["userInfo"]['id'];
        $result = array();
        if(IS_POST){
            $name =  $_POST['name'];
            if(D("route")->where("idSeller = '{$_REQUEST["idSeller"]}' and idMedia = '{$_REQUEST["idMedia"]}' and idPlace='0' and level = '{$_REQUEST["level"]}'")->find())
                   $this->redirect("/Route/add?id={$_REQUEST['idMedia']}",array("status"=>"300"));
            
                   
            // 判断psd和adn是否全选  如果全选  存数据库-1     start
            D("buyer")->idSelect();
            // 判断psd和adn是否全选  如果全选  存数据库-1  end
            
            
            
            $t = date("Y-m-d H:i:s",time());
            $_REQUEST['status'] = "1";
            $_REQUEST['idPlace'] = "0";
            $_REQUEST['muid'] = $userInfo;
            $_REQUEST['cuid'] = $userInfo;
            $_REQUEST['mtime'] = $t;
            $_REQUEST['ctime'] = $t;
            
            if(D("route")->add($_REQUEST)){
                $this->redirect("/Route/index?id={$_REQUEST['idMedia']}");
            }else{
                $this->error("系统错误:添加路由错误~");
            }
        }else{
            $id = $_GET['id'];
            if($id){
                $media = D("media")->where("id = {$id}")->find();
                $seller = D("seller")->where("id = '{$media["sellerid"]}'")->find();
                $sellerSonId = D("seller")->where("id = '{$media["sellersonid"]}'")->find();
                if($sellerSonId){
                    $result['parent_company'] = $seller['company'];
                    $result['company'] = $sellerSonId['company'];
                }else{
                    $result['company'] = $seller['company'];
                    $result['parent_company'] = "0";
                }
                
                $buyers = D("buyer")->where('status = 2')->select();
                $result['buyers'] = $buyers;
                $result['email'] = $seller['email'];
                
                $result['id_seller'] = $seller['parentid']!="0"?$seller['parentid']:$seller['id'];
                $result['name'] = $media['name'];
                $result['id'] = $media['id'];
            }
        }
        $this->assign("result",$result);
        $this->display();
    }
    
    
    /**
     * 路由修改
     */
    function edit(){
        $id = I('id');
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            $name =  I['name'];
            $route = D("route")->where("name = '{$name}'")->find();
            if($route){
                if($route['id'] != $id)
                       $this->redirect("/Route/add?id={$route['idmedia']}",array("status"=>"300"));
            }
            
            // 判断psd和adn是否全选  如果全选  存数据库-1     start
            D("buyer")->idSelect();
            // 判断psd和adn是否全选  如果全选  存数据库-1  end
            
            
            $data['level'] = $_REQUEST['level'];
            $data['name'] = $_REQUEST['name'];
            $data['dspIds'] = $_REQUEST['dspIds'];
            $data['adnIds'] = $_REQUEST['adnIds'];
            $data['muid']    = $userInfo;
            $data['mtime']    = date("Y-m-d H:i:s",time());
            $result = D("route")->where("id = {$id}")->save($data);
            if($result>0 || $result ===0){
                $this->redirect("/Route/index?id={$_REQUEST['media_id']}");
            }else{
                $this->error("系统错误:修改路由错误~");
            }
        }else{
            if($id){
                
                //关联查询路由表 媒体表  媒体账户表 信息
                $route = D("route")->where("id = {$id}")->find();
                
                //修改状态
                if(I('type') == "2"){
                    $msg = array();
                    $route['status'] = $route['status']=="1"?"2":"1";
                    $result = D("route")->where("id = {$id}")->save(array("status"=>$route['status']));
                    if($result>0 || $result ===0) $msg['status'] = "200";
                    $this->ajaxReturn($msg);
                }
                
                
                $media = D("media")->where("id = {$route['idmedia']}")->find();
                $seller = D("seller")->where("id = '{$media["sellerid"]}'")->find();
                $sellerSonId = D("seller")->where("id = '{$media["sellersonid"]}'")->find();
                if($sellerSonId){
                    $result['parent_company'] = $seller['company'];
                    $result['company'] = $sellerSonId['company'];
                }else{
                    $result['company'] = $seller['company'];
                    $result['parent_company'] = "0";
                }
                
                $buyers = D("buyer")->where('status = 2')->select();
                
                
                //过滤已经选择的列表
                foreach ($buyers as $key => $val){
                    if($val['buytype'] == "1" && $route['dspids'] == "-1"){
                        unset($buyers[$key]);
                        continue;
                    }
                    if($val['buytype'] == "2" && $route['adnids'] == "-1"){
                        unset($buyers[$key]);
                        continue;
                    }
                    
                    
                    if(strstr($route['dspids'],$val['id']) || strstr($route['adnids'],$val['id'])){
                        unset($buyers[$key]);
                        continue;
                    }
                }
                
                //查出已选择的dsp列表
                if($route['dspids']){
                    if($route['dspids'] != "-1"){
                        $where1['id'] = array("in",$route['dspids']);
                    }else{
                        $where1['buyType'] = 1;
                    }
                    $where1['status'] = "2";
                    $result['dsps'] = D("buyer")->where($where1)->select();
                }
                
                //查出已选择的adn列表
                if($route['adnids']){
                    if($route['adnids'] != "-1"){
                        $where2['id'] = array("in",$route['adnids']);
                    }else{
                        $where2['buyType'] = 2;
                    }
                    
                    $result['adns'] = D("buyer")->where($where2)->select();
                }
                
                //显示
                $result['buyers'] = $buyers;
                $result['email'] = $seller['email'];
                $result['id_seller'] = $seller['id'];
                $result['media_name'] = $media['name'];
                $result['media_id'] = $media['id'];
                $result['level'] = $route['level'];
                $result['name'] = $route['name'];
                $result['id'] = $id;
            }
            $this->assign("result",$result);
        }
        $this->display();
    }
}
