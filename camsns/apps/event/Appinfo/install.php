<?php
if (!defined('SITE_PATH')) exit();

header('Content-Type: text/html; charset=utf-8');

$sql_file  = APPS_PATH.'/event/Appinfo/install.sql';
//执行sql文件
$res = M('')->executeSqlFile($sql_file);
if(!empty($res)){//错误
	echo $res['error_code'];
	echo '<br />';
	echo $res['error_sql'];
	//清除已导入的数据
	include_once(APPS_PATH.'/event/Appinfo/uninstall.php');
	exit;
}