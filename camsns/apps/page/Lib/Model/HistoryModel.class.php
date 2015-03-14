<?php
class HistoryModel extends Model{
	protected	$tableName	=	'diy_history';
	protected	$fields		=	array (0 => 'id', 1 => 'pageId', 2 => 'uid', 3 => 'layoutData', 4 => 'widgetData',5=>"cTime",'_autoinc' => true, '_pk' => 'id');
	
	public function addData($pageId,$uid,$layoutData,$widgetData){
		$map['pageId'] = $pageId;
		$map['uid']    = $uid;
		$map['layoutData'] = $layoutData;
		$map['widgetData'] = $widgetData;
		$map['cTime']      = time();
		return $this->add($map);
	}
}