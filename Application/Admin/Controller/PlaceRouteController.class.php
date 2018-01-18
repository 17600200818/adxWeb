<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class PlaceRouteController extends BaseController{

    public function index(){
        $result = array();
        if(IS_POST){
        }else{
            $idPlace = $_GET['id'];
            $routes = D("route")->where("idPlace = {$idPlace}")->select();
            $place=D("place")->where("id={$_GET['id']}")->find();
            $placename=$place['name'];
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
                $routes[$key]['placename'] = $placename;
                $routes[$key]['num'] = $key+1;
            }
            $this->assign("routes",json_encode($routes));
            $this->assign("id",$idPlace);
        }
        $editAllow = isAllow('PlaceRoute', 'edit') ? 1 : 0;
        $isAddAllow = isAllow('PlaceRoute', 'add') ? 1 : 0;
        $this->assign('isSetStatus', $editAllow);
        $this->assign('isAddAllow', $isAddAllow);
        $this->assign('editAllow', $editAllow);
        $this->display();
    }

    public function add(){
        $userInfo = $_SESSION["userInfo"]['id'];
        $result = array();
        if(IS_POST){
            $name =  $_POST['name'];
            $result = ['status' => 'error', 'msg' => ''];
            if(D("route")->where("idPlace = '{$_REQUEST["idPlace"]}' and idMedia = '{$_REQUEST["idMedia"]}' and idSeller='{$_REQUEST["idSeller"]}' and level = '{$_REQUEST["level"]}'")->find()){
                $this->error("该路由已经存在");
            }
             if(D("route")->where("idPlace = '{$_REQUEST["idPlace"]}' and  level = '{$_REQUEST["level"]}'")->find()){
                
                    $this->error('该广告位路由级别'.$_REQUEST["level"].'已经存在');
            }

            // 判断psd和adn是否全选  如果全选  存数据库-1     start
            D("buyer")->idSelect();
            // 判断psd和adn是否全选  如果全选  存数据库-1  end

            $t = date("Y-m-d H:i:s",time());
          
            $data = [
                'name' => $_REQUEST['name'],
                'idSeller' => $_REQUEST['idSeller'],
                'idMedia' =>$_REQUEST['idMedia'],
                'idPlace' =>$_REQUEST['idPlace'],
                'level' => $_REQUEST['level'],
                'dspIds' => $_REQUEST['dspIds'],
                'adnIds' => $_REQUEST['adnIds'],
                
                'status' => 1,
                'cuid' => $_SESSION["userInfo"]['id'],
                'ctime' => $t,
            ];
                $data['gainType']=$_REQUEST['gainType']?$_REQUEST['gainType']:0;
                $data['gainRate']=$_REQUEST['gainRate']?$_REQUEST['gainRate']:0;
            if(D("route")->add($data) == false){
                $this->error("添加失败");
            }else{
                $this->redirect("/PlaceRoute/index/id/{$_REQUEST['idPlace']}");
            }
            $this->ajaxReturn($result);
            exit;
        }else{
            $id = $_GET['id'];
            if($id){
                $place = D("place")->where("id = {$id}")->find();
                $seller=D('seller')->where("id={$place[sellerid]}")->find();
                $media=D('media')->where("id={$place[mediaid]}")->find();
//               status = 2
                $buyers = D("buyer")->where('1')->select();
                $result['buyers'] = $buyers;
                $result['media'] = $media['name'];
                $result['place'] = $place['name'];
                $result['company'] = $seller['company'];
                $result['id_seller'] = $place['sellerid'];
                $result['id_place'] = $id;
                
                $result['id_media']=$place['mediaid'];
            }
        }
       $isAllowGain=$this->isAllowGain($_SESSION['userInfo']['id'])?1:0;
        $this->assign("isAllowGain",$isAllowGain);
        $this->assign("result",$result);
        $this->display();
    }

    function edit(){
        $id = I('id');
        $userInfo = $_SESSION["userInfo"]['id'];
      
      
        if(IS_POST){
            $name = I('name');
//            $route = D("route")->where("level = '{$level}' and  idplace = '{$idplace}' ")->find();
          
            if(D("route")->where("idPlace = '{$_REQUEST["idPlace"]}' and  level = '{$_REQUEST["level"]}' and id!='{$id}'")->find()){
                
                    $this->error('该广告位路由级别'.$_REQUEST["level"].'已经存在');
            }
//            if($route){
//                if($route['id'] != $id)
//                    $this->error('该广告位路由级别'.$level.'已经存在');
//            }

            // 判断psd和adn是否全选  如果全选  存数据库-1     start
            D("buyer")->idSelect();
            // 判断psd和adn是否全选  如果全选  存数据库-1  end

            $data['gainType']=$_REQUEST['gainType']?$_REQUEST['gainType']:0;
                $data['gainRate']=$_REQUEST['gainRate']?$_REQUEST['gainRate']:0;
            $data['level'] = $_REQUEST['level'];
            $data['name'] = $_REQUEST['name'];
            $data['dspIds'] = $_REQUEST['dspIds'];
            $data['adnIds'] = $_REQUEST['adnIds'];
            $data['muid']    = $userInfo;
            $data['mtime']    = date("Y-m-d H:i:s",time());
            $result = D("route")->where("id = {$id}")->save($data);
            if($result>0 || $result ===0){
                $this->redirect("/PlaceRoute/index?id={$_REQUEST['idPlace']}");
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


                $seller = D("seller")->where("id = '{$route["idseller"]}'")->find();
//                status = 2
                $buyers = D("buyer")->where('1')->select();


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
//                    $where1['status'] = "2";
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
                $place=D('place')->where("id={$route['idplace']}")->find();
                $media=D('media')->where("id={$route['idmedia']}")->find();
                //显示
                $result['buyers'] = $buyers;
                $result['email'] = $seller['email'];
                $result['company'] = $seller['company'];
                $result['id_seller'] = $seller['id'];
                $result['level'] = $route['level'];
                $result['name'] = $route['name'];
                $result['id_media']=$route['idmedia'];
                $result['id_place']=$route['idplace']; 
                $result['gaintype']=$route['gaintype']; 
                $result['gainrate']=$route['gainrate']; 
                $result['medianame']=$media['name'];
                $result['placename']=$place['name'];
                $result['id'] = $id;
            }
            $this->assign("result",$result);
        }
         $isAllowGain=$this->isAllowGain($_SESSION['userInfo']['id'])?1:0;
        $this->assign("isAllowGain",$isAllowGain);
        $this->display();
    }
    protected function isAllowGain($id){
         $powerItems = D('User')->table('v_user')->join('left join v_role_power_items as pi on v_user.idRole = pi.idRole')->where('v_user.id = '.$id.' and pi.roleStatus = 1 and pi.idPowerItem=343' )->select();
        
        if ($powerItems) {
            return true;
        }
    
    return false;
    }
    
    
}
