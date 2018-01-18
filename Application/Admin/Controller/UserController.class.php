<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class UserController extends BaseController {

    public function index() {
        if(!empty($_POST)){
            $sidx = I('sidx') ? I('sidx') : 'id';
            $sord = I('sord') ? I('sord') : 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:1;
            $sort = 'status asc,'.$sidx." ".$sord;
            $where = '1';
            if (I('status')) {
                $where .= ' and v_user.status = '.I('status');
            }
            if (I('email')) {
                $where .= ' and email like "%'.I('email').'%"';
            }
            if (I('name')) {
                $where .= ' and v_user.name like "%'.I('name').'%"';
            }
            if (I('idRole')) {
                $where .= ' and idRole = '.I('idRole');
            }
            if (I('id')) {
                $where .= ' and v_user.id like "%'.I('id').'%"';
            }


            $res =  D("User")->getList($sort,$where,$page,$rows);
            $i = ($page-1)*$rows + 1;
            foreach($res['data'] as $k => $v){
                $data[$k]['sort'] = $i;
                $data[$k]['id'] = $v['id'];
                $data[$k]['name'] = $v['name'];
                $data[$k]['rolename'] = $v['rolename'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['mobiletel'] = $v['mobiletel'];
                $data[$k]['lastloginipaddr'] = $v['lastloginipaddr'];
                $data[$k]['status'] = $v['status'];
                $data[$k]['idrole'] = $v['idrole'];
                $i++;
            }

            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = $res['count'];
            $this->ajaxReturn($result);
        }

        $isAllow = isAllow('user', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('user', 'setStatus') ? 1 : 0;
        $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $roles = D('Role')->where('idPlatform = 1')->select();
        $this->assign('roles', $roles);
        $this->display();
    }

    public function add() {
        if( !IS_POST ) {
            $roles = D('Role')->where('idPlatform = 1 and status = 1')->select();
            $this->assign('roles', $roles);
            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];
        if (!I('email') || !I('password') || !I('confirmPassword') || !I('linkman') || !I('mobileTel') || I('password') != I('confirmPassword')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }
        $user = D('User')->where('email = "'.I('email').'"')->find();
        if (!empty($user)) {
            $result['msg'] = '邮箱已经存在';
            $this->ajaxReturn($result);
        }

        $data = [
            'email' => I('email'),
            'password' => md5(I('password')),
            'name' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'status' => 2,
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
            'idRole' => I('role'),
        ];
        $userId = D('User')->addUser($data);
        if ($userId) {
            $result['status'] = 'ok';
        }else{
            $result['msg'] = '添加失败';
        }
        $this->ajaxReturn($result);
    }

    public function edit() {
        $id = I('id');
        if (!I('email')) {
            $user = D('User')->join('left join user_role on user.id = user_role.idUser')->field('user.*, user_role.idRole')->where('user.id = '.$id)->find();
            $roles = D('Role')->where(['idPlatform' => 1])->select();
            $this->assign('roles', $roles);
            $this->assign('user', $user);
            $this->display();
            exit;
        }

        $data = [
            'email' => I('email'),
            'name' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'idRole' => I('role'),
        ];
        $result = D('User')->editUser($id, $data);
        $this->ajaxReturn($result);
    }

    public function setPwd() {
        $id = I('id');
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

        $result = D('user')->setPwd($id, $npwd);
        $this->ajaxReturn($result);
    }

    public function setStatus() {
        $id = I('id');
        $result = D('User')->setStatus($id);
    }
}