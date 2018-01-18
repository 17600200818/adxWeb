<?php
namespace Buyer\Model;
use Think\Model;
class RolePowerItemsModel extends Model {

    protected $connection = 'DB_MAIN';

    public function getPowerItemsFromRoleId($id) {
        $RolePowerItemsArr = $this->where(['idRole' => $id, 'status' => 1])->select();
        $powerItemsArr = array();
        foreach ($RolePowerItemsArr as $v) {
            $powerItemsArr[] = $v['idpoweritem'];
        }

        return $powerItemsArr;
    }
}
?>