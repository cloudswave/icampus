<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> '积分商城',	
	// 应用简介 [必填]
	'DESCRIPTION'				=> '使用积分兑换相应物品',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	//为空
	'ICON_URL'					=> '',
	//为空
	'LARGE_ICON_URL'			=> '',
	// 版本号 [必填]
	'VERSION'			=> 'V1.1',
	// 后台入口 [选填]
	'ADMIN_ENTRY'				=> 'shop/Admin/index',
	// 统计入口 [选填]（格式：Model/method）
	'STATISTICS_ENTRY'			=> 'Statistics/statistics',
	//公司名称
	'COMPANY_NAME'				=> 'Delay.X',
	//快捷方式子导航
	'CHILD_MENU'				=> array(
										//我的XX  直接使用应用名，其他链接使用语言包KEY
										'shop'=>array(
															//链接地址
															'url'	 => 'shop/Index/myshop',
															//是否公开：为0 则不在档案页展示，为1则在档案页展示
															'public' => 0, 
													),
													
										),

	//是否有移动端
	'HAS_MOBILE'				=> '0',
);