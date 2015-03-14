<?php
error_reporting(E_ERROR);	//设置错误级别
define('SITE_PATH',dirname(__FILE__));

//获取参数
$url	=	$_GET['url'];

//初步验证合法性
$result	=	preg_match('/^[a-zA-Z0-9]+$/',$url,$match);
if(!$result)	die('error01,wrong parameters!');

$url_id	=	getDncodeNum($url);
if($url_id<=0) die('error02,wrong parameters!');

//加载数据库查询类
require(SITE_PATH.'/addons/library/SimpleDB.class.php');

//引入数据库配置
$db_config	=	require(SITE_PATH.'/config/config.inc.php');
$db	=	new SimpleDB($db_config);

//查询短网址记录 - 有条件的，此处可用memcached做一层缓存
$result	=	$db->query("SELECT * FROM ".$db_config['DB_PREFIX']."url where id='".$url_id."' limit 1");

//状态为1的 跳转到正确地址
if($result[0]['status']==1){
	header('location:'.$result[0]['url']);
}else{
	die('error03,wrong parameters!');
}

//本地化 URL解码方法 将字母转换成数字ID
function getDncodeNum($num){
	//编码符号集一定要与加密的相同
	$index = "HwpGAejoUOPr6DbKBlvRILmsq4z7X3TCtky8NVd5iWE0ga2MchSZxfn1Y9JQuF";
	$out	= 0;
	$len	= strlen($num) - 1;
	for ($t = 0; $t <= $len; $t++) {
		$out = $out + strpos($index, substr($num, $t, 1 )) * pow(62, $len - $t);
	}
	//去除偏移量
	$out    -= 10000;	//初始值设置成10000
	return intval($out);
}
?>