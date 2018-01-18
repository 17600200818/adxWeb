<?php
namespace V1\Model;
use Think\Model;
class AdvertiserFileModel extends Model {

	protected $connection = 'DB_MAIN';

	/*
	 * 获取广告主资质列表
	 */
	public function getAdaudit($id,$sort){
		$res = $this->table('advertiser_file')
			->field('id,code,name,filePath,status')
			->order($sort)
			->where('idAdvertiser = '.$id)
			->select();
		if($res){
			return $res;
		}else{
			return false;
		}
	}


	/*
	 * 添加广告主资质
	 */
	public function upload($data){
		$res = $this->table("advertiser_file")->add($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}


	/*
	 * 修改广告主资质状态
	 */
	public function setAuditStatus($id, $data) {
		$result = $this->table('advertiser_file')->where("id in ($id)")->save($data);
		return $result;
	}









}
?>