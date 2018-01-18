<?php
namespace Buyer\Model;
use Think\Model;
class AdvertiserModel extends Model {

	protected $connection = 'DB_MAIN';

	/*
	 * 获取广告主列表
	 * @param  $sort 广告主排序规则
	 * @param  $where 广告主筛选条件
	 * @param  $page 当前页
	 * @param  $rows 每页展示条数
	 *
	 */
	public function advertiser_list($sort,$where,$page,$rows) {
		$res = array();
		$list = $this->table('advertiser')
			->field('id,name,idBuyer,idBuyerAdvertiser,category1,category2,siteName,domain,ctime,status')
			->order($sort)
			->page($page,$rows)
			->where($where)
			->select();

		$count = $this->table('advertiser')
			->where($where)
			->count();

		if ($list && $count) {
			$res['data'] = $list;
			$res['count'] = $count;
			return $res;
		} else {
			return false;
		}
	}


	/*
	 * 获取广告主详细信息
	 * @param $id advertiser表自增ID
	 */
	public function advertiser_detail($id) {
		$condition['id'] = $id;
		$res = $this->table('advertiser')->where($condition)->find();
		if ($res) {
			return $res;
		} else {
			return false;
		}
	}



	/*
	 * 修改广告主状态
	 */
	public function setStatus($id, $data) {
		$result = $this->table('advertiser')->where("id in ($id)")->save($data);
		return $result;
	}


	/*
	 * 编辑广告主
	 */
	public function edit($id,$data){
		$result = $this->table('advertiser')->where("id = ".$id)->save($data);
		return $result;
	}


	/*
	 * 获取dsp列表
	 */
	public function getDspList(){
		$res = array();
		$list = $this->table('buyer')
			->field("id,company")
			->select();
		if($list){
			foreach($list as $k => $v){
				$res[$v['id']]['company'] = $v['company'];
				$res[$v['id']]['id'] = $v['id'];
			}
			return $res;
		}else{
			return false;
		}
	}


	/*
	 * 获取dspID
	 */
	public function getDspId($company){
		$res = $this->table('buyer')
			->where('company like "'.$company.'%"')
			->field("id,company")
			->select();
		if($res){
			return $res;
		}else{
			return false;
		}
	}

	public function getAdvertiserList($idBuyer = ''){
		$res = array();
		$list = $this->table('advertiser')
			->field('id,name')
			->where('idBuyer = '.$idBuyer)
			->select();
		if($list){
			foreach($list as $k =>$v){
				$res[$v['id']]['name'] = $v['name'] ? $v['name'] : $v['id'];
				$res[$v['id']]['id'] = $v['id'];
			}
			return $res;
		}else{
			return false;
		}
	}


}
?>