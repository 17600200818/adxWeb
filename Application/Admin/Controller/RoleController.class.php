<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class RoleController extends BaseController {
    public function index() {
        if (I('type') == 'getList') {
            $this->getList();
            exit;
        }
        $flatforms = D('PowerItem')->getPlatforms();

        $this->assign('flatforms', $flatforms);
        $this->display();
    }

    public function add() {
        if (I('ajaxGetHtml')) {
            $idPlatform = I('idPlatform');
            if ($idPlatform) {
                $powerItemList = D('PowerItem')->getPowerItemHtml(0, 0, $idPlatform);
            }else {
                $powerItemList = D('PowerItem')->getPowerItemHtml();
            }
            echo $powerItemList;
            exit;
        }

        if (!IS_POST) {
            $powerItemList = D('PowerItem')->getPowerItemHtml();
            $platForms = D('PowerItem')->getPlatforms();

            $this->assign('platForms', $platForms);
            $this->assign('powerItemList', $powerItemList);
            $this->display();
            exit;
        }

        $role = D('Role')->where(['name' => I('name')])->find();
        if (!empty($role)) {
            redirect('/role/add',2,'用户名重复');
            exit;
        }

        $ctime = date('Y-m-d H:i:s');
        $data = [
            'idPlatform' => I('platform'),
            'name' => I('name'),
            'status' => 1,
            'remark' => I('remark'),
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => $ctime,
        ];
        $roleId = D('Role')->add($data);

        if (!empty(I('powerItem')) && $roleId) {
            $dataList = array();
            foreach (I('powerItem') as $v) {
                $dataList[] = [
                    'idPlatform' => I('platform'),
                    'idRole' => $roleId,
                    'idPowerItem' => $v,
                    'status' => 1,
                    'cuid' => $_SESSION['userInfo']['id'],
                    'ctime' => $ctime,
                ];
            }
            D('RolePowerItems')->addAll($dataList);
        }

        $this->redirect('role/index');
    }

    public function getList() {
        $str = '';
        $roles = D('Role')->select();
        $platforms = D('PowerItem')->getPlatforms();
        $status = I('status');
        $platform = I('platform');
        $name = I('name');

        foreach ($roles as $v) {
            if ($status && $v['status'] != $status) {
                continue;
            }
            if ($platform && $v['idplatform'] != $platform) {
                continue;
            }
            if ($name && !strstr($v['name'], $name)) {
                continue;
            }

            $v['idplatform'] = $platforms[$v['idplatform']]['name'];
            $v['status'] = $v['status'] == 1 ? 'checked' : '';
            $str .= '<tr>
                      <td>'.$v['id'].'</td>
                      <td>'.$v['name'].'</td>
                      <td>'.$v['idplatform'].'</td>
                      <td>'.$v['remark'].'</td>
                      <td class="hidden-480">
                        <input type="checkbox" '.$v['status'].' class="ace ace-switch ace-switch-4 statusVal" onclick="changeStatus('.$v['id'].')" />
                        <span class="lbl middle"></span>
                      </td>
                     
                      <td>
                        <a href="/role/edit/id/'.$v['id'].'"><button type="button" class="btn btn-xs btn-warning">修改</button></a>
                      </td>
                    </tr>';
        }

        if($str == '') {
            $str = '<tr><td colspan="8" style="text-align:center;">无数据</td></tr>';
        }

        echo $str;
    }

    public function edit() {
        $id = I('id');
        $powerItems = D('RolePowerItems')->getPowerItemsFromRoleId($id);

        if (I('ajaxGetHtml')) {
            $idPlatform = I('idPlatform');
            if ($idPlatform) {
                $powerItemList = D('PowerItem')->getPowerItemHtml(0, $powerItems, $idPlatform);
            }else {
                $powerItemList = D('PowerItem')->getPowerItemHtml(0, $powerItems);
            }
            echo $powerItemList;
            exit;
        }

        if (!I('post.name')) {
            $platForms = D('PowerItem')->getPlatforms();
            $role = D('Role')->join('left join platform on role.idPlatform=platform.id ')->field('role.*, platform.name as rolename')->where(['role.id'=>$id])->find();
            $powerItemList = D('PowerItem')->getPowerItemHtml(0, $powerItems, $role['idplatform']);

            $this->assign('role', $role);
            $this->assign('platForms', $platForms);
            $this->assign('powerItemList', $powerItemList);
            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];

        if (!I('name') || !I('platform')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }

        $data = [
            'idPlatform' => I('platform'),
            'name' => I('name'),
            'remark' => I('remark'),
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
        ];
        $result = D('Role')->where(['id' => $id])->save($data);

        //修改权限角色列表
        $add = array();
        $del = array();
        $add = array_diff(I('powerItem'), $powerItems);
        $del = array_diff($powerItems, I('powerItem'));

        if (!empty($del)) {
            $delStr = implode(',', $del);
            D('RolePowerItems')->where('idRole='.$id.' and idPowerItem in ('.$delStr.')')->save(['status' => 2]);
        }
        if (!empty($add)) {
            $dataList = array();
            foreach ($add as $v) {
                $rpitem = D('RolePowerItems')->where('idRole='.$id.' and idPowerItem ='.$v)->find();
                if (empty($rpitem)) {
                    $data = [
                        'idPlatform' => I('platform'),
                        'idRole' => $id,
                        'idPowerItem' => $v,
                        'status' => 1,
                        'cuid' => 6000,
                        'ctime' => date('Y-m-d H:i:s'),
                    ];
                    D('RolePowerItems')->add($data);
                }else {
                    D('RolePowerItems')->where('idRole='.$id.' and idPowerItem ='.$v)->save(['status' => 1]);
                }
            }
        }

        $result = ['status' => 'ok', 'msg' => ''];
        $this->ajaxReturn($result);
    }

    public function setStatus() {
        $id = I('id');
        $result = D('Role')->setStatus($id);
        return $result;
    }

}