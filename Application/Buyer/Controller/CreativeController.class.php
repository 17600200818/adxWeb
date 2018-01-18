<?php

namespace Buyer\Controller;
/**
 * 素材审核控制器
 */
class CreativeController extends BaseController {
    public $row = 15;
    /**
     *素材审核列表页
     */
    public function index() {
        $dspList = D("Advertiser")->getDspList();
        $fileTypes = C('FILE_EXT');
        if(!empty($_POST)){
            $where = "idBuyer = ".$_SESSION['buyer']['userInfo']['id']." ";
            if(isset($_POST['flag']) && $_POST['flag'] == 'search'){
                $page = 1;
            }else{
                $page = isset($_POST['page'])?$_POST['page']:1;
            }
            $buyerCrid = isset($_POST['buyerCrid'])?$_POST['buyerCrid']:'';
            $cretiveId = isset($_POST['creativeId'])?$_POST['creativeId']:'';
            $idBuyer = isset($_POST['idBuyer'])?$_POST['idBuyer']:'';
            $idAdvertiser = isset($_POST['idAdvertiser'])?$_POST['idAdvertiser']:'';
            $width = isset($_POST['width'])?$_POST['width']:'';
            $height = isset($_POST['height'])?$_POST['height']:'';
            $status = isset($_POST['status'])?$_POST['status']:'';
            $startDate = isset($_POST['start_time']) && $_POST['start_time'] !=''?$_POST['start_time']:'';
            $endDate = isset($_POST['end_time']) && $_POST['end_time'] !=''?$_POST['end_time']:'';
            $filetype = isset($_POST['filetype_str'])?$_POST['filetype_str']:'';
            $category = isset($_POST['category'])?$_POST['category']:'';
            $creativeUrl = isset($_POST['creativeUrl'])?$_POST['creativeUrl']:'';
            if($buyerCrid != ''){
                $where .= ' and buyerCrid = '.$buyerCrid;
            }
            if($cretiveId != ''){
                $where .= ' and id = '.$cretiveId;
            }
            if($idBuyer != ''){
                $where .= ' and idBuyer = '.$idBuyer;
            }
            if($idAdvertiser != ''){
                $where .= ' and advertiserId = '.$idAdvertiser;
            }
            if($width != ''){
                $where .= ' and width = '.$width;
            }
            if($height != ''){
                $where .= ' and height = '.$height;
            }
            if($status != ''){
                $where .= ' and status = '.$status;
            }
            if ($creativeUrl != '') {
                $where .= " and url like '%{$creativeUrl}%'";
            }
            if($category != '-' && $category != '' ){
                $category_arr = explode("-",$category);
                $category1 = $category_arr[0];
                $category2 = $category_arr[1];
                $where .= ' and category1 = '.$category1 .' and category2 ='.$category2;
            }
            if($filetype != ''){
                $where .= ' and fileExt in ("';
                $file_arr = explode(",",$filetype);
                foreach($file_arr as $k => $v){
                    if($k == count($file_arr) - 1){
                        $where .= $v.'")';
                    }else{
                        $where .= $v.'","';
                    }

                }

            }
            if($startDate != '' &&  $endDate != ''){
                if(strtotime($startDate) > time()){
                    $msg['status'] = 'error';
                    $this->ajaxReturn($msg);
                }
                //BETWEEN value1 AND value2
                $where .= " and ctime >= '".date("Y-m-d H:i:s",strtotime($startDate))."' and ctime <= '".(date("Y-m-d H:i:s",strtotime($endDate))+24*3600)."'";
            }
            $list = D("Creative")->getcreativelist($where,$page,$this->row);
            $res = array();
            if(!empty($list)){
                foreach($list['data'] as $k => $v){
                    switch($v['status']){
                        case "1":
                            $v['status'] = '待审核';
                            break;
                        case "2":
                            $v['status'] = '通过';
                            break;
                        case "3":
                            $v['status'] = '未通过';
                            break;
                    }
                    $v['buyerName'] = $dspList[$v['idbuyer']]['company'];
                    $v['size'] = $v['width']."*".$v['height'];
                    $res[$k] = $v;
                }

            }else{

            }
            $msg['status'] = 'ok';
            $msg['data'] = $res;
            $pages = ceil($list['count']/$this->row);
            $msg['page'] = $pages;
            $msg['nowPage'] = $page;
            $this->ajaxReturn($msg);

        }

        $isStatusInfo = isAllow('advertiser', 'statusInfo');
        $isAudit = isAllow('creative', 'audit');
        $isMulitAudit = isAllow('creative', 'mulitAudit');


        $startTime = date('Y-m-01', strtotime('-1 month'));
        $endTime = date('Y-m-d');
        $category = D("SysIndustryCategory")->getCagegory();
        $advertisers = D('Advertiser')->where("idBuyer = ".$_SESSION['buyer']['userInfo']['id'])->select();
        $this->assign('advertisers', $advertisers);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign("dspList",$dspList);
        $this->assign("category",$category['c1']);
        $this->assign("fileTypes",$fileTypes);
        $this->assign("isStatusInfo",$isStatusInfo);
        $this->assign("isAudit",$isAudit);
        $this->assign("isMulitAudit",$isMulitAudit);
        $this->display();
    }

