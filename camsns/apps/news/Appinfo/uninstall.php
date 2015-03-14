<?php
/**
 * 卸载频道应用
 * @author xiaomage <707514663@qq.com>
 * @version TS3.0
 */
if(!defined('SITE_PATH')) exit();
// 数据库表前缀
$db_prefix = C('DB_PREFIX');
// 卸载数据SQL数组
$sql = array(
	// Channel数据
	"DROP TABLE IF EXISTS `{$db_prefix}news`;",
	"DROP TABLE IF EXISTS `{$db_prefix}news_category`;",
    "DELETE FROM `{$db_prefix}system_config` WHERE `key` LIKE 'news_Admin_%'",
    "DELETE FROM `{$db_prefix}lang` WHERE `key` = 'PUBLIC_APPNAME_NEWS'",
);
// 执行SQL
foreach($sql as $v) {
	D('')->execute($v);
}
//移除缓存文件
$filename = CONF_PATH.'/news_category.php';
if (file_exists($filename))
{
    unlink($filename);
}
//清除缓存
F('_system_config_lget_pageKey',null);