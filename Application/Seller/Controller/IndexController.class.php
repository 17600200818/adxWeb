<?php
namespace Seller\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
//        echo '123';die;
        if (IS_POST) {//是否提交表单
            $email = I("email");
            $password = I("password");
            $userInfo =  D("seller")->findByEmail($email); 
            
            if(empty($userInfo)){
                $msg['status'] = 5;
                $msg['errorInfo'] = "用户不存在";
            } else {
                if($userInfo["password"] != md5($password)){
                    $msg['status'] = 6;
                    $msg['errorInfo'] = "密码错误";
                }else {
                    if($userInfo["status"] == 1){
                        $msg['status'] = 1;
                        $msg['errorInfo'] = "用户待审核";
                    }else if($userInfo["status"] == 2){     //  正常
                        $where = array('idRole'=>$userInfo["idrole"], "roleStatus"=>1, "itemStatus"=>1);
                        $arrPowerInfo = D("PowerItem")->findByRoleId($where);
                        
                        if(empty($arrPowerInfo)){
                            $msg['status'] = 7;
                            $msg['errorInfo'] = "用户没有权限";
                        }else{

                            foreach($arrPowerInfo as $idItem => $powerItem){
                                $idRoot = $this->getPowerRoot($arrPowerInfo, $idItem);
                                if($idRoot != null) {
                                    $arrPowerInfo[$idItem]["idRoot"] = $idRoot;
                                }

                                if($powerItem['displayflag'] == 2)
                                    continue;

                                $controller = trim($powerItem["controller"]);
                                $action = trim($powerItem["action"]);
                                if(!empty($controller) && !empty($action)) {
                                    $jumpUrl = sprintf("/%s/%s", $controller, $action);
                                    $msg['url'] = $jumpUrl;
                                    
                                }
                            }

                            $_SESSION['userInfo'] = $userInfo;
                            $_SESSION['powerInfo'] = $arrPowerInfo;
                            $msg['status'] = 2;
                        }
                    }else if($userInfo["status"] == 3){
                        $msg['status'] = 3;
                        $msg['errorInfo'] = "用户审核不通过";
                    }else if($userInfo["status"] == 4){
                        $msg['status'] = 4;
                        $msg['errorInfo'] = "用户已经停用";
                    }
                }
            }
          
            
            $msg['url']='/seller.php/Welcome/index';
            $this->ajaxReturn($msg);
        }
        else{
            $this->display('/Common/login');
        }
    }

    public function logout() {
        unset($_SESSION['userInfo']);
        unset($_SESSION['powerInfo']);
        $this->redirect('/');
    }

    private function getPowerRoot($arrPowerInfo, $id, $level = 0){
        if(!isset($arrPowerInfo[$id]))
            return null;

        if($arrPowerInfo[$id]['parentid'] == 0)
            return $arrPowerInfo[$id]['idpoweritem'];

        if($level > 10)
            return null;

        $level++;

        return $this->getPowerRoot($arrPowerInfo, $arrPowerInfo[$id]['parentid'], $level);
    }

}
