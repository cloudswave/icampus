<?php
//微博Api接口

class WeiboStatusesApi extends Api{

	//获取最新更新的公共微博消息
	function public_timeline(){
		return model('Feed')->public_timeline($this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page) ;
	}

	//获取当前用户所关注用户的最新微博信息
	function friends_timeline(){
		return model('Feed')->friends_timeline($this->data['type'], $this->mid, $this->since_id, $this->max_id, $this->count, $this->page) ;
	} 

	//获取用户发布的微博信息列表
	function user_timeline() {
		return model('Feed')->user_timeline($this->data['type'], $this->user_id, $this->user_name, $this->since_id, $this->max_id, $this->count, $this->page) ;
	}

	//获取当前用户的@列表
	function mentions(){
		return model('Atme')->mentions($this->mid, $this->since_id, $this->max_id, $this->count, $this->page, $this->data['table']);
	}

	//提到我的feed
	function mentions_feed(){
		return model('Atme')->mentions_feed($this->mid, $this->since_id, $this->max_id, $this->count, $this->page);
	}
	
	//收藏的feed列表
	function favorite_feed(){
		return model('Collection')->getCollectionFeedForApi($this->mid, $this->since_id, $this->max_id, $this->count, $this->page);
	}

	//赞某条微博
	function add_digg(){
		$feed_id = intval ( $this->data['feed_id'] );
		$res = model('FeedDigg')->addDigg($feed_id , $this->mid);
		if ( $res ){
			return 1; 
		} else {
			return 0;
		}
	}

	//某条微薄详细内容
	function show(){
		$feed = model('Feed')->getFeedInfo($this->id, true);    //getFeedInfo获取指定微博的信息，用于资源模型输出???
		$diggarr = model('FeedDigg')->checkIsDigg($this->id, $this->mid);
		$feed['is_digg'] = $diggarr[$this->id] ? 1 : 0;
		return $feed;
	}
	
	//发布一条微薄
	public function update ($data) {
		if (!empty($data)) {
			$this->data['type'] = $data['type'];
			$this->data['attach_id'] = $data['attach_id'][0];
			unset($data);
		}

		$data['uid'] = $this->mid;
		$data['body'] = $this->data['content'];
		$data['from'] = $this->data['from'] ? intval($this->data['from']) : '0';
		$data['app'] = $this->data['app_name'] ? $this->data['app_name'] : 'public';
		$data['type'] = isset($this->data['type']) ?$this->data['type'] : 'post';
		$data['app_row_id'] = $this->data['app_id'] ? $this->data['app_id']:'0';
		$data['publish_time'] = time();
		
		$feed_id = model('Feed')->data($data)->add();
		
		//更新最近@的人
		model('Atme')->updateRecentAtForApi( $data['body'] , $feed_id);
		// 添加关联数据
		$attach_id = $this->data['attach_id'];
		$attach_id = trim(t($attach_id), "|");
		if (!empty($attach_id)) {
			$attach_id = explode('|', $attach_id);
			array_map('intval', $attach_id);
		}
		$data['attach_id'] = $attach_id;
		$feed_data = D('FeedData')->data(array('feed_id'=>$feed_id,'feed_data'=>serialize( $data ),'client_ip'=>get_client_ip(),'feed_content'=>$data['body']))->add();

		if( $feed_id && $feed_data ){
			//加积分
			model('Credit')->setUserCredit($this->mid,'add_weibo');
			//Feed数
			model('UserData')->setUid($this->mid)->updateKey('feed_count',1);
			//if($app =='public'){ //TODO 微博验证条件
				model('UserData')->setUid($this->mid)->updateKey('weibo_count',1);
			//}
		
			return $feed_id;
		} else {
			return 0;
		}

	}

	//上传功能
	public function upload(){
		$d['attach_type'] = 'feed_image';
		$d['upload_type'] = 'image';		
		$GLOBALS['fromMobile'] = true;
    	$info = model('Attach')->upload($d);
    	if($info['status']){
    		$data['type'] = 'postimage';//图片类型微博
    		$data['attach_id'] = array($info['info'][0]['attach_id']);
    		return $this->update($data);
    	}else{
    		return 0;//上传失败
    	}
	}

	//删除一条微博
	function destroy(){
		$return  = model('Feed')->doEditFeed($this->id,'delFeed','',$this->mid);
		// 删除话题相关信息
		$return['status'] == 1 && model('FeedTopic')->deleteWeiboJoinTopic($this->id);
		// 删除频道关联信息
		D('Channel', 'channel')->deleteChannelLink($this->id);
		// 删除@信息
		model('Atme')->setAppName('Public')->setAppTable('feed')->deleteAtme(null, $this->id, null);
		// 删除收藏信息
		model('Collection')->delCollection($this->id,'feed');
		return (int) $return['status'];
	}

