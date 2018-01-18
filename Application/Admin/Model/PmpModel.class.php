<?php
namespace Admin\Model;
use Think\Model;
class PmpModel extends Model {

	protected $connection = 'DB_MAIN';

	/*
	 * 获取PMP列表
	 * @param  $sort 排序规则
	 * @param  $where筛选条件
	 * @param  $page 当前页
	 * @param  $rows 每页展示条数
	 *
	 */
	public function pmp_list($sort,$where,$page,$rows) {
		$res = array();
		$list = $this->table('pmp')
			->field('id,name,pmpType,price,level,saleType,startDate,endDate,status')
			->order($sort)
			->page($page,$rows)
			->where($where)
			->select();
		//$sql = $this->getLastSql();
		$count = $this->table('pmp')
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


	public function info($id){
		$res = $this->table('pmp')
			->where('id = '.$id)
			->find();
		if($res){
			return $res;
		}else{
			return false;
		}
	}


	/*
	 * 修改PMP状态
	 */
	public function setStatus($id, $data) {
		$result = $this->table('pmp')->where("id in ($id)")->save($data);
		return $result;
	}


	/*
	 * 获取媒体账户列表
	 */
	public function getMedia($id){
		$list = array();
		$list1 = array();
		$res = $this->table('seller')->select();
		foreach($res as $k => $v){
			$list[$v['id']] = $v;
			$list1[$v['company']] = $v['id'];
		}
		$result['data'] = $res;
		$result['data1'] = $list;
		$result['data2'] = $list1;
		return $result;
	}


	/*
	 * 获取媒体账户广告位列表
	 */
	public function getMediaTree($id){
		$list = array();
		$mediaList = array();
		$mediaInfo = array();
		$info = D("Pmp")->info($id);
		$placeList = D("Place")->getPlaceList();
		$placedirect = $info['placedirect'];
		$place_arr = json_decode($placedirect);
		foreach($place_arr as $k => $v){
			$place_arr[$k] = $placeList['placeId'][$v];
		}
		$media = $this->table('media')->select();
		$place = $this->table('place')->select();

		foreach($media as $k => $v){
			if($v['sellersonid'] > 0){
				$sellerId = $v['sellersonid'];
			}else{
				$sellerId = $v['sellerid'];
			}
			$mediaList[$sellerId][] = $v;
			$mediaInfo[$v['id']] = $v;
		}
		foreach($place as $k => $v){
			if($v['sellersonid'] > 0){
				$sellerId = $v['sellersonid'];
			}else{
				$sellerId = $v['sellerid'];
			}
			$placeList[$sellerId][$v['mediaid']][] = $v;
		}
		$res = $this->table('seller')->where('idRole = 3 and parentId = 0')->select();
		foreach($res as $k => $v){
			$mediaName = $mediaList[$v['id']];
			foreach($mediaName as $k1 =>$v1){
				$placeName = $placeList[$v['id']][$v1['id']];
				foreach($placeName as $k2 => $v2){
					$list[$v['company']]['name'] = $v['company'];
					$list[$v['company']]['type'] = 'folder';
					$list[$v['company']]['additionalParameters']['children'][$v1['name']]['name'] = $v1['name'];
					$list[$v['company']]['additionalParameters']['children'][$v1['name']]['type'] = 'folder';
					$list[$v['company']]['additionalParameters']['children'][$v1['name']]['additionalParameters']['children'][$v2['name']]['name'] = $v2['name'];
					$list[$v['company']]['additionalParameters']['children'][$v1['name']]['additionalParameters']['children'][$v2['name']]['type'] = 'item';
					if(in_array($v2['name'],$place_arr)){
						$list[$v['company']]['additionalParameters']['children'][$v1['name']]['additionalParameters']['children'][$v2['name']]['additionalParameters']['item-selected'] = true;
					}
				}
			}
		}
		$parent = $this->table('seller')->where('idRole = 2')->select();
		$child = $this->table('seller')->where('idRole = 3 and parentId != 0')->select();
		$childList = array();
		foreach($child as $k => $v){
			$childList[$v['parentid']][] = $v;
		}
		foreach($parent as $k => $v){
			$children = $childList[$v['id']];
			foreach($children as $k1 => $v1){
				$mediaName = $mediaList[$v1['id']];
				foreach($mediaName as $k2 =>$v2){
					$placeName = $placeList[$v1['id']][$v2['id']];
					foreach($placeName as $k3 => $v3){
						$list[$v['company']]['name'] = $v['company'];
						$list[$v['company']]['type'] = 'folder';
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['name'] = $v1['company'];
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['type'] = 'folder';
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['additionalParameters']['children'][$v2['name']]['name'] = $v2['name'];
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['additionalParameters']['children'][$v2['name']]['type'] = 'folder';
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['additionalParameters']['children'][$v2['name']]['additionalParameters']['children'][$v3['name']]['name'] = $v3['name'];
						$list[$v['company']]['additionalParameters']['children'][$v1['company']]['additionalParameters']['children'][$v2['name']]['additionalParameters']['children'][$v3['name']]['type'] = 'item';
						if(in_array($v3['name'],$place_arr)){
							$list[$v['company']]['additionalParameters']['children'][$v1['company']]['additionalParameters']['children'][$v2['name']]['additionalParameters']['children'][$v3['name']]['additionalParameters']['item-selected'] = true;
						}
					}
				}

			}
		}

		return $list;
	}

	public function edit($data,$id){
		$result = $this->table('pmp')->where("id =".$id)->save($data);
		return $result;
	}






}
?>