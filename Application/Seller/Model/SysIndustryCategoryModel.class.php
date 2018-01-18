<?php
namespace Seller\Model;
use Think\Model;
class SysIndustryCategoryModel extends Model {

	protected $connection = 'DB_MAIN';

	public function getCagegory(){
		$res = array();
		$list = $this->table('sys_industry_category')
			->field('c1,n1,c2,n2')
			->select();
		if($list){
			foreach($list as $k =>$v){
				$res['c1'][$v['c1']]['c1'] = $v['c1'];
				$res['c1'][$v['c1']]['n1'] = $v['n1'];
				$res['c2'][$v['c2']]['c2'] = $v['c2'];
				$res['c2'][$v['c2']]['n2'] = $v['n2'];
			}
			return $res;
		}else{
			return false;
		}
	}


	public function getSubCagegory($c1){
		$res = array();
		$list = $this->table('sys_industry_category')
			->where("c1 = ".$c1)
			->field('c2,n2')
			->select();
		if($list){
			foreach($list as $k =>$v){
				$res[$v['c2']]['c2'] = $v['c2'];
				$res[$v['c2']]['n2'] = $v['n2'];
			}
			return $res;
		}else{
			return false;
		}
	}
}
?>