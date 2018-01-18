<?php

namespace Seller\Controller;

use Common\Controller;
use Common\Controller\PageCus;
/**
 * 后台首页控制器
 */
class MediaController extends BaseController {
    /**
     * 媒体列表
     */
    public function index(){
        $result = array();
        $userInfo = $_SESSION["userInfo"]['id'];

        if(IS_POST){
            $name_seller = I("name_seller");
            $name_media  = I("name_media");
            $status      = I("status");
            $do_main     = I("do_main");
            //分页使用参数 还有一个参数(page)在PageCus调用
            $limit= I("rows");      //显示行数
            $sidx = I("sidx");      //排序name
            $sord = I("sord");      //排序方式
            
            
            //判断权限  是否是媒体代理账号
            
           
            
//            if($_SESSION['userInfo']['idrole']== 2){
//                
////                $sql = "select distinct m.id,m.sellersonid,m.id,m.name,m.mediatype,m.domain,m.category1,m.category2,m.status,m.sellerId from media m,seller s where (s.id=m.sellerSonId or m.sellerId=s.id) and s.id IN(".$temp_is_sellerID.")";
//                  $sql="select * from media where sellersonid = {$userInfo} ";
//                  
//                }else{
////                $sql = "select distinct m.id,m.sellersonid,m.id,m.name,m.mediatype,m.domain,m.category1,m.category2,m.status,m.sellerId from media m,seller s where (s.id=m.sellerSonId or m.sellerId=s.id)";
//                   $sql="select * from media where sellerid = {$userInfo} or sellersonid = {$userInfo}";
//                    }
                    
                 $sql="select * from media where sellerid = {$userInfo}";  
            $count=0;
//            if($name_seller!=""){
//                $sql .= " and s.company like '%{$name_seller}%' ";
//                $count++;
//            }
            if($name_media!=""){
                $sql .= " and name like '%{$name_media}%'";
                $count++;
            }
            if($status!=""){
                $sql .= " and status = {$status}";
                $count++;;
            }
             if($do_main!=""){
                $sql .= " and domain like '%{$do_main}%'";
                $count++;;
            }
            if($sidx){
                $sql .=  " ORDER BY {$sidx} {$sord}";
            }else{
                $sql .=  " ORDER BY id desc";
            }
            if($count>0){
                $media_count = count(D("media")->query($sql));
            }else{
                //查询media总数
                $media_count =count(D("media")->query("select * from media where sellerid = {$userInfo}"));
            }
//            
            
            //分页封装类
            $Page_project = new PageCus($media_count, $limit);
            //返回nowPage当前页   totalPages总页数
            $show_project = $Page_project->show();
            //查询媒体
            
            $sql .=  " LIMIT {$Page_project->firstRow},{$Page_project->listRows}";
            $medias = D("media")->query($sql);
            
            if(!$medias){
//                 $medias = D("media")->query(str_replace("m.sellerId=s.id","(s.id=p.sellerSonId)",$sql));
            }
            
            
            if($medias){
                foreach ($medias as $key => $val){
                    $seller = D("seller")->where("id = '{$val["sellerid"]}'")->find();
                    $parent_seller = D("seller")->where("id = '{$val["sellersonid"]}'")->find();
                    $sysMediaCategory = D("SysMediaCategory")->where("c1 = '{$val['category1']}' and c2 = '{$val['category2']}'")->find();
                    $result[$key]['id'] = $val['id'];
                    $result[$key]['sellername'] = $seller['company'];
                    $result[$key]['agentname'] = $parent_seller['id']>0?$parent_seller['company']:"无";
                    $result[$key]['name'] = $val['name'];
                    $result[$key]['mediatype'] = $val['mediatype']=="1"?'<span class="label label-info arrowed-right arrowed-in">web</span>':'<span class="label label-danger arrowed">app</span>';
                   if($val['mediatype']!="1"){
                       $result[$key]['mediatype'] = $val['mediatype']=="2"?'<span class="label label-danger arrowed">app</span>':'<span class="label label-success arrowed-in arrowed-in-right">web+app</span>';
                   }
                    $result[$key]['domain'] = $val['domain'];
                    $result[$key]['storeurl'] = $val['storeurl'];
                    $result[$key]['category1'] = $sysMediaCategory['n1'];
                    $result[$key]['category2'] = $sysMediaCategory['n2'];
                    $result[$key]['status'] = $val['status']=='1'?'<span class="label label-sm arrowed-in-right arrowed-in label-success">正常</span>':'<span class="label label-sm label-inverse arrowed-in">停用</span>';
                    $result[$key]['saf']   = 1;
                    $result[$key]['num']   = $key+1;
                }
            }
            $result2['page']    = $show_project['nowPage'];
            $result2['total']   = $show_project['totalPages'];
            $result2['records'] = $media_count;
            $result2['data'] = $result;
            $this->ajaxReturn($result2);
        }else{
        }
           $ismediaImport = isAllow('media', 'mediaImport') ? 1 : 0;
              $ismediaExport = isAllow('media', 'mediaExport') ? 1 : 0;
                $isAllow = isAllow('media', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('media', 'setStatus') ? 1 : 0;
        $isAddAllow=  isAllow('media', 'add') ? 1 : 0;
        $isDirect=isAllow('media','setDirect')?1:0;
        $this->assign('isDirect', $isDirect);
        $this->assign('isAddAllow', $isAddAllow);
        $this->assign('ismediaImport', $ismediaImport);
        $this->assign('ismediaExport', $ismediaExport);
                $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $this->display();
    }
    
 public function edit(){
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            $result = array();
            $data['sellerId'] = $userInfo;
            $data['name'] = $_POST['name'];
            $id = $_POST['mid'];
            if($id){
                $data2['name']               = $_POST['name'];
                $data2['mediaType']          = $_POST['mediaType'];
                $data2['category1']          = $_POST['category1'];
                $data2['category2']          = $_POST['category2'];
                $data2['domain']             = $_POST['domain'];
                $data2['storeurl']             = $_POST['storeurl'];
                $data2['muid']    = $userInfo;
                $data2['mtime']    = date("Y-m-d H:i:s",time());
                D("media")->where("id = {$id}")->save($data2);
                $this->redirect('/Media/index');
            }
        }else{
            $id = $_GET['id']==""?0:$_GET['id'];
            if($id){
                //查询当前媒体信息
                $media = D("media")->where("id = {$id}")->find();
                //修改状态
                if(I('type') == "2"){
                    $msg = array();
                    $media['status'] = $media['status']=="1"?"2":"1";
                    $result = D("media")->where("id = {$id}")->save(array("status"=>$media['status']));
                    if($result>0 || $result ===0) $msg['status'] = "200";
                    $this->ajaxReturn($msg);
                }
                
                
                //查询媒体信息是否有代理记录
                if($media['sellersonid']!=0){
                    $agent_seller = D("seller")->where("id = {$media['sellersonid']}")->find();
                }else{
                    $agent_seller = D("seller")->where("id = {$media['sellerid']}")->find();
                }
                if($agent_seller['parentid'] != 0){
                    $pSeller = D("seller")->where("id = {$agent_seller['parentid']}")->find();
                    if($pSeller){
                        $media['parent_company'] = $pSeller['company'];
                        $media['parent_parent_id'] = $pSeller['id'];
                    }
                }
            }
        }
        
        $sellers = D("seller")->where("status = 2")->select();
        $this->assign("sellersJson",json_encode($sellers));
        $this->assign("edit_media",$media);
        $this->assign("agent_seller",$agent_seller);
        $this->assign("sellers",$sellers);
        $this->display();
    }
    
