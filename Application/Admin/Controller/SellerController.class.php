<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class SellerController extends BaseController{

    public function index() {
        if(IS_POST){
            $sidx = I('sidx') ? I('sidx') : 'id';
            $sord = I('sord') ? I('sord') : 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:1;
            $sort = 'status asc,'.$sidx." ".$sord;
            $where = 'seller.parentId = 0';
            if (I('status')) {
                $where .= ' and seller.status = '.I('status');
            }
            if (I('email')) {
                $where .= ' and email like "%'.I('email').'%"';
            }
            if (I('company')) {
                $where .= ' and company like "%'.I('company').'%"';
            }
            if (I('idRole')) {
                $where .= ' and idRole = '.I('idRole');
            }
             if (I('id')) {
                $where .= ' and seller.id like "%'.I('id').'%"';
            }

            $res =  D("Seller")->getList($sort,$where,$page,$rows);
            $i = ($page-1)*$rows + 1;
            foreach($res['data'] as $k => $v){
                $data[$k]['sort'] = $i;
                $data[$k]['company'] = $v['company'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['rolename'] = $v['rolename'];
                $data[$k]['isssp'] = $v['isssp'] == 1 ? '<span class="label label-sm label-info arrowed arrowed-righ">是</span>' : '<span class="label label-sm label-inverse arrowed-in">否</span>';

                if ($v['isauditcreative'] == 1) {
                    $data[$k]['creativeaudittype'] = $v['creativeaudittype'] == 1 ? '<span class="label label-sm label-danger arrowed arrowed-right">先审后投</span>' : '<span class="label label-sm arrowed-in-right arrowed-in label-success">先投后审</span>';
                }else {
                    $data[$k]['creativeaudittype'] = '';
                }

                $data[$k]['isauditcreative'] = $v['isauditcreative'] == 1 ? '<span class="label label-sm label-danger arrowed arrowed-right">审核</span>' : '<span class="label label-sm arrowed-in-right arrowed-in label-success">不审核</span>';

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

        $roles = D('Role')->where('idPlatform = 2')->select();
        $isAllow = isAllow('seller', 'edit') ? 1 : 0;
        $isSetStatus = isAllow('seller', 'setStatus') ? 1 : 0;
        $this->assign('isSetStatus', $isSetStatus);
        $this->assign('isAllow', $isAllow);
        $this->assign('roles', $roles);
        $this->display();
    }

    public function add() {
        if( !IS_POST ) {
            $roles = D('Role')->where('idPlatform = 2 and status = 1')->select();
            $this->assign('roles', $roles);

            //检查是否有设置盈利模式权限
            $userId = $_SESSION['userInfo']['id'];
            $powerItems = D('User')->getParamItemsFromUserId($userId);
            $gainTypeHidden = isset($powerItems[51]) ? '' : 'display:none;';
            $this->assign('gainTypeHidden', $gainTypeHidden);

            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];
        if (!I('email') || !I('company') || !I('password') || !I('confirmPassword') || !I('linkman') || !I('mobileTel') || I('password') != I('confirmPassword')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }
        $user = D('Seller')->where('email = "'.I('email').'"')->find();
        if (!empty($user)) {
            $result['msg'] = '邮箱被占用';
            $this->ajaxReturn($result);
        }

        $data = [
            'company' => I('company'),
            'email' => I('email'),
            'password' => md5(I('password')),
            'parentId' => '0',
            'idRole' => I('role'),
            'linkman' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'gainType' => I('gainType'),
            'gainRate' => I('gainRate') ? I('gainRate') : '0',
            'isSsp' => I('isSsp'),
            'isAuditCreative' => I('isAuditCreative'),
            'creativeAuditType' => I('isAuditCreative') == 2 ? 1 : I('creativeAuditType'),
            'status' => '2',
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
        ];
        $sellerId = D('Seller')->add($data);
        if ($sellerId) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '添加失败';
        }
        $this->ajaxReturn($result);
    }

    public function edit() {
        $id = I('id');
        if (!IS_POST) {
            $roles = D('Role')->where(['idPlatform' => 2])->select();
            $seller = D('Seller')->join('left join role on seller.idRole = role.id')->field('seller.*, role.name')->where('seller.id = '.$id)->find();

            //检查是否有设置盈利模式权限
            $userId = $_SESSION['userInfo']['id'];
            $powerItems = D('User')->getParamItemsFromUserId($userId);
            $gainTypeHidden = isset($powerItems[51]) ? '' : 'display:none;';
            if ($seller['parentid'] != 0) {
                $gainTypeHiddenChild = 'display:none;';
                $gainTypeHidden = 'display:none;';
                $this->assign('gainTypeHiddenChild', $gainTypeHiddenChild);
                $auditTypeHidden = 'display:none;';
            }
            if ($seller['isauditcreative'] == 2) {
                $auditTypeHidden = 'display:none;';
            }
            $this->assign('auditTypeHidden', $auditTypeHidden);

            $this->assign('gainTypeHidden', $gainTypeHidden);

            $this->assign('roles', $roles);
            $this->assign('seller', $seller);
            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];
        $seller = D('Seller')->where('id != '.$id.' and email = "'.I('email').'"')->find();
        if (!empty($seller)) {
            $result['msg'] = '邮箱被占用';
            $this->ajaxReturn($result);
        }

        $data = [
            'email' => I('email'),
            'company' => I('company'),
            'linkman' => I('linkman'),
            'mobileTel' => I('mobileTel'),
            'idRole' => I('role'),
            'gainType' => I('gainType'),
            'gainRate' => I('gainRate'),
            'isSsp' => I('isSsp'),
            'isAuditCreative' => I('isAuditCreative'),
            'creativeAuditType' => I('isAuditCreative') == 2 ? 1 : I('creativeAuditType'),
            'muid' => $_SESSION['userInfo']['id'],
            'mtime' => date('Y-m-d H:i:s'),
        ];
        $ret = D('Seller')->where(['id'=>$id])->save($data);
        if ($ret) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '修改失败';
        }
        $this->ajaxReturn($result);
    }

    public function setStatus() {
        $id = I('id');
        $result = D('Seller')->setStatus($id);
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

        $result = D('Seller')->setPwd($id, $npwd);
        $this->ajaxReturn($result);
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
            //查询是否为全部dsp
            if ($_POST['white'] != '' && D("Buyer")->isAllRtb($_POST['white'])) {
                $_POST['white'] = -1;
            }
            if ($_POST['black'] != '' && D("Buyer")->isAllRtb($_POST['black'])) {
                $_POST['black'] = -1;
            }
            $data['buyerBlacklist'] = $_POST['black'];
            $data['buyerWhitelist'] = $_POST['white'];
            $data['exclude_ad_category'] = $excludedAdCategory;
            $data['exclude_ad_url'] = $_POST['exclude_ad_url'];
            $data['muid']    = $userInfo;
            $data['mtime']    = date("Y-m-d H:i:s",time());
            if($id){
                D("Seller")->where("id={$id}")->save($data);
                $result['status'] = "200";
                $result['url'] = "/seller/index";
            }

//            $this->ajaxReturn($result);
            $this->redirect('/seller/index');
        }else{
            $id = $_GET['id'];
            $seller = D("Seller")->field("exclude_ad_url,exclude_ad_category,buyerBlacklist,buyerWhitelist")->where("id={$id}")->find();
            $categoryAry =  json_decode($seller['exclude_ad_category'],true);
            $blackList = D('Buyer')->getDirectList($seller['buyerblacklist']);
            $whiteList = D('Buyer')->getDirectList($seller['buyerwhitelist']);

            $this->assign('blackList', $blackList);
            $this->assign('whiteList', $whiteList);
            $this->assign("categoryAry",$categoryAry['content']);
            $this->assign("seller",$seller);
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
}