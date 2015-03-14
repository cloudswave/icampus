<?php
/*
 * Webim插件AJAX请求入口文件
 * @author ery lee <ery.lee at gmail.com>
 * @version 5.1
 */ 
define('WEBIM_VERSION', '5.1');
define('WEBIM_PRODUCTION_NAME', 'thinksns');
define('WEBIM_DEBUG', false);
define('WEBIMDB_CHARSET', 'utf8');
//define('WEBIM_PATH', '.');

if(WEBIM_DEBUG) {
	error_reporting( E_ALL );
} else {
	error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );
}

//NOTICE: Have to redefine SITE_URL.
define('__ROOT__', chop($_SERVER['PHP_SELF'], '/addons/plugin/Webim/index.php'));
defined('SITE_PATH') or define('SITE_PATH', dirname(dirname(dirname(dirname(__FILE__)))));

require_once(SITE_PATH . '/core/core.php');

define('WEBIM_URL', SITE_URL . '/addons/plugin/Webim');

$IMC = require_once('conf/config.php');

tsload('lib/HttpClient.class.php');
tsload('lib/WebimClient.class.php');

tsload('model/SettingModel.class.php');
tsload('model/HistoryModel.class.php');
tsload('WebimAction.class.php');
tsload('ThinkIM.class.php');

$mod = new WebimAction();

$act = $mod->input('action');

if($act) {
	call_user_func(array($mod, $act));
} else {
	header( "HTTP/1.0 400 Bad Request" );
	exit("No 'action' input parameter!");
}


