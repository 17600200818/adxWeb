<?php
namespace Buyer\Controller;
use Think\Controller;

class CommonController extends Controller {

    /*
     * 得到行业二级分类
     */
    public function getIndustryCategory2() {
        $msg['status'] = 'error';
        $category1 = isset($_POST['category1'])?$_POST['category1']:'';
        $list = D("SysIndustryCategory")->getSubCagegory($category1);
        if($list){
            $msg['status'] = 'ok';
            $msg['data'] = $list;
        }
        $this->ajaxReturn($msg);
    }

    /*
     * 得到媒体二级分类
     */
    public function getMediaCategory2() {
    }

    /**
     * 导出数据为excel表格
     *@param $data    一个二维数组,结构如同从数据库查出来的数组
     *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
     *@param $filename 下载的文件名
     *@examlpe
        $stu = M ('User');
        $arr = $stu -> select();
        exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
     */
    function exportexcel($data=array(),$title=array(),$filename='report'){
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=".$filename.".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        //导出xls 开始
        if (!empty($title)){
            foreach ($title as $k => $v) {
                $title[$k]=iconv("UTF-8", "GB2312",$v);
            }
            $title= implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)){
            foreach($data as $key=>$val){
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
                }
                $data[$key]=implode("\t", $data[$key]);

            }
            echo implode("\n",$data);
        }
    }

    /*
     * 获取广告位尺寸列表
     */
    public function getSizeList(){
        $msg['status'] = 'error';
        if(empty($_SESSION['buyer']['userInfo'])){
            $this->ajaxReturn($msg);
        }
        $res = D("Place")->getSizeList();
        if($res){
            $list = array();
            foreach($res as $k => $v){
                $arr_result[$k]['name'] = $v['width']."*".$v['height'];
                $arr_result[$k]['size'] = $v['width']."*".$v['height'];
                $arr_result[$k]['pId'] = '0';
            }
            $data = array();
            $data['status'] = 'ok';
            $data['data'] = array_values($arr_result);
            echo json_encode($data);
        }
    }


    /*
     * 获取城市id
     */
    public function getCityId($city,$flag = 1){
        $msg['status'] = 'error';
        if(empty($_SESSION['buyer']['userInfo'])){
            $this->ajaxReturn($msg);
        }
        $res = D("sysCity")->getCity();
        foreach($city as $k => $v){
            if($flag == 1){
                if($res['city_id'][$v] == ''){
                    $city[$k] = $res['city_id'][$v.'市']['area_id'];
                }else{
                    $city[$k] = $res['city_id'][$v]['area_id'];
                }
            }else{
                $city[$k] = $res['city_name'][$v];
            }
        }
        return $city;
    }

    /**
     * 获取媒体分类
     */
    public function get_category() {
        $c1 = intval($_POST['c1']); //获取媒体一级分类
        if ($c1) {//二级分类
            $subcategory = D('SysMediaCategory');
            $res = $subcategory->field('c2,n2')->where('c1 = ' . $c1)->select();
            if ($res) {
                $msg['status'] = "ok";
                $msg['data'] = $res;
            } else {
                $msg['status'] = "error";
            }
        } else {//一级分类
            $category = D('SysMediaCategory');
            $res = $category->field('distinct c1,n1')->select();
            if ($res) {
                $msg['status'] = "ok";
                $msg['data'] = $res;
            } else {
                $msg['status'] = "error";
            }
        }
        $this->ajaxReturn($msg);
    }
    
    /**
     * 广告分类
     */
    public function ajaxGetCategoryTree() {
        $db = D('SysIndustryCategory');
        $str_sql = "SELECT * FROM  `sys_industry_category`";
        $result = $db->query($str_sql);
        foreach ($result as $k => $v) {
            $arr_result[$v['c1']]['id'] = $v['c1'];
            $arr_result[$v['c1']]['name'] = $v['n1'];
            $arr_result[$v['c1']]['pId'] = '0';
            $arr_result[$v['c2']]['id'] = $v['c2'];
            $arr_result[$v['c2']]['name'] = $v['n2'];
            $arr_result[$v['c2']]['pId'] = $v['c1'];
        }
        $data = array();
        $data['status'] = 'ok';
        $data['data'] = array_values($arr_result);
        echo json_encode($data);
    }
    
    
    /**
     *  获取根据媒体账户id 查询所有媒体信息
     */
    public function getMediaLists($sellerId,$isAgent){
        if($isAgent == "1"){
            $medias = D("media")->where("status = 1 and sellerSonId = {$sellerId}")->select();
        }else{
            $medias = D("media")->where("status = 1 and sellerId = {$sellerId}")->select();
        }
        $result['count'] = count($medias);
        $result['data'] = $medias;
        $this->ajaxReturn($result);
    }
      /**
     *  获取根据媒体账户id 查询媒体信息
     */
    public function getSellerInfo($sellerId){
//        if($isAgent == "1"){
            $result = D("seller")->where("status = 2 and id = {$sellerId}")->select();
//        }else{
//            $seller = D("seller")->where("status = 2 and sellerId = {$sellerId}")->select();
//        }
            
        
//        $result = $seller[0]['creativeaudittype'];
        $this->ajaxReturn($result[0]);
    }
    
    /**
     * 广告位提取代码
     */
    public function extractCode(){
        $pid = $_GET['id'];
        $place = D("place")->where("id = {$pid}")->getField("id,width,height,name");
        $this->ajaxReturn($place);
    }

    /*
     * 获取dsp列表
     */
    public function ajaxGetDspTree(){
        $msg['status'] = 'error';
        if(empty($_SESSION['buyer']['userInfo'])){
            $this->ajaxReturn($msg);
        }
        $result = D('Advertiser')->getDspList();
        foreach ($result as $k => $v) {
            $arr_result[$v['id']]['id'] = $v['id'];
            $arr_result[$v['id']]['name'] = $v['company'];
            $arr_result[$v['id']]['pId'] = '0';
        }
        $data = array();
        $data['status'] = 'ok';
        $data['data'] = array_values($arr_result);
        echo json_encode($data);
    }


    /*
     * 获取媒体广告位tree
     */
    public function getMediaTree(){
        $msg['status'] = 'error';
        if(empty($_SESSION['buyer']['userInfo'])){
            $this->ajaxReturn($msg);
        }
        $id = I("id");
        $res = D("Pmp")->getMediaTree($id);
        $json =json_encode($res,JSON_UNESCAPED_UNICODE);
        $this->ajaxReturn($json);
    }

    /*
     * 获取广告主列表
     */
    public function getAdvertiserList(){
        $msg['status'] = 'error';
        if(empty($_SESSION['buyer']['userInfo'])){
            $this->ajaxReturn($msg);
        }
        $idBuyer = isset($_POST['idBuyer'])?$_POST['idBuyer']:'';
        $list = D("Advertiser")->getAdvertiserList($idBuyer);
        if($list){
            $msg['status'] = 'ok';
            $msg['data'] = $list;
        }
        $this->ajaxReturn($msg);
    }
    /*
     * 检查广告位ID是否重复
     */
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
}
