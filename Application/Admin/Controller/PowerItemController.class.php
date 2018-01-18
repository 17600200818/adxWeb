<?php

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;
class PowerItemController extends BaseController {

    public function index() {
        if (I('type') == 'getList') {
            $this->getList();
            exit;
        }
        $flatforms = D('PowerItem')->getPlatforms();
        $this->assign('flatforms', $flatforms);
        $this->display();
    }

    public function edit() {
        $id = I('id');
        if (!IS_POST) {
            $powerItem = D('PowerItem')->where(['id'=>$id])->find();
            $platForm = D('PowerItem')->table('platform')->where(['id' => $powerItem['idplatform']])->find();

            $this->assign('platForm', $platForm);
            $this->assign('powerItemArr', $powerItem);
            $this->display();
            exit;
        }

        if (!I('name')) {
            $this->display();
            echo "<script>alert('修改失败');</script>";
            exit;
        }

        $data = [
//            'idPlatform' => I('platform'),
            'name' => I('name'),
            'controller' => I('controller'),
            'action' => I('action'),
            'displayFlag' => I('displayFlag') == 'on' ? 1 : 2,
            'itemOrder' => I('itemOrder'),
            'remark' => I('remark'),
            'muid' => $_SESSION['userInfo']['id'],
            'mtime' => date('Y-m-d H:i:s'),
        ];
        $result = D('PowerItem')->where(['id' => $id])->save($data);
        $this->redirect('powerItem/index');
    }

    public function add() {
        $platForms = D('PowerItem')->getPlatforms();
        $powerArr = D('PowerItem')->powerItemList();
        $this->assign('platForms', $platForms);

        if( !IS_POST ) {
            $displaySel = 1;
            if (I('get.id')) {
                $powerArr = D('PowerItem')->where('id='.I('get.id'))->find();
                $platFormId = $powerArr['idplatform'];
                $platForm = D('PowerItem')->table('platform')->where(['id' => $platFormId])->find();
                $this->assign('platForm', $platForm);

                $displaySel = 0;
            }

            $this->assign('displaySel', $displaySel);
            $this->assign('powerItems', $powerArr);
            $this->display();
            exit;
        }

        $result = ['status' => 'error', 'msg' => ''];
        if (!I('name') || !I('platform')) {
            $result['msg'] = '添加失败';
            $this->ajaxReturn($result);
        }

        if (I('parentId') == 0) {
            $level = 1;
        }else {
            $parent = D('PowerItem')->where(['id' => I('parentId')])->find();
            $level = $parent['level'] + 1;
        }
        $data = [
            'idPlatform' => I('platform'),
            'name' => I('name'),
            'parentId' => I('parentId'),
            'level' => $level,
            'controller' => I('controller'),
            'action' => I('action'),
            'displayFlag' => I('displayFlag') == 'on' ? 1 : 2,
            'itemOrder' => I('itemOrder'),
            'status' => I('status') == 'on' ? 1 : 2,
            'remark' => I('remark'),
            'cuid' => $_SESSION['userInfo']['id'],
            'ctime' => date('Y-m-d H:i:s'),
        ];
        $platId = D('PowerItem')->add($data);
        if ($platId) {
            $result['status'] = 'ok';
        }else{
            $result['msg'] = '添加失败';
        }

        $this->ajaxReturn($result);
    }

    public function getList() {
        $powerArr = D('PowerItem')->powerItemList();
        $platforms = D('PowerItem')->getPlatforms();
        $str = '';
        $status = I('status');
        $platform = I('flatform');

        foreach ($powerArr as $v) {
            if ($status && $v['status'] != $status) {
                continue;
            }
            if ($platform && $v['idplatform'] != $platform) {
                continue;
            }

            $v['idplatform'] = $platforms[$v['idplatform']]['name'];
            $v['displayflag'] = $v['displayflag'] == 1 ? '显示' : '不显示' ;
            $v['status'] = $v['status'] == 1 ? 'checked' : '';
            $str .= '<tr>
                      <td>'.$v['id'].'</td>
                      <td>'.$v['idplatform'].'</td>
                      <td>'.$v['name'].'</td>
                      <td>'.$v['controller'].'</td>
                      <td>'.$v['action'].'</td>
                      <td>'.$v['parentid'].'</td>
                      <td>'.$v['displayflag'].'</td>
                      <td class="hidden-480">
                        <input type="checkbox" '.$v['status'].' class="ace ace-switch ace-switch-4 statusVal" onclick="changeStatus('.$v['id'].')" />
                        <span class="lbl middle"></span>
                      </td>
                      <td>
                        <a href="/powerItem/edit/id/'.$v['id'].'"><button type="button" class="btn btn-xs btn-warning">修改</button></a>
                        <a href="/powerItem/add/id/'.$v['id'].'">
                          <button type="button" class="btn btn-xs btn-success">
                            添加
                            <i class="glyphicon glyphicon-plus"></i>
                          </button>
                        </a>
                      </td>
                    </tr>';
        }

        if($str == '') {
            $str = '<tr><td colspan="8" style="text-align:center;">无数据</td></tr>';
        }

        echo $str;
    }

    public function setStatus() {
        $id = I('id');
        $result = D('PowerItem')->editStatus($id);
        $this->ajaxReturn($result);
    }
}