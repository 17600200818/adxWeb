<?php
namespace Buyer\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        if (IS_POST) {//是否提交表单
            $email = trim(I("email"));
            $password = I("password");
            $vcode=I('vcode');
            $userInfo =  D("Buyer")->findByEmail($email);
            $verify = new \Think\Verify();
            $check_vcode = $verify->check($vcode);

            if(empty($userInfo)){
                $msg['status'] = 5;
                $msg['errorInfo'] = "用户不存在";
            } else {
                if($userInfo["password"] != md5($password)){
                    $msg['status'] = 6;
                    $msg['errorInfo'] = "密码错误";
                }else {
                    if (!$check_vcode) {
                        $msg['status'] = 7;
                        $msg['error_info'] = '验证码输入错误!';
                        $this->ajaxReturn($msg);
                    }
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
                                    $jumpUrl = '/buyer.php/reportBuyer/index';
                                    $msg['url'] = $jumpUrl;
                                }
                            }

                            $_SESSION['buyer']['userInfo'] = $userInfo;
                            $_SESSION['buyer']['powerInfo'] = $arrPowerInfo;
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
            $this->ajaxReturn($msg);
        }
        else{
            $this->display('/Common/login');
        }
    }

    public function logout() {
        unset($_SESSION['buyer']['userInfo']);
        unset($_SESSION['buyer']['powerInfo']);
        $this->redirect("/");
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

    //调用验证码
    public function vcode() {
        $fontSize = I('get.f', 18); //字体大小
        $length = I('get.l', 4); //验证码字数
        $imageW = I('get.w', 150); //宽
        $imageH = I('get.h', 0); //高
        $imagecn = I('get.cn', 0); //使用中文验证码
        $useZh = false;
        if ($imagecn == 1) {
            $useZh = true;
        }
        $verify = new \Think\Verify();
        $verify->fontSize = $fontSize; //字体大小
        $verify->length = $length; //验证码字数
        $verify->imageW = $imageW; //宽
        $verify->imageH = $imageH; //高
        $verify->useZh = $useZh; //使用中文验证码
        //$verify->useCurve= false;//混淆曲线
        $verify->entry();
    }
    /**
     * 显示验证码
     * @param type $length 验证码的长度
     * @param type $height 图片的高度
     * @param type $fontSize 文字的大小
     * @param type $useCurve
     * @param type $fonttf 字体
     * @echo Image
     */
    public function show_verify() {
        $verify = new \Think\Verify(array(
            'codeSet'=>'23945867',
            'length' => I('get.length', 4),
            'imageH' => I('get.height', 50),
            'imageW' => I('get.width', 238),
            'fontSize' => I('get.size', 20),
            'useCurve' => FALSE,
            'fontttf' => '5.ttf',
        ));
        $verify->entry();
    }
    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function check_verify($code, $id = '') {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}
