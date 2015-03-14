<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> '分歧终端机',	
	// 应用简介 [必填]
	'DESCRIPTION'				=> '分歧终端机,好玩有趣赢金币,促进用户活跃和积分流通,欢迎加入ThinkSNS二次开发技术交流Q群197167657.',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	//为空
	'ICON_URL'					=> '',
	//为空
	'LARGE_ICON_URL'			=> '',
	// 版本号 [必填]
	'VERSION_NUMBER'			=> '1.0',
	// 后台入口 [选填]
	'ADMIN_ENTRY'				=> '',
	// 统计入口 [选填]（格式：Model/method）
	'STATISTICS_ENTRY'			=> '',
	//公司名称
	'COMPANY_NAME'				=> 'Yovae<yovae@qq.com>',
	//是否有移动端
	'HAS_MOBILE'				=> '1',
);