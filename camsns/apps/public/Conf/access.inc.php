<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 */
return array (
	"access" => array (
		
		//搜索
		'public/Search/*' => true,                 
		
		//网站公告
		'public/Index/announcement' => true,

		'public/Index/about' => true,

		// 个人主页
		'public/Profile/index' => true,
		'public/Profile/following' => true,
		'public/Profile/follower' => true,
		'public/Profile/data' => true,
		
		// 微博内容
		'public/Profile/feed' => true,
		
		// 微博话题
		'public/Topic/index' => true,

		// 微博排行榜
		'public/Rank/*' => true,	

		'public/Feed/addDigg' => true,
		'public/Feed/delDigg' => true,
	)
		 
);