	//转发一条微博
	function repost(){
		$p['app_name'] = $this->data['app_name'] ? $this->data['app_name'] : 'public';
		$p['comment']  = $this->data['comment'];
		$p['body']     = $this->data['content'];
		$p['sid']      = isset($this->data['sid'])?$this->data['sid']:$this->id;	
		$p['type']	   = isset($this->data['type']) ? $this->data['type'] : 'feed';
		$p['from']     = $this->data['from'] ? intval($this->data['from']) : '0';
		$p['forApi']   = true;
		$p['curid'] = $this->data['curid'];
		$p['curtable'] = $this->data['curtable'];
		$p['content'] = '';
		$return = model('Share')->shareFeed($p, 'share');
		if($return['status'] == 1){
			//添加积分
			model('Credit')->setUserCredit($this->mid,'forward_weibo');
			
		}
		return (int) $return['status'];
	}

	//获取指定微博的评论列表
	function comments(){
		$where = "row_id ='{$this->id}' AND `table`='feed'";
		return model('Comment')->getCommentListForApi($where,$this->since_id , $this->max_id , $this->count , $this->page);
	}

	//获取指定微博的转发列表
	function reposts(){
		return model('Feed')->repost_timeline($this->id, $this->since_id, $this->max_id, $this->count, $this->page) ;
	}

	//获取当前用户收到的评论
	function comments_to_me() {
		$where = " ( (app_uid = '{$this->mid}' or to_uid = '{$this->mid}') and uid != '{$this->mid}' )";
		// $where = " ( app_uid = '{$this->mid}' or to_uid = '{$this->mid}' )";
		return model('Comment')->getCommentListForApi($where,$this->since_id , $this->max_id , $this->count , $this->page,true);
	}

	//获取当前用户收到的评论,除去自己的评论
	function comments_to_me_true() {
		$where = " ( (app_uid = '{$this->mid}' or to_uid = '{$this->mid}') and uid != '{$this->mid}' )";
		return model('Comment')->getCommentListForApi($where,$this->since_id , $this->max_id , $this->count , $this->page,true);
	}

	//获取当前用户发出的评论
	function comments_by_me() {
		$where = " uid = '{$this->mid}' ";
		return model('Comment')->getCommentListForApi($where,$this->since_id , $this->max_id , $this->count , $this->page,true);
	}

	//删除评论接口 
	function comment_destroy(){
		$comment_id = intval($this->data['comment_id']);
		$uid = $this->data['uid'] ? intval($this->data['uid']) : $this->mid;
		if (empty($comment_id) || empty($uid)) {
			return 0;
		}
		$res = model('Comment')->deleteComment($comment_id, $uid);
		$result = $res ? 1 : 0;
		return $result;
	}

