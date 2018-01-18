<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class AgentSellerController extends BaseController{

    public function index() {
        $parentId = I('id');

        if(IS_POST){
            $sidx = 'id';
            $sord = 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:1;
            $sort = $sidx." ".$sord;
            $where = 1;

            $parentId = I('parentId');
            if ($parentId) {
                $where .= ' and seller.parentId = '.$parentId;
            }

            if (I('status')) {
                $where .= ' and status = '.I('status');
            }
            if (I('email')) {
                $where .= ' and email like "%'.I('email').'%"';
            }
            if (I('idRole')) {
                $where .= ' and idRole = '.I('idRole');
            }
            $res =  D("Seller")->getList($sort,$where,$page,$rows);
            foreach($res['data'] as $k => $v){
                $data[$k]['id'] = $v['id'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['rolename'] = $v['rolename'];
                $data[$k]['linkman'] = $v['linkman'];
                $data[$k]['mobiletel'] = $v['mobiletel'];
                $data[$k]['lastloginipaddr'] = $v['lastloginipaddr'];
                $data[$k]['status'] = $v['status'];
                $data[$k]['idrole'] = $v['idrole'];

            }
            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = 4;
            $this->ajaxReturn($result);

        }

        $roles = D('Role')->where('idPlatform = 2')->select();
        $isAllow = isAllow('agentSeller', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('agentSeller', 'setStatus') ? 1 : 0;
        $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $this->assign('roles', $roles);
        $this->assign('parentId', $parentId);
        $this->display();
    }

    public function add() {
        $parentId = I('parentId');
        if( !IS_POST ) {
            //检查是否有设置盈利模式权限
            $gainTypeHidden = 'display:none;';
            $this->assign('gainTypeHidden', $gainTypeHidden);

            $this->assign('parentId', $parentId);
            $this->display();
            exit;
        }

        //提交表单
        $result = ['status' => 'error', 'msg' => ''];

        if (!I('email') || !I('company') || !I('password') || !I('confirmPassword') || !I('linkman') || !I('mobileTel') || I('password') != I('confirmPassword')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }

        $user = D('Seller')->where('email = "'.I('email').'"')->find();
        if (!empty($user)) {
            $result['msg'] = '邮箱已经存在';
            $this->ajaxReturn($result);
        }

        $data = [
            'company' => I('company'),
            'email' => I('email'),
            'password' => md5(I('password')),
            'parentId' => $parentId,
            'idRole' => I('role'),
            'linkman' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'gainType' => I('gainType'),
            'gainRate' => I('gainRate'),
            'status' => 2,
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
        ];

        $id = D('Seller')->add($data);
        if ($id) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '添加失败';
        }
        $this->ajaxReturn($result);
    }
}