<?php
namespace Seller\Controller;
use Think\Controller;

/**
 *  公共内部接口类
 */
class ApiController extends Controller {
//     /**
//      *  获取根据媒体账户id 查询所有媒体信息 
//      */
//     public function getMediaLists($sellerId,$isAgent){
//         if($isAgent == "1"){
//             $medias = D("media")->where("status = 1 and sellerSonId = {$sellerId}")->select();
//         }else{
//             $medias = D("media")->where("status = 1 and sellerId = {$sellerId}")->select();
//         }
//         $result['count'] = count($medias);
//         $result['data'] = $medias;
//         $this->ajaxReturn($result);
//     }
    
//     /**
//      * 广告位提取代码
//      */
//     public function extractCode(){
//         $pid = $_GET['id'];
//         $place = D("place")->where("id = {$pid}")->getField("id,width,height,name");
//         $this->ajaxReturn($place);
//     }
}
