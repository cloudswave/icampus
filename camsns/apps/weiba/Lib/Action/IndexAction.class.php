<?php
/**
 * 微吧控制器
 * @author 
 * @version TS3.0
 */
class IndexAction extends Action {

	/**
	 * 微吧首页
	 * @return void
	 */
	public function index() {
		//微吧推荐
		$this->_weiba_recommend(9,100,100);
		//热帖推荐
		$this->_post_recommend(10);
		//微吧排行榜
		$this->_weibaOrder();
		//帖子列表
		$this->_postList();
		$this->setTitle( '微吧首页' );
		$this->setKeywords( '微吧首页' );
		$this->display();
	}

	/**
	 * 微吧列表
	 * @return void
	 */
	public function weibaList(){
		$cid = intval ( $_GET['cid'] );
		$cid && $map['cid'] = $cid;
		$map['is_del'] = 0;
		$weibaList = D('weiba')->where($map)->order('recommend desc,follower_count desc,thread_count desc')->findpage(10);
		$weiba_ids = getSubByKey($weibaList['data'],'weiba_id');
		$followStatus = D('weiba')->getFollowStateByWeibaids($this->mid,$weiba_ids);
		foreach($weibaList['data'] as $k=>$v){
			$weibaList['data'][$k]['logo'] = getImageUrlByAttachId($v['logo'],100,100);
			$weibaList['data'][$k]['following'] = $followStatus[$v['weiba_id']]['following'];
		}
		//$weiba_ids = getSubByKey($weibaList['data'], 'weiba_id');
		//$this->_assignFollowState($weiba_ids);
		$weibacate = D('WeibaCategory')->getAllWeibaCate();
		
		$this->assign('weibaList',$weibaList);
		//微吧推荐
		$this->_weiba_recommend(9);
		//微吧排行榜
		$this->_weibaOrder();
		$this->assign( 'cid' , $cid );
		$this->assign('nav','weibalist');
		$this->assign( 'weibacate' , $weibacate );
		$this->setTitle( '微吧列表' );
		$this->setKeywords( '全站微吧列表' );
		$this->display();
	}

	/**
	 * 帖子列表
	 */
	public function postList(){
		//微吧推荐
		$this->_weiba_recommend(9);
		//帖子列表
		$this->_postList();

		$this->setTitle( '全站帖子列表' );
		$this->setKeywords( '全站帖子列表' );
		$this->display();
	}

