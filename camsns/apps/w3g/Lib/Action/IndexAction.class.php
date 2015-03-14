<?php
class IndexAction extends BaseAction {
	private $_config;					// 注册配置信息字段
	private $_register_model;			// 注册模型字段
	private $_user_model;				// 用户模型字段
	private $_invite;					// 是否是邀请注册
	private $_invite_code;				// 邀请码

	// 个人首页
	public function index($uid = 0) {
		$data['user_id'] = $uid <= 0 ? $this->mid : $uid;
	   	$data['page']    = $this->_page;
	   	$data['count'] = 10;
	   	// 用户资料
	   	$profile = api('User')->data($data)->show();
	   	$this->assign('profile', $profile['avatar_small']);
		// 微博列表friends_timeline
		$weibolist = api('WeiboStatuses')->data($data)->friends_timeline();
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		// dump($weibolist['0']);
		//分页模块
		$count = D('W3gPage', 'w3g')->getWeiboCount($data['type'], $data['user_id']);
		$this->assign('count',$count);
		/*$maxWeiboID = $weibolist['0']['weibo_id'];
		$this->assign('maxWeioboID',$maxWeiboID);
		$this->assign('xin','xin');*/
		$this->assign('headtitle', '我的微博');
		//dump($weibolist);
		$this->display('index');
	}

	// 下拉刷新
	public function resetScrollDownRefresh($uid = 0) {
		$data['user_id'] = $uid <= 0 ? $this->mid : $uid;
	   	$data['page']    = $this->_page;
	   	$data['count'] = 11;
	   	$data['since_id']=intval($_GET['since_id']);
	   	// 用户资料
	   	$profile = api('User')->data($data)->show();
	   	$this->assign('profile', $profile['avatar_small']);
		// 微博列表friends_timeline
		$weibolist = api('WeiboStatuses')->data($data)->friends_timeline();
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		//分页模块
		$count = D('W3gPage', 'w3g')->getWeiboCount($data['type'], $data['user_id']);
		$this->assign('count',$count);
		$this->assign('headtitle', '下拉刷新');
		$this->display('resetScrollDownRefresh');
	}
	// 微博广场
	public function resetScrollDownRefreshSquare() {
		$data['page'] = $this->_page;
		$data['count'] = 11;
	   	$data['since_id']=intval($_GET['since_id']);
		$weibolist = api('WeiboStatuses')->data($data)->public_timeline();
		// $weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		$this->assign('headtitle', '下拉刷新for广场');
		//分页模块
		$count = D('W3gPage', 'w3g')->getAllWeiboCount($data['type'], $data['user_id']);
		$this->assign('count',$count);
		$this->display('resetScrollDownRefreshSquare');
	}

	// 微博广场
	public function publicsquare() {
		$data['page'] = $this->_page;
		$data['count'] = 10;
		$weibolist = api('WeiboStatuses')->data($data)->public_timeline();
		// $weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		$this->assign('headtitle', '微博广场');
		//分页模块
		$count = D('W3gPage', 'w3g')->getAllWeiboCount($data['type'], $data['user_id']);
		$this->assign('count',$count);
		$this->display('publicsquare');
	}

	// XX的微博
	public function weibo() {
        // 微博列表
        // $data['user_id']  = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
        $data['user_id']  = isset($_GET['uid']) ? intval($_GET['uid']) : $this->mid;
        $data['page'] = intval($_GET['page']);
        $data['count'] = 10;

        // 用户资料
        $profile = api('User')->data($data)->show();
    	$following = $profile['follow_state']['following'];
		$follower = $profile['follow_state']['follower'];
		if($following){
			$profile['follow_state']['value'] = '已关注';
		}else{
			$profile['follow_state']['value'] = '未关注';
		}
		if($following && $follower){
			$profile['follow_state']['value'] = '互相关注';
		}
		$this->assign('profile', $profile);
        // 微博列表
        $weibolist = api('WeiboStatuses')->data($data)->user_timeline();
		$weibolist = $this->__formatByContent($weibolist);
        $this->assign('weibolist', $weibolist);
        // dump($weibolist['0']);
        $this->assign('hideUsername', '1');
        //分页模块
		$count = D('W3gPage', 'w3g')->getMyWeiboCount($data['type'], $data['user_id']);
		$this->assign('count',$count);
        if($this->mid==$this->uid){
        	$this->assign('headtitle', '我的主页');
        }else {
        	$this->assign('headtitle', $profile['uname'].'的主页');
        }
        $this->display();
	}

	// @提到我的
	public function atMe() {
		$data['page'] = $this->_page;
		$data['count'] = 10;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // @XX的微博列表
        $weibolist = api('WeiboStatuses')->data($data)->mentions();
		$weibolist = $this->__formatByContent($weibolist);
		// 提示数字归0
		model ( 'UserCount' )->resetUserCount( $this->mid, 'unread_atme' );
		// 分页模块
		$count = D('W3gPage', 'w3g')->getAtmeCount($this->mid);
		$this->assign('count',$count);
		// digg
		$feedIds = getSubByKey($weibolist, 'feed_id');
		$feedIds = array_filter($feedIds);
		$sdiggArr = model('FeedDigg')->checkIsDigg($feedIds, $this->mid);
		$sdiggArr = array_keys($sdiggArr);
		foreach ($weibolist as &$value) {
			if (!empty($value['feed_id']) && in_array($value['feed_id'], $sdiggArr)) {
				$value['is_digg'] = 1;
			} else {
				$value['is_digg'] = 0;
			}
		}

		$this->assign('weibolist', $weibolist);
		// dump($weibolist['0']);
		$this->assign('atMe',1);
        $this->assign('headtitle', '@我的');
        $this->display('atme');
	}

