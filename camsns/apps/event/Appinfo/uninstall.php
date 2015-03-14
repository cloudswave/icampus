<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// event数据
	"DROP TABLE IF EXISTS `{$db_prefix}event`;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_opts;",
	"DROP TABLE IF EXISTS `{$db_prefix}event_photo;",
    "DROP TABLE IF EXISTS `{$db_prefix}event_type;",
    "DROP TABLE IF EXISTS `{$db_prefix}event_user;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'event'",
	// 模板数据
	//"DELETE FROM `{$db_prefix}template` WHERE `type` = 'event';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'event';",
	//"DELETE FROM `{$db_prefix}credit_node` WHERE `appname` = 'event';",
);

foreach ($sql as $v)
	M('')->execute($v);