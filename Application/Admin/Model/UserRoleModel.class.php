<?php
namespace Admin\Model;
use Think\Model;
class UserRoleModel extends Model {
    
    protected $connection = 'DB_MAIN';
    
	public function byIdList($where){
	  return $this->find($where);
	}
}
?>