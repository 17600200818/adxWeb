<?php

namespace Admin\Controller;
/**
 * 广告主审核控制器
 */
class PmpController extends BaseController {

    /**
     *PMP列表页
     */
    public function index() {
        if(!empty($_POST)){
            $time = time();
            $sidx = $_POST['sidx'] != ''?$_POST['sidx']:'id';
            $sord = $_POST['sord'] != ''?$_POST['sord']:'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:15;
            $sort = $sidx." ".$sord;
            $id = I('id');
            $name = I('name');
            $pmpType = I('pmpType');
            $status = I('status');
            $start_time = I('start_time');
            $end_time = I('end_time');
            $time = strtotime($start_time);
            $where = "1";
            if($id != ''){
                $where.= ' and id ='.$id;
            }
            if($name != ''){
                $where.= " and name  like '".$name."%'";
            }
            if($pmpType != ''){
                $where.= ' and pmpType ='.$pmpType;
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
            $res =  D("Pmp")->pmp_list($sort,$where,$page,$rows);
            $data = array();
            foreach($res['data'] as $k => $v){
                $data[$k]['id'] = $v['id'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['status'] = $v['status'];
                $data[$k]['pmpType'] = $v['pmptype'];
                $data[$k]['price'] = $v['price'];
                $data[$k]['level'] = $v['level'];
                switch($v['saletype']){
                    case '1':
                        $data[$k]['saleType'] = '最高价';
                        break;
                    case '2':
                        $data[$k]['saleType'] = '第二出价';
                        break;
                    case '3':
                        $data[$k]['saleType'] = '固定价格';
                        break;
                }
                $data[$k]['startDate'] = $v['startdate'];
                $data[$k]['endDate'] = $v['enddate'];
                switch($v['pmptype']){
                    case '1':
                        $data[$k]['pmpType'] = '保价保量';
                        break;
                    case '2':
                        $data[$k]['pmpType'] = '保价不保量';
                        break;
                    case '3':
                        $data[$k]['pmpType'] = '不保价保量';
                        break;
                    case '4':
                        $data[$k]['pmpType'] = '不保价不保量';
                        break;
                }

            }
            //rows":15,"record":95,"page":1,"total":7,"sord":null,"sidx":null,"search":null
            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = $res['count'];
            $this->ajaxReturn($result);

        }
        $this->display();
    }

    /*
     * PMP修改
     */
    public function edit(){
        $id = I("id");
        $info = D("Pmp")->info($id);
        $dspList = D("Advertiser")->getDspList();
        $mediaList = D("Pmp")->getMedia($id);
        $placeList = D("Place")->getPlaceList();
        $common = A("Common");
        if(!empty($_POST)){
            $msg['status'] = 'error';
            $name = isset($_POST['name'])?$_POST['name']:'';
            $pmpType = isset($_POST['pmpType'])?$_POST['pmpType']:'';
            $price = isset($_POST['price'])?$_POST['price']:'';
            $level = isset($_POST['level'])?$_POST['level']:'';
            $saleType = isset($_POST['saleType'])?$_POST['saleType']:'';
            $startDate = isset($_POST['startDate'])?$_POST['startDate']:'';
            $endDate = isset($_POST['endDate'])?$_POST['endDate']:'';
            $hourDirect = isset($_POST['hourDirect'])?$_POST['hourDirect']:'';
            $deviceDirect = isset($_POST['deviceDirect'])?$_POST['deviceDirect']:'';
            $instDirect = isset($_POST['instDirect'])?$_POST['instDirect']:'';
            $areaDirect = isset($_POST['areaDirect'])?$_POST['areaDirect']:'';
            $areaDirect = explode(',',$areaDirect);
            $common = A("Common");
            $areaDirect = $common->getCityId($areaDirect,1);
            $dsp = isset($_POST['dsp'])?$_POST['dsp']:'';
            $size = isset($_POST['size'])?$_POST['size']:'';
            $mediaDirect = isset($_POST['mediaDirect'])?$_POST['mediaDirect']:'';
            if(!empty($mediaDirect)){
                $mediaDirectArr = explode("|",$mediaDirect);
                $mediaDirect = array();
                foreach($mediaDirectArr as $k => $v){
                    $arr = explode('-',$v);
                    $arr_deal = explode(',',$arr[1]);
                    if($arr_deal[0] == ''){
                        continue;
                    }
                    foreach($arr_deal as $k1 => $v1){
                        $mediaDirect[$mediaList['data2'][$arr[0]]][] = $v1;
                    }

                }
            }
            $adplace = isset($_POST['adplace'])?$_POST['adplace']:'';
            if($name == '' || $pmpType == '' || $price == '' || $level == '' || $saleType == '' || $startDate == '' || $endDate == ''){
                $msg['error_info'] = '信息不完整！';
            }

            $data['name'] = $name;
            $data['pmpType'] = $pmpType;
            $data['price'] = $price;
            $data['level'] = $level;
            $data['saleType'] = $saleType;
            $data['startDate'] = date("Y-m-d H:i:s",strtotime($startDate));;
            $data['endDate'] = date("Y-m-d H:i:s",strtotime($endDate));;
            if($hourDirect != ''){
                $data['hourDirect'] = json_encode(explode(',',$hourDirect));
            }
            if($deviceDirect != ''){
                $data['deviceDirect'] = $deviceDirect;
            }
            if($instDirect != ''){
                $data['instlDirect'] = json_encode(explode(',',$instDirect));
            }
            if($areaDirect != ''){
                $data['areaDirect'] = json_encode($areaDirect);
            }
            if($dsp != ''){
                $data['buyerDirect'] = json_encode($dsp);
            }
            if($size != ''){
                $data['sizeDirect'] = json_encode($size);
            }
            if($mediaDirect != ''){
                $data['sellerDealDirect'] = json_encode($mediaDirect);
            }
            if($adplace != ''){
                $place_arr = explode(',',$adplace);
                foreach($place_arr as $k => $v){
                    $place_arr[$k] = $placeList['placeName'][$v];
                }
                $data['placeDirect'] = json_encode($place_arr);
            }
            $data['muid'] = $_SESSION['userInfo']['id'];
            $data['mtime'] = date("Y-m-d H:i:s",time());
            $res = D("Pmp")->edit($data,$id);
            if($res){
                $msg['status'] = 'ok';
            }
            $this->ajaxReturn($msg);
        }
        $buyerDirect = array();
        $info['buyerdirect'] = json_decode($info['buyerdirect'],true);
        foreach($info['buyerdirect'] as $k => $v){
            $buyerDirect[$k]['company'] = $dspList[$v]['company'];
            $buyerDirect[$k]['id'] = $dspList[$v]['id'];
        }
        $sizeDirect = json_decode($info['sizedirect'],true);
        $areaDirect = json_decode($info['areadirect'],true);
        $areaDirect = $common->getCityId($areaDirect,2);
        $hourDirect = json_decode($info['hourdirect'],true);
        $info['startdate'] = date("d-m-Y",strtotime($info['startdate']));
        $info['enddate'] = date("d-m-Y",strtotime($info['enddate']));
        $list = json_decode($info['sellerdealdirect'],true);
        $sellerDealDirect = array();
        $i = 0;
        foreach($list as $k => $v){
            foreach($v as $k1 => $v1){
                $i++ ;
                $sellerDealDirect[$i]['company'] = $mediaList['data1'][$k]['company'];
                $sellerDealDirect[$i]['dealId'] = $v1;
            }
        }

        $instDirect = json_decode($info['instldirect'],true);
        $inst = C("INSTL");
        $deviceType = C("DEVICE_TYPE");
        $this->assign("hourDirect",$hourDirect);
        $this->assign("buyerDirect",$buyerDirect);
        $this->assign("sizeDirect",$sizeDirect);
        $this->assign("areaDirect",$areaDirect);
        $this->assign("sellerDealDirect",$sellerDealDirect);
        $this->assign("info",$info);
        $this->assign("instDirect",$instDirect);
        $this->assign("id",$id);
        $this->assign("mediaList",$mediaList['data1']);
        $this->assign("inst",$inst);
        $this->assign("deviceType",$deviceType);
        $this->display();
    }


    /*
     * 添加PMP
     */

    public function add(){
        $mediaList = D("Pmp")->getMedia();
        if(!empty($_POST)){
            $placeList = D("Place")->getPlaceList();
            $msg['status'] = 'error';
            $name = isset($_POST['name'])?$_POST['name']:'';
            $pmpType = isset($_POST['pmpType'])?$_POST['pmpType']:'';
            $price = isset($_POST['price'])?$_POST['price']:'';
            $level = isset($_POST['level'])?$_POST['level']:'';
            $saleType = isset($_POST['saleType'])?$_POST['saleType']:'';
            $startDate = isset($_POST['startDate'])?$_POST['startDate']:'';
            $endDate = isset($_POST['endDate'])?$_POST['endDate']:'';
            $hourDirect = isset($_POST['hourDirect'])?$_POST['hourDirect']:'';
            $deviceDirect = isset($_POST['deviceDirect'])?$_POST['deviceDirect']:'';
            $instDirect = isset($_POST['instDirect'])?$_POST['instDirect']:'';
            $areaDirect = isset($_POST['areaDirect'])?$_POST['areaDirect']:'';
            $areaDirect = explode(',',$areaDirect);
            $common = A("Common");
            $areaDirect = $common->getCityId($areaDirect);
            $dsp = isset($_POST['dsp'])?$_POST['dsp']:'';
            $size = isset($_POST['size'])?$_POST['size']:'';
            $mediaDirect = isset($_POST['mediaDirect'])?$_POST['mediaDirect']:'';
            if(!empty($mediaDirect)){
                $mediaDirectArr = explode("|",$mediaDirect);
                $mediaDirect = array();
                foreach($mediaDirectArr as $k => $v){
                    $arr = explode('-',$v);
                    $arr_deal = explode(',',$arr[1]);
                    foreach($arr_deal as $k1 => $v1){
                        $mediaDirect[$mediaList['data2'][$arr[0]]][] = $v1;
                    }
                }
            }
            $adplace = isset($_POST['adplace'])?$_POST['adplace']:'';
            if($name == '' || $pmpType == '' || $price == '' || $level == '' || $saleType == '' || $startDate == '' || $endDate == ''){
                $msg['error_info'] = '信息不完整！';
            }
            $place_arr = explode(',',$adplace);
            foreach($place_arr as $k => $v){
                $place_arr[$k] = $placeList['placeName'][$v];
            }
            $data['placeDirect'] = json_encode($place_arr);
            $data['name'] = $name;
            $data['pmpType'] = $pmpType;
            $data['price'] = $price;
            $data['level'] = $level;
            $data['saleType'] = $saleType;
            $data['startDate'] = date("Y-m-d H:i:s",strtotime($startDate));;
            $data['endDate'] = date("Y-m-d H:i:s",strtotime($endDate));;
            $data['hourDirect'] = json_encode(explode(',',$hourDirect));
            $data['deviceDirect'] = $deviceDirect;
            $data['instlDirect'] = json_encode(explode(',',$instDirect));
            $data['areaDirect'] = json_encode($areaDirect);
            $data['buyerDirect'] = json_encode($dsp);
            $data['sizeDirect'] = json_encode($size);
            $data['sellerDealDirect'] = json_encode($mediaDirect);
            $data['placeDirect'] = json_encode($place_arr);
            $data['status'] = 1;
            $data['cuid'] = $_SESSION['userInfo']['id'];
            $data['ctime'] = date("Y-m-d H:i:s",time());
            $res = D("Pmp")->add($data);
            if($res){
                $msg['status'] = 'ok';
            }
            $this->ajaxReturn($msg);
        }
        $inst = C("INSTL");
        $deviceType = C("DEVICE_TYPE");
        $this->assign("mediaList",$mediaList['data1']);
        $this->assign("inst",$inst);
        $this->assign("deviceType",$deviceType);
        $this->display();
    }


    /*
     * 设置PMP状态
     */
    public function setStatus(){
        $msg['status'] = 'error';
        $id = I('id');
        $status = I("status");
        $data['status'] = $status;
        $res = D("Pmp")->setStatus($id,$data);
        //$sql = D("Pmp")->getLastSql();
        if($res){
            $msg['status'] = 'ok';
        }
        $this->ajaxReturn($msg);

    }

}
