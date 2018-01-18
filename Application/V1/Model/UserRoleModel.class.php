<?php
namespace V1\Model;
use Think\Model;
class UserRoleModel extends Model {
    
    protected $connection = 'DB_MAIN';
    
	public function byIdList($where){
	  return $this->find($where);
	}
}
?>