<?php
namespace Buyer\Model;
use Think\Model;
class CreativeAuditModel extends Model {

	protected $connection = 'DB_MAIN';
	/*
	 * 获取素材上传列表
	 *
	 */
	public function getcreativeaudit($crid,$idBuyer){
		$res = $this->table('creative_audit c')
			->join('seller s ON c.idSeller = s.id')
			->where('c.remark = 1 and s.parentId = 0 and c.idBuyer = '.$idBuyer.' and s.isAuditCreative = 1 and c.crid = '.$crid)
			->field('c.status,s.company,c.allow,c.id,c.mediaCrid')
			->select();
		if ($res) {
			return $res;
		} else {
			return false;
		}

	}


	/*
	 * 设置汇选审核状态
	 */
	public function setAuditStatus($id,$status){
		$data['allow'] = $status;
		$res = $this->table('creative_audit')
			->where('id ='.$id)
			->save($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

}
?>