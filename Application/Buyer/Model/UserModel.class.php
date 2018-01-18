<?php
namespace Buyer\Model;
use Think\Model;
class UserModel extends Model {
    
    protected $connection = 'DB_MAIN';

    public function getList($sort,$where,$page,$rows) {
        $res = array();
        $join = 'left join role on v_user.idRole = role.id';
        $field = 'v_user.*, role.name as rolename';
        $list = $this->table('v_user')->order($sort)
            ->join($join)
            ->field($field)
            ->page($page,$rows)
            ->where($where)
            ->select();

        $count = $this->table('v_user')->where($where)->count();

        if ($list && $count) {
            $res['data'] = $list;
            $res['count'] = $count;
            return $res;
        } else {
            return false;
        }
    }

	public function findByEmail($email){
		$where = array('email'=>$email);
		$userInfo = $this->table('v_user')->where($where)->select();
		if(empty($userInfo))
			return null;

		return $userInfo[0];
	}

    public function setStatus($id) {
        $where = ['id' => $id];
        $user = $this->where($where)->find();
        $status = $user['status'] == 2 ? 4 : 2;
        $data = [
            'status' => $status,
            'mid' => $_SESSION['buyer']['user_id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $result = $this->where($where)->save($data);
        return $result;
    }

    public function addUser($data) {
        $result = false;

        $idRole = $data['idRole'];
        unset($data['idRole']);
        $userId = $this->add($data);
        if ($userId) {
            $userRole = [
                'idUser' => $userId,
                'idPlatform' => 1,
                'idRole' => $idRole,
                'cuid' => $_SESSION['buyer']['userInfo']['id'],
                'ctime' => date('Y-m-d H:i:s'),
            ];
            $result = D('UserRole')->add($userRole);
        }

        return $result;
    }

    public function editUser($id, $data) {
        $result = ['status' => 'error', 'msg' => ''];


        $idRole = $data['idRole'];
        unset($data['idRole']);

        $user = $this->where('email = "'.$data['email'].'" and id != '.$id)->find();
        if(!empty($user)) {
            $result['msg'] = '邮箱被占用';
            return $result;
        }

        $ret = $this->where(['id'=> $id])->save($data);
        if ($ret === false) {
            $result['msg'] = '修改失败';
            return $result;
        }

        $userRole = [
            'idRole' => $idRole,
            'muid' => $_SESSION['buyer']['userInfo']['id'],
            'mtime' => date('Y-m-d H:i:s'),
        ];

        $ret = D('UserRole')->where('idUser = '.$id)->save($userRole);
        $sql = D('UserRole')->getLastSql();
        if ($ret) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '修改用户角色表失败';
            $result['sql'] = $sql;
        }

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

    public function setPwdHead($id, $pwd, $npwd) {
        $result = ['status' => 'error', 'msg' => ''];

        $user = $this->where(['id' => $id, 'password' => md5($pwd)])->find();
        if (empty($user)) {
            $result['msg'] = '原密码错误';
            return $result;
        }

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

    public function getParamItemsFromUserId($id) {
        $powerItems = $this->table('v_user')->join('left join v_role_power_items as role on v_user.idRole = role.idRole')->field('role.controller, role.action, role.idPowerItem')->where(['v_user.id' => $id, 'role.roleStatus = 1'])->select();

        $powerItemsArr = array();
        foreach ($powerItems as $v) {
            $powerItemsArr[$v['idpoweritem']] = strtolower($v['controller']).'/'.strtolower($v['action']);
        }

        return $powerItemsArr;
    }

}
?>