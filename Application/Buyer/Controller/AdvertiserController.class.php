<?php

namespace Buyer\Controller;
/**
 * 广告主审核控制器
 */
class AdvertiserController extends BaseController {
    /**
     *广告主审核列表页
     */
    public function index(){
        if(!empty($_POST)){
            $time = time();
            $sidx = $_POST['sidx'] != ''?$_POST['sidx']:'id';
            $sord = $_POST['sord'] != ''?$_POST['sord']:'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:15;
            $sort = $sidx." ".$sord;
//            $idbuyeradvertiser = I('idbuyeradvertiser');
            $id = I('id');
            $name = I('name');
            $status = I('status');
            $start_time = I('start_time');
            $end_time = I('end_time');
            $time = strtotime($start_time);
            $where = " 1";
            $dspID = $_SESSION['buyer']['userInfo']['id'];
            $where.= ' and idBuyer = '.$dspID;
            if($id != ''){
                $where.= ' and id ='.$id;
            }
            if($name != ''){
                $where.= " and name  like '".$name."%'";
            }
            if($status != ''){
                $where .= ' and status ='.$status;
            }
            if($start_time != '' &&  $end_time != ''){
                if(strtotime($start_time) > $time || strtotime($end_time) < $time ){
                    $msg['status'] = 'error';
                    $this->ajaxReturn($msg);
                }
                //BETWEEN value1 AND value2
                $where .= " and ctime >= '".date("Y-m-d H:i:s",strtotime($start_time))."' and ctime <= '".date("Y-m-d H:i:s",strtotime($end_time))."'";
            }
            $res =  D("Advertiser")->advertiser_list($sort,$where,$page,$rows);
            $dspList = D("Advertiser")->getDspList();
            $data = array();
            //判断用户是否拥有媒体审核状态权限
            $isSetAuditStatus = isAllow('advertiser', 'setAuditStatus');

            foreach($res['data'] as $k => $v){
                $data[$k]['id'] = $v['id'];
                $data[$k]['idbuyer'] = $dspList[$v['idbuyer']]['company'];
                $data[$k]['idbuyeradvertiser'] = $v['idbuyeradvertiser'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['sitename'] = $v['sitename'];
                $data[$k]['domain'] = $v['domain'];
                $data[$k]['ctime'] = $v['ctime'];
                $data[$k]['issetauditstatus'] = $isSetAuditStatus;
                switch($v['status']){
                    case '1':
                        $status = '待审核';
                        break;
                    case '3':
                        $status = '未通过';
                        break;
                    case '2':
                        $status = '通过';
                        break;
                }
                $data[$k]['status'] = $status;
            }

            //rows":15,"record":95,"page":1,"total":7,"sord":null,"sidx":null,"search":null
            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = $res['count'];
            $this->ajaxReturn($result);

        }

        $advertiserList = D('Advertiser')->getAdvertiserList($_SESSION['buyer']['userInfo']['id']);
        $this->assign("advertiserList",$advertiserList);
        $this->display();
    }


    /*
     * 广告主修改
     */
    public function edit(){
        $id = isset($_GET['id'])?$_GET['id']:"";
        $info = D("Advertiser")->advertiser_detail($id);
        $dspList = D("Advertiser")->getDspList();
        if(!empty($_POST)){
            $msg['status'] = 'error';
            $id = isset($_POST['id'])?$_POST['id']:'';
            $AdvertiserName = isset($_POST['AdvertiserName'])?$_POST['AdvertiserName']:'';
            $SiteName = isset($_POST['SiteName'])?$_POST['SiteName']:'';
            $domain = isset($_POST['domain'])?$_POST['domain']:'';
            $address = isset($_POST['address'])?$_POST['address']:'';
            $category1 = isset($_POST['category1'])?$_POST['category1']:'';
            $category2 = isset($_POST['category2'])?$_POST['category2']:'';
            if($id == '' || $AdvertiserName == '' || $SiteName == '' || $domain == '' || $address == '' || $category1 == '' || $category2 == ''){
                $msg['error_info'] = '参数不完整！';
                $this->ajaxReturn($msg);
            }
            $data['name'] = $AdvertiserName;
            $data['siteName'] = $SiteName;
            $data['domain'] = $domain;
            $data['address'] = $address;
            $data['category1'] = $category1;
            $data['category2'] = $category2;
            $res = D("Advertiser")->edit($id,$data);
            //$sql = D("Advertiser")->getLastSql();
            if($res){
                $msg['status'] = 'ok';
            }
            $this->ajaxReturn($msg);

        }
        $info['idbuyer'] = $dspList[$info['idbuyer']]['company'];
        $category = D("SysIndustryCategory")->getCagegory();
        $this->assign("category",$category);
        $this->assign('info',$info);
        $this->display();
    }


    /*
     * 获取广告主资质列表
     */

    public function advertiserFile(){
        $sidx = $_POST['sidx'] != ''?$_POST['sidx']:'id';
        $sord = $_POST['sord'] != ''?$_POST['sord']:'desc';
        $sort = $sidx." ".$sord;
        $idbuyeradvertiser = isset($_POST['advertiserId'])?$_POST['advertiserId']:'';
        $list = D('AdvertiserFile')->getAdaudit($idbuyeradvertiser,$sort);
        foreach($list as $k => $v){
            $list[$k]['filePath'] = '/Public/uploads/advertiser/'.$v['filepath'];
            switch($v['status']){
                case '1':
                    $list[$k]['status'] = '待审核';
                    break;
                case '3':
                    $list[$k]['status'] = '未通过';
                    break;
                case '2':
                    $list[$k]['status'] = '通过';
                    break;
            }
        }
        $result['data'] = $list;
        $this->ajaxReturn($result);
    }


    /*
     * 设置资质审核状态
     */
    public function audit(){
        $msg['status'] = 'error';
        $id = isset($_POST['id'])?$_POST['id']:"";
        $status = isset($_POST['status'])?$_POST['status']:"";
        if($id != '' && $status != ''){
            switch($status){
                case '通过':
                    $status = 2;
                    break;
                case '未通过':
                    $status = 3;
                    break;
            }
            $data['status'] = $status;
            $data['muid'] = $_SESSION['buyer']['userInfo']['id'];
            $data['mtime'] = date("Y-m-d H:i:s",time());
            $res = D("AdvertiserFile")->setAuditStatus($id,$data);
            if($res){
                $msgf['status'] = 'ok';
                $this->ajaxReturn($msg);
            }
        }else{
            $this->ajaxReturn($msg);
        }
    }


    /*
     * 设置广告主状态
     */
    public function setStatus(){
        $msg['status'] = 'error';
        $idbuyeradvertiser = I('id');
        $status = I("status");
        if($status !== ''){
            $data['status'] = $status;
        }else{
            $this->ajaxReturn($msg);
        }
        $res = D("Advertiser")->setStatus($idbuyeradvertiser,$data);
        if($res){
            $msg['status'] = 'ok';
        }
        $this->ajaxReturn($msg);

    }

    /*
     * 广告主资质上传
     */
    public function upload(){
        $msg['status'] = 'error';
        $id = isset($_POST['id'])?$_POST['id']:"";
        //存到本地目录，按照时间分级创建目录
        $sdir = "./Public/uploads/advertiser/".date("Y/m/d/");
        if(is_dir($sdir)){
        }else{
            if (mkdirs($sdir)){
            }else{
            }
        }
        $fileName = md5($id.'_'.time().'_'.rand(1,1000)).'.jpg';
        $destination = $sdir.$fileName; //获取文件地址及名称
        $move_file = move_uploaded_file($_FILES['file']['tmp_name'],$destination); //文件存储到本地
        if($move_file) {
            $code = isset($_POST['code'])?$_POST['code']:"";
            $name = isset($_POST['name'])?$_POST['name']:"";
            if($id == '' || $code== '' || $name == ''){
                $this->ajaxReturn($msg);
            }
            $idBuyer = D("Advertiser")->advertiser_detail($id);
            $data['idAdvertiser'] = $id;
            $data['idBuyer'] = $idBuyer['idbuyer'];
            $data['filePath'] = date("Y/m/d/").$fileName;
            $data['code'] = $code;
            $data['name'] = $name;
            $data['cuid'] = $_SESSION['buyer']['userInfo']['id'];
            $data['ctime'] = date("Y-m-d H:i:s",time());
            $data['status'] = 1;
            $res = D("AdvertiserFile")->upload($data);
            if($res){
                $msg['status'] = 'ok';
            }
            $this->ajaxReturn($msg);
        }
    }

    public function statusInfo(){
        $msg['status'] = 'error';
        $id = isset($_POST['id'])?$_POST['id']:"";
        if(empty($id)){
            $this->ajaxReturn($msg);
        }
        $info = D("AdvertiserAudit")->getStatus($id);
        //$sql = D("AdvertiserAudit")->getLastSql();
        foreach($info as $k=>$v){
            switch($v['status']){
                case "1":
                    $info[$k]['status'] = '待上传';
                    break;
                case "2":
                    $info[$k]['status'] = '待审核';
                    break;
                case "3":
                    $info[$k]['status'] = '审核通过';
                    break;
                case "4":
                    $info[$k]['status'] = '审核不通过';
                    break;
                case "5":
                    $info[$k]['status'] = '停用';
                    break;
                case "6":
                    $info[$k]['status'] = '待重新上传';
                    break;
            }
            switch($v['allow']){
                case "1":
                    $info[$k]['allow'] = '允许';
                    break;
                case "2":
                    $info[$k]['allow'] = '不允许';
                    break;
            }
        }
        if($info){
            $msg['status'] = 'ok';
            $msg['data'] = $info;
        }else{
            $msg['status'] = 'ok';
            $msg['data'] = array();
        }
        $this->ajaxReturn($msg);
    }

    public function setAuditStatus(){
        $msg['status'] = 'error';
        $id = isset($_POST['id'])?$_POST['id']:"";
        $status= isset($_POST['status'])?$_POST['status']:"";
        if($id == '' || $status == ''){
            $this->ajaxReturn($msg);
        }

        $res = D("AdvertiserAudit")->setAuditStatus($id,$status);
        if($res){
            $msg['status'] = 'ok';
        }
        $this->ajaxReturn($msg);
    }


}
