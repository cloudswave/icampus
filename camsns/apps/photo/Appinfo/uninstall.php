<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// photo数据
	"DROP TABLE IF EXISTS `{$db_prefix}photo`;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_album`;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_index`;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_mark`;",
	"DELETE FROM `{$db_prefix}system_data` WHERE list='photo';",
);

foreach ($sql as $v)
	M('')->execute($v);