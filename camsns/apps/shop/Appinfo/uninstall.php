<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// Blog数据
	"DROP TABLE IF EXISTS `{$db_prefix}shop`;",
	"DROP TABLE IF EXISTS `{$db_prefix}shop_convert`;",
	"DROP TABLE IF EXISTS `{$db_prefix}shop_user`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_config` WHERE `key` = 'shop_Admin_index';",
	"DELETE FROM `{$db_prefix}system_config` WHERE `key` = 'shop_Admin_addshop';",
	"DELETE FROM `{$db_prefix}system_config` WHERE `key` = 'shop_Admin_editshop';",
	"DELETE FROM `{$db_prefix}system_config` WHERE `key` = 'shop_Admin_convert';",
	"DELETE FROM `{$db_prefix}system_config` WHERE `key` = 'shop_Admin_update';",
	// 积分规则
	"DELETE FROM `{$db_prefix}lang` WHERE `en` = 'shop';",
);

foreach ($sql as $v)
	D('')->execute($v);