	//通知数目
	public function MCount(){
		// $amap['uid'] = $this->mid;
		$mcount = D('UserCount')->getUnreadCount($this->mid);
		$this->assign('mcount',$mcount);
		$this->display('mcount');
	}

	// 评论我的
	public function replyMe() {
		$data['page']     = $this->_page;
		$data['count'] = 10;
		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // 评论的微博列表
        $commentlist = api('WeiboStatuses')->data($data)->comments_to_me_true();
		// $commentlist = $this->__formatByContent($commentlist);
		foreach ($commentlist as  &$value) {
			//因为评论我的页面中没有is_del，下句是加上
			$value['sourceInfo']['is_del'] = M('Feed')->where('feed_id='.$value['sourceInfo']['feed_id'])->getField('is_del');
			// 微吧
			if (in_array($value['app'], array('weiba'))) {
				$feedInfo = model('Feed')->getFeedInfo($value['sourceInfo']['source_id']);
				$value['sourceInfo']['uname'] = $feedInfo['api_source']['source_user_info']['uname'];
				$value['sourceInfo']['api_source'] = $feedInfo['api_source'];
			}
			if(empty($value['to_comment_id'])){
				continue;
			}
			$preCom = M('Comment')->where('uid, content')->where("comment_id='{$value['to_comment_id']}'")->find();
			$uinfo = model('User')->getUserInfo($preCom['uid']);
		}
        $this->assign('commentlist', $commentlist);
        // 分页模块
		$count = D('W3gPage', 'w3g')->getComCount($this->mid);
		$this->assign('count',$count);
        // 提示数字归0
		model ( 'UserCount' )->resetUserCount ( $this->mid, 'unread_comment' );
        $this->assign('headtitle', '评论我的');
        $this->display('replyMe');
    }

	// 我的收藏
	public function favorite() {
		$data['page']	= $this->_page;
		$data['count'] = 10;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // 收藏列表
        $weibolist = api('WeiboStatuses')->data($data)->favorite_weibo();
        $weibolist = $this->__formatByContent($weibolist);
		foreach ($weibolist as $k => $v) {
			if($v['feed_id']){
				$weibolist[$k]['content'] = $v['feed_content'];
				$weibolist[$k]['uid'] = $weibolist[$k]['source_user_info']['uid'] ;
				$weibolist[$k]['favorited'] = 1;
        		$weibolist[$k]['weibo_id'] = $weibolist[$k]['feed_id'];
			}else{
				unset($weibolist[$k]);
			}
        	
        }
        //当前用户的总收藏数
        $this->assign('count',$profile['count_info']['favorite_count']);
        // digg
		$feedIds = getSubByKey($weibolist, 'feed_id');
		$feedIds = array_filter($feedIds);
		$sdiggArr = model('FeedDigg')->checkIsDigg($feedIds, $this->mid);
		$sdiggArr = array_keys($sdiggArr);
		foreach ($weibolist as &$value) {
			if (!empty($value['feed_id']) && in_array($value['feed_id'], $sdiggArr)) {
				$value['is_digg'] = 1;
			} else {
				$value['is_digg'] = 0;
			}
		}
        $this->assign('weibolist', $weibolist);
        $this->assign('favorite',1);
        $this->display();
	}

	//下面的方法是判断是否被收藏，在WeiboStatuses这个接口里面封装好了判断是否被收藏的信息，所以不用下边这个了
	private function __formatByFavorite($weibolist) {     //format  格式化的意思
		$ids = implode(',', getSubByKey($weibolist, 'weibo_id'));
        $favorite = D('Favorite','weibo')->isFavorited($ids, $this->mid);    //D('Favorite,'weibo') 实例blog项目下的Favorite模型
        foreach ($weibolist as $k => $v) {
        	if ( in_array($v['weibo_id'], $favorite) ) {
        		$weibolist[$k]['is_favorite'] = 1;
        	}else {
        		$weibolist[$k]['is_favorite'] = 0;
        	}
        }
        return $weibolist;
	}

