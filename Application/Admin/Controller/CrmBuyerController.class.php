<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class CrmBuyerController extends BaseController {

    public function index() {
        $id = I('id');
        if (IS_POST) {
            $sidx = I('sidx') ? I('sidx') : 'id';
            $sord = I('sord') ? I('sord') : 'desc';
            $page = $_POST['page'] != ''?$_POST['page']:1;
            $rows = $_POST['rows'] != ''?$_POST['rows']:999;
            $sort = $sidx." ".$sord;
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
            $i = 1;
            foreach($res['data'] as $k => $v){
                $data[$k]['sort'] = $i;
                $data[$k]['company'] = $v['company'];
                $data[$k]['email'] = $v['email'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['buytype'] = $v['buytype'] == 1 ? '<span class="label label-sm label-inverse arrowed-in">rtb</span>' : '<span class="label label-sm label-info arrowed arrowed-righ">adn</span>';
                $data[$k]['creativeaudittype'] = $v['creativeaudittype'] == 1 ? '<span class="label label-sm label-warning">先审后投</span>' : '<span class="label label-sm label-success">先投后审</span>';
                $data[$k]['status'] = $v['status'] == 2 ? '<span class="label label-sm label-info arrowed arrowed-righ">正常</span>'  : '<span class="label label-sm label-inverse arrowed-in">停用</span>';

                $i++;
            }

            $data = D('UserBuyer')->getList($id, $data);
            $result['data'] = $data;
            $result['page'] = $page;
            $result['total'] = ceil($res['count']/$rows);
            $result['status'] = 'ok';
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
        $editResult = D('UserBuyer')->where('idUser = '.$idUser.' and allow = 1')->save($editData);
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
            $editResult = D('UserBuyer')->where('idUser = '.$idUser.' and idBuyer in ('.$idList.')')->save($editData);
            if ($editResult === false) {
                $result['msg'] = '修改失败';
                $this->ajaxReturn($result);
            }
        }

        if (!empty($idArr)) {
            $idBuyers = D('UserBuyer')->field('idBuyer')->where(['idUser' => $idUser])->select();
            $idBuyerArr = [];
            foreach ($idBuyers as $v) {
                $idBuyerArr[] = $v['idbuyer'];
            }
            $addArr = array_diff($idArr, $idBuyerArr);
            if (!empty($addArr)) {
                $addList = [];
                foreach ($addArr as $v) {
                    $addList[] = [
                        'idUser' => $idUser,
                        'idBuyer' => $v,
                        'allow' => $allow,
                        'cuid' => $_SESSION['userInfo']['id'],
                        'ctime' => date('Y-m-d h:i:s'),
                    ];
                }
                $addResult = D('UserBuyer')->addAll($addList);
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