    /**
     * 媒体添加
     */
    public function add(){
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            $result = array();
            $data['sellerId'] = $userInfo;
            $data['name'] = $_POST['name'];
            $id = $_POST['mid'];
            $media = D("media")->where($data)->find();
            if($media){
                $this->error("媒体名字已存在");
            }else{
                $t = date("Y-m-d H:i:s",time());
                $_REQUEST['cuid'] = $userInfo;
                $_REQUEST['muid']    = 0;
                $_REQUEST['ctime'] = $t;
                $_REQUEST['sellerId']=$userInfo;
//                var_dump($_REQUEST);die;
                if(D("media")->add($_REQUEST)){
                    //成功
                    $this->redirect('/Media/index');
                }else{
                    //数据库添加失败
                    $this->error("数据库添加失败");
                }
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
        }else{
          
            $sellers = D("seller")->where("status = 2 and id = {$_SESSION['userInfo']['id']}")->select();
           
        }

        $this->assign("sellersJson",json_encode($sellers));
        $this->assign("sellers",$sellers);

        $this->display();
    }
    /**
     * 路由设置
     */
    public function setRoute(){
        $this->display();
    }
    
    /**
     * 定向设置
     */
    public function setDirect(){
        $userInfo = $_SESSION["userInfo"]['id'];
        if(IS_POST){
            //修改
            $result = array('status'=>"500");
            
            $id = $_POST['id'];
            $excludedAdCategory = $this->getDelCategory($_POST['category']); //禁止行业
            $data['exclude_ad_category'] = $excludedAdCategory;
            $data['exclude_ad_url'] = $_POST['exclude_ad_url'];
            $data['muid']    = $userInfo;
            $data['mtime']    = date("Y-m-d H:i:s",time());
            if($id){
                D("media")->where("id={$id}")->save($data);
                $result['status'] = "200";
                $result['url'] = "/Media/index";
            }
            
            $this->ajaxReturn($result);
        }else{
            $id = $_GET['id'];
            $media = D("media")->field("exclude_ad_url,exclude_ad_category")->where("id={$id}")->find();
            $categoryAry =  json_decode($media['exclude_ad_category'],true);
            $this->assign("categoryAry",$categoryAry['content']);
            $this->assign("media",$media);
            $this->assign("id",$id);
        }
        $this->display();
    }
    
