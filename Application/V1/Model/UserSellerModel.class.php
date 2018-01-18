<?php
namespace V1\Model;
use Think\Model;
class UserSellerModel extends Model {

    protected $connection = 'DB_MAIN';

    public function getList($id, $data) {
        $userSellers = $this->where(['idUser' => $id])->select();
        $ubArr = [];
        foreach ($userSellers as $v) {
            $ubArr[$v['idseller']] = $v['allow'];
        }
        foreach ($data as $k => $v) {
            if (isset($ubArr[$v['id']])) {
//                $data[$k]['allow'] = $ubArr[$v['id']] == 1 ? '<span class="label label-sm label-info arrowed arrowed-righ">允许</span>' : '<span class="label label-sm label-inverse arrowed-in">拒绝</span>';
                $data[$k]['allow'] = $ubArr[$v['id']] == 1 ? 'checked="checked"' : '';
            }else {
//                $data[$k]['allow'] = '<span class="label label-sm label-warning">未操作</span>';
                $data[$k]['allow'] = '';
            }
        }

        return $data;
    }
}