    /*
     * 素材详细页面
     */
    public function info(){
        $msg['status'] = 'error';
        $id = isset($_POST['id'])?$_POST['id']:"";
        if(empty($id)){
            $this->ajaxReturn($msg);
        }
        $list = D("Creative")->getCreativeDetail($id);
        $dspList = D("Advertiser")->getDspList();
        $advertiserList = D("Advertiser")->getAdvertiserList($list['idbuyer']);
        $buyerId = $list['idbuyer'];
        $list['idbuyer'] = $dspList[$list['idbuyer']]['company'];
        $list['idadvertiser'] = $advertiserList[$list['advertiserid']]['name'];
        $cats = D('SysIndustryCategory')->getCagegory();
        $list['category1'] = $cats['c1'][$list['category1']]['n1'];
        $list['category2'] = $cats['c2'][$list['category2']]['n2'];
        $audit = D("CreativeAudit")->getcreativeaudit($list['id'],$buyerId);
        foreach($audit as $k=>$v){
            switch($v['status']){
                case "1":
                    $audit[$k]['status'] = '待上传';
                    break;
                case "2":
                    $audit[$k]['status'] = '待审核';
                    break;
                case "3":
                    $audit[$k]['status'] = '审核通过';
                    break;
                case "4":
//                    $audit[$k]['status'] = '<span data-rel="popover" title="'.$v['crid'].'" data-content="'.$v['errorid'].': '.$v['remark'].'">审核不通过</span>';
                    $audit[$k]['status'] = '<span data-rel="popover" title="'.$v['errorid'].': '.$v['remark'].'" data-content="'.$v['errorid'].': '.$v['remark'].'">审核不通过</span>';
                    break;
                case "5":
                    $audit[$k]['status'] = '停用';
                    break;
                case "6":
                    $audit[$k]['status'] = '待重新上传';
                    break;
            }
            switch($v['allow']){
                case "1":
                    $audit[$k]['allow'] = '通过';
                    break;
                case "2":
                    $audit[$k]['allow'] = '拒绝';
                    break;
            }
        }
        if($list){
            $msg['status'] = 'ok';
            $msg['data']['creative_info'] = $list;
            $msg['data']['creative_upload'] = $audit;
        }
        $this->ajaxReturn($msg);
    }

    /*
     * 设置素材状态
     */
    public function audit(){
        $msg['status'] = 'error';
        if(!empty($_POST)){
            $id = isset($_POST['id'])?$_POST['id']:"";
            $status = isset($_POST['status'])?$_POST['status']:"";
            if($id != "" && $status != ""){
                $data['status'] = $status;
                $data['muid'] = $_SESSION['buyer']['userInfo']['id'];
                $data['mtime'] = date("Y-m-d H:i:s",time());
                $res = D("Creative")->setStatus($id,$data);
                if($res){
                    $msg['status'] = 'ok';
                }
            }
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

        $res = D("CreativeAudit")->setAuditStatus($id,$status);
        if($res){
            $msg['status'] = 'ok';
        }
        $this->ajaxReturn($msg);
    }



}
