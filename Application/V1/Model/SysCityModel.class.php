<?php
namespace V1\Model;
use Think\Model;
class SysCityModel extends Model {

	protected $connection = 'DB_MAIN';

	public function getCity(){
		$res = array();
		$city_id = array();
		$city_name = array();
		$list = $this->table("sys_city")
			->field("city_id as area_id,name,province_id,provincename")
			->where('country_id = 137100100100100')
			->union("SELECT province_id as area_id,name,province_id,name as provincename FROM sys_province where country_id = 137100100100100 and name like '%市'")
			->select();
		if($list){
			foreach($list as $k => $v){
				if($v['name'] == '其它'){
					$city_id[$v['provincename'].'-'.$v['name']] = $v;
					$city_name[$v['area_id']] = $v['provincename'].'-'.$v['name'];
				}else{
					$city_id[$v['name']] = $v;
					if(strpos($v['name'],'市')){
						$city_name[$v['area_id']] = mb_substr($v['name'],0,-1,'utf-8');
					}else{
						$city_name[$v['area_id']] = $v['name'];
					}
				}
			}
			$res['city_id'] = $city_id;
			$res['city_name'] = $city_name;
			return $res;
		}else{
			return false;
		}

	}
}
?>