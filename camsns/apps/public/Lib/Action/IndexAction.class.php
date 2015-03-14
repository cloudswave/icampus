<?php
/**
 * 首页控制器
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class IndexAction extends Action {
	
	/**
	 * 我的首页 - 微博页面
	 * @return void
	 */
	public function index(){
		// 安全过滤
		$d['type'] = t($_GET['type']) ? t($_GET['type']) : 'following';
		$d['feed_type'] = t($_GET['feed_type']) ? t($_GET['feed_type']) : '';
		$d['feed_key'] = t($_GET['feed_key']) ? t($_GET['feed_key']) : '';
		// 关注的人
		if($d['type'] === 'following') {
			$d['groupname'] = L('PUBLIC_ACTIVITY_STREAM');			// 我关注的
			$d['followGroup'] = model('FollowGroup')->getGroupList($this->mid);
			foreach($d['followGroup'] as $v) {
				if($v['follow_group_id'] == t($_REQUEST['fgid'])) {
					$d['groupname'] = $v['title'];
					break;
				}
			}
		}
		// 判断频道是否开启
		$isChannelOpen = model('App')->isAppNameOpen('channel');
		$this->assign('isChannelOpen', $isChannelOpen);
		// 关注的频道
		if($isChannelOpen && $d['type'] === 'channel') {
			$d['channelname'] = '我关注的频道';
			$d['channelGroup'] = D('ChannelFollow', 'channel')->getFollowList($this->mid);
			foreach($d['channelGroup'] as $v) {
				if($v['channel_category_id'] == t($_REQUEST['fgid'])) {
					$d['channelname'] = $v['title'];
					break;
				}
			}
		}
		$this->assign($d);
		// 设置默认话题
		$weiboSet = model('Xdata')->get('admin_Config:feed');
		$initHtml = $weiboSet['weibo_default_topic'];		// 微博框默认话题
		if($initHtml){
			$initHtml = '#'.$initHtml.'#';
		}
		$this->assign('initHtml' , $initHtml);
		
		$title = empty($weiboSet['weibo_send_info']) ? '随时记录' : $weiboSet['weibo_send_info'];
		$this->assign('title', $title);
		// 设置标题与关键字信息
		switch($d['type']) {
			case 'all':
				$this->setTitle('全站动态');
				$this->setKeywords('全站动态');
				break;
			case 'channel':
				$this->setTitle('我关注的频道');
				$this->setKeywords('我关注的频道');
				break;
			default:
				$this->setTitle(L('PUBLIC_INDEX_INDEX'));
				$this->setKeywords(L('PUBLIC_INDEX_INDEX'));
		}

		$this->display();
	}

	public function loginWithoutInit(){
		$this->index();
	}

	/**
	 * 我的微博页面
	 */
	public function myFeed() {
		// 获取用户统计数目
		$userData = model('UserData')->getUserData($this->mid);
		$this->assign('feedCount', $userData['weibo_count']);
		// 微博过滤内容
		$feedType = t($_REQUEST['feed_type']);
		$this->assign('feedType', $feedType);
		// 搜索使用
		$this->assign('feed_key', t($_REQUEST['feed_key']));
		$this->assign('feed_type', t($_REQUEST['feed_type']));
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		$this->setTitle('我的微博');	
		$this->setKeywords('我的微博');
		$this->display();
	}

	/**
	 * 我的关注页面
	 */
	public function following() {
		// 获取关组分组ID
		$gid = intval($_REQUEST['gid']);
		$this->assign('gid', $gid);
		// 获取指定用户的关注分组
		$groupList = model('FollowGroup')->getGroupList($this->mid);
		$key = t($_REQUEST['follow_key']);
		if ($key === '') {
			// 获取用户ID
			switch($gid) {
				case 0:
					$followGroupList = model('Follow')->getFollowingsList($this->mid);
					break;
				case -1:
					$followGroupList = model('Follow')->getFriendsList($this->mid);
					break;
				case -2:
					$followGroupList = model('FollowGroup')->getDefaultGroupByPage($this->mid);
					break;
				default:
					$followGroupList = model('FollowGroup')->getUsersByGroupPage($this->mid, $gid);
			}
		} else {
			$followGroupList = model('Follow')->searchFollows($key, 'following', 20, $this->mid, $gid);
			$this->assign('follow_key', $key);
			$this->assign('jsonKey', json_encode($key));
		}
		$fids = getSubByKey($followGroupList['data'], 'fid');
		// 获取用户信息
		$followUserInfo = model('User')->getUserInfoByUids($fids);
		// 获取用户的统计数目
		$userData = model('UserData')->getUserDataByUids($fids);
		// 获取用户用户组信息
		$userGroupData = model('UserGroupLink')->getUserGroupData($fids);
		$this->assign('userGroupData',$userGroupData);
		// 获取用户的最后微博数据
		//$lastFeedData = model('Feed')->getLastFeed($fids);
		// 获取用户的关注信息状态值
		$followState = model('Follow')->getFollowStateByFids($this->mid, $fids);
		// 获取用户的备注信息
		$remarkInfo = model('Follow')->getRemarkHash($this->mid);
		// 获取用户标签
		$this->_assignUserTag($fids);
		// 关注分组信息
		$followGroupStatus = model('FollowGroup')->getGroupStatusByFids($this->mid, $fids);
		$this->assign('followGroupStatus', $followGroupStatus);
		// 组装数据
		foreach($followGroupList['data'] as $key => $value) {
			$followGroupList['data'][$key] = $followUserInfo[$value['fid']];
			$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], $userData[$value['fid']]);
			$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('feedInfo'=>$lastFeedData[$value['fid']]));
			$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('followState'=>$followState[$value['fid']]));
			$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('remark'=>$remarkInfo[$value['fid']]));
		}
		$this->assign($followGroupList);
		// 获取登录用户的所有分组
		$userGroupList = model('FollowGroup')->getGroupList($this->mid);
		$userGroupListFormat = array();
		foreach($userGroupList as $value) {
			$userGroupListFormat[] = array('gid'=>$value['follow_group_id'], 'title'=>$value['title']);
		}
		$groupList = array(array('gid'=>0, 'title'=>'全部'), array('gid'=>-1, 'title'=>'相互关注'), array('gid'=>-2, 'title'=>'未分组'));
		!empty($userGroupListFormat) && $groupList = array_merge($groupList, $userGroupListFormat);
		$this->assign('groupList', $groupList);
		// 前5个的分组ID
		$this->assign('topGroup', array_slice(getSubByKey($groupList, 'gid'), 0, 3));
		foreach($groupList as $value) {
			if($value['gid'] == $gid) {
				$this->assign('gTitle', $value['title']);
				break;
			}
		}
		// 关注人数
		$midData = model('UserData')->getUserData($this->mid);
		$this->assign('followingCount', $midData['following_count']);
		// 显示的分类个数
		$this->assign('groupNums', 3);
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		
		$userInfo = model('User')->getUserInfo($this->mid);
		$lastFeed = model('Feed')->getLastFeed(array($fids[0]));
		$this->setTitle('我的关注');
        $this->setKeywords($userInfo['uname'].'的关注');
		$this->display();
	}

	/**
	 * 我的粉丝页面
	 */
	public function follower() {
		// 清空新粉丝提醒数字
		if($this->uid == $this->mid){
			$udata = model('UserData')->getUserData($this->mid);
			$udata['new_folower_count'] > 0 && model('UserData')->setKeyValue($this->mid,'new_folower_count',0);	
		}
		// 获取用户的粉丝列表
		$key = t($_REQUEST['follow_key']);
		if ($key === '') {
			$followerList = model('Follow')->getFollowerList($this->mid, 20);
		} else {
			$followerList = model('Follow')->searchFollows($key, 'follower', 20, $this->mid);
			$this->assign('follow_key', $key);
			$this->assign('jsonKey', json_encode($key));
		}
		$fids = getSubByKey($followerList['data'], 'fid');
		// 获取用户信息
		$followerUserInfo = model('User')->getUserInfoByUids($fids);
		// 获取用户统计数目
		$userData = model('UserData')->getUserDataByUids($fids);
		// 获取用户标签
		$this->_assignUserTag($fids);
		// 获取用户用户组信息
		$userGroupData = model('UserGroupLink')->getUserGroupData($fids);
		$this->assign('userGroupData',$userGroupData);
		// 获取用户的最后微博数据
		//$lastFeedData = model('Feed')->getLastFeed($fids);
		// 获取用户的关注信息状态
		$followState = model('Follow')->getFollowStateByFids($this->mid, $fids);
		// 组装数据
		foreach($followerList['data'] as $key => $value) {
			$followerList['data'][$key] = array_merge($followerList['data'][$key], $followerUserInfo[$value['fid']]);
			$followerList['data'][$key] = array_merge($followerList['data'][$key], $userData[$value['fid']]);
			$followerList['data'][$key] = array_merge($followerList['data'][$key], array('feedInfo'=>$lastFeedData[$value['fid']]));
			$followerList['data'][$key] = array_merge($followerList['data'][$key], array('followState'=>$followState[$value['fid']]));
		}
		$this->assign($followerList);
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		// 粉丝人数
		$midData = model('UserData')->getUserData($this->mid);
		$this->assign('followerCount', $midData['follower_count']);

		$userInfo = model('User')->getUserInfo($this->mid);
		$lastFeed = model('Feed')->getLastFeed(array($fids[0]));
		$this->setTitle('我的粉丝');
        $this->setKeywords($userInfo['uname'].'的粉丝');
		$this->display();
	}
	
	/**
	 * 意见反馈页面
	 */
	public function feedback() {
		$feedbacktype = model('Feedback')->getFeedBackType();
		$this->assign('type', $feedbacktype);
		$this->display();
	}
	
	/**
	 * 获取验证码图片操作
	 */
	public function verify() {
		tsload(ADDON_PATH.'/library/Image.class.php');
		tsload(ADDON_PATH.'/library/String.class.php');
		Image::buildImageVerify();
	}

	/**
	 * 获取指定用户小名片所需要的数据
	 * @return string 指定用户小名片所需要的数据
	 */
	public function showFaceCard() {
		if(empty($_REQUEST['uid'])) {
			exit(L('PUBLIC_WRONG_USER_INFO'));			// 错误的用户信息
		}
		
		$this->assign('follow_group_status', model('FollowGroup')->getGroupStatus($GLOBALS['ts']['mid'], $GLOBALS['ts']['uid']));
		$this->assign('remarkHash', model('Follow')->getRemarkHash($GLOBALS['ts']['mid']));
		
		$uid = intval($_REQUEST['uid']);
		$data['userInfo'] = model('User')->getUserInfo($uid);
		$data['userInfo']['groupData'] = model('UserGroupLink')->getUserGroupData($uid);   //获取用户组信息
		$data['user_tag'] = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($uid);
		$data['user_tag'] = empty($data['user_tag']) ? '' : implode('、',$data['user_tag']);
		$data['follow_state'] = model('Follow')->getFollowState($this->mid, $uid);
		
		$depart = model('Department')->getAllHash();
		$data['department'] = isset($depart[$data['userInfo']['department_id']]) ? $depart[$data['userInfo']['department_id']] : '';
		
		$count = model('UserData')->getUserData($uid);
		if(empty($count)) {
			$count = array('following_count'=>0,'follower_count'=>0,'feed_count'=>0,'favorite_count'=>0,'unread_atme'=>0,'weibo_count'=>0);
		}
		$data['count_info'] = $count;
		
		// 用户字段信息
		$profileSetting = D('UserProfileSetting')->where('type=2')->getHashList('field_id');
		$profile = model('UserProfile')->getUserProfile($uid);
		$data['profile'] = array();
		foreach($profile as $k=>$v) {
			if(isset($profileSetting[$k])) {
				$data['profile'][$profileSetting[$k]['field_key']] = array('name'=>$profileSetting[$k]['field_name'],'value'=>$v['field_data']);
			}
		}

		// 判断隐私
		if($this->uid != $this->mid) {
			$UserPrivacy = model('UserPrivacy')->getPrivacy($this->mid, $this->uid);
			$this->assign('UserPrivacy', $UserPrivacy);
		}
		//判断用户是否已认证
		$isverify = D('user_verified')->where('verified=1 AND uid='.$uid)->find();
		if($isverify){
			$this->assign('verifyInfo',$isverify['info']);
		}
		$this->assign($data);
		$this->display();
	}

	/**
	 * 公告详细页面
	 */
	public function announcement() {
		$map['type'] = 1;
		$map['id'] = intval($_GET['id']);
		$d['announcement'] = model('Xarticle')->where($map)->find();
		// 组装附件信息
		$attachIds = explode('|', $d['announcement']['attach']);
		$attachInfo = model('Attach')->getAttachByIds($attachIds);
		$d['announcement']['attachInfo'] = $attachInfo;
		$this->assign($d);
		$this->display();
	}

	/**
	 * 公告列表页面
	 */
	public function announcementList() {
		$map['type'] = 1;
		$list = model('Xarticle')->where($map)->findPage(20);
		// 获取附件类型
		$attachIds = array();
		foreach($list['data'] as &$value) {
			$value['hasAttach'] = !empty($value['attach']) ? true : false;
		}

		$this->assign($list);
		$this->display();
	}

	/**
	 * 自动提取标签操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function getTags() {
		$text = t($_REQUEST['text']);
		$format = !empty($_REQUEST['format']) ? t($_REQUEST['format']) : 'string';
		$limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : '3';
		$tagX = model("Tag");
		$tagX->setText($text);		// 设置text
		$result = $tagX->getTop($limit,$format);  // 获取前10个标签
		exit($result);
	}

	/**
	 * 根据指定应用和表获取指定用户的标签,同个人空间中用户标签
	 * @param array uids 用户uid数组
	 * @return void
	 */
	private function _assignUserTag($uids) {
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($uids);
		$this->assign('user_tag', $user_tag);
	}

	/**
	 * 弹窗发布微博
	 * @return void
	 */
	public function sendFeedBox()
	{
		$initHtml = t($_REQUEST['initHtml']);
		if(!empty($initHtml)) {
			$data['initHtml'] = $initHtml;
		}
		//投稿数据处理
		$channelID = h($_REQUEST['channelID']);
		if(!empty($channelID)){
			$data['channelID'] = $channelID;
			$data['type'] = 'submission';
		}

		$this->assign($data);
		$this->display();
	}
	public function scoredetail(){
		$list = model('Credit')->getLevel();
		$this->assign( 'list' , $list );
		$this->display();
	}


		public function about() {
		  $this->display();
	}
}