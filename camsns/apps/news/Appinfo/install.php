<?php
/**
 * 安装CMS应用
 * @author xiaomage <707514663@qq.com>
 * @version TS3.0
 */
if (! defined('SITE_PATH'))
{
    exit();
}

//先卸载
include_once(APPS_PATH.'/news/Appinfo/uninstall.php');
    
// SQL文件
$sql_file = APPS_PATH . '/news/Appinfo/install.sql';
$res = D('')->executeSqlFile($sql_file);
if(!empty($res))
{
	echo $res['error_code'];
	echo '<br />';
	echo $res['error_sql'];
	//清除已导入的数据
	include_once(APPS_PATH.'/news/Appinfo/uninstall.php');
	exit;
}

//生成语言缓存
model('Lang')->createCacheFile('PUBLIC',0);