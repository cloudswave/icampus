<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// gift数据
	"DROP TABLE IF EXISTS `{$db_prefix}gift`;",
	"DROP TABLE IF EXISTS `{$db_prefix}gift_category;",
	"DROP TABLE IF EXISTS `{$db_prefix}gift_user;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'gift'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `name` = 'gift_send_weibo';",
);

foreach ($sql as $v)
	M('')->execute($v);