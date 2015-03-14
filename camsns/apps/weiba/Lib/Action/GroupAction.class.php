<?php
/**
 * 微吧控制器
 * @author 
 * @version TS3.0
 */
class GroupAction extends Action {

	/**
	 * 微吧首页
	 * @return void
	 */
	public function index() {
		//微吧推荐
		$this->_weiba_recommend(10);
		//热帖推荐
		$this->_post_recommend(30);
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
		$weibaList = D('weiba')->where('is_del=0')->order('recommend desc,follower_count desc,thread_count desc')->findpage(10);
		foreach($weibaList['data'] as $k=>$v){
			$weibaList['data'][$k]['logo'] = getImageUrlByAttachId($v['logo']);
		}
		$weiba_ids = getSubByKey($weibaList['data'], 'weiba_id');
		$this->_assignFollowState($weiba_ids);
		$this->assign('weibaList',$weibaList);
		//微吧推荐
		$this->_weiba_recommend(10);
		//微吧排行榜
		$this->_weibaOrder();
		$this->assign('nav','weibalist');

		$this->setTitle( '微吧列表' );
		$this->setKeywords( '全站微吧列表' );
		$this->display();
	}

	/**
	 * 帖子列表
	 */
	public function postList(){
		//微吧推荐
		$this->_weiba_recommend(10);
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
		$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->findAll(),'weiba_id'));
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
				$postList = D('weiba_post')->where($map)->order('post_time desc')->findpage(20);
				break;
		}	
		// if($postList['nowPage']==1){  //列表第一页加上全局置顶的帖子
		// 	$topPostList = D('weiba_post')->where('top=2 and is_del=0')->order('post_time desc')->findAll();
		// 	!$topPostList && $topPostList = array();
		// 	!$postList['data'] && $postList['data'] = array();
		// 	$postList['data'] = array_merge($topPostList,$postList['data']);
		// }
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
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
		$weiba_detail['logo'] = getImageUrlByAttachId($weiba_detail['logo']);
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

	/**
	 * 发布帖子
	 * @return void
	 */
	public function post(){
		$weiba_id = intval($_GET['weiba_id']);
		$this->assign('weiba_id',$weiba_id);
		$weiba = D('weiba')->where('weiba_id='.$weiba_id)->find();
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
		$checkContent = str_replace('&nbsp;', '', $_POST['content']);
		$checkContent = str_replace('<br />', '', $checkContent);
		$checkContent = str_replace('<p>', '', $checkContent);
		$checkContent = str_replace('</p>', '', $checkContent);
		$checkContents = preg_replace('/<img(.*?)src=/i','img',$checkContent);
		$checkContents = preg_replace('/<embed(.*?)src=/i','img',$checkContents);
		if(strlen(t($_POST['title']))==0) $this->error('帖子标题不能为空');
		if(strlen(t($checkContents))==0) $this->error('帖子内容不能为空');
		preg_match_all('/./us', t($_POST['title']), $match);  
        if(count($match[0])>30){     //汉字和字母都为一个字
        	$this->error('帖子标题不能超过30个字');
        } 
		$data['weiba_id'] = intval($_POST['weiba_id']);
		$data['title'] = t($_POST['title']);
		$data['content'] = h($_POST['content']);
		$data['post_uid'] = $this->mid;
		$data['post_time'] = time();
		$data['last_reply_time'] = $data['post_time'];
		$res = D('weiba_post')->add($data);
		if($res){
			D('weiba')->where('weiba_id='.$data['weiba_id'])->setInc('thread_count');
			//同步到微博
			$feed_id = D('weibaPost')->syncToFeed($res,$data['title'],t($checkContent),$this->mid);
			D('weiba_post')->where('post_id='.$res)->setField('feed_id',$feed_id);
			//$this->assign('jumpUrl', U('weiba/Index/postDetail',array('post_id'=>$res)));
			//$this->success('发布成功');
			return $this->ajaxReturn($res, '发布成功', 1);
		}else{
			$this->error('发布失败');
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
		$this->_assignUserInfo($post_detail['post_uid']);
		$this->assign('post_detail',$post_detail);
		D('weiba_post')->where('post_id='.$post_id)->setInc('read_count');
		$weiba_name = D('weiba')->where('weiba_id='.$post_detail['weiba_id'])->getField('weiba_name');
		$this->assign('weiba_name', $weiba_name);
		//获得吧主uid
		$map['weiba_id'] = $post_detail['weiba_id'];
		$map['level'] = array('in','2,3');
		$this->assign('weiba_admin',getSubByKey(D('weiba_follow')->where($map)->order('level desc')->field('follower_uid')->findAll(),'follower_uid'));
		//该作者的其他帖子
		$map1['post_id'] = array('neq',$post_id);
		$map1['post_uid'] = $post_detail['post_uid'];
		$map1['is_del'] = 0;
		$otherPost = D('weiba_post')->where($map1)->order('reply_count desc')->limit(5)->findAll();
		foreach($otherPost as $k=>$v){
			$otherPost[$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
		}
		$this->assign('otherPost',$otherPost);
		//最新10条
		$newPost = D('weiba_post')->where('is_del=0')->order('post_time desc')->limit(10)->findAll();
		foreach($newPost as $k=>$v){
			$newPost[$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
		}
		$this->assign('newPost',$newPost);
		$this->_weibaOrder();

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
		if(in_array($this->mid,$weiba_admin) || CheckPermission('core_admin','admin_login') || $this->mid==$post_detail['post_uid']){
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

		$checkContent = str_replace('&nbsp;', '', $_POST['content']);
		$checkContent = str_replace('<br />', '', $checkContent);
		$checkContent = str_replace('<p>', '', $checkContent);
		$checkContent = str_replace('</p>', '', $checkContent);
		$checkContents = preg_replace('/<img(.*?)src=/i','img',$checkContent);
		$checkContents = preg_replace('/<embed(.*?)src=/i','img',$checkContents);
		if(strlen(t($_POST['title']))==0) $this->error('帖子标题不能为空');
		if(strlen(t($checkContents))==0) $this->error('帖子内容不能为空');
		preg_match_all('/./us', t($_POST['title']), $match);  
        if(count($match[0])>30){     //汉字和字母都为一个字
        	$this->error('帖子标题不能超过30个字');
        } 
		$post_id = intval($_POST['post_id']);
		$data['title'] = t($_POST['title']);
		$data['content'] = h($_POST['content']);
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
			$this->error('编辑失败');
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
		$post_id = intval($_POST['post_id']);
		if(D('weiba_post')->where('post_id='.$post_id)->setField('is_del',1)){
			if(intval($_POST['log'])==1){
				$post_detail = D('weiba_post')->where('post_id='.$post_id)->find();
				D('log')->writeLog($post_detail['weiba_id'],$this->mid,'删除了帖子“'.$post_detail['title'].'”','posts');
			}
			D('weiba')->where('weiba_id='.intval($_POST['weiba_id']))->setDec('thread_count');
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
		
		$currentValue = intval($_POST['currentValue']);
		$targetValue = intval($_POST['targetValue']);
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
						break;
					case '1':     //设为吧内置顶
							$config['typename'] = "吧内置顶";
							model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了吧内置顶','posts');
						break;
					case '2':     //设为全局置顶
							$config['typename'] = "全局置顶";
							model('Notify')->sendNotify($post_detail['post_uid'], 'weiba_post_set', $config); 
							D('log')->writeLog($post_detail['weiba_id'],$this->mid,'将帖子“<a href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'" target="_blank">'.$post_detail['title'].'</a>”设为了全局置顶','posts');
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

		//微吧排行榜
		$this->_weibaOrder();
		$this->assign('nav','search');
		if($k == ""){
			if($_REQUEST['type'] == '1'){
				$this->display('search_weiba');
			}else{
				$this->display('search_post');
			}
		}
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
					$weibaList['data'][$k]['logo'] = getImageUrlByAttachId($v['logo']);
				}
				$weiba_ids = getSubByKey($weibaList['data'], 'weiba_id');
				$this->_assignFollowState($weiba_ids);
				$this->assign('weibaList',$weibaList);
			}else{
				//微吧推荐
				$this->_weiba_recommend(10);
			}
			$this->display('search_weiba');
		}else{
			//搜帖子
			$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->findAll(),'weiba_id'));
			$map['title'] = array('like','%'.$k.'%');
			//$where['content'] = array('like','%'.$k.'%');
			//$where['_logic'] = 'or';
			//$map['_complex'] = $where;
			$postList = D('weiba_post')->where($map)->order('post_time desc')->findPage(20);
			if($postList['data']){
				foreach($postList['data'] as $k=>$v){
					$postList['data'][$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
				}
				$post_uids = getSubByKey($postList['data'], 'post_uid');
				$reply_uids = getSubByKey($postList['data'], 'last_reply_uid');
				$uids = array_unique(array_merge($post_uids,$reply_uids));
				$this->_assignUserInfo($uids);
				$this->assign('postList',$postList);
			}else{
				//微吧推荐
				$this->_weiba_recommend(10);
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
		$weiba_id = intval($_GET['weiba_id']);
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
		if(strlen(t($_POST['reason']))==0) $this->error('申请理由不能为空');
		preg_match_all('/./us', t($_POST['reason']), $match);  
        if(count($match[0])>140){     //汉字和字母都为一个字
        	$this->error('申请理由不能超过140个字');
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
	private function _weiba_recommend($limit){
		$weiba_recommend = D('weiba')->where('recommend=1 and is_del=0')->limit($limit)->findAll();
		foreach($weiba_recommend as $k=>$v){
			$weiba_recommend[$k]['logo'] = getImageUrlByAttachId($v['logo']);
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
		$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->findAll(),'weiba_id'));
		$map['recommend'] = 1;
		$map['is_del'] = 0;
		$post_recommend = D('weiba_post')->where($map)->order('recommend_time desc')->limit($limit)->findAll();
		foreach($post_recommend as $k=>$v){
			$post_recommend[$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
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
			$weiba_order[$k]['logo'] = getImageUrlByAttachId($v['logo']);
		}
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
		$map['weiba_id'] = array('in',getSubByKey(D('weiba')->where('is_del=0')->findAll(),'weiba_id'));
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
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k]['weiba'] = D('weiba')->where('weiba_id='.$v['weiba_id'])->getField('weiba_name');
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
	
}