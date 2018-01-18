<?php

namespace Buyer\Controller;

use Think\Controller;
use Org\Util\Rbac;
require_once APP_PATH.'Admin/Common/Common.class.php';
class SelfController extends BaseController{

    public function setPwd() {
        $id = $_SESSION['buyer']['userInfo']['id'];
        $pwd = I('pwd');
        $npwd = I('newPwd');
        $cnpwd = I('confirmNewPwd');
        $result = ['status' => 'error', 'msg' => ''];

        if (strlen($npwd) < 6) {
            $result['msg'] = '密码至少6位';
            $this->ajaxReturn($result);
        }
        if ($npwd != $cnpwd) {
            $result['msg'] = '两次密码输入不一致';
            $this->ajaxReturn($result);
        }

        $result = D('user')->setPwdHead($id, $pwd, $npwd);
        $this->ajaxReturn($result);
    }

    public function profile() {
        $id = $_SESSION['buyer']['userInfo']['id'];
        $name = I('name');
        $mobileTel = I('mobileTel');
        $result = ['status' => 'error', 'msg' => ''];
        if (!$mobileTel || !isMobile($mobileTel)) {
            $result['msg'] = '需填入正确手机号码';
            $this->ajaxReturn($result);
        }


        $ret = D('User')->where(['id' => $id])->save(['name' => $name, 'mobileTel' => $mobileTel]);
        if ($ret === false) {
            $result['msg'] = '修改错误';
        }else {
            $_SESSION['buyer']['userInfo']['name'] = $name;
            $_SESSION['buyer']['userInfo']['mobileTel'] = $mobileTel;
            $result['status'] = 'ok';
            $result['msg'] = '修改成功';
            $result['name'] = $name;
        }

        $this->ajaxReturn($result);
    }
}