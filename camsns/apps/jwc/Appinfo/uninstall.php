<?php
/**
 * 卸载频道应用
 * @author xiaomage <707514663@qq.com>
 * @version TS3.0
 */
if(!defined('SITE_PATH')) exit();
// 数据库表前缀
$db_prefix = C('DB_PREFIX');
// 卸载数据SQL数组

//移除缓存文件
$filename = CONF_PATH.'/news_category.php';
if (file_exists($filename))
{
    unlink($filename);
}
//清除缓存
F('_system_config_lget_pageKey',null);