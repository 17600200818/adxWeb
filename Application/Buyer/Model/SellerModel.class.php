<?php
namespace Buyer\Model;
use Think\Model;
class SellerModel extends Model {

    protected $connection = 'DB_MAIN';

    public function getList($sort,$where,$page,$rows) {
        $res = array();
        $join = 'left join role on seller.idRole = role.id';
        $field = 'seller.*, role.name as rolename';
        $list = $this->order($sort)
            ->join($join)
            ->field($field)
            ->page($page,$rows)
            ->where($where)
            ->select();

        $count = $this->where($where)->count();

        if ($list && $count) {
            $res['data'] = $list;
            $res['count'] = $count;
            return $res;
        } else {
            return false;
        }
    }

    public function setStatus($id) {
        $where = ['id' => $id];
        $buyer = $this->where($where)->find();
        $status = $buyer['status'] == 2 ? 4 : 2;
        $data = [
            'status' => $status,
            'mid' => $_SESSION['buyer']['user_id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $result = $this->where($where)->save($data);
        return $result;
    }

    public function setPwd($id, $npwd) {
        $result = ['status' => 'error', 'msg' => ''];

        $data = [
            'password' => md5($npwd),
            'muid' => $_SESSION['buyer']['userInfo']['id'],
            'mtime' => date('Y-m-d H:i:s'),
        ];
        $r = $this->where(['id' => $id])->save($data);

        if ($r) {
            $result['status'] = 'ok';
            $result['msg'] = '修改成功';
        }else {
            $result['msg'] = '修改失败';
        }

        return $result;
    }

    public function getIdKeyArr($field = '*') {
        $sellers = $this->field($field)->select();

        $resultArr = [];
        foreach ($sellers as $v) {
            $resultArr[$v['id']] = $v;
        }

        return $resultArr;
    }
}
?>