    public function getDelCategory($categoryAry = array()) {
        $arr = array();
        foreach ($categoryAry as $key => $item) {
            $temp = explode('-', $item);
            $arr['id'][$key] = $temp[0];
            $arr['content'][$temp[0]] = $temp[1];
        }
        return json_encode($arr);
    }
 public function setStatus() {
       $id = $_GET['id']==""?0:$_GET['id'];
            if($id){
                //查询当前媒体信息
                $media = D("media")->where("id = {$id}")->find();
                //修改状态
                if(I('type') == "2"){
                    $msg = array();
                    $media['status'] = $media['status']=="1"?"2":"1";
                    $result = D("media")->where("id = {$id}")->save(array("status"=>$media['status']));
                    if($result>0 || $result ===0) $msg['status'] = "200";
                    $this->ajaxReturn($msg);
                }
    }
    
}
 public function mediaExport(){
     $userInfo = $_SESSION["userInfo"]['id'];
      
            $name_media  = I("name_media");
            $status      = I("status");
            $do_main     = I("do_main");

             $sql="select * from media where sellerid = {$userInfo} ";  
            $count=0;
            
            if($name_media!=""){
                $sql .= " and name like '%{$name_media}%'";
                $count++;
            }
            if($status!=""){
                $sql .= " and status = {$status}";
                $count++;;
            }
             if($do_main!=""){
                $sql .= " and domain like '%{$do_main}%'";
                $count++;;
            }
            if($sidx){
                $sql .=  " ORDER BY {$sidx} {$sord}";
            }else{
                $sql .=  " ORDER BY id desc";
            }
            if($count>0){
                $media_count = count(D("media")->query($sql));
            }else{
                //查询media总数
                $media_count =count(D("media")->query("select * from media where sellerid = {$userInfo}"));
            }
            
            $medias = D("media")->query($sql);
            
//            var_dump($medias);die;
            $filename = "媒体列表";
             ob_end_clean();
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'.xls');
        
        
        $title = array(
          '媒体ID', 
            
            
            '媒体名称',
            '媒体类型',
            '域名包名',
            '下载地址',
            '一级分类',
            "二级分类",
            "状态",
            
        );
        $data =array();
        $result = array();
        foreach ($medias as $key => $val){
//            $seller = D("seller")->where("id = '{$val["sellerid"]}'")->find();
//                    $parent_seller = D("seller")->where("id = '{$val["sellersonid"]}'")->find();
                    $sysMediaCategory = D("SysMediaCategory")->where("c1 = '{$val['category1']}' and c2 = '{$val['category2']}'")->find();
                    $result[$key]['id'] = $val['id'];
                  
                    $result[$key]['name'] = $val['name'];
                    $result[$key]['mediatype'] = $val['mediatype']=="1"?'web':'app';
                   if($val['mediatype']!="1"){
                       $result[$key]['mediatype'] = $val['mediatype']=="2"?'app':'web+app';
                   }
                    $result[$key]['domain'] = $val['domain'];
                    $result[$key]['storeurl'] = $val['storeurl'];
                    $result[$key]['category1'] = $sysMediaCategory['n1'];
                    $result[$key]['category2'] = $sysMediaCategory['n2'];
                    $result[$key]['status'] = $val['status'];      
                    
                    
                       $temparr = array(
                $result[$key]['id'] ,
//                $result[$key]['sellername'] ,
//                $result[$key]['agentname'] ,
                $result[$key]['name'] ,
                $result[$key]['mediatype'] ,
                $result[$key]['domain'] ,
                $result[$key]['storeurl'] ,
                $result[$key]['category1'] ,
                $result[$key]['category2'] ,
                $result[$key]['status'] ,
            );
                       $data[$key] = $temparr;
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
public function mediaImport(){
       $suss=0; $failed=0; $chann=0;
    if (!empty($_FILES)) {
            $userInfo = $_SESSION["userInfo"]['id'];
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728 ;// 设置附件上传大小
            $upload->exts = array('xlsx','xls');// 设置附件上传类型
            $upload->rootPath = './Public/uploads/media/';
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
//            var_dump(is_readable($file_name));die;
            $objPHPExcel = $objReader->load($file_name,$encode='utf-8');//加载文件
            $sheet = $objPHPExcel->getSheet(0);//取得sheet(0)表
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            $highestColumn = $sheet->getHighestColumn(); // 取得总列数
            
            $t = date("Y-m-d H:i:s",time());
            $userInfo = $_SESSION["userInfo"]['id'];
            $mes = "";
            if(($objPHPExcel->getActiveSheet()->getCell("A".'1')->getValue())!='媒体名称'){
                 $this->error("文件格式有误,请下载导入模板");
            }
            if(($objPHPExcel->getActiveSheet()->getCell("B".'1')->getValue())!='媒体类型'){
                 $this->error("文件格式有误,请下载导入模板");
            }
            if(($objPHPExcel->getActiveSheet()->getCell("C".'1')->getValue())!='域名/包名'){
                 $this->error("文件格式有误,请下载导入模板");
            }
            if(($objPHPExcel->getActiveSheet()->getCell("D".'1')->getValue())!='下载地址'){
                 $this->error("文件格式有误,请下载导入模板");
            }
            
            for($i=2;$i<=$highestRow;$i++)
            {
                $bidfloor = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                if(!is_float($bidfloor)){
                    $mes .= $i.",";
                    continue;
                }
//                $mediaid = $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
//                $media2=D('media')->where("sellerid= {$userInfo} and id = {$mediaid}")->find();
//                $media = D("media")->where("id = {$mediaid} ")->find();
                if(TRUE){
                    
                  
                    $data['name']                   =    $objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                    $data['mediaType']              =    $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                    $data['domain']                 =    $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                    $data['storeurl']               =    $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                    $data['category1']              =    '0';
                    $data['category2']              =    '0';
                    $data['cuid']                   =    $userInfo;
                    $data['muid']                   =    $userInfo;
                    $data['ctime']                  =    $t;
                    $data['mtime']                  =    $t;
                   $data['sellerId']                =    $_SESSION["userInfo"]['id'];
                   
                  
                  $res= D("media")->add($data);
                  
              if($res) { $suss++;}else{$failed++;}
                   
              }else{
                  $failed++;
                    $mes .= $i.",";
                }
            }
            
            $this->assign("media_mes",$mes);
            $this->redirect('/Media/index',array("place_mes"=>$mes,"suss"=>$suss,"failed"=>$failed,"chann"=>$chann));
        }else
        {
            $this->error("请选择上传的文件");
        }
}
}
