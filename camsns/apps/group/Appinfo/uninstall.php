<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// group数据
	"DROP TABLE IF EXISTS `{$db_prefix}group`;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_album;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_attachment;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_category;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_invite_verify;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_log;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_member;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_photo;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_post;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_tag;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_topic;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_feed;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_feed_data;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_atme;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_comment;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_user_count;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_topic_category;",
    "DROP TABLE IF EXISTS `{$db_prefix}group_topic_collect;",    
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'group'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `type` = 'group';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'group';",
);

foreach ($sql as $v)
	M('')->execute($v);