	/**
	 * 我的微吧
	 * @return  void
	 */
	public function myWeiba(){
		$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->field('weiba_id')->findAll(),'weiba_id'));
		$map['is_del'] = 0;
		switch (t($_GET['type'])) {
			case 'myPost':
				$map['post_uid'] = $this->mid;
				$postList = D('weiba_post')->where($map)->order('post_time desc')->findpage(20);
				break;
			case 'myReply':
				$myreply = D('weiba_reply')->where('uid='.$this->mid)->order('ctime desc')->field('post_id')->findAll();
				$map['post_id'] = array('in',array_unique(getSubByKey($myreply, 'post_id')));
				$postList = D('weiba_post')->where($map)->order('last_reply_time desc')->findpage(20);
				break;
			case 'myFavorite':
				$myFavorite = D('weiba_favorite')->where('uid='.$this->mid)->order('favorite_time desc')->findAll();
				//dump($myFavorite);exit;
				$map['post_id'] = array('in',getSubByKey($myFavorite, 'post_id'));
				$postList = D('weiba_post')->where($map)->findpage(20);
				break;
			default:
				$myFollow = D('weiba_follow')->where('follower_uid='.$this->mid)->findAll();
				$map['weiba_id'] = array('in',getSubByKey($myFollow, 'weiba_id'));
				$postList = D('weiba_post')->where($map)->order('last_reply_time desc')->findpage(20);
				break;
		}	
		// if($postList['nowPage']==1){  //列表第一页加上全局置顶的帖子
		// 	$topPostList = D('weiba_post')->where('top=2 and is_del=0')->order('post_time desc')->findAll();
		// 	!$topPostList && $topPostList = array();
		// 	!$postList['data'] && $postList['data'] = array();
		// 	$postList['data'] = array_merge($topPostList,$postList['data']);
		// }
		$weiba_ids = getSubByKey($postList['data'], 'weiba_id');
		$nameArr = $this->_getWeibaName($weiba_ids);
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k]['weiba'] = $nameArr[$v['weiba_id']];
		}
		$post_uids = getSubByKey($postList['data'], 'post_uid');
		$reply_uids = getSubByKey($postList['data'], 'last_reply_uid');
		$uids = array_unique(array_merge($post_uids,$reply_uids));
		$this->_assignUserInfo($uids);
		$this->assign('postList',$postList);
		$this->assign('type',t($_GET['type']));
		$this->assign('nav','myweiba');

		$this->setTitle( '我的微吧' );
		$this->setKeywords( '我的微吧' );
		$this->display();
	}

	/**
	 * 微吧详情页
	 * @return void
	 */
	public function detail(){
		$weiba_id = intval($_GET['weiba_id']);
		$weiba_detail = D('weiba')->where('is_del=0 and weiba_id='.$weiba_id)->find();
		if(!$weiba_detail){
			$this->error('该微吧不存在或已被解散');
		}
		$weiba_detail['logo'] = getImageUrlByAttachId($weiba_detail['logo'],100,100);
		//吧主
		$map['weiba_id'] = $weiba_id;
		$map['level'] = array('in','2,3');
		$weiba_admin = D('weiba_follow')->where($map)->order('level desc')->field('follower_uid,level')->findAll();
		if($weiba_admin){
			foreach($weiba_admin as $k=>$v){
				// 获取用户用户组信息
				$userGids = model('UserGroupLink')->getUserGroup($v['follower_uid']);
				$userGroupData = model('UserGroup')->getUserGroupByGids($userGids[$v['follower_uid']]);
				foreach($userGroupData as $key => $value) {
					if($value['user_group_icon'] == -1) {
						unset($userGroupData[$key]);
						continue;
					}
					$userGroupData[$key]['user_group_icon_url'] = THEME_PUBLIC_URL.'/image/usergroup/'.$value['user_group_icon'];
				}
				$weiba_admin[$k]['userGroupData'] = $userGroupData;
			}
			$weiba_admin_uids = getSubByKey($weiba_admin, 'follower_uid');		
			$this->_assignFollowUidState($weiba_admin_uids);
			$this->assign('weiba_admin',$weiba_admin);
			$this->assign('weiba_admin_uids',$weiba_admin_uids);
			$this->assign('weiba_super_admin',D('weiba_follow')->where('level=3 and weiba_id='.$weiba_id)->getField('follower_uid'));
			$this->assign('weiba_admin_count',D('weiba_follow')->where($map)->count());
			
		}
		$isadmin = 0;
		if( in_array( $this->mid , $weiba_admin_uids ) || CheckPermission('core_admin','admin_login')){
			$isadmin = 1;
		}
		$this->assign('isadmin',$isadmin);
		//帖子
		$maps['is_del'] = 0;
		
		if($_GET['type']=='digest'){
			$maps['digest'] = 1;
			$order = 'post_time desc';
			$this->assign('type','digest');
			$this->assign('post_count',D('weiba_post')->where('is_del=0 AND digest=1 AND weiba_id='.$weiba_id)->count());
		}else{
			$maps['top'] = 0;
			$this->assign('type','all');
			$this->assign('post_count',D('weiba_post')->where('is_del=0 AND weiba_id='.$weiba_id)->count());
		}
		if($_GET['order']=='post_time'){
			$order = 'post_time desc';
			$this->assign('order','post_time');
		}else{
			$order = 'last_reply_time desc';
			$this->assign('order','reply_time');
		}
		$maps['weiba_id'] = $weiba_id;
		$list = D('weiba_post')->where($maps)->order($order)->findpage(20); 

		if($_GET['type']!='digest' && $list['nowPage']==1){  //列表第一页加上全局置顶的帖子
			$topPostList = D('weiba_post')->where('top=2 and is_del=0')->order('post_time desc')->findAll();  //全局置顶
			$innerTop = D('weiba_post')->where('top=1 and is_del=0 and weiba_id='.$weiba_id)->order('post_time desc')->findAll();
			!$topPostList && $topPostList = array();
			!$innerTop && $innerTop = array();
			!$list['data'] && $list['data'] = array();
			$list['data'] = array_merge($topPostList,$innerTop,$list['data']);
		}
		$post_uids = getSubByKey($list['data'], 'post_uid');
		$reply_uids = getSubByKey($list['data'], 'last_reply_uid');
		!$weiba_admin_uids && $weiba_admin_uids = array();
		$uids = array_unique(array_filter(array_merge($post_uids,$reply_uids,$weiba_admin_uids)));
		$this->_assignUserInfo($uids);

		$this->_assignFollowState($weiba_id);
		$this->assign('list',$list);
		$this->assign('weiba_detail',$weiba_detail);

		if($_GET['type']=='digest'){
			$jinghua = '精华帖';
		}
		$this->assign('nav' , 'weibadetail');
		$this->assign('weiba_name' , $weiba_detail['weiba_name']);
		$this->assign('weiba_id', $weiba_id );
		$this->setTitle( $weiba_detail['weiba_name'].$jinghua );
		$this->setKeywords( $weiba_detail['weiba_name'].$jinghua );
		$this->setDescription( $weiba_detail['weiba_name'].','.$weiba_detail['intro'] );
		$this->display();
	}

	/**
	 * 关注微吧
	 */
	public function doFollowWeiba(){
		$res = D('weiba')->doFollowWeiba($this->mid, intval($_REQUEST['weiba_id']));
    	$this->ajaxReturn($res, D('weiba')->getError(), false !== $res);
	}

	/**
	 * 取消关注微吧
	 */
	public function unFollowWeiba(){
		$res = D('weiba')->unFollowWeiba($this->mid, intval($_GET['weiba_id']));
    	$this->ajaxReturn($res, D('weiba')->getError(), false !== $res);
	}

	/**
	 * 检查发帖权限
	 * @return boolean 是否有发帖权限 0：否  1：是
	 */
	public function checkPost(){
		$weiba_id = intval($_POST['weiba_id']);
		$map['weiba_id'] = $weiba_id;
		$map['follower_uid'] = $this->mid;
		if(D('weiba_follow')->where($map)->find()){
			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 弹窗加入微吧
	 */
	public function joinWeiba(){
		$weiba_id = intval($_GET['weiba_id']);
		$this->assign('weiba_id',$weiba_id);
		$this->display();
	}
	public function quickPost(){
		$map['status'] = 1;
		$map['is_del'] = 0;
		$list = D('Weiba')->where($map)->field('weiba_id,weiba_name')->findAll();
		$this->assign('list' , $list);
		$this->display();
	}
	/**
	 * 检查微吧 权限
	 */
	public function checkWeibaStatus(){
		$weibaid = intval ( $_POST['weibaid'] );
		$poststatus = D('weiba')->where('weiba_id='.$weibaid)->getField('who_can_post');
		switch ( $poststatus ){
			case 1:
				$follow_state = D('weiba')->getFollowStateByWeibaids($this->mid,$weibaid);
				if ( !$follow_state[$weibaid]['following'] && !CheckPermission('core_admin','admin_login')){
					echo 1;
				}
				break;
			case 2:
				//吧主
				$map['weiba_id'] = $weibaid;
				$map['level'] = array('in','2,3');
				$weiba_admin = D('weiba_follow')->where($map)->order('level desc')->field('follower_uid,level')->findAll();
				
				if ( !in_array(  $this->mid , getSubByKey( $weiba_admin , 'follower_uid' )) && !CheckPermission('core_admin','admin_login') ){
					echo 2;
				}
				break;
			case 3:
				//吧主
				$map['weiba_id'] = $weibaid;
				$map['level'] = 3;
				$weiba_admin = D('weiba_follow')->where($map)->order('level desc')->field('follower_uid,level')->find();
				if ( $this->mid != $weiba_admin['follower_uid']  && !CheckPermission('core_admin','admin_login') ){
					echo 3;
				}
				break;
		}
	}
	/**
	 * 发布帖子
	 * @return void
	 */
	public function post(){
		if( !CheckPermission('weiba_normal','weiba_post') ){
			$this->error('对不起，您没有权限进行该操作！');
		}
		$weiba_id = intval($_GET['weiba_id']);
		$weiba = D('weiba')->where('weiba_id='.$weiba_id)->find();
		$this->assign('weiba_id',$weiba_id);
		$this->assign('weiba_name', $weiba['weiba_name']);

		$this->setTitle( '发表帖子 '.$weiba['weiba_name'] );
		$this->setKeywords( '发表帖子 '.$weiba['weiba_name'] );
		$this->setDescription( $weiba['weiba_name'].','.$weiba['intro'] );
		$this->display();
	}

	/**
	 * 执行发布帖子
	 * @return void
	 */
	public function doPost(){
		if( !CheckPermission('weiba_normal','weiba_post') ){
			$this->error('对不起，您没有权限进行该操作！',true);
		}
		$weibaid = intval($_POST['weiba_id']);
		if ( !$weibaid ){
			$this->error('请选择微吧！',true);
		}
		$weiba = D('weiba')->where('weiba_id='.$weibaid)->find();
		if ( !CheckPermission('core_admin','admin_login') ){
			switch ( $weiba['who_can_post'] ){
				case 1:
					$map['weiba_id'] = $weibaid;
					$map['follower_uid'] = $this->mid;
					$res = D('weiba_follow')->where($map)->find();
					if ( !$res && !CheckPermission('core_admin','admin_login')){
						$this->error('对不起，您没有发帖权限，请关注该微吧！',true);
					}
					break;
				case 2:
					$map['weiba_id'] = $weibaid;
					$map['level'] = array('in','2,3');
					$weiba_admin = D('weiba_follow')->where($map)->order('level desc')->field('follower_uid')->findAll();
					if ( !in_array( $this->mid , getSubByKey( $weiba_admin , 'follower_uid') ) && !CheckPermission('core_admin','admin_login')){
						$this->error( '对不起，您没有发帖权限，仅限管理员发帖！',true );
					}
					break;
				case 3:
					$map['weiba_id'] = $weibaid;
					$map['level'] = 3;
					$weiba_admin = D('weiba_follow')->where($map)->order('level desc')->field('follower_uid')->find();
					if ( $this->mid != $weiba_admin['follower_uid']  && !CheckPermission('core_admin','admin_login') ){
						$this->error( '对不起，您没有发帖权限，仅限吧主发帖！',true );
					}
					break;
			}
		}
		
		$checkContent = str_replace('&nbsp;', '', $_POST['content']);
		$checkContent = str_replace('<br />', '', $checkContent);
		$checkContent = str_replace('<p>', '', $checkContent);
		$checkContent = str_replace('</p>', '', $checkContent);
		$checkContents = preg_replace('/<img(.*?)src=/i','img',$checkContent);
		$checkContents = preg_replace('/<embed(.*?)src=/i','img',$checkContents);
		if(strlen(t($_POST['title']))==0) $this->error('帖子标题不能为空',true);
		if(strlen(t($checkContents))==0) $this->error('帖子内容不能为空',true);
		preg_match_all('/./us', t($_POST['title']), $match);  
        if(count($match[0])>30){     //汉字和字母都为一个字
        	$this->error('帖子标题不能超过30个字',true);
        }
		if ( $_POST['attach_ids'] ){
			$attach = explode('|', $_POST['attach_ids']);
			foreach ( $attach as $k=>$a){
				if ( !$a ){
					unset($attach[$k]);
				}
			}
			$attach = array_map( 'intval' , $attach);
			$data['attach'] =  serialize($attach);
		}
		$data['weiba_id'] = $weibaid;
		$data['title'] = t($_POST['title']);
		$data['content'] = h($_POST['content']);
		$data['post_uid'] = $this->mid;
		$data['post_time'] = time();
		$data['last_reply_uid'] = $this->mid;
		$data['last_reply_time'] = $data['post_time'];
		$res = D('weiba_post')->add($data);
		if($res){
			D('weiba')->where('weiba_id='.$data['weiba_id'])->setInc('thread_count');
			//同步到微博
			$feed_id = D('weibaPost')->syncToFeed($res,$data['title'],t($checkContent),$this->mid);
			D('weiba_post')->where('post_id='.$res)->setField('feed_id',$feed_id);
			//$this->assign('jumpUrl', U('weiba/Index/postDetail',array('post_id'=>$res)));
			//$this->success('发布成功');

			//添加积分
			model('Credit')->setUserCredit($this->mid,'publish_topic');

			return $this->ajaxReturn($res, '发布成功', 1);
		}else{
			$this->error('发布失败',true);
		}
	}

	/**
	 * 帖子详情页
	 * @return void
	 */
	public function postDetail(){
		$post_id = intval($_GET['post_id']);
		$post_detail = D('weiba_post')->where('is_del=0 and post_id='.$post_id)->find();
		if(!$post_detail || D('weiba')->where('weiba_id='.$post_detail['weiba_id'])->getField('is_del')) $this->error('帖子不存在或已被删除');
		if(D('weiba_favorite')->where('uid='.$this->mid.' AND post_id='.$post_id)->find()){
			$post_detail['favorite'] = 1;
		}
		if ( $post_detail['attach'] ){
			$attachids = unserialize( $post_detail['attach'] );
			$attachinfo = model('Attach')->getAttachByIds( $attachids );
			foreach($attachinfo as $ak => $av) {
				$_attach = array(
						'attach_id'   => $av['attach_id'],
						'attach_name' => $av['name'],
						'attach_url'  => getImageUrl($av['save_path'].$av['save_name']),
						'extension'   => $av['extension'],
						'size'		  => $av['size']
				);
				$post_detail['attachInfo'][$ak] = $_attach;
			}
		}
		$post_detail['content'] = html_entity_decode($post_detail['content'], ENT_QUOTES, 'UTF-8');
		$this->assign('post_detail',$post_detail);
		//dump($post_detail);
		D('weiba_post')->where('post_id='.$post_id)->setInc('read_count');
		$weiba_name = D('weiba')->where('weiba_id='.$post_detail['weiba_id'])->getField('weiba_name');
		$this->assign('weiba_id' , $post_detail['weiba_id']);
		$this->assign('weiba_name', $weiba_name);
		//获得吧主uid
		$map['weiba_id'] = $post_detail['weiba_id'];
		$map['level'] = array('in','2,3');
		$weiba_admin = getSubByKey(D('weiba_follow')->where($map)->order('level desc')->field('follower_uid')->findAll(),'follower_uid');
		$weiba_manage = false;
		if ( CheckWeibaPermission( $weiba_admin , 0 , 'weiba_global_top')
				 || CheckWeibaPermission( $weiba_admin , 0 , 'weiba_top') 
				 || CheckWeibaPermission( $weiba_admin , 0 , 'weiba_recommend' )
				 || CheckWeibaPermission( $weiba_admin , 0 , 'weiba_edit' )
				 || CheckWeibaPermission( $weiba_admin , 0 , 'weiba_del' )){
			$weiba_manage = true;
		}
		$this->assign( 'weiba_manage' , $weiba_manage );
		$this->assign('weiba_admin', $weiba_admin );
		//该作者的其他帖子
		$this->_assignUserInfo($post_detail['post_uid']);
		$map1['post_id'] = array('neq',$post_id);
		$map1['post_uid'] = $post_detail['post_uid'];
		$map1['is_del'] = 0;
		$otherPost = D('weiba_post')->where($map1)->order('reply_count desc')->limit(5)->findAll();
		$weiba_ids = getSubByKey($otherPost, 'weiba_id');
		$nameArr = $this->_getWeibaName($weiba_ids);
		foreach($otherPost as $k=>$v){
			$otherPost[$k]['weiba'] = $nameArr[$v['weiba_id']];
		}
		$this->assign('otherPost',$otherPost);
		//最新10条
		$newPost = D('weiba_post')->where('is_del=0')->order('post_time desc')->limit(10)->findAll();
		$weiba_ids = getSubByKey($newPost, 'weiba_id');
		$nameArr = $this->_getWeibaName($weiba_ids);
		foreach($newPost as $k=>$v){
			$newPost[$k]['weiba'] = $nameArr[$v['weiba_id']];
		}
		$this->assign('newPost',$newPost);
		$this->_weibaOrder();
		$this->assign( 'nav' , 'weibadetail' );
		$this->setTitle( $post_detail['title'].' '.$weiba_name );
		$this->setKeywords( $post_detail['title'].' '.$weiba_name );
		$this->setDescription( $post_detail['title'].','.t(getShort($post_detail['content'],100)) );
		$this->display();
	}

	/**
	 * 收藏帖子
	 * @return void
	 */
	public function favorite(){
		$data['post_id'] = intval($_POST['post_id']);
		$data['weiba_id'] = intval($_POST['weiba_id']);
		$data['post_uid'] = intval($_POST['post_uid']);
		$data['uid'] = $this->mid;
		$data['favorite_time'] = time();
		if(D('weiba_favorite')->add($data)){

			//添加积分
			model('Credit')->setUserCredit($this->mid,'collect_topic');
			model('Credit')->setUserCredit($data['post_uid'],'collected_topic');

			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 取消收藏帖子
	 * @return void
	 */
	public function unfavorite(){
		$map['post_id'] = intval($_POST['post_id']);
		$map['uid'] = $this->mid;
		if(D('weiba_favorite')->where($map)->delete()){
			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 编辑帖子
	 * @return void
	 */
	public function postEdit(){
		
		$post_id = intval($_GET['post_id']);
		
		$post_detail = D('weiba_post')->where('post_id='.$post_id)->find();
		//获得吧主uid
		$map['weiba_id'] = $post_detail['weiba_id'];
		$map['level'] = array('in','2,3');
		$weiba_admin = getSubByKey(D('weiba_follow')->where($map)->order('level desc')->field('follower_uid')->findAll(),'follower_uid');
		
		//管理权限判断
		if ( !CheckWeibaPermission( $weiba_admin , 0 ,'weiba_edit') ){
			//用户组权限判断
			if ( !CheckPermission('weiba_normal','weiba_edit') ){
				$this->error('对不起，您没有权限进行该操作！');
			}
		}
		
		if( $this->mid==$post_detail['post_uid'] || CheckWeibaPermission( $weiba_admin , 0 ,'weiba_edit')){
			$post_detail['attach'] = unserialize( $post_detail['attach'] );
			$this->assign('post_detail',$post_detail);
			if($_GET['log']) $this->assign('log',intval($_GET['log']));
			$this->assign('weiba_name',D('weiba')->where('weiba_id='.$post_detail['weiba_id'])->getField('weiba_name'));

			$this->setTitle( '编辑帖子 '.$weiba['weiba_name'] );
			$this->setKeywords( '编辑帖子 '.$weiba['weiba_name'] );
			$this->setDescription( $post_detail['title'].','.t(getShort($post_detail['content'],100)) );
			$this->display();
		}else{
			$this->error('您没有权限！');
		}
		
	}

	/**
	 * 执行编辑帖子
	 * @return void
	 */
	public function doPostEdit(){
		$weiba = D('weiba_post')->where('post_id='.intval($_POST['post_id']))->field('weiba_id,attach')->find();
		if ( !CheckWeibaPermission( '' , $weiba['weiba_id'] ,'weiba_edit') ){
			if ( !CheckPermission('weiba_normal','weiba_edit') ){
				$this->error('对不起，您没有权限进行该操作！',true);
			}
		}
		$checkContent = str_replace('&nbsp;', '', $_POST['content']);
		$checkContent = str_replace('<br />', '', $checkContent);
		$checkContent = str_replace('<p>', '', $checkContent);
		$checkContent = str_replace('</p>', '', $checkContent);
		$checkContents = preg_replace('/<img(.*?)src=/i','img',$checkContent);
		$checkContents = preg_replace('/<embed(.*?)src=/i','img',$checkContents);
		if(strlen(t($_POST['title']))==0) $this->error('帖子标题不能为空',true);
		if(strlen(t($checkContents))==0) $this->error('帖子内容不能为空',true);
		preg_match_all('/./us', t($_POST['title']), $match);  
        if(count($match[0])>30){     //汉字和字母都为一个字
        	$this->error('帖子标题不能超过30个字',true);
        }
		$post_id = intval($_POST['post_id']);
		$data['title'] = t($_POST['title']);
		$data['content'] = h($_POST['content']);
		$data['attach'] = '';
		if ( $_POST['attach_ids'] ){
			$attach = explode('|', $_POST['attach_ids']);
			foreach ( $attach as $k=>$a){
				if ( !$a ){
					unset($attach[$k]);
				}
			}
			$attach = array_map( 'intval' , $attach);
			$data['attach'] =  serialize($attach);
		}
		$res = D('weiba_post')->where('post_id='.$post_id)->save($data);
		if($res!==false){
			$post_detail = D('weiba_post')->where('post_id='.$post_id)->find();
			if(intval($_POST['log'])==1){
				D('log')->writeLog($post_detail['weiba_id'],$this->mid,'编辑了帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”','posts');
			}
			//同步到微博
			$feedInfo = D('feed_data')->where('feed_id='.$post_detail['feed_id'])->find();
			$datas = unserialize($feedInfo['feed_data']);
			$datas['content'] = '【'.$data['title'].'】'.getShort(t($checkContent),100).'&nbsp;';
			$datas['body'] = $datas['content'];
			$data1['feed_data'] = serialize($datas);
			$data1['feed_content'] = $datas['content'];
			$feed_id = D('feed_data')->where('feed_id='.$post_detail['feed_id'])->save($data1);
			model('Cache')->rm('fd_'.$post_detail['feed_id']);
			return $this->ajaxReturn($post_id, '编辑成功', 1);
		}else{
			$this->error('编辑失败',true);
		}
	}

	/**
	 * 编辑帖子回复
	 * @return void
	 */
	/*
	public function replyEdit(){
		$reply_id = intval($_GET['reply_id']);
		$reply_detail = D('weiba_reply')->where('reply_id='.$reply_id)->find();
		$reply_detail['content'] = parse_html($reply_detail['content']);
		$this->assign('reply_detail',$reply_detail);
		$this->assign('weiba_name',D('weiba')->where('weiba_id='.$reply_detail['weiba_id'])->getField('weiba_name'));
		$this->assign('post_title',D('weiba_post')->where('post_id='.$reply_detail['post_id'])->getField('title'));
		$this->display();
	}
	*/
	/**
	 * 执行编辑帖子回复
	 * @return void
	 */
	/*
	public function doReplyEdit(){
		//dump($_POST);exit;
		if(strlen(t($_POST['content']))==0) $this->error('回复内容不能为空');
		$reply_id = intval($_POST['reply_id']);
		$data['content'] = t($_POST['content']);
		$res = D('weiba_reply')->where('reply_id='.$reply_id)->save($data);
		if($res!==false){
			return $this->ajaxReturn(intval($_POST['post_id']), '编辑成功', 1);
		}else{
			$this->error('编辑失败');
		}
	}
	*/
	/**
	 * 删除帖子
	 * @return void
	 */
	public function postDel(){
		$weibaid = D('weiba_post')->where('post_id='.intval($_POST['post_id']))->getField('weiba_id');
		if ( !CheckWeibaPermission( '' , $weibaid , 'weiba_del') ){
			if ( !CheckPermission('weiba_normal','weiba_del') ){
				echo 0;return;
			}
		}
		$post_id = $_POST['post_id'];
		if(D('weiba_post')->where('post_id='.$post_id)->setField('is_del',1)){
			$post_detail = D('weiba_post')->where('post_id='.$post_id)->find();
			if(intval($_POST['log'])==1){
				D('log')->writeLog($post_detail['weiba_id'],$this->mid,'删除了帖子“'.$post_detail['title'].'”','posts');
			}
			D('weiba')->where('weiba_id='.intval($_POST['weiba_id']))->setDec('thread_count');

			//添加积分
			model('Credit')->setUserCredit($this->mid,'delete_topic');

			// 删除相应的微博信息
			model('Feed')->doEditFeed($post_detail['feed_id'], 'delFeed', '', $this->mid);
			
			echo 1;
		}
	}

	/**
	 * 设置帖子类型(置顶或精华)
	 * @return void
	 */
	public function postSet(){
		$post_id = intval($_POST['post_id']);
		$type = intval($_POST['type']);
		if($type==1){
			$field = 'top';
		}
		if($type==2){
			$field = 'digest';
		}
		if($type==3){
			$field = 'recommend';
		}
		$currentValue = intval($_POST['currentValue']);
		$targetValue = intval($_POST['targetValue']);
		if ( $targetValue == '1' && $type == 1 ){
			$action = 'weiba_top';
		} else if( $targetValue == '2' && $type == 1){
			$action = 'weiba_global_top';
		} else if ( $type == 2 ){
			$action = 'weiba_marrow';
		} else if ( $type == 3 ){
			$action = 'weiba_recommend';
		}
		$weiba_id = D('weiba_post')->where('post_id='.$post_id)->getField('weiba_id');
		if ( $targetValue == '0' && $type == 1 ){
			if ( !CheckWeibaPermission( '' , $weiba_id , 'weiba_top') && !CheckWeibaPermission( '' , $weiba_id , 'weiba_global_top') ){
				$this->error( '对不起，您没有权限进行该操作！' );
			}
		} else {
			if ( !CheckWeibaPermission( '' , $weiba_id , $action) ){
				$this->error( '对不起，您没有权限进行该操作！' );
			}
		}
		
		if(D('weiba_post')->where('post_id='.$post_id)->setField($field,$targetValue)){
			$post_detail = D('weiba_post')->where('post_id='.$post_id)->find();
			$config['post_name'] = $post_detail['title'];
			$config['post_url'] = '<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>';
			if($type==1){
				switch ($targetValue) {
					case '0':      //取消置顶
						if($currentValue==1){
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”取消了吧内置顶','posts');
						}else{
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”取消了全局置顶','posts');
						}

						//添加积分
						model('Credit')->setUserCredit($post_detail['post_uid'],'untop_topic_all');

						break;
					case '1':     //设为吧内置顶
							$config['typename'] = "吧内置顶";
							model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了吧内置顶','posts');
						
						//添加积分
						model('Credit')->setUserCredit($post_detail['post_uid'],'top_topic_weiba');

						break;
					case '2':     //设为全局置顶
							$config['typename'] = "全局置顶";
							model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了全局置顶','posts');
						
						//添加积分
						model('Credit')->setUserCredit($post_detail['post_uid'],'top_topic_all');

						break;
				}
			}
			if($type==2){
				switch ($targetValue) {
					case '0':     //取消精华
						D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”取消了精华','posts');
						break;
					case '1':     //设为精华
							$config['typename'] = "精华";
							model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了精华','posts');
						
						//添加积分
						model('Credit')->setUserCredit($post_detail['post_uid'],'dist_topic');
						break;
				}
			}
			if($type==3){
				switch ($targetValue) {
					case '0':     //取消推荐
						D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”取消了推荐','posts');
						break;
					case '1':     
						//设为推荐
						$config['typename'] = "推荐";
						model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
						D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了推荐','posts');
						
						//添加积分
						model('Credit')->setUserCredit($post_detail['post_uid'],'recommend_topic');

						break;
				}
			}
			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 搜索微吧或帖子
	 * @return  void
	 */
	public function search(){
		$k = t($_REQUEST['k']);
		$this->setTitle( '搜索'.$k );
		$this->setKeywords( '搜索'.$k );
		$this->setDescription( '搜索'.$k );

		//微吧推荐
		$this->_weiba_recommend(9,50,50);
		//微吧排行榜
		$this->_weibaOrder();
		$this->assign('nav','search');
		if($k == ""){
			if($_REQUEST['type'] == '1'){
				$this->display('search_weiba');
			}else{
				$this->display('search_post');
			}
			exit;
		}
		$_POST['k'] && $_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'].'&k='.$k;
		$this->assign('searchkey',$k);
		$map['is_del'] = 0;
		if($_REQUEST['type'] == '1'){
			//搜微吧
			$map['weiba_name'] = array('like','%'.$k.'%');
			//$where['intro'] = array('like','%'.$k.'%');
			//$where['_logic'] = 'or';
			//$map['_complex'] = $where;
			$weibaList = D('weiba')->where($map)->findPage(10);
			if($weibaList['data']){
				foreach($weibaList['data'] as $k=>$v){
					$weibaList['data'][$k]['logo'] = getImageUrlByAttachId($v['logo'],100,100);
				}
				$weiba_ids = getSubByKey($weibaList['data'], 'weiba_id');
				$this->_assignFollowState($weiba_ids);
				$this->assign('weibaList',$weibaList);
			}else{
				//微吧推荐
				$this->_weiba_recommend(9,50,50);
			}
			$this->display('search_weiba');
		}else{
			//搜帖子
			$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->field('weiba_id')->findAll(),'weiba_id'));
			$map['title'] = array('like','%'.$k.'%');
			//$where['content'] = array('like','%'.$k.'%');
			//$where['_logic'] = 'or';
			//$map['_complex'] = $where;
			$postList = D('weiba_post')->where($map)->order('post_time desc')->findPage(20);
			if($postList['data']){
				$weiba_ids = getSubByKey($postList['data'], 'weiba_id');
				$nameArr = $this->_getWeibaName($weiba_ids);
				foreach($postList['data'] as $k=>$v){
					$postList['data'][$k]['weiba'] = $nameArr[$v['weiba_id']];
				}
				$post_uids = getSubByKey($postList['data'], 'post_uid');
				$reply_uids = getSubByKey($postList['data'], 'last_reply_uid');
				$uids = array_unique(array_merge($post_uids,$reply_uids));
				$this->_assignUserInfo($uids);
				$this->assign('postList',$postList);
			}else{
				//微吧推荐
				$this->_weiba_recommend(9,50,50);
			}
			$this->display('search_post');
		}	
	}

	/**
	 * 检查是否有申请资格
	 * @return void
	 */
	public function checkApply(){
		$weiba_id = intval($_POST['weiba_id']);
		if(intval($_POST['type']) == 3){
			if(D('weiba_follow')->where('weiba_id='.$weiba_id.' AND level=3')->find()){
				echo 2;
				exit;
			} 
		}
		if(D('weiba_apply')->where('weiba_id='.$weiba_id.' AND follower_uid='.$this->mid)->find()){
			echo -1;exit;
		}
		if(D('weiba_follow')->where('weiba_id='.$weiba_id.' AND follower_uid='.$this->mid.' AND (level=3 OR level=2)')->find()){
			echo -2;exit;
		}
		if(D('weiba_post')->where('weiba_id='.$weiba_id.' AND post_uid='.$this->mid)->count()>=5){
			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 申请成为吧主或小吧
	 * @return void
	 */
	public function apply(){
		if ( !CheckPermission('weiba_normal','weiba_apply_manage') ){
			$this->error( '对不起，您没有权限执行该操作！' );
		}
		$weiba_id = intval($_GET['weiba_id']);
		if(D('weiba_follow')->where('weiba_id='.$weiba_id.' AND follower_uid='.$this->mid.' AND (level=3 OR level=2)')->find()){
			$this->error('您已经是吧主，不能重复申请');
		}
		if(D('weiba_post')->where('weiba_id='.$weiba_id.' AND post_uid='.$this->mid)->count()<5){
			$this->error('您需要发布5篇以上帖子才能申请');
		}
		if(!D('weiba_follow')->where('weiba_id='.$weiba_id.' AND follower_uid='.$this->mid)->find()) $this->error('您尚未关注该微吧');
		$type = intval($_GET['type']);
		if($type!=2 && $type!=3) $this->error('参数错误');
		$this->assign('weiba_name',D('weiba')->where('weiba_id='.$weiba_id)->getField('weiba_name'));
		$this->assign('type',$type);
		$this->assign('weiba_id',$weiba_id);
		$this->display();
	}

	/**
	 * 执行申请成为吧主或小吧
	 * @return void
	 */
	public function doApply(){
		if ( !CheckPermission('weiba_normal','weiba_apply_manage') ){
			$this->error( '对不起，您没有权限执行该操作！' );
		}
		if(strlen(t($_POST['reason']))==0) $this->error('申请理由不能为空');
		preg_match_all('/./us', t($_POST['reason']), $match);  
        if(count($match[0])>140){     //汉字和字母都为一个字
        	$this->error('申请理由不能超过140个字');
        } 
        if(D('weiba_follow')->where('weiba_id='.intval($_POST['weiba_id']).' AND follower_uid='.$this->mid.' AND (level=3 OR level=2)')->find()){
			$this->error('您已经是吧主，不能重复申请');
		}
		$data['follower_uid'] = $this->mid;
		$data['weiba_id'] = intval($_POST['weiba_id']);
		$data['type'] = intval($_POST['type']);
		$data['status'] = 0;
		$data['reason'] = t($_POST['reason']);
		$res = D('weiba_apply')->add($data);
		if($res){
			$weiba = D('weiba')->where('weiba_id='.$data['weiba_id'])->find();
			$actor = model('User')->getUserInfo($this->mid);
            $config['name'] = $actor['space_link'];
			$config['weiba_name'] = $weiba['weiba_name'];
			$config['source_url'] = U('weiba/Manage/member',array('weiba_id'=>$data['weiba_id'],'type'=>'apply'));
			if($data['type']==3){
				model('Notify')->sendNotify($weiba['uid'], 'weiba_apply', $config); 
			}else{
				model('Notify')->sendNotify($weiba['admin_uid'], 'weiba_apply', $config); 
			}
			 
			return $this->ajaxReturn($data['weiba_id'], '申请成功，请等待管理员审核', 1);
		}else{
			$this->error('申请失败');
		}
	}

	/**
	 * 微吧推荐
	 * @param integer limit 获取微吧条数
	 * @return void
	 */
	private function _weiba_recommend($limit=9,$width=100,$height=100){
		$weiba_recommend = D('weiba')->where('recommend=1 and is_del=0')->limit($limit)->findAll();
		foreach($weiba_recommend as $k=>$v){
			$weiba_recommend[$k]['logo'] = getImageUrlByAttachId($v['logo'],$width,$height);
		}
		$weiba_ids = getSubByKey($weiba_recommend, 'weiba_id');
		$this->_assignFollowState($weiba_ids);
		$this->assign('weiba_recommend',$weiba_recommend);
	}

	/**
	 * 热帖推荐
	 * @param integer limit 获取微吧条数
	 * @return void
	 */
	private function _post_recommend($limit){
		$db_prefix = C('DB_PREFIX');
		$sql = "SELECT a.* FROM `{$db_prefix}weiba_post` a, `{$db_prefix}weiba` b WHERE a.weiba_id=b.weiba_id AND ( b.`is_del` = 0 ) AND ( a.`recommend` = 1 ) AND ( a.`is_del` = 0 ) ORDER BY a.recommend_time desc LIMIT ".$limit;
		$post_recommend = D('weiba_post')->query($sql);
		$weiba_ids = getSubByKey($post_recommend, 'weiba_id');
		$nameArr = $this->_getWeibaName($weiba_ids);
		foreach($post_recommend as $k=>$v){
			$post_recommend[$k]['weiba'] = $nameArr[$v['weiba_id']];
			$post_recommend[$k]['user'] = model( 'User' )->getUserInfo( $v['post_uid'] );
			$post_recommend[$k]['replyuser'] = model( 'User' )->getUserInfo( $v['last_reply_uid'] );
			$images = matchImages($v['content']);
			$images[0] && $post_recommend[$k]['image'] = array_slice( $images , 0 , 5 );
		}
		$this->assign('post_recommend',$post_recommend);
	}

	/**
	 * 微吧排行榜
	 * @return void
	 */
	private function _weibaOrder(){
		$weiba_order = D('weiba')->where('is_del=0')->order('follower_count desc,thread_count desc')->limit(10)->findAll();
		foreach($weiba_order as $k=>$v){
			$weiba_order[$k]['logo'] = getImageUrlByAttachId($v['logo'],30,30);
		}
		$map['post_uid'] = $this->mid;
		$postCount = D('weiba_post')->where($map)->count();
		$reply = D('weiba_reply')->where('uid='.$this->mid)->group('post_id')->findAll();
		$replyCount = count( $reply );
		$favoriteCount = D('weiba_favorite')->where('uid='.$this->mid)->count();
		$followCount = D('weiba_follow')->where('follower_uid='.$this->mid)->count();
		
		$data['postCount'] = $postCount ? $postCount : 0;
		$data['replyCount'] = $replyCount ? $replyCount : 0;
		$data['favoriteCount'] = $favoriteCount ? $favoriteCount : 0;
		$data['followCount'] = $followCount ? $followCount : 0;
		$this->assign( $data );
		//dump($weiba_order);exit;
		$this->assign('weiba_order',$weiba_order);
		
	}

	/**
	 * 获取uid与微吧的关注状态
	 * @return void
	 */
	private function _assignFollowState($weiba_ids){
		// 批量获取uid与微吧的关注状态
		$follow_state = D('weiba')->getFollowStateByWeibaids($this->mid,$weiba_ids);
		//dump($follow_state);exit;
		$this->assign('follow_state', $follow_state);
	}

	/**
	 * 批量获取用户的相关信息加载
	 * @param string|array $uids 用户ID
	 */
	private function _assignUserInfo($uids) {
		!is_array($uids) && $uids = explode(',', $uids);
		$user_info = model('User')->getUserInfoByUids($uids);
		$this->assign('user_info', $user_info);
		//dump($user_info);exit;
	}

	/**
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 * @param  array $fids 用户uid数组
	 * @return void
	 */
	private function _assignFollowUidState($fids = null) {
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = model('Follow')->getFollowStateByFids($this->mid, $fids);
		$this->assign('follow_user_state', $follow_state);
		//dump($follow_state);exit;
	}

	/**
	 * 帖子列表
	 */
	private function _postList(){
		$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->field('weiba_id')->findAll(),'weiba_id'));
		$map['top'] = array('neq',2);
		$map['is_del'] = 0;
		$postList = D('weiba_post')->where($map)->order('post_time desc')->findpage(20);
		if($postList['nowPage']==1){  //列表第一页加上全局置顶的帖子
			$map['top'] = 2;
			$topPostList = D('weiba_post')->where($map)->order('post_time desc')->findAll();
			!$topPostList && $topPostList = array();
			!$postList['data'] && $postList['data'] = array();
			$postList['data'] = array_merge($topPostList,$postList['data']);
		}
		
		$weiba_ids = getSubByKey($postList['data'], 'weiba_id');
		$nameArr = $this->_getWeibaName($weiba_ids);
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k]['weiba'] = $nameArr[$v['weiba_id']];
		}
		//dump($postList);exit;
		$post_uids = getSubByKey($postList['data'], 'post_uid');
		$reply_uids = getSubByKey($postList['data'], 'last_reply_uid');
		$uids = array_unique(array_merge($post_uids,$reply_uids));
		$this->_assignUserInfo($uids);
		//微吧排行榜
		$this->_weibaOrder();
		$this->assign('postList',$postList);
	}
	private function _getWeibaName($weiba_ids){
		$weiba_ids = array_unique($weiba_ids);
		if(empty($weiba_ids)){
			return false;
		}
		$map['weiba_id'] = array('in', $weiba_ids);
		$names = D('weiba')->where($map)->field('weiba_id,weiba_name')->findAll();
		foreach ( $names as $n){
			$nameArr[$n['weiba_id']] = $n['weiba_name'];
		}		
		return $nameArr;
	}
	
}