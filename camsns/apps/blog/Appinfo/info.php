<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> '日志',
	// 应用简介 [必填]
	'DESCRIPTION'				=> '想分享你的文章给你的好友么，快来记录日志吧',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	// 应用图标 [必填]
	'ICON_URL'					=> SITE_URL . '/apps/blog/Appinfo/icon_app.png',
	// 应用图标 [必填]
	'LARGE_ICON_URL'			=> SITE_URL . '/apps/blog/Appinfo/icon_app_large.png',
	// 版本号 [必填]
	'VERSION_NUMBER'			=> '36263',
	// 后台入口 [选填]
	'ADMIN_ENTRY'				=> 'blog/Admin/index',
	// 统计入口 [选填]（格式：Model/method）
	'STATISTICS_ENTRY'			=> 'BlogStatistics/statistics',
	// 应用的主页 [选填]
	'HOMEPAGE_URL'				=> '',
	// 应用类型
	'CATEGORY'					=> '工具',
	// 发布日期
	'RELEASE_DATE'				=> '2008-08-08',
	// 最后更新日期
	'LAST_UPDATE_DATE'			=> '2012-07-12',

	// 附加链接名称 [选填]
	'SIDEBAR_TITLE'				=> '发表',
	// 附件链接的入口 [选填]（格式：Action/act）
	'SIDEBAR_ENTRY'				=> 'Index/addBlog',
	// 附加链接的图标 [选填]
	'SIDEBAR_ICON_URL'			=> '',
	// 是否在附加链接中展示子菜单 [选填]（0:否 1:是）
	'SIDEBAR_SUPPORT_SUBMENU'	=> '0',

	// 作者名 [必填]
	'AUTHOR_NAME'				=> 'Satan',
	// 作者Email [必填]
	'AUTHOR_EMAIL'				=> 'wangzuo0714@gmail.com',
	// 作者主页 [选填]
	'AUTHOR_HOMEPAGE_URL'		=> '',
	// 贡献者姓名 [选填]
	'CONTRIBUTOR_NAMES'			=> '杨德升, 赵杰,水上铁',
);
?>