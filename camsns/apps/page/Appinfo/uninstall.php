<?php
/**
 * 卸载频道应用
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if(!defined('SITE_PATH')) exit();
// 数据库表前缀
$db_prefix = C('DB_PREFIX');
// 卸载数据SQL数组
$sql = array(
	// Channel数据
	"DROP TABLE IF EXISTS `{$db_prefix}diy_canvas`;",
	"DROP TABLE IF EXISTS `{$db_prefix}diy_page`;",
	"DROP TABLE IF EXISTS `{$db_prefix}diy_widget`;",
);
// 执行SQL
foreach($sql as $v) {
	D('')->execute($v);
}