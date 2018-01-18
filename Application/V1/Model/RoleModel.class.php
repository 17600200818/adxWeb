<?php
namespace V1\Model;
use Think\Model;
class RoleModel extends Model {

    protected $connection = 'DB_MAIN';

    //修改状态
    public function setStatus($id) {
        $where = ['id' => $id];
        $powerItem = $this->where($where)->find();
        $status = $powerItem['status'] == 1 ? 2 : 1;
        $data = [
            'status' => $status,
            'mid' => $_SESSION['userInfo']['id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $result = $this->where($where)->save($data);
        return $result;
    }
}
?>