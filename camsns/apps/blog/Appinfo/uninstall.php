<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// Blog数据
	"DROP TABLE IF EXISTS `{$db_prefix}blog`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_category`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_item`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_mention`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_outline`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_source`;",
	"DROP TABLE IF EXISTS `{$db_prefix}blog_subscribe`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'blog'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `name` = 'blog_create_weibo' OR `name` = 'blog_share_weibo';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'blog';",
);

foreach ($sql as $v)
	M('')->execute($v);