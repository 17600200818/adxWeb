<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class SellerRouteController extends BaseController{

    public function index(){
        $result = array();
        if(IS_POST){
        }else{
            $idSeller = $_GET['id'];
            $routes = D("route")->where("idSeller = {$idSeller} and idMedia = 0 and idPlace = 0")->select();
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
            $this->assign("id",$idSeller);
        }
        $this->display();
    }

    public function add(){
        $userInfo = $_SESSION["userInfo"]['id'];
        $result = array();
        if(IS_POST){
            $name =  $_POST['name'];
            $result = ['status' => 'error', 'msg' => ''];
            if(D("route")->where("idSeller = '{$_REQUEST["idSeller"]}' and idMedia = '0' and idPlace='0' and level = '{$_REQUEST["level"]}'")->find()){
                $this->error("该路由已经存在");
            }

            // 判断psd和adn是否全选  如果全选  存数据库-1     start
            D("buyer")->idSelect();
            // 判断psd和adn是否全选  如果全选  存数据库-1  end

            $t = date("Y-m-d H:i:s",time());
            $data = [
                'name' => $_REQUEST['name'],
                'idSeller' => $_REQUEST['idSeller'],
                'idMedia' => 0,
                'idPlace' => 0,
                'level' => $_REQUEST['level'],
                'dspIds' => $_REQUEST['dspIds'],
                'adnIds' => $_REQUEST['adnIds'],
                'status' => 1,
                'cuid' => $_SESSION["userInfo"]['id'],
                'ctime' => $t,
            ];

            if(D("route")->add($data) == false){
                $this->error("添加失败");
            }else{
                $this->redirect("/sellerRoute/index/id/{$_REQUEST['idSeller']}");
            }
            $this->ajaxReturn($result);
            exit;
        }else{
            $id = $_GET['id'];
            if($id){
                $seller = D("seller")->where("id = '{$id}'")->find();
//                $buyers = D("buyer")->where('status = 2')->select();
                $buyers = D("buyer")->select();
                $result['buyers'] = $buyers;
                $result['email'] = $seller['email'];
                $result['company'] = $seller['company'];
                $result['id_seller'] = $id;
                $result['id'] = $id;
            }
        }
        $this->assign("result",$result);
        $this->display();
    }

    function edit(){
        $id = I('id');
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            $editRoute = D('Route')->where("id = $id")->find();
            $sellerId = $editRoute['idseller'];
            $mediaId = $editRoute['idmedia'];
            $placeId = $editRoute['idplace'];
            $name = I('name');
            $route = D("route")->where("level = {$_REQUEST['level']} and idSeller = $sellerId and idMedia = $mediaId and idPlace = $placeId")->select();
            foreach ($route as $v) {
                if($v){
                    if($v['id'] != $id)
                        $this->error('该级别已存在路由');
                }
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
                $this->redirect("/sellerRoute/index?id={$_REQUEST['idSeller']}");
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
//                $buyers = D("buyer")->where('status = 2')->select();
                $buyers = D("buyer")->select();


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

                //显示
                $result['buyers'] = $buyers;
                $result['email'] = $seller['email'];
                $result['company'] = $seller['company'];
                $result['id_seller'] = $seller['id'];
                $result['level'] = $route['level'];
                $result['name'] = $route['name'];
                $result['id'] = $id;
            }
            $this->assign("result",$result);
        }
        $this->display();
    }
}