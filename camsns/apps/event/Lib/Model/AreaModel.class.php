<?php
/**
 * IndexAction
 * 活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class AreaModel extends Model{
	//var $tableName = 'network';
    function getNetworkList($pid='0') {
		return $this->_MakeTree($pid);
	}
	
	function _MakeTree($pid,$level='0') {
		$result = $this->where('pid='.$pid)->findall();
		if($result){
			foreach ($result as $key => $value){
				$id = $value['area_id'];
				$list[$id]['id']    = $value['area_id'];
				$list[$id]['pid']    = $value['pid'];
				$list[$id]['title']  = $value['title'];
				$list[$id]['level']  = $level;
				$list[$id]['child'] = $this->_MakeTree($value['area_id'],$level+1);
			}
		}
		return $list;
	}
}
