<?php
class IndexAction extends BaseAction
{
	// 个人首页
	public function index($uid = 0)
	{
        $data['user_id'] = $uid <= 0 ? $this->mid : $uid;
        $data['page']    = $this->_page;

		// 用户资料
		$profile = api('User')->data($data)->show();
		$this->assign('profile', $profile);

		// 微博列表
		$weibolist = api('WeiboStatuses')->data($data)->friends_timeline();
		$weibolist = $this->__formatByContent($weibolist);
		// dump($weibolist['0']);
		$this->assign('weibolist', $weibolist);
		$this->display('index');
	}

	// 微博广场
	public function publicsquare()
	{
		$data['page'] = $this->_page;
		$weibolist = api('WeiboStatuses')->data($data)->public_timeline();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		$this->display();
	}

	// XX的微博
	public function weibo()
	{
		$data['user_id']  = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$data['page']     = $this->_page;

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
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
        $this->assign('weibolist', $weibolist);

        $this->assign('hideUsername', '1');
        $this->display();
	}

	// @我
	public function atMe()
	{
		$data['page'] = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // dump($profile);
        // @XX的微博列表
        $weibolist = api('WeiboStatuses')->mentions();
		$weibolist = $this->__formatByContent($weibolist);
		// 提示数字归0
		model ( 'UserCount' )->resetUserCount( $this->mid, 'unread_atme' );

		$this->assign('weibolist', $weibolist);
		// dump($weibolist);
		$this->assign('atMe',1);
        $this->display('weibo');
	}

