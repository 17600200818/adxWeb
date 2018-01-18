<?php

namespace Buyer\Controller;

use Think\Controller;
use Org\Util\Rbac;
class BuyerController extends BaseController {

    public function index() {
        if(!empty($_POST)){
            $sidx = I('sidx') ? I('sidx') : 'id';
            $sord = I('sord') ? I('sord') : 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:1;
            $sort = 'status asc,'.$sidx." ".$sord;
            $where = 1;
            if (I('status')) {
                $where .= ' and status = '.I('status');
            }
            if (I('email')) {
                $where .= ' and email like "%'.I('email').'%"';
            }
            if (I('company')) {
                $where .= ' and company like "%'.I('company').'%"';
            }
            if (I('buyType')) {
                $where .= ' and buyType = '.I('buyType');
            }
            $res =  D("Buyer")->buyer_list($sort,$where,$page,$rows);
            $i = ($page-1)*$rows + 1;
            foreach($res['data'] as $k => $v){
                $data[$k]['sort'] = $i;
                $data[$k]['company'] = $v['company'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['buytype'] = $v['buytype'] == 1 ? '<span class="label label-sm label-inverse arrowed-in">rtb</span>' : '<span class="label label-sm label-info arrowed arrowed-righ">adn</span>';
                $data[$k]['creativeaudittype'] = $v['creativeaudittype'] == 1 ? '<span class="label label-sm label-danger arrowed arrowed-right">先审后投</span>' : '<span class="label label-sm arrowed-in-right arrowed-in label-success">先投后审</span>';
                $data[$k]['status'] = $v['status'];

                $i++;
            }
            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = $res['count'];
            $this->ajaxReturn($result);

        }
        $isAllow = isAllow('buyer', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('buyer', 'setStatus') ? 1 : 0;
        $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $this->display();
    }

    public function add() {
        if (!IS_POST) {
            $userId = $_SESSION['buyer']['userInfo']['id'];
            $powerItems = D('User')->getParamItemsFromUserId($userId);
            $gainTypeHidden = isset($powerItems[45]) ? '' : 'display:none;';

            $this->assign('gainTypeHidden', $gainTypeHidden);
            $this->display();
            exit();
        }

        $result = ['status' => 'error', 'msg' => ''];

        if (!I('email') || !I('company') || !I('password') || !I('confirmPassword') || !I('linkman') || !I('mobileTel') || I('password') != I('confirmPassword')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }

        $data = [
            'email' => I('email'),
            'password' => md5(I('password')),
            'linkman' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'company' => I('company'),
            'address' => I('address'),
            'zip' => I('zip'),
            'idRole' => 4,
            'buyType' => I('buyType'),
            'creativeAuditType' => I('creativeAuditType'),
            'gainType' => I('gainType'),
            'gainRate' => I('gainRate') == '' ? 0 : I('gainRate'),
            'status' => 2,
            'cuid' => $_SESSION['buyer']['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
        ];

        $result = D('Buyer')->addBuyer($data);
        $this->ajaxReturn($result);
    }

    public function edit() {
        $id = I('id');
        if (!IS_POST) {
            $buyer = D('Buyer')->where(['id' => $id])->find();
            $userId = $_SESSION['buyer']['userInfo']['id'];
            $powerItems = D('User')->getParamItemsFromUserId($userId);
            $gainTypeHidden = isset($powerItems[45]) ? '' : 'display:none;';

            $this->assign('gainTypeHidden', $gainTypeHidden);
            $this->assign('buyer', $buyer);
            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];

        if (!I('email') || !I('company') || !I('linkman') || !I('mobileTel')) {
            $result['msg'] = '修改失败';
            $this->ajaxReturn($result);
        }
        $buyer = D('Buyer')->where('id !='.$id.' and email = "'.I('email').'"')->find();
        if (!empty($buyer)) {
            $result['msg'] = '邮箱被占用';
            $this->ajaxReturn($result);
        }

        $data = [
            'email' => I('email'),
            'linkman' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'company' => I('company'),
            'address' => I('address'),
            'zip' => I('zip'),
            'buyType' => I('buyType'),
            'creativeAuditType' => I('creativeAuditType'),
            'gainType' => I('gainType'),
            'gainRate' => I('gainRate'),
            'muid' => $_SESSION['buyer']['userInfo']['id'],
            'mtime' => date('Y-m-d H:i:s'),
        ];
        $ret = D('Buyer')->where(['id' => $id])->save($data);
        if ($ret) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '修改失败';
        }
        $this->ajaxReturn($result);
    }

    public function setStatus() {
        $id = I('id');
        $result = D('Buyer')->setStatus($id);
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

        $result = D('Buyer')->setPwd($id, $npwd);
        $this->ajaxReturn($result);
    }

    public function setParam() {
        $id = I('id');
        if (!IS_POST) {
            $param = D('BuyerParam')->where(['id' => $id])->find();
            $param['id'] = $id;
            $this->assign('param', $param);
            $this->display();
            exit();
        }

        $result = ['status' => 'error', 'msg' => ''];

        $data = [
            'id' => I('id'),
            'token' => I('token'),
            'ipList' => I('ipList'),
            'priceKey' => I('priceKey'),
            'adxQps' => I('adxQps'),
            'buyerQps' => I('buyerQps'),
            'cookieMappingUrl' => I('cookieMappingUrl'),
            'winNoticeUrl' => I('winNoticeUrl'),
            'bidUrl' => I('bidUrl'),
        ];
        $result = D('BuyerParam')->addBuyerParam($data);
        $this->ajaxReturn($result);
    }

    public function info() {
        $user = $_SESSION['buyer']['userInfo'];
        switch ($user['status']) {
            case 1:
                $status = '<button class="btn btn-xs btn-warning">待审核</button>';
                break;
            case 2:
                $status = '<button class="btn btn-xs btn-success">正常</button>';
                break;
            case 3:
                $status = '<button class="btn btn-xs btn-danger">审核不通过</button>';
                break;
            case 4:
                $status = '<button class="btn btn-xs btn-inverse">停用</button>';
                break;
        }
        $user['status'] = $status;

        $this->assign('user', $user);
        $this->display();
    }
}