	// 发布评论
	public function comment() {
		$feedInfo = model('Feed')->getFeedInfo($this->data['row_id']);

		$ifShareFeed = isset($this->data['ifShareFeed']) ? $this->data['ifShareFeed'] : '0';

		if (isset($this->data['app_name'])) {
			$data['app'] = $this->data['app_name'];
		} else if (in_array($feedInfo['app'], array('weiba'))) {
			$data['app'] = 'weiba';
		} else {
			$data['app'] = 'public';
		}
		$data['table']  = isset($this->data['table_name']) ? $this->data['table_name'] : 'feed';
    	$data['app_row_id']  = isset($this->data['app_row_id']) ? $this->data['app_row_id'] : '0';
    	$data['app_row_table']  = isset($this->data['app_row_table']) ? $this->data['app_row_table'] : 'feed';
    	$data['app_uid']	 = isset($this->data['app_uid']) ? $this->data['app_uid'] : '0';
    	$data['comment_old'] = isset($this->data['comment_old']) ? $this->data['comment_old'] : '0';
    	$data['content']	 = isset($this->data['content']) ? $this->data['content'] : '';	//评论内容
    	$data['row_id'] 	 = isset($this->data['row_id']) ? $this->data['row_id'] : '0';
    	$data['to_comment_id'] = isset($this->data['to_comment_id']) ? $this->data['to_comment_id'] : '0';
    	$data['to_uid']		= isset($this->data['to_uid']) ? $this->data['to_uid'] : '0';
    	$data['at'] = $this->data['at'];
    	if($data['comment_id'] = model('Comment')->addComment($data,true)){
    		//转发到我的微博
    		if($ifShareFeed == 1){
    			//根据评论的对象获取原来的内容
    			$s['sid'] = $data['row_id'];
    			$s['app_name']	= $data['app'];
    			// w3g版本用到
    			if (APP_NAME == 'w3g' && ($data['comment_old'] != '')) {
    				$s['body'] = $data['content'].'//@'.$data['at'].'：'.$data['comment_old'];
    			} else {
    				// Android IOS
    				$commentInfo = model('Source')->getSourceInfo($data['table'], $data['row_id'], false, $data['app']);
					$oldInfo = isset($commentInfo['sourceInfo']) ? $commentInfo['sourceInfo'] : $commentInfo;
					// 根据评论的对象获取原来的内容
					$arr = array ('post', 'postimage', 'postfile', 'weiba_post', 'postvideo');
					$scream = '';
					if (!in_array($feedInfo['type'], $arr)) {
						$scream = '//@'.$commentInfo['source_user_info']['uname'].'：'.$commentInfo ['source_content'];
					}
					if (!empty($data ['to_comment_id'])) {
						$replyInfo = model('Comment')->init($data['app'], $data['table'])->getCommentInfo($data['to_comment_id'], false);
						$replyScream = '//@'.$replyInfo['user_info']['uname'].' ：';
						$data['content'] .= $replyScream.$replyInfo['content'];
					}
    				$s['body'] = $data['content'].$scream;
    			}
    			$s['type'] = 'feed';
    			$s['comment'] = $data['comment_old'];
    			$s['comment_touid'] = $data['app_uid'];
    			$s['from'] = $this->data['from'];
    			// 如果为原创微博，不给原创用户发送@信息
				if ($feedInfo['type'] == 'post' && empty($data['to_uid'])) {
					$lessUids[] = $this->mid;
				}
    			//接触转发到我的微博的时间限制
    			unlockSubmit();
    			model('Share')->shareFeed($s, 'comment', $lessUids);
    		}
    		if (in_array($feedInfo['app'], array('weiba'))) {
    			$wdata['row_id'] = $data['row_id'];
    			$wdata['to_comment_id'] = $data['to_comment_id'];
    			$wdata['to_uid'] = $data['to_uid'];
    			$wdata['content'] = $data['content'];
    			$wdata['comment_id'] = $data['comment_id'];
    			$this->_upateToweiba($wdata);
    		}
    		return 1;
    	} else {
    		return 0;
    	}
	}

	// 同步到微吧
	private function _upateToweiba ($data) {
		$postDetail = D('weiba_post')->where('feed_id='.$data ['row_id'])->find();
		if (!$postDetail) {
			return false;
		}
		$datas['weiba_id'] = $postDetail['weiba_id'];
		$datas['post_id'] = $postDetail['post_id'];
		$datas['post_uid'] = $postDetail['post_uid'];
		$datas['to_reply_id'] = $data['to_comment_id'] ? D('weiba_reply')->where('comment_id='.$data['to_comment_id'])->getField('reply_id') : 0;
		$datas['to_uid'] = $data['to_uid'];
		$datas['uid'] = $this->mid;
		$datas['ctime'] = time();
		$datas['content'] = $data['content'];
		$datas['comment_id'] = $data['comment_id'];
		if (D('weiba_reply')->add($datas)) {
			$map['last_reply_uid'] = $this->mid;
			$map['last_reply_time'] = $datas['ctime'];
			$map['reply_count'] = array('exp', "reply_count+1");
			D('weiba_post')->where('post_id='.$datas['post_id'])->save($map);
		}
	}

	//返回收藏列表
	public function favorite_weibo(){
		return model('Collection')->getCollectionForApi($this->mid, $this->since_id, $this->max_id, $this->count, $this->page);
	}

	//收藏一条资源
	public  function favorite_create(){
		$data['source_table_name'] = $this->data['source_table_name']; // feed
		$data['source_id'] 	= $this->data['source_id'];	 //140
		$data['source_app'] = $this->data['source_app']; //public
				
		if( model('Collection')->addCollection($data)){
			return 1;
		}else{
			return 0;
		}
	}

	//取消收藏
	public function  favorite_destroy(){
		 if( model('Collection')->delCollection($this->data['source_id'], $this->data['source_table_name']) ){
		 		return 1;	
		 }
		 return 0;
	}

	//按关键字搜索微博
	public function weibo_search_weibo(){
		$_REQUEST['p'] = $_REQUEST['page'] = $this->page;
		$this->count?$this->count:100;
		$this->data['key'] = t(trim($this->data['key']));
		$this->data['key'] = str_ireplace(array("%","'",'"'), '', $this->data['key']);
		if(empty($this->data['key']))
			return 0;

		$GLOBALS['ts']['uid'] = $GLOBALS['ts']['mid'] = $this->mid;
		
		return model('Feed')->searchFeed($this->data['key'], 'all', '', $this->count, true);
	}

