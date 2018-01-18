<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class SellerCrmController extends BaseController {

    public function index() {
        $id = I('id');
        if (IS_POST) {
            $sidx = I('sidx') ? I('sidx') : 'id';
            $sord = I('sord') ? I('sord') : 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:999;
            $sort = $sidx." ".$sord;
            $where = 'seller.parentId = 0';
            if (I('status')) {
                $where .= ' and status = '.I('status');
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

            $res =  D("Seller")->getList($sort,$where,$page,$rows);
            $i = ($page-1)*$rows + 1;
            foreach($res['data'] as $k => $v){
                $data[$k]['sort'] = $i;
                $data[$k]['company'] = $v['company'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['rolename'] = $v['rolename'];
                $data[$k]['isssp'] = $v['isssp'] == 1 ? '<span class="label label-sm label-info arrowed arrowed-righ">是</span>' : '<span class="label label-sm label-inverse arrowed-in">否</span>';
                $data[$k]['isauditcreative'] = $v['isauditcreative'] == 1 ? '<span class="label label-sm label-warning">审核</span>' : '<span class="label label-sm label-success">不审核</span>';
                $data[$k]['creativeaudittype'] = $v['creativeaudittype'] == 1 ? '<span class="label label-sm label-warning">先审后投</span>' : '<span class="label label-sm label-success">先投后审</span>';
                $data[$k]['status'] = $v['status'] == 2 ? '<span class="label label-sm label-info arrowed arrowed-righ">正常</span>'  : '<span class="label label-sm label-inverse arrowed-in">停用</span>';
                $data[$k]['idrole'] = $v['idrole'];

                $i++;
            }

            $data = D('UserSeller')->getList($id, $data);
            $result['data'] = $data;
            $result['page'] = $page;
            $result['status'] = 'ok';
            $result['total'] = ceil($res['count']/$rows);
            $result['records'] = $res['count'];
            $this->ajaxReturn($result);
        }
        $this->assign('userId', $id);
        $this->display('');
    }

    public function set() {
        $result = ['status' => 'error', 'msg' => ''];
        $idArr = I('idList');
        $allow = I('allowType');
        $idList = implode(',', $idArr);
        $idUser = I('userId');
        if ($idArr == '') {
            $idArr = [];
        }

        $editData = [
            'allow' => 2,
            'muid' => $_SESSION['userInfo']['id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $editResult = D('UserSeller')->where('idUser = '.$idUser.' and allow = 1')->save($editData);
        if ($editResult === false) {
            $result['msg'] = '修改失败';
            $this->ajaxReturn($result);
        }

        if ($idList != '') {
            $editData = [
                'allow' => $allow,
                'muid' => $_SESSION['userInfo']['id'],
                'mtime' => date('Y-m-d h:i:s'),
            ];
            $editResult = D('UserSeller')->where('idUser = '.$idUser.' and idSeller in ('.$idList.')')->save($editData);
            if ($editResult === false) {
                $result['msg'] = '修改失败';
                $this->ajaxReturn($result);
            }
        }

        if (!empty($idArr)) {
            $idSellers = D('UserSeller')->field('idSeller')->where(['idUser' => $idUser])->select();
            $idSellerArr = [];
            foreach ($idSellers as $v) {
                $idSellerArr[] = $v['idseller'];
            }
            $addArr = array_diff($idArr, $idSellerArr);
            if (!empty($addArr)) {
                $addList = [];
                foreach ($addArr as $v) {
                    $addList[] = [
                        'idUser' => $idUser,
                        'idSeller' => $v,
                        'allow' => $allow,
                        'cuid' => $_SESSION['userInfo']['id'],
                        'ctime' => date('Y-m-d h:i:s'),
                    ];
                }
                $addResult = D('UserSeller')->addAll($addList);
                if ($addResult == false) {
                    $result['msg'] = '添加失败';
                    $this->ajaxReturn($result);
                }
            }
        }

        $result['status'] = 'ok';
        $this->ajaxReturn($result);
    }
}