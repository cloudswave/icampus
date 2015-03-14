<?php
function IsHotList(){
	//读取推荐列表
	$votes = M('vote')->where(' isHot="1" ')->order( 'rTime DESC' )->limit(20)->findAll();
	foreach($votes as &$value){
		$value['username'] = getUserName($value['uid']);
		$value['title']    = getShort($value['title'],12-strlen($value['username'])/2);
	}
	return $votes;
}
//获取配置
function getConfig($key){
	$config = model('Xdata')->lget("vote");
	$config['defaultTime'] = $config['defaultTime']?$config['defaultTime']:7776000;
	$config['limitpage']   = $config['limitpage']?$config['limitpage']:20;
	$config['join']  	   = $config['join']=='following'?$config['join']:'all';
	return $config[$key];
}
?>