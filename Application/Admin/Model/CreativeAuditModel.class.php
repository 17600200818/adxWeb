<?php
namespace Admin\Model;
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
			->where('s.parentId = 0 and c.idBuyer = '.$idBuyer.' and s.isAuditCreative = 1 and c.crid = '.$crid)
			->field('c.status,s.company,c.allow,c.id,c.mediaCrid,c.crid,c.errorId,c.remark')
			->select();
		if ($res) {
		    foreach ($res as $k => $v) {
		        if (!$v['mediacrid']) $res[$k]['mediacrid'] = ' ';
            }
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