<?php
namespace V1\Model;
use Think\Model;
class PlaceModel extends Model {
	protected $connection = 'DB_MAIN';
	/*
	 * 获取广告位尺寸列表
	 *
	 */
	public function getSizeList() {
		$res = $this->table('sys_place_size')
			->field('deviceType,width,height')
			->select();
		if ($res) {
			return $res;
		} else {
			return false;
		}
	}


	/*
	 * 获取广告位列表
	 */
	public function getPlaceList(){
		$res = array();
		$placeId = array();
		$placeName = array();
		$list = $this->table('place')
			->field('name,id')
			->select();
		if ($list) {
			foreach($list as $k => $v){
				$placeId[$v['id']] = $v['name'];
				$placeName[$v['name']] = $v['id'];
			}
			$res['placeId'] = $placeId;
			$res['placeName'] = $placeName;
			return $res;
		} else {
			return false;
		}
	}

	public function getIdKeyArr($field = '*') {
		$places = $this->field($field)->select();

		$resultArr = [];
		foreach ($places as $v) {
			$resultArr[$v['id']] = $v;
		}

		return $resultArr;
	}

}
?>
