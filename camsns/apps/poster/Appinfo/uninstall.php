<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// poster数据
	"DROP TABLE IF EXISTS `{$db_prefix}poster`;",
	"DROP TABLE IF EXISTS `{$db_prefix}poster_small_type;",
	"DROP TABLE IF EXISTS `{$db_prefix}poster_type;",
    "DROP TABLE IF EXISTS `{$db_prefix}poster_widget;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'poster'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'poster';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'poster';",
);

foreach ($sql as $v)
	M('')->execute($v);