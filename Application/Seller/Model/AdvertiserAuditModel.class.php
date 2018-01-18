<?php
namespace Seller\Model;
use Think\Model;
class AdvertiserAuditModel extends Model {

	protected $connection = 'DB_MAIN';
	/*
	 * 获取广告主上传列表
	 *
	 */
	public function getStatus($aid){
		$res = $this->table('advertiser_audit a')
			->join('seller s ON a.idSeller = s.id')
			->where('a.remark = 1 and s.parentId = 0 and s.isAuditCreative = 1 and a.idAdvertiser = '.$aid )
			->field('a.status,s.company,a.allow,a.id')
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
		$res = $this->table('advertiser_audit')
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