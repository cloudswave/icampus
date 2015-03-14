<?php

/**
 * 用户集成定制类
 */
class ThinkIM {

	/*
	 * 当前用户或者访客
	 */
	private $user = NULL;

	/*
	 * 是否管理员
	 */
	private $is_admin = false;

	/*
	 * 是否访客
	 */
	private $is_visitor = false;

	/*
	 * 是否登录
	 */
	private $is_login = false;

	/*
	 * 初始化当前用户信息
	 */
	function __construct() {
		global $IMC;
		if($this->uid()) {
			$this->setUser();			
			$this->is_login = true;
		} else if($IMC['visitor']) {
			$this->setVisitor();
			$this->is_login = true;
			$this->is_visitor = true;	
		}
	}

	/*
	 * 接口函数: 集成项目的uid
	 */
	public function uid() {
		return $_SESSION['mid'];
	}

	public function user() {
		return $this->user;	
	}

	public function isAdmin() {
		return is_admin;
	}

	public function isVisitor() {
		return $this->is_visitor;
	}

	public function logined() {
		return $this->is_login;	
	}

	/*
	 * 接口函数: 读取当前用户的好友在线好友列表
	 *
	 * Buddy对象属性:
	 *
	 * 	uid: 好友uid
	 * 	id:  同uid
	 *	nick: 好友昵称
	 *	pic_url: 头像图片
	 *	show: available | unavailable
	 *  url: 好友主页URL
	 *  status: 状态信息 
	 *  group: 所属组
	 */
	public function buddies() {
		global $IMC;
		//根据当前用户uid获取双向follow的好友id列表
		$follows = model('Follow')->getFriendsData($this->uid());
		if(!$follows) $follows = array();
		$fids = array_map(function($u) { return $u['fid']; }, $follows);
		//获取好友信息列表
		$friends = model('User')->getUserInfoByUids($fids);
		if(!$friends) $friends = array();
		//获取管理员信息列表
		$admins = model('User')->getUserInfoByUids($IMC['admin_uids']);
		if(!$admins) $admins = array();
		//转换为Webim Buddy对象.
		return $this->toBuddies(array_merge($admins, $friends));
	}

	/*
	 * 接口函数: 根据好友id列表、陌生人id列表读取用户, id列表为逗号分隔字符串
	 *
	 * 用户属性同上
	 */
	function buddiesByIds($friend_uids = "", $stranger_uids = "") {
		//根据id列表获取好友列表
		$friends = model('User')->getUserInfoByUids($friend_uids);
		if(!$friends) $friends = array();
		$strangers = model('User')->getUserInfoByUids($stranger_uids);
		if(!$strangers) $strangers = array();
		return array_merge(
			$this->toBuddies($friends),
			$this->toBuddies($strangers, 'stranger')	
		);
	}

	/*
	 * User对象转化为Buddy对象
	 */
	private function toBuddies($users, $group = "friend") {
		$buddies = array();
		foreach($users as $user) {
			$buddies[] = (object)array(
				'uid'		=> (string)$user['uid'],
				'id'		=> (string)$user['uid'],
				'group'		=> $group,
				'nick'		=> $user['uname'],
				'pic_url' 	=> $user['avatar_small'],
				'url'		=> $user['space_url'],
				'status'	=> $user['intro'],
			);
		}
		return $buddies;
	}
	
	/*
	 * 接口函数：读取当前用户的Room列表
	 *
	 * Room对象属性:
	 *
	 *	id:		Room ID,
	 *	nick:	显示名称
	 *	url:	Room主页地址
	 *	pic_url: Room图片
	 *	status: Room状态信息
	 *	count:  0
	 *	all_count: 成员总计
	 *	blocked: true | false 是否block
	 */
	public function rooms() {
		//根据当前用户id获取群组列表
		//$uid = $this->uid();
		return array( );	
	}

	/*
	 * 接口函数: 根据id列表读取rooms, id列表为逗号分隔字符串
	 *
	 * Room对象属性同上
	 */
	public function roomsByIds($ids = "") {
		return array();	
	}

	/*
	 * 接口函数: 当前用户通知列表
	 *
	 * Notification对象属性:
	 *
	 * 	text: 文本
	 * 	link: 链接
	 */	
	public function notifications() {
		$notices = array();
		$uid = $this->uid();
		$userCount = model('UserCount')->getUnreadCount($uid);
		if(!$userCount) $userCount = array();
		if ($userCount['unread_notify']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount['unread_notify'] . '</strong> 个系统消息'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Message&act=notify");
		}
		if ($userCount['unread_message']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_message"] . '</strong> 个站内短消息'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Message&act=index");
		}
		if ($userCount['unread_atme']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_atme"] . '</strong> 个好友@了你'),
				"link" => SITE_URL . "/index.php?app=public&mod=Mention&act=index");
		}
		if ($userCount['unread_comment']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount["unread_comment"] . '</strong> 评论'), 
				"link" => SITE_URL . "/index.php?app=public&mod=Comment&act=index&type=receive");
		}
		if($userCount['new_folower_count']) {
			$notices[] = array(
				"text" => ('您有<strong>' . $userCount['new_folower_count'] . '</strong>位新粉丝'),
				"link" => SITE_URL . "/index.php?app=public&mod=Index&act=follower&uid=" . $uid);	

		}
		return $notices;
	}

	/*
	 * 返回菜单列表
	 */
	public function menulist() {
		$apps = model('App')->getUserApp($this->uid());
		if(!$apps) $apps = array();
		$menu = array();
		foreach($apps as $app) {
			$menu[] = (object)array(
				'title' => $app['app_alias'],
				'icon' => $app['icon_url'],
				'link' => SITE_URL . "/index.php?app=" . $app['app_name'],
			);
		}
		return $menu;
	}

	/*
	 * 接口函数: 初始化当前用户对象，与站点用户集成.
	 */
	private function setUser() {
		$uid = $this->uid();
		$user = model('User')->getUserInfo($uid);
		if ($user['admin_level'] != 0) {
			$this->is_admin = true;
		} else {
			$this->is_admin = false;
		}
		$this->user = (object)array(
			'uid'		=> (string)$uid,
			'id'		=> (string)$uid,
			'nick'		=> $user['uname'],
			'pic_url'	=> $user['avatar_small'],
			'show'		=> "available",
			'url'		=> $user['space_url'],
			'status'	=> $user['intro'],
		);
	}
	
	/*
	 * 接口函数: 创建访客对象，可根据实际需求修改.
	 */
	private function setVisitor() {
		if ( isset($_COOKIE['_webim_visitor_id']) ) {
			$id = $_COOKIE['_webim_visitor_id'];
		} else {
			$id = substr(uniqid(), 6);
			setcookie('_webim_visitor_id', $id, time() + 3600 * 24 * 30, "/", "");
		}
		$this->user = (object)array(
			'uid' => 'vid:'.$id,
			'id' => 'vid:'.$id,
			'nick' => "v".$id,
			'pic_url' => WEBIM_URL . "/static/images/chat.png",
			'show' => "available",
			'url' => "#",
			'status' => '网站访客',
		);
	}

}
