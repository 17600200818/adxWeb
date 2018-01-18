<?php
namespace Admin\Model;
use Think\Model;
class BuyerParamModel extends Model
{
    protected $connection = 'DB_MAIN';

    public function addBuyerParam($data) {
        $result = ['status' => 'error', 'msg' => ''];

        $id = $data['id'];
        if (!$id) {
            $result['msg'] = 'id为空';
            return $result;
        }

        $param = $this->where(['id' => $id])->find();
        if (empty($param)) {
            $data['token'] = MD5(date('Y-m-d H:i:s', time()) . mt_rand(100000, 999999));
            $data['priceKey'] = $this->generateRand(32);
            $data['bidUrl'] = '';
            $data['cuid'] = $_SESSION['userInfo']['id'];
            $data['ctime'] = date('Y-m-d H:i:s');
            $ret = $this->add($data);
        }else{
//            $data['token'] = MD5(date('Y-m-d H:i:s', time()) . mt_rand(100000, 999999));
//            $data['priceKey'] = $this->generateRand(32);
            $data['muid'] = $_SESSION['userInfo']['id'];
            $data['mtime'] = date('Y-m-d H:i:s');
            $ret = $this->where(['id' => $id])->save($data);
        }
        if ($ret) {
            $result['status'] = 'ok';
        }else {
            $result['msg'] = '添加失败';
        }
        return $result;
    }

    //生成价格解密密钥
    public function generateRand($l) {
        $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        srand((double) microtime() * 1000000);
        $rand = '';
        for ($i = 0; $i < $l; $i++) {
            $rand.= strtoupper(dechex(ord($c[rand() % strlen($c)])));
        }
        return $rand;
    }
}