	// 评论我的
	public function replyMe() {
		$data['page']     = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);
        // dump($profile);
        // 评论的微博列表
        $commentlist = api('WeiboStatuses')->data($data)->comments_to_me();
        // dump($commentlist);
		// $commentlist = $this->__formatByContent($commentlist);
        $this->assign('commentlist', $commentlist);
        // 提示数字归0
		model ( 'UserCount' )->resetUserCount ( $this->mid, 'unread_comment' );
        $this->assign('headtitle', '评论我的');
        $this->display('commentlist');
	}

	// 我的收藏
	public function favorite()
	{
		$data['page']	= $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // 收藏列表
        $weibolist = api('WeiboStatuses')->favorite_weibo();
		foreach ($weibolist as $k => $v) {
			if($v['feed_id']){
				$weibolist[$k]['content'] = $v['feed_content'];
				$weibolist[$k]['uid'] = $weibolist[$k]['source_user_info']['uid'] ;
				$weibolist[$k]['favorited'] = 1;
        		$weibolist[$k]['weibo_id'] = $weibolist[$k]['feed_id'];
        		// $weibolist[$k]['ctime'] = 
			}else{
				unset($weibolist[$k]);
			}
        	
        }
        $weibolist = $this->__formatByContent($weibolist);
        // dump($weibolist);
        $this->assign('weibolist', $weibolist);
        // 
        $this->assign('favorite',1);
        $this->display('weibo');
	}

	private function __formatByFavorite($weibolist)
	{
		$ids = implode(',', getSubByKey($weibolist, 'weibo_id'));
        // $favorite = D('Favorite','weibo')->isFavorited($ids, $this->mid);
        foreach ($weibolist as $k => $v) {
        	if ( in_array($v['weibo_id'], $favorite) ) {
        		$weibolist[$k]['favorited'] = 1;
        	}else {
        		$weibolist[$k]['favorited'] = 0;
        	}
        }
        return $weibolist;
	}

	private function __formatByContent($weibolist)
	{
		$self_url = urlencode($this->_self_url);
		foreach ($weibolist as $k => $v) {
			switch ($v['app']) {
				case 'public':
					if($v['feed_id']){
						$weibolist[$k]['weibo_id'] = $weibolist[$k]['feed_id'];
						$weibolist[$k]['content'] = wapFormatContent($v['content'], true, $self_url);
						// 视频处理
						if($v['type'] == 'postvideo'){
							$weibolist[$k]['content'] = $v['source_body'];
						}
						// 非视频微博
						if ($v['transpond_data']['content']) {
							$weibolist[$k]['transpond_data']['content'] = wapFormatContent($v['transpond_data']['content'], true, $self_url);
							$weibolist[$k]['transpond_data']['weibo_id'] = $weibolist[$k]['transpond_data']['feed_id'];
							// $weibolist[$k]['transpond_data']['is_del'] = 0;
						}else{
							$row_id = model('Feed')->where('feed_id='.$v['feed_id'])->getField('app_row_id');
							$uid = model('Feed')->where('feed_id='.$row_id)->getField('uid');
							$weibolist[$k]['transpond_data'] = model('User')->getUserInfo($this->uid);
							// $weibolist[$k]['transpond_data']['content'] = '此微博已被删除';
							// $weibolist[$k]['transpond_data']['is_del'] = 1;
						}
						$map['source_id'] = $v['feed_id'];
						$map['uid'] = $this->mid;
						$fav = model('Collection')->where($map)->getField('source_id');
						if($fav){
							$weibolist[$k]['favorited'] = 1;
						}else{
							$weibolist[$k]['favorited'] = 0;
						}
						$weibolist[$k]['ctime'] = date('Y-m-d H:i', $v['publish_time']);
					}
					else{
						// unset($weibolist[$k]);date('Y-m-d H:i', $v['ctime']);
						if($weibolist[$k]['row_id']){
							$weibolist[$k]['ctime'] = date('Y-m-d H:i', $v['comment_user_info']['ctime']);	
						}else{
							unset($weibolist[$k]);
						}
						
					}
					break;
					case 'weiba':
						$weiba_post = D('WeibaPost')->where('post_id='.$v['app_row_id'])->find();
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
			

			
		}
		return $weibolist;
	}

	private function __formatByComment($comment)
	{
		$self_url = urlencode($this->_self_url);
		foreach ($comment as $k => $v) {
			$comment[$k]['content'] = wapFormatComment($v['content'], true, $self_url);
		}
		return $comment;
	}

	// 关注列表
	public function following() {
		$this->__followlist('user_following');
	}

	// 粉丝列表
	public function followers() {
		$this->__followlist('user_followers');
	}

	// 话题
	public function topic() {
		$map['recommend'] = 1;
		$order = 'recommend_time DESC';
		$topic = M('feed_topic')->where($map)->field('topic_id,topic_name,count')->order($order)->findAll();
		$this->assign('topic', $topic);
		$this->display();
	}

	// 微博详情
	public function detail() {
		if(intval($_GET['weibo_id'])){
			$data['id']   = intval($_GET['weibo_id']);
		}elseif(intval($_GET['id'])){
			$data['id']   = intval($_GET['id']);
		}
		$detail       = api('WeiboStatuses')->data($data)->show();
		// $detail['favorited'] = api('WeiboStatuses')->data($data)->isFavorite() ? 1 : 0;
		$map['source_id'] = $data['id'];
		$map['uid'] = $this->mid;
		$detail['iscoll']['colled'] = model('Collection')->where($map)->count() ? 1 : 0;
		$detail['content'] = wapFormatContent($detail['content'], true, urlencode($this->_self_url));
		$detail['weibo_id'] = $detail['feed_id'];
		$this->assign('weibo', $detail);
		// dump($detail);
		// 微吧帖子处理
		switch ($_GET['type']) {
			case 'weiba':
				$weiba_post = D('WeibaPost','weiba')->where('post_id='.$detail['app_row_id'])->find();
				// $weiba_post['content'] =
				$this->assign('weiba_post',1);
				$this->assign('weiba',$weiba_post);
				break;
			
			default:
				# code...
				break;
		}
		// dump($weiba_post);
		$data['page'] = $this->_page;
		$comment      = api('WeiboStatuses')->data($data)->comments();
		$comment	  = $this->__formatByComment($comment);

		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		$this->assign('weibo_nums',$weibo_nums);
		$this->assign('comment', $comment);
		$this->display();
	}

	// 图片
	public function image() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$weibo = api('WeiboStatuses')->data(array('id'=>$weibo_id))->show();
		$image = intval($weibo['transpond_id']) == 0 ? $weibo['type_data'] :  $weibo['transpond_data']['type_data'];
		if (empty($image)) {
			redirect(U('wap/Index/index'), 3, '无图片信息');
		}

		$this->assign('weibo_id',$weibo_id);
		$this->assign('image', $image);
		$this->display();
	}

	private function __followlist($type) {
		$data['user_id'] = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$data['page']    = $this->_page;

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
				$followlist[$key]['user']['follow_state']['value'] = '未关注';
			}

			if($following && $follower){
				$followlist[$key]['user']['follow_state']['value'] = '互相关注';
			}
		}
		$this->assign('userlist', $followlist);
		$this->assign('type', $type);
		$this->display('followlist');
	}

	public function doFollow() {
        $user_id = intval($_GET['user_id']);
		if ( !in_array($_GET['from'], array('user_following', 'user_followers', 'search', 'weibo')) ||
			 !in_array($_GET['type'], array('follow', 'unfollow'))     ||
			 $user_id <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		if($_GET['from'] == 'user_following'){
			$from = 'following';
		}
		if($_GET['from'] == 'user_followers'){
			$from = 'followers';
		}
		$data['user_id'] = $user_id;
		$method = $_GET['type'] == 'follow' ? 'follow_create' : 'follow_destroy';
		switch ($_GET['from']) {
			case 'search':
				$target = U('wap/Index/doSearch',array('key'=>$_REQUEST['key'],'page'=>$_REQUEST['page'],'user'=>'1'));
				break;
			case 'weibo':
				$target = U('wap/Index/weibo', array('uid'=>$user_id));
				break;
			default:
				$target = U('wap/Index/'.$from);
		}
		if ( api('User')->data($data)->$method() ) {
			redirect($target, 1, '操作成功');
		}else {
			redirect($target, 3, '操作失败');
		}
	}

	public function post() {
		// 自动携带搜索的关键字
		$this->assign('keyword', isset($_REQUEST['key']) ? '#'.$_REQUEST['key'].'# ' : '');
		$this->display();
	}

	public function doPost() {
		$data = array();
		$data['uid'] 		= $this->mid;
		$data['body']		= preg_replace('/^\s+|\s+$/i', '', $_POST['content']);
		// $data['from']		= $this->data['from'] ? intval($this->data['from']) : '0';
		$data['app']        = $this->data['app_name'] ? $this->data['app_name'] : 'public';
		$data['type']       = isset($data['type']) ? $data['type'] : 'post';
		$data['app_row_id'] = $this->data['app_id'] ? $this->data['app_id']:'0';
		$data['publish_time']= time();

		$_POST['content'] = preg_replace('/^\s+|\s+$/i', '', $_POST['content']);

		$pathinfo = pathinfo($_FILES['pic']['name']);
		$ext = $pathinfo['extension'];
		
		$system_default = model('Xdata')->get('admin_Config:attach');
		$allowExts = $system_default['attach_allow_extension'];// array('jpg', 'png', 'gif', 'jpeg','bmp');
		$allowExts = explode(',', $allowExts);

		$pictype = in_array(strtolower($ext),$allowExts,true);
		if(!empty($_FILES['pic']['name']) && !$pictype){
			redirect(U('wap/Index/index'), 3, '上传图片格式不符');
			exit;
		}
		// 判断是图片附件
		$is_pic = strpos($_FILES['pic']['type'], 'image/');
		if(!empty($_FILES['pic']['name']) && $is_pic === false){
			redirect(U('wap/Index/index'), 3, '只能上传图片附件');
			exit;
		}

		if ( empty($_POST['content']) && !empty($_FILES['pic']['name']) ) {
			$_POST['content'] = '图片分享';
		}
		if ( empty($_POST['content']) && empty($_FILES['pic']['name']) ) {
			redirect(U('wap/Index/index'), 3,  '内容不能为空');
		}
		if (isset($_POST['nosplit'])) {
			$this->assign('content', $_POST['content']);
			$this->index();
		}
		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		// 字数统计
		$length = mb_strlen($_POST['content'], 'UTF8');
        $parts  = ceil($length/$weibo_nums);
		if (!isset($_POST['split']) && $length > $weibo_nums) {
			if(!empty($_FILES['pic']['name'])) { // 自动发一条图片微博
				$data['pic']      = $_FILES['pic'];
				$data['content']  = '图片分享';
				$data['from']     = $this->_type_wap;
				$res = api('WeiboStatuses')->data($data)->upload();
			}

			// 提示是否自动拆分
			$this->assign('content', $_POST['content']);
			$this->assign('length', $length);
			$this->assign('weibo_nums',$weibo_nums);
			$this->assign('parts', $parts);
			$this->display('split');
		}else {
			$api_method = 'update';
			if ($_FILES['pic']['size']>0) {
				$data['pic']		= $_FILES['pic'];
				$api_method 		= 'upload';
			}
			// 自动拆分成多条
			for ($i = 1; $i <= $parts; $i++) {
				$sub_content      = mb_substr($_POST['content'], 0, $weibo_nums, 'UTF8');
				$data['content']  = $sub_content;
				$data['from']     = $this->_type_wap;
                $_POST['content'] = mb_substr($_POST['content'], $weibo_nums, -1, 'UTF8');
				$res = api('WeiboStatuses')->data($data)->$api_method();
				if (!$res) {
					redirect(U('wap/Index/index'), 3, '发布失败，请稍后重试');
				}
			}
			//添加话题
            model('FeedTopic')->addTopic(html_entity_decode($data['content'], ENT_QUOTES, 'UTF-8'), $res, 'post');
			model('Cache')->rm('fd_'.$res);
			model('Cache')->rm('feed_info_'.$res);
			redirect(U('wap/Index/index'), 1, '发布成功');
		}
	}

	public function comment() {
		$weibo_id 	= intval($_GET['weibo_id']);
		$comment_id	= intval($_GET['comment_id']);
		$uid		= intval($_GET['uid']);
		if ( $weibo_id <= 0 || $comment_id <= 0 || $uid <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$this->assign('weibo_id', $weibo_id);
		$this->assign('comment_id', $comment_id);
		$this->assign('uname', getUserName($uid));
		$to_uid = model('Feed')->where('feed_id='.$weibo_id)->getField('uid');
		$this->assign('to_uid',$to_uid);

		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		$this->assign('weibo_nums',$weibo_nums);

		$this->display();
	}

	public function doComment() {
		if ( ($weibo_id = intval($_POST['weibo_id'])) <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		if ( empty($_POST['content']) ) {
			redirect(U('wap/Index/detail',array('weibo_id'=>$weibo_id)), 3,  '内容不能为空');
		}
		// 评论所需内容组装
		$data['ifShareFeed'] = isset($_POST['ifShareFeed']) ? $_POST['ifShareFeed'] : '0';

		$data['app']    = 'public';
		$data['table']  = 'feed';
    	$data['app_row_id']  = isset($this->data['app_row_id']) ? $this->data['app_row_id'] : '0';
    	$data['app_uid']	 = isset($_POST['app_uid']) ? $_POST['app_uid'] : '0';
    	$data['comment_old'] = isset($this->data['comment_old']) ? $this->data['comment_old'] : '0';
    	$data['content']	 = isset($_POST['content']) ? $_POST['content'] : '';	//评论内容
    	$data['content'] = mb_substr($_POST['content'], 0, $_POST['weibo_nums'], 'UTF8');
    	$data['row_id'] 	 = isset($_POST['weibo_id']) ? $_POST['weibo_id'] : '0';
    	$data['to_comment_id'] = isset($_POST['comment_id']) ? $_POST['comment_id'] : '0';
    	$data['to_uid']		= isset($_POST['to_uid']) ? $_POST['to_uid'] : '0';
    	$data['from']			 	= $this->_type_wap;
    	// dump($data);exit;
		$res = api('WeiboStatuses')->comment($data,true);
		if ($res) {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 1, '评论成功');
		}else {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 3, '评论失败, 请稍后重试');
		}
	}

	public function forward() {
		$weibo_id = intval($_GET['weibo_id']);
		if ( $weibo_id <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$data['id']	= $weibo_id;
		$weibo = api('WeiboStatuses')->data($data)->show();
		$weibo['weibo_id'] = $weibo['feed_id'];
		if (!$weibo) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		// 微吧帖子处理
		switch ($_GET['type']) {
			case 'weiba':
				$weiba_post = D('WeibaPost','weiba')->where('post_id='.$weibo['app_row_id'])->find();
				$weiba_post['uname'] = model('User')->where('uid='.$weiba_post['post_uid'])->getField('uname');
				$this->assign('weiba_post',1);
				$this->assign('weiba',$weiba_post);
				break;
			
			default:
				# code...
				break;
		}
		// dump($weiba_post);
		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		$this->assign('weibo_nums',$weibo_nums);
		// dump($weibo);
		$this->assign('weibo', $weibo);
		$this->display();
	}

	public function doForward() {

		$weibo_id = intval($_POST['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/forward',array('weibo_id'=>$weibo_id)), 3, '参数错误');
		}
		if (empty($_POST['content'])) {
			redirect(U('wap/Index/forward',array('weibo_id'=>$weibo_id)), 3, '内容不能为空');
		}

		$data['id']	= $weibo_id;
		$weibo = api('WeiboStatuses')->data($data)->show();
		unset($data);
		if ( empty($weibo) ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$p['comment']  = $_POST['comment'];
		// 整合被转发的内容
		if ( $weibo['is_repost'] == 1 ) {
			$_POST['content'] .= "//@{$weibo['uname']}:{$weibo['source_body']}";
		}

		// 仅取前140字
		$admin_Config = model('Xdata')->lget('admin_Config');
		$weibo_nums = $admin_Config['feed']['weibo_nums'];
		$_POST['content'] = mb_substr($_POST['content'], 0,$weibo_nums , 'UTF8');
		// 
		$data['content']		= $_POST['content'];
		$data['from']			= $this->_type_wap;
		$data['transpond_id']	= $weibo['transpond_id'] ? $weibo['transpond_id'] : $weibo_id;
		if (intval($_POST['isComment']) == 1) {
			$weibo = api('WeiboStatuses')->data(array('id'=>$weibo_id))->show();
			$data['reply_data']	= $weibo['weibo_id'];
			if ( !empty($weibo['transpond_data']) ) {
				$data['reply_data']	.= ',' . $weibo['transpond_data']['weibo_id'];
			}
		}

		// 组装接口数据
		$p['app_name'] = $weibo['app'];
		$p['body']     = $_POST['content'];
		$p['content']     = $_POST['content'];
		if($weibo['app'] == 'public'){
			$p['id'] =  $weibo['feed_id'];
		}else{
			$p['id'] =  $weibo['app_row_id'];
		}
		$p['type']	   =  $weibo['app_row_table'];
		$p['from']     = $this->data['from'] ? intval($this->data['from']) : '0';
		$p['forApi']   = true;
		$p['curid'] = $weibo_id;
		$p['curtable'] = $weibo['app_row_table'];
		$p['sid'] = $weibo_id;
		// $res = model('Share')->shareFeed($p);
		$res = api('WeiboStatuses')->data($p)->repost();
		if ($res) {
			// redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id,'type'=>$weibo['type'])), 1, '转发成功');
			redirect(U('wap/Index/index'), 1, '转发成功');
		}else {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 3, '转发失败, 请稍后重试');
		}
	}

	public function doSearch()
	{	
		$key = t($_REQUEST['key']);
		if ( empty($key) )
			redirect(U('wap/Index/search'), 3, '请输入关键字');
		// 搜人  搜微博
		if ( isset($_REQUEST['user']) ) {
			$method  = 'weibo_search_user';
			$display = 'searchuser';
		}else {
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
		if($method == 'weibo_search_user'){
			$res = api('WeiboStatuses')->data($data)->$method();
			// 数组组装符合T2 格式
			foreach ($res as $key => $value) {
				if($value['follow_state']['following']){
					$res[$key]['follow_state']['value'] = '已关注';
				}else{
					$res[$key]['follow_state']['value'] = '未关注';
				}

				if($value['follow_state']['following'] && $value['follow_state']['follower']){
					$followlist[$key]['follow_state']['value'] = '互相关注';
				}
			}
		}
		if($method == 'weibo_search_weibo'){
			$res = api('WeiboStatuses')->data($data)->$method();
			// dump($res);
		}
		if($method == 'wap_search_topic'){
			$res = api('WeiboStatuses')->data($data)->$method();
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
			$res = $this->__formatByFavorite($res);
			$res = $this->__formatByContent($res);
			$this->assign('weibolist', $res);
		}
		if($display == 'searchtopic'){
			$res = $this->__formatByFavorite($res);
			$res = $this->__formatByContent($res);
			$this->assign('weibolist', $res);
			// dump($res);
		}
		$this->assign('keyword', $_REQUEST['key']);
		$this->display($display);
	}

	public function doDelete() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		
		$target = U('wap/Index/'.$_GET['from'], array('key'=>urlencode($_GET['key']),'page'=>$_GET['page']));
		$data['id'] = $weibo_id;
		$detail = api('WeiboStatuses')->data($data)->show();
		$data['source_table_name'] = $detail['app_row_table'];

		$res = api('WeiboStatuses')->data($data)->destroy();
		if ($res) {
			redirect($target , 1,  '删除成功');
		}else {
			redirect($target, 3, '删除失败，请稍后重试');
		}
	}

	public function doFavorite() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('wap/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));
		$data['id'] = $weibo_id;
		// 收藏数据组合
		$detail = api('WeiboStatuses')->data($data)->show();
		$data['source_table_name'] = $detail['app_row_table'];
		$data['source_id'] = $detail['feed_id'];
		$data['source_app'] = $detail['app'];
		$res = api('WeiboStatuses')->data($data)->favorite_create();
		if ($res) {
			redirect($target, 1, '收藏成功');
		}else {
			redirect($target, 3,'收藏失败，请稍后重试');
		}
	}

	public function doUnFavorite() {

		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('wap/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		// $res = api('Favorites')->data($data)->destroy();
		$res = model('Collection')->delCollection($data['id'],'feed');
		if ($res) {
			redirect($target, 1, '取消成功');
		}else {
			redirect($target, 3, '取消失败，请稍后重试');
		}
	}

	public function urlalert() {
		if( !isset($_GET['url']) || !isset($_GET['from_url']) ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$this->assign('url', $_GET['url']);
		$this->assign('from_url', $_GET['from_url']);
		$this->display();
	}
	public function apixxx(){
		$this->display();
	}
}