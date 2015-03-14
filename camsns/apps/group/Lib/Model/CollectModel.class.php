<?php

class CollectModel extends Model {
	var $tableName = 'group_topic_collect';
	 function isCollect($tid,$mid){
	 	return $this->where('tid='.$tid." AND mid=".$mid)->count();
	 }

}


?>