	private function __formatByContent($weibolist)
	{
		$self_url = urlencode($this->_self_url);
		
		foreach ($weibolist as $k => &$v) {
			switch ($v['app']) {
				case 'public':
					if($v['feed_id']){
						$weibolist[$k]['weibo_id'] = $weibolist[$k]['feed_id'];
						// $weibolist[$k]['content'] = wapFormatContent($v['content'], true, $self_url);
						// 视频处理
						if($v['type'] == 'postvideo'){
							$weibolist[$k]['content'] = $v['source_body'];
						}
						// 非视频微博
						if ($v['transpond_data']['content']) {
							$weibolist[$k]['transpond_data']['content'] = wapFormatContent($v['transpond_data']['content'], true, $self_url);
							$weibolist[$k]['transpond_data']['weibo_id'] = $weibolist[$k]['transpond_data']['feed_id'];
						}else{
							$row_id = model('Feed')->where('feed_id='.$v['feed_id'])->getField('app_row_id');
							$uid = model('Feed')->where('feed_id='.$row_id)->getField('uid');
							$weibolist[$k]['transpond_data'] = model('User')->getUserInfo($this->uid);
						}
						$weibolist[$k]['ctime'] = date('Y-m-d H:i', $v['publish_time']);
					}else{
						if($weibolist[$k]['row_id']){
							$weibolist[$k]['ctime'] = strtotime($weibolist[$k]['ctime']);
						}else{
							unset($weibolist[$k]);
						}
						
					}
					break;
					case 'weiba':
						$weiba_post = D('WeibaPost', 'weiba')->where('post_id='.$v['app_row_id'])->find();
						$weibolist[$k]['weibo_id'] = $weibolist[$k]['feed_id'];
						$weibolist[$k]['transpond_data'] = $weiba_post;
						$weibolist[$k]['transpond_data']['weibo_id'] = $weibolist[$k]['feed_id'];
						$weibolist[$k]['transpond_data']['uname'] = model('User')->where('uid='.$weiba_post['post_uid'])->getField('uname');
						$weibolist[$k]['transpond_data']['uid'] = $weiba_post['post_uid'];
						break;
				
				default:
					# code...
					break;
			}

			$map['source_id'] = $v['feed_id'];
			$map['uid'] = $this->mid;
			$fav = model('Collection')->where($map)->getField('source_id');
			if($fav){
				$weibolist[$k]['favorited'] = 1;
			}else{
				$weibolist[$k]['favorited'] = 0;
			}
			

			//链接
			//$v['content'] = replaceUrl($v['content']);
			
		}

		//dump($weibolist);
		return $weibolist;
	}

	private function __formatByComment($comment) {
		$self_url = urlencode($this->_self_url);
		foreach ($comment as $k => $v) {
			$comment[$k]['content'] = wapFormatComment($v['content'], true, $self_url);
		}
		return $comment;
	}

	// 话题
	public function topic() {
		$map['recommend'] = 1;
		$order = 'recommend_time DESC';
		$topic = M('feed_topic')->where($map)->field('topic_id,topic_name,count')->order($order)->findAll();
		$this->assign('topic', $topic);
		$this->display();
	}


	// 关注列表
	public function following() {
		$this->__followlist('user_following');
	}

	// 粉丝列表
	public function followers() {
		$this->__followlist('user_followers');
	}

	// 微博详情
	public function detail() {
		if(intval($_GET['weibo_id'])){
			$data['id']   = intval($_GET['weibo_id']);
		}elseif(intval($_GET['id'])){
			$data['id']   = intval($_GET['id']);
		}
		$detail       = api('WeiboStatuses')->data($data)->show();
		$map['source_id'] = $data['id'];
		$map['uid'] = $this->mid;
		$detail['iscoll']['colled'] = model('Collection')->where($map)->count() ? 1 : 0;
		// $detail['is_favorite'] = api('Favorites')->data($data)->isFavorite() ? 1 : 0;
		$detail['content'] = wapFormatContent($detail['content'], false, urlencode($this->_self_url));
		// $detail = $this->__formatByContent($detail);
		$this->assign('weibo', $detail);
		// dump($detail);
		$data['page'] = $this->_page;
		$data['count'] = 10;
		$comment      = api('WeiboStatuses')->data($data)->comments();
		foreach ($comment as $key => $value) {
			$comment[$key]['level'] = M('credit_user')->where('uid='.$value['uid'])->find();
		}
		$this->assign('comment', $comment);
		$this->assign('headtitle', '微博详情');
		$this->display();
	}

