<?php
namespace V1\Model;
use Think\Model;
class BuyerModel extends Model {

	protected $connection = 'DB_MAIN';

    public function buyer_list($sort,$where,$page,$rows) {
        $res = array();
        $list = $this->order($sort)
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
            'mid' => $_SESSION['user_id'],
            'mtime' => date('Y-m-d h:i:s'),
        ];
        $result = $this->where($where)->save($data);
        return $result;
    }

    public function setPwd($id, $npwd) {
        $result = ['status' => 'error', 'msg' => ''];

        $data = [
            'password' => md5($npwd),
            'muid' => $_SESSION['userInfo']['id'],
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

    public function addBuyer($data) {
        $result = ['status' => 'error', 'msg' => ''];
        $email = $data['email'];
        $buyer = $this->where(['email' => $email])->find();
        if (!empty($buyer)) {
            $result['msg'] = '该邮箱已经注册';
            return $result;
        }

        $buyerId = $this->add($data);

        if ($buyerId) {
            $param = [
                'id' => $buyerId,
                'cuid' => $data['cuid'],
                'ctime' => $data['ctime'],
            ];
            $id = D('BuyerParam')->addBuyerParam($param);
            if ($id) {
                $result['status'] = 'ok';
            }else {
                $result['msg'] = '添加买方参数失败';
            }
        }else {
            $result['msg'] = '添加买方失败';
        }

        return $result;
    }
    
    /**
     * 判断dsp  或者 adn  是否全选
     */
    public function idSelect() {
        $buyers = D("buyer")->where("status = 2")->select();
        $dsp_temp = "";
        $adn_temp = "";
        $is = 0;
        $buycount = 0;
        $is2 = 0;
        $buycount2 = 0;
        foreach ($buyers as $key => $val){
        
            if($val['buytype'] == 1){
                if(strpos($_REQUEST['dspIds'],$val['id'].",")  !== false){
                    $is++;
                }
                $buycount++;
            }else{
                if(strpos($_REQUEST['adnIds'],$val['id'].",")  !== false){
                    $is2++;
                }
                $buycount2++;
            }
        
        }
        if($is == $buycount){
            if ($is == 0) {
                $_REQUEST['dspIds'] = "";
            }else {
                $_REQUEST['dspIds'] = "-1";
            }
        }
        
        if($is2 == $buycount2){
            if ($is2 == 0) {
                $_REQUEST['adnIds'] = "";
            }else {
                $_REQUEST['adnIds'] = "-1";
            }
        }
    }

    public function getIdKeyArr($field = '*') {
        $sellers = $this->field($field)->select();

        $resultArr = [];
        foreach ($sellers as $v) {
            $resultArr[$v['id']] = $v;
        }

        return $resultArr;
    }

    public function getDirectList($list) {
        $list = trim($list, ',');
        if ($list == -1) {
            $leftWhere = 0;
            $rightWhere = 1;
        }elseif ($list) {
            $leftWhere = "id not in ($list)";
            $rightWhere = "id in ($list)";
        }else {
            $leftWhere = 1;
            $rightWhere = 0;
        }

        $leftArr = $this->field('*')->where($leftWhere." and buyType=1")->select();
        $rightArr = $this->field('*')->where($rightWhere." and buyType=1")->select();

        $result = array('leftArr' => $leftArr, 'rightArr' => $rightArr);
        return $result;
    }

    public function isAllRtb($ids) {
        $ids = trim($ids, ',');
        $idNum = count(explode(',', $ids));
        $rtbsNum = $this->where('buyType=1')->count();

        return $idNum == $rtbsNum;
    }
}
?>