<?php
/**
 * 微吧分类
 * @author Stream
 *
 */
class WeibaCategoryModel extends Model{
	
	var $tableName = 'weiba_category';
	protected $fields = array(
			1=>'id',
			2=>'name'
			);
	
	public function getAllWeibaCate($map){
		$list = $this->where($map)->findAll();
		$temp = array();
		foreach ( $list as $v ){
			$temp[$v['id']] = $v['name'];
		}
		return $temp;
	}
}