	// 图片
	public function image() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
		}
		$weibo = api('Statuses')->data(array('id'=>$weibo_id))->show();

		$image = intval($weibo['transpond_id']) == 0 ? $weibo['type_data'] :  $weibo['transpond_data']['type_data'];
		if (empty($image)) {
			$this->redirect(U('w3g/Index/index'), 3, '无图片信息');
		}

		$this->assign('weibo_id',$weibo_id);
		$this->assign('image', $image);
		$this->display();
	}

	private function __followlist($type) {
		$data['user_id'] = $_GET['uid'] <= 0 ? $this->mid : intval($_GET['uid']);
		$data['page']    = $this->_page;
		$data['count'] = 10;
		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // 粉丝OR关注列表
		$followlist = api('User')->data($data)->$type();
		// 数组组装符合T2 格式
		foreach ($followlist as $key => $value) {
			unset($followlist[$key]);
			$followlist[$key]['user'] = $value;
			$following = $followlist[$key]['user']['follow_state']['following'];
			$follower = $followlist[$key]['user']['follow_state']['follower'];

			if($following){
				$followlist[$key]['user']['follow_state']['value'] = '已关注';
			}else{
				$followlist[$key]['user']['follow_state']['value'] = '加关注';
			}

			if($following && $follower){
				$followlist[$key]['user']['follow_state']['value'] = '已互粉';
			}
		}
		//加标题
		if($type == 'user_following' && $_GET['uid'] == $this->mid){
			$this->assign('headtitle', '我的关注');
		}elseif($type == 'user_following' && $_GET['uid'] != $this->mid){
			$this->assign('headtitle', "{$profile['uname']}的关注");
		}
		if($type == 'user_followers' && $_GET['uid'] == $this->mid){
			$this->assign('headtitle', '我的粉丝');
		}elseif($type == 'user_followers' && $_GET['uid'] != $this->mid){
			$this->assign('headtitle', "{$profile['uname']}的粉丝");
		}
		//分页模块
		$count = D('W3gPage','w3g')->getMyFansCount($data['user_id']);
		$this->assign('count', $count);
		$bcount = D('W3gPage','w3g')->getMyFollCount($data['user_id']);
		$this->assign('bcount', $bcount);
		$this->assign('userlist', $followlist);
		// dump($followlist[2]);
		$this->assign('type', $type);
		$this->display('followlist');
	}

	//关注
	public function doFollow() {
		$user_id = intval($_GET['user_id']);
		if ( !in_array($_GET['from'], array('user_following', 'user_followers', 'search', 'weibo')) ||
			 !in_array($_GET['type'], array('follow', 'unfollow'))     ||
			 $user_id <= 0 ) {
			// redirect(U('w3g/Index/index'), 3, '参数错误');
			echo '0';
			exit;
		}
		$data['user_id'] = $user_id;
		$method = $_GET['type'] == 'follow' ? 'follow_create' : 'follow_destroy';
		if ( api('User')->data($data)->$method() ) {
			echo '1';
		}else {
			echo '0';
		}
	}
	//3.0W3G版这个没有用到
	public function post() {
		// 自动携带搜索的关键字
		$this->assign('keyword', isset($_REQUEST['key']) ? '#'.$_REQUEST['key'].'# ' : '');

		$this->assign('headtitle', '发表微博');

		//检查可同步的平台的key值是否可用
		$config = model('AddonData')->lget('login');
		$this->assign('sync',count($config['publish']));

		$this->display();
	}

	public function doPost() {
		$_POST['content'] = preg_replace('/^\s+|\s+$/i', '', $_POST['content']);

		$pathinfo = pathinfo($_FILES['pic']['name']);   //pathinfo() 函数以数组的形式返回文件路径的信息。
		$ext = $pathinfo['extension'];  //extension上传文件的后缀类型
		$allowExts = array('jpg', 'png', 'gif', 'jpeg');

		$uploadCondition = $_FILES['pic'] && in_array(strtolower($ext),$allowExts,true);

		if(!empty($_FILES['pic']['tmp_name']) && !$uploadCondition){
			// redirect(U('w3g/Index/index'), 3, '只能上传图片附件');
			echo '只能上传图片附件';
			exit;
		}
		if ( empty($_POST['content']) && !$_FILES['pic'] ) {
			// $this->redirect(U('w3g/Index/post'), 2, '内容不能为空');
			echo '内容不能为空';
			exit;
		}
		//下面是分拆选项中选择不分拆后的跳转
		if (isset($_POST['nosplit'])) {
			$this->assign('content', $_POST['content']);
			//$this->index();
			// $this->redirect(U('w3g/Index/index'), 2, '发布失败，字数超过限制');
			echo '发布失败，字数超过限制';
			exit();
		}
		$data = array();
		// 获取附件
		if(isset($_POST['feed_attach_type'])){
			$feed_attach_type=strval($_POST['feed_attach_type']);
			if($feed_attach_type==='image'){
				$data['type']='postimage';
			}else if($feed_attach_type==='file'){
				$data['type']='postfile';
			}
		}
		if(isset($_POST['attach_id'])){
			$attach_id=strval($_POST['attach_id']);
			if($attach_id!==''){
				$data['attach_id']=$attach_id;
			}
		}
		//发布微博限制字数,与后台设置一样
		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		// 字数统计
		$length = mb_strlen($_POST['content'], 'UTF8');
        $parts  = ceil($length/$weibo_nums);
		if (!isset($_POST['split']) && $length > $weibo_nums) {
			// 自动发一条图片微博
			if(!empty($_FILES['pic']['name'])){
				$data['pic']      = $_FILES['pic'];
				$data['content']  = '图片分享';
				$data['from']     = $this->_type_wap;
				$res = api('weiboStatuses')->data($data)->upload();
			}
				echo 'many';
				exit();
			// 提示是否自动拆分
			$this->assign('content', $_POST['content']);
			$this->assign('length', $length);
			$this->assign('parts', $parts);
			$this->display('split');
		}else {
			$api_method = 'update';
			if ($_FILES['pic']) {
				$data['pic']		= $_FILES['pic'];
				$api_method 		= 'upload';
			}
			// 自动拆分成多条
			for ($i = 1; $i <= $parts; $i++) {
				$sub_content      = mb_substr($_POST['content'], 0, 140, 'UTF8');
				$data['content']  = $sub_content;
				$data['from']     = $this->_type_wap;
				$data['app_name'] = 'public';
                $_POST['content'] = mb_substr($_POST['content'], 140, -1, 'UTF8');
				$res = api('WeiboStatuses')->data($data)->$api_method();
				// $res = $this->__formatByContent($res);
				if (!$res) {
					echo '0';
					//
					//return ;
				}else{
					//添加话题
		            model('FeedTopic')->addTopic(html_entity_decode($data['content'], ENT_QUOTES, 'UTF-8'), $res, 'post');
					model('Cache')->rm('fd_'.$res);
					model('Cache')->rm('feed_info_'.$res);
					//添加积分
					X('Credit')->setUserCredit($this->mid,'add_weibo');
					model('Credit')->setUserCredit($this->mid, 'forum_post');
					// $this->redirect(U('w3g/Index/doPostTrue'), 3, '发布成功');
					// echo 1;
					$this->doPostTrue();
					// header("location:".U('w3g/Index/doPostTrue'));
				}
			}
		}
	}

	//发表成功后用来传递给首页发表成功的页面
	public function doPostTrue(){
		$uid = $data['user_id'] = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$profile = api('User')->data($data)->show();
		$data['id'] = $profile['last_feed']["$uid"]['feed_id'];
		$feed = api('WeiboStatuses')->data($data)->show();
		// $feed = $this->__formatByContent($feed);
		// dump($feed);
		$this->assign('feed',$feed);
		$this->display('doPostTrue');
	}

	//因为评论的方式变了，所以没有用到以下方法
	public function comment() {
		$weibo_id 	= intval($_GET['weibo_id']);
		$comment_id	= intval($_GET['comment_id']);
		$uid		= intval($_GET['uid']);
		if ( $weibo_id <= 0 || $uid <= 0 ) {
			// $this->redirect(U('w3g/Index/index'), 3, '参数错误');
			// return ;
			echo '参数错误';
			exit();
		}
		$this->assign('weibo_id', $weibo_id);
		$this->assign('comment_id', $comment_id);
		$this->assign('uname', getUserName($uid));
		$this->display();
	}

	/**
	 * 添加评论接口，目前只支持微博与微吧
	 * @return integer 返回状态
	 */
	public function doComment() {
		$feed_id = intval($_POST['feed_id']);
		if ($feed_id <= 0) {
			echo '参数错误';
			exit;
		}
		$content = t($_POST['content']);
		if (empty($content)) {
			echo '内容不能为空';
			exit;
		}
		// 仅取前140字
		$content = mb_substr($content, 0, 140, 'UTF8');
		
		$type = t($_POST['type']);

		if (in_array($type, array('weiba_repost', 'weiba_post'))) {
			$data['app_name'] = 'weiba';
			$data['app_row_id'] = model('Feed')->where('feed_id='.$feed_id)->getField('app_row_id');
		} else {
			$data['app_name'] = 'public';
		}
		$data['table_name'] = 'feed';

	    // app_uid  是被评论微博作者的ID
    	$data['app_uid'] = isset($_POST['appid']) ? intval($_POST['appid']) : '0';
    	$data['comment_old'] = isset($_POST['comment_old']) ? $_POST['comment_old'] : '0';
    	// 评论内容
    	$data['content'] = isset($content) ? $content : '';	
    	// row_id  是被评论微博的ID
    	$data['row_id'] = isset($_POST['rowid']) ? intval($_POST['rowid']) : '0';  
    	// 评论所需内容组装
		$data['ifShareFeed'] = isset($_POST['ifShareFeed']) ? intval($_POST['ifShareFeed']) : '0';
    	$data['from'] = $this->_type_wap;
    	$data['at'] = $_POST['at'];		// TODO
		$res = api('WeiboStatuses')->data($data)->comment();
		if ($res) {
			$this->doCommentTrue($data['row_id'], intval($_POST['feed_id']));
			// header("location:?app=w3g&mod=Index&act=doCommentTrue&rowid=".$data['row_id']."&weibo_id=".$_POST['feed_id']);
		} else {
			echo '0';
		}
	}

	//发表成功后用来传递给首页发表成功的页面
	public function doCommentTrue($id, $feedId){
		$_GET['weibo_id'] = $feedId;
		$data['id']   = $id;
		$data['page'] = $this->_page;
		$data['count'] = 10;
		$comment      = api('WeiboStatuses')->data($data)->comments();
		$detail       = api('WeiboStatuses')->data($data)->show();
		foreach ($comment as $key => $value) {
			$comment[$key]['level'] = M('credit_user')->where('uid='.$value['uid'])->find();
		}
		//$comment	  = $this->__formatByComment($comment);
		// dump($comment);
		$this->assign('weibo', $detail);
		$this->assign('comment', $comment);

		$this->display('doCommentTrue');
	}

	//对一条评论 评论后 用来传递给详情页回复成功的页面
	public function doCommentD(){
		if ( ($feed_id = intval($_POST['rowid'])) <= 0 ) {
			// $this->redirect(U('w3g/Index/index'), 3, '参数错误');
			echo '参数错误';
			exit;
		}
		if ( empty($_POST['content']) ) {
			// $this->redirect(U('w3g/Index/detail',array('feed_id'=>$feed_id)), 3, '内容不能为空');
			// return ;
			echo '内容不能为空';
			exit();
		}
		//原微博的内容
		$map['comment_id'] = $_POST['comment_id'];
		$preComment = M('Comment')->where($map)->find();
		// 仅取前140字
		$_POST['content'] = mb_substr($_POST['content'], 0, 140, 'UTF8');
		$data['user_id'] = $_POST['touid'];
		$commentd = api('User')->data($data)->show();
		// 整合被转发的内容
		// $_POST['content'] = "回复@{$commentd['uname']}：".$_POST['content']."//@{$commentd['uname']}：".$preComment['content'];
		$_POST['content'] = "回复@{$commentd['uname']}：".$_POST['content'];

		$data['app']    		= 'public';
		$data['table']  		= 'feed';
    	// $data['app_row_id'] 	= isset($this->data['app_row_id']) ? $this->data['app_row_id'] : '0';
    	$data['app_uid']	 	= isset($_POST['appid']) ? $_POST['appid'] : '0';    //app_uid  是被评论微博作者的ID
    	$data['comment_old'] 	= isset($_POST['comment_old']) ? $_POST['comment_old'] : '0';
    	$data['content']	 	= isset($_POST['content']) ? $_POST['content'] : '';	//评论内容
    	// $data['content'] 		= mb_substr($_POST['content'], 0, $_POST['weibo_nums'], 'UTF8');
		$data['row_id'] = isset($_POST['rowid']) ? $_POST['rowid'] : '0';   //row_id  是被评论微博的ID
    	$commentInfo = model('Comment')->getCommentInfo(intval($_POST['comment_id']));
    	if ($commentInfo['app'] == 'weiba') {
    		$feedInfo = model('Feed')->getFeedInfo($data['row_id']);
    		$data['app_row_id'] = $feedInfo['app_row_id'];
    	}
    	$data['to_comment_id']   = isset($_POST['comment_id']) ? $_POST['comment_id'] : '0'; 
    	$data['to_uid']			= isset($_POST['touid']) ? $_POST['touid'] : '0';
    	$data['ifShareFeed'] = isset($_POST['ifShareFeed']) ? $_POST['ifShareFeed'] : '0';
    	$data['at'] = $_POST['at'];
    	$data['from']			= $this->_type_wap;
		$res = api('WeiboStatuses')->data($data)->comment();
		// $res = $this->__formatByContent($res);
		if ($res) {
			// header("location:?app=w3g&mod=Index&act=doCommentTrue&rowid=".$data['row_id']."&weibo_id=".$_POST['rowid']);
			$this->doCommentTrue($data['row_id'], intval($_POST['rowid']));
		}else {
			echo '0';
		}
	}

	//转发   forward转发的意思
	public function forward() {
		$weibo_id = intval($_GET['weibo_id']);
		if ( $weibo_id <= 0 ) {
			// $this->redirect(U('w3g/Index/index'), 3, '参数错误');
			// return ;
			echo '参数错误';
			exit();
		}
		$data['id']	= $weibo_id;
		$weibo = api('WeiboStatuses')->data($data)->show();
		// $weibo = $this->__formatByContent($weibo);
		if (!$weibo) {
			// $this->redirect(U('w3g/Index/index'), 3, '参数错误');
			// return ;
			echo '参数错误';
			exit();
		}

		$this->assign('weibo', $weibo);
		$this->assign('headtitle', '转发微博');
		$this->display();
	}

	//接受转发数据
	public function doForward() {

		$weibo_id = intval($_POST['feed_id']);
		if ($weibo_id <= 0) {
			echo '参数错误';
			exit();
		}
		if (empty($_POST['content'])) {
			echo '内容不能为空';
			exit();
		}

		$data['id']	= $weibo_id;
		$weibo = api('WeiboStatuses')->data($data)->show();
		unset($data);
		if ( empty($weibo) ) {
			// redirect(U('wap/Index/index'), 3, '参数错误');
			echo '参数错误';
			exit();
		}
		$p['comment']  = $_POST['comment'];
		// 整合被转发的内容
		if ( $weibo['is_repost'] == 1 ) {
			$_POST['content'] .= "//@{$weibo['uname']}：{$weibo['feed_content']}";
		}

		// 仅取前140字
		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		$_POST['content'] = mb_substr($_POST['content'], 0,$weibo_nums , 'UTF8');
		
		$data['content']		= $_POST['content'];
		$data['from']			= $this->_type_wap;
		$data['transpond_id']	= $weibo['transpond_id'] ? $weibo['transpond_id'] : $weibo_id;
		if (intval($_POST['isComment']) == 1) {
			$weibo = api('WeiboStatuses')->data(array('id'=>$weibo_id))->show();
			// $weibo = $this->__formatByContent($weibo);
			$data['reply_data']	= $weibo['weibo_id'];
			if ( !empty($weibo['transpond_data']) ) {
				$data['reply_data']	.= ',' . $weibo['transpond_data']['weibo_id'];
			}
		}
		// 组装接口数据
		$p['app_name'] = $weibo['app'];
		$p['body']     = $_POST['content'];
		$p['content']     = $_POST['content'];
		if(!in_array($weibo['type'], array('repost', 'weiba_post', 'weiba_repost'))) {
			$p['id'] =  $weibo['feed_id'];
			$weibo['type'] = 'feed';
		} elseif ($weibo['type'] == 'weiba_post' || $weibo['type'] == 'weiba_repost'){
			$p['id'] = $weibo['app_row_id'];
			$weibo['type'] = 'weiba_post';
			$weibo['app_row_table'] = 'feed';
		}  else {
			$p['id'] =  $weibo['app_row_id'];
			$weibo['type'] = 'feed';
		}
		$p['type']	   =  $weibo['type'];
		$p['from']     = $data['from'] ? intval($data['from']) : '0';
		$p['forApi']   = true;
		$p['curid'] = $weibo_id;
		$p['curtable'] = $weibo['app_row_table'];
		$p['sid'] = $p['id'];
		$res = api('WeiboStatuses')->data($p)->repost();
		if ($res) {
			// redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id,'type'=>$weibo['type'])), 1, '转发成功');
			// redirect(U('wap/Index/index'), 1, '转发成功');
			//添加积分
			X('Credit')->setUserCredit($this->mid,'add_weibo');
			model('Credit')->setUserCredit($this->mid, 'forum_post');
			// $this->redirect(U('w3g/Index/doPostTrue'), 3, '发布成功');
			// header("location:".U('w3g/Index/doForwardTrue'));
			$this->doForwardTrue();
		}else {
			// redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 3, '转发失败, 请稍后重试');
			echo '0';
		}
	}

	//转发成功后用来传递给首页发表成功的页面
	public function doForwardTrue(){
		$uid = $data['user_id'] = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$profile = api('User')->data($data)->show();
		$data['id'] = $profile['last_feed']["$uid"]['feed_id'];
		$feed = api('WeiboStatuses')->data($data)->show();
		// $feed = $this->__formatByContent($feed);
		$this->assign('feed',$feed);
		$this->display('doForwardTrue');
	}

	// //搜用户
	// public function searchuser(){
	// 	$this->assign('back','back');
	// 	$this->display();
	// }


	//删除微博
	public function doDelete() {
		$weibo_id = intval($_POST['weibo_id']);
		$type = $_POST['type'];
		if ($weibo_id <= 0) {
			// $this->redirect(U('w3g/Index/index', 3, '参数错误'));
			// return ;
			echo '参数错误';
			exit();
		}
		$data['id'] = $weibo_id;
		$detail = api('WeiboStatuses')->data($data)->show();
		$data['source_table_name'] = $detail['app_row_table'];

		// 不存在时
		if(!$detail){
			echo 0;
			exit();
		}
		// 非作者时
		if($detail['uid']!=$this->mid){
			// 没有管理权限不可以删除
			if(!CheckPermission('core_admin','feed_del')){
				echo 0;
				exit();
			}
		// 是作者时
		}else{
			// 没有前台权限不可以删除
			if(!CheckPermission('core_normal','feed_del')){
				echo 0;
				exit();
			}
		}

		$res = api('WeiboStatuses')->data($data)->destroy();
		// 微吧帖子删除
		switch ($type) {
			case 'weiba_post':
				$postInfo = D('weiba_post')->where('feed_id='.$weibo_id)->find();
				$postId = $postInfo['post_id'];
				$weibaId = $postInfo['weiba_id'];
				if (D('weiba_post')->where('post_id='.$postId)->setField('is_del', 1)) {
					$postDetail = D('weiba_post')->where('post_id='.$postId)->find();
					D('Log', 'weiba')->writeLog($postDetail['weiba_id'], $this->mid, '删除了帖子“'.$postDetail['title'].'”', 'posts');
					D('weiba')->where('weiba_id='.$weibaId)->setDec('thread_count');
					model('Credit')->setUserCredit($this->mid, 'delete_topic');
				}
				break;
		}

		if ($res) {
			echo "1";
			exit();
		}else {
			echo "0";
			exit();
		}
	}


	//收藏
	public function doFavorite() {
		$weibo_id = intval($_POST['feed_id']);
		if ($weibo_id <= 0) {
			// redirect(U('w3g/Index/index', 3, '参数错误'));
			echo '参数错误';
			exit();
		}
		$data['id'] = $weibo_id;
		// 收藏数据组合
		$detail = api('WeiboStatuses')->data($data)->show();
		// $data['source_table_name'] = $detail['app_row_table'];
		$data['source_table_name'] = 'feed';
		$data['source_id'] = $detail['feed_id'];
		// $data['source_app'] = $detail['app'];
		$data['source_app'] = 'public';
		$res = api('WeiboStatuses')->data($data)->favorite_create();
		$res = $this->__formatByContent($res);
		if ($res) {
			echo '1';
		}else {
			echo '0';
		}
	}

	//取消收藏
	public function doUnFavorite() {
		$type = empty($_POST['type'])?$type='feed':$type=$_POST['type'];
		$weibo_id = intval($_POST['feed_id']);
		if ($weibo_id <= 0) {
			// redirect(U('w3g/Index/index', 3, '参数错误'));
			echo '参数错误';
			exit();
		}
		$data['id'] = $weibo_id;
		// $res = api('Favorites')->data($data)->destroy();
		$res = model('Collection')->delCollection($data['id'],$type);
		// dump($res);
		if ($res) {
			echo '1';
		}else {
			echo '0';
		}
	}

	public function urlalert() {
		if( !isset($_GET['url']) || !isset($_GET['from_url']) ) {
			redirect(U('w3g/Index/index'), 3, '参数错误');
		}
		$this->assign('url', $_GET['url']);
		$this->assign('from_url', $_GET['from_url']);
		$this->display();
	}

	// URL重定向
	function redirect($url,$time=0,$msg='') {
		//多行URL地址支持
		$url = str_replace(array("\n", "\r"), '', $url);
		if(empty($msg))
		$msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			// redirect
			if(0===$time) {
				header("Location: ".$url);
			}else {
				header("refresh:{$time};url={$url}");
				// 防止手机浏览器下的乱码
				$str = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$str .= $msg;
			}
		}else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time!=0)
			$str   .=   $msg;
		}
		$this->assign('msg', $str);

		$this->display('redirect');
		exit;
	}

	//获取最新微博数
	function countnew(){
		$map="weibo_id>{$_POST['nowMaxID']} AND isdel=0";
    	$map.=" AND ( uid IN (SELECT fid FROM ".C('DB_PREFIX')."weibo_follow WHERE uid=$this->uid) OR uid=$this->uid )";
    	$countnew = M('Weibo')->where($map)->count();
		echo $countnew?$countnew:'0';
	}

	//搜索话题
	function doSearch(){
		$key = t($_REQUEST['key']);
		if ( empty($key) )
			// redirect(U('w3g/Index/search'), 3, '请输入关键字');
			echo '请输入关键字';
		// 搜人  搜微博
		if ( isset($_REQUEST['user']) ) {
			$method  = 'weibo_search_user';
			$display = 'searchuser';
		}elseif(isset($_REQUEST['weibo'])) {
			$method  = 'weibo_search_weibo';
			$display = 'searchweibo';
		}
		// 搜话题
		if(!isset($_REQUEST['user']) && !isset($_REQUEST['weibo'])){
			$method  = 'wap_search_topic';
			$display = 'searchtopic';
		}

		$data['key'] 	= t($_REQUEST['key']);
		$data['page']	= $this->_page;
		$data['count'] = 10;
		if($method == 'weibo_search_user'){
			$res = api('WeiboStatuses')->data($data)->$method();
			$res = $this->__formatByContent($res);
			// dump($res);
			// 数组组装符合T2 格式
			foreach ($res as $key => $value) {
				if($value['follow_state']['following']){
					$res[$key]['follow_state']['value'] = '已关注';
				}else{
					$res[$key]['follow_state']['value'] = '加关注';
				}

				if($value['follow_state']['following'] && $value['follow_state']['follower']){
					$followlist[$key]['follow_state']['value'] = '互相关注';
				}
			}
		}
		if($method == 'weibo_search_weibo'){
			$res = api('WeiboStatuses')->data($data)->$method();
			$res = $this->__formatByContent($res);
			// dump($res['0']);
		}
		if($method == 'wap_search_topic'){
			$res = api('WeiboStatuses')->data($data)->$method();
			// $res = $this->__formatByContent($res);
		}
		if ($display == 'searchuser') {
			$userlist = array();
			foreach ($res as $k => $v) {
				$userlist[$k]['user'] = $v;
			}
			$this->assign('userlist', $userlist);
			$this->assign('type', 'search');
		}
		if($display == 'searchweibo') {
			$res = $this->__formatByContent($res);
			$this->assign('weibolist', $res);
		}
		if($display == 'searchtopic'){
			// $res = $this->__formatByContent($res);
			$this->assign('weibolist', $res);
		}
		$this->assign('keyword', $_REQUEST['key']);
		$this->assign('headtitle', '搜索');
		$this->display($display);
	}
	// ajax上传-iframe页
	public function ajax_iframe() {
		$this->assign('headtitle', 'AJAX_iframe');
		$this->display('ajax_iframe');
	}

	public function ajax_image_upload () {
		$data['attach_type'] = t($_REQUEST['attach_type']);
        $data['upload_type'] = $_REQUEST['upload_type']?t($_REQUEST['upload_type']):'file';

        $thumb  = intval($_REQUEST['thumb']);
        $width  = intval($_REQUEST['width']);
        $height = intval($_REQUEST['height']);
        $cut    = intval($_REQUEST['cut']);

        
        $option['attach_type'] = $data['attach_type'];
        $info = model('Attach')->upload($data, $option);

    	if($info['status']){
    		$data = $info['info'][0];
            if($thumb==1){
                $data['src'] = getImageUrl($data['save_path'].$data['save_name'],$width,$height,$cut);
            }else{
                $data['src'] = $data['save_path'].$data['save_name'];
            }
    		
    		$data['extension']  = strtolower($data['extension']);
    		$return = array('status'=>1,'data'=>$data);
    	}else{
    		$return = array('status'=>0,'data'=>$info['info']);
    	}
    	$this->assign('return', $return);
    	$this->assign('attach_id', $return['data']['attach_id']);
    	$this->assign('attach_src', $return['data']['src']);
    	$this->assign('attach_type', $return['data']['type']);

    	$this->display('ajax_image_upload');
	}

		// if(intval($_GET['weibo_id'])){
		// 	$data['id']   = intval($_GET['weibo_id']);
		// }elseif(intval($_GET['id'])){
		// 	$data['id']   = intval($_GET['id']);
		// }
		// $detail       = api('Statuses')->data($data)->show();

		// $detail['is_favorite'] = api('Favorites')->data($data)->isFavorite() ? 1 : 0;

		// $detail['content'] = wapFormatContent($detail['content'], false, urlencode($this->_self_url));

		// $this->assign('weibo', $detail);

		// $data['page'] = $this->_page;
		// $comment      = api('Statuses')->data($data)->comments();
		// //$comment	  = $this->__formatByComment($comment);
		// $this->assign('comment', $comment);
		// $this->display();

}