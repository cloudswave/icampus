<?php

if(!defined('SITE_PATH')) exit();
// 数据库表前缀
$db_prefix = C('DB_PREFIX');
// 卸载数据SQL数组
$sql = array(
    "DROP TABLE IF EXISTS `{$db_prefix}app_schedule`;",
);
// 执行SQL
foreach($sql as $v) {
    D('')->execute($v);
}