	//按话题搜索微博
	public function weibo_search_topic(){
		$_REQUEST['p'] = $_REQUEST['page'] = $this->page;
		$this->count?$this->count:100;
		$this->data['key'] = t(trim($this->data['key']));
		$this->data['key'] = trim($this->data['key'],'#');
		$this->data['key'] = str_ireplace(array("%","'",'"'), '', $this->data['key']);
		if(empty($this->data['key']))
			return 0;

		$GLOBALS['ts']['uid'] = $GLOBALS['ts']['mid'] = $this->mid;

		return model('Feed')->searchFeed($this->data['key'], 'topic', '', $this->count, true);
	}

	//微博里搜索用户
	public function weibo_search_user() {
		$_REQUEST['p'] = $_REQUEST['page'] = $this->page;
		$this->count = empty($this->count) ? $this->count : 100;
		$this->data['key'] = t(trim($this->data['key']));
		$this->data['key'] = str_ireplace(array("%","'",'"'), '', $this->data['key']);
		if(empty($this->data['key'])) {
			return 0;
		}
		$data = model('User')->searchUser($this->data['key'], 0, $this->count, '', '', 0, 10);
		$return  = array();
		if (intval($data['totalPages']) < $this->page) {
			return $return;
		}
		foreach($data['data'] as $v) {
			$return[] = model('User')->formatForApi($v, $v['uid'], $this->mid);
		}
		return  $return;
	}

	//@最近联系人
	public function search_at(){
		$users = model('UserData')->where("`key`='user_recentat' and uid=".$this->mid)->getField('value');
		$data = unserialize($users);
		if ( !$data ){
			exit('[]');
		}
		foreach ($data as &$value) {
			$userInfo = model('User')->getUserInfo($value['uid']);
			$value['sex'] = $userInfo['sex'];
			$userData = model('UserData')->setUid($value['uid'])->getUserData();
			$value['count_info'] = $userData;
		}
		exit(json_encode($data));
	}

	//搜索话题
	public function search_topic(){
		$key = trim ( t ( $_REQUEST['key'] ) );
		$feedtopicDao = model('FeedTopic');
		$data = $feedtopicDao->where("topic_name like '%".$key."%' and recommend=1")->field('topic_id,topic_name')->limit(10)->findAll();
		if ( !$data ){
			exit('[]');
		}
		exit( json_encode($data) );
	}

	// wap搜索话题
	public function wap_search_topic(){
		$_REQUEST['p'] = $_REQUEST['page'] = $this->page;
		$GLOBALS['ts']['uid'] = $GLOBALS['ts']['mid'] = $this->mid;
		return model('Feed')->searchFeed(t($this->data['key']), 'topic', $this->max_id, $this->count, true);
	}
	
	// 搜索话题
	public function wap_search_user(){
		$key = trim ( t ( $_REQUEST['key'] ) );
		$feedtopicDao = model('FeedTopic');
		$data = $feedtopicDao->where("topic_name like '%".$key."%' and recommend=1")->field('topic_id,topic_name')->limit(10)->findAll();
		return $data;
	}

	//用户的微博配图
	public function weibo_photo(){
		// 获取微博配图信息
		$uid = $this->data['uid'] ? intval( $this->data['uid'] ) : $this->mid;
		$count = $this->count > 0 ? intval ( $this->count ) : 10;
		$page = $this->page > 0 ? intval ( $this->page ) : 1;

		$feedImages = $this->getUserAttachData($uid, $count, $page);
		foreach ( $feedImages  as &$v ){
			$v['imageinfo'] = getImageInfo( $v['savepath'] );
			$v['savepath'] = getImageUrl( $v['savepath'] );
		}
		return $feedImages;
	}

	private function getUserAttachData ($uid, $limit = 10, $page = 1) {
		$map['a.uid'] = $uid;
		$map['a.type'] = 'postimage';
		$map['is_del'] = 0;
		$limit_start = ($page-1)*$limit;
		$list = D()->table('`'.C('DB_PREFIX').'feed` AS a LEFT JOIN `'.C('DB_PREFIX').'feed_data` AS b ON a.`feed_id` = b.`feed_id`')
		   		   ->field('a.`feed_id`, a.`publish_time`, b.`feed_data`')
		   		   ->where($map)
		   		   ->order('feed_id DESC')
		   		   ->limit($limit_start.','.$limit)
		   		   ->findAll();

		// 获取附件信息
		foreach ($list as &$value) {
			$tmp = unserialize($value['feed_data']);
			$attachId = is_array($tmp['attach_id']) ? intval($tmp['attach_id'][0]) : intval($tmp['attach_id']);
			$attachInfo = model('Attach')->getAttachById($attachId);
			$value['savepath'] = $attachInfo['save_path'].$attachInfo['save_name'];
			$value['name'] = $attachInfo['name'];
			$value['body'] = parseForApi($tmp['body']);
		}

		return $list;
	}
}
?>