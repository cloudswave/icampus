<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// vote数据
	"DROP TABLE IF EXISTS `{$db_prefix}vote`;",
	"DROP TABLE IF EXISTS `{$db_prefix}vote_opt`;",
	"DROP TABLE IF EXISTS `{$db_prefix}vote_user`;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'vote'",
	// 模板数据
	//"DELETE FROM `{$db_prefix}template` WHERE `name` = 'vote_create_weibo' OR `name` = 'vote_share_weibo';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'vote';",
	//"DELETE FROM `{$db_prefix}credit_node` WHERE `appname` = 'vote';",
);

foreach ($sql as $v) {
	$res = M('')->execute($v);
}