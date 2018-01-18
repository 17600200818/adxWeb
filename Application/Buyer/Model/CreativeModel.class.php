<?php
namespace Buyer\Model;
use Think\Model;
class CreativeModel extends Model {

	protected $connection = 'DB_MAIN';
	/*
	 * 获取素材列表
	 * @param  $where 素材筛选条件
	 *
	 */
	public function getcreativelist($where,$page,$rows){
		$res = array();
		$list = $this->table('creative')
			->where($where)
			->page($page,$rows)
			->order("ctime desc")
			->select();
		$count = $this->table('creative')
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
	 * 获取素材详情
	 */

	public function getCreativeDetail($id){
		$res = array();
		$list = $this->table('creative')
			->find($id);
		if ($list) {
			return $list;
		} else {
			return false;
		}
	}

	/*
	 * 修改素材审核状态
	 */
	public function setStatus($id,$data){
		if (!$id) {
			return false;
		}
		$res = $this->table('creative')->where("id in ($id)")->save($data);
		$sql = $this->getLastSql();
		if ($res) {
			return true;
		} else {
			return false;
		}
	}
}
?>