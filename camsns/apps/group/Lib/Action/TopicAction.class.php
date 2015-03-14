<?php
	class TopicAction extends BaseAction
	{
		var $topic;
		var $post;
		public function _initialize(){
			parent::_initialize();

			// 判断功能是否开启
			if (!$this->groupinfo['openBlog']) {
				$this->assign('jumpUrl', U('group/Group/index', array('gid'=>$this->gid)));
				$this->error('帖子功能已关闭');
			}

			$this->topic = D('Topic');
			$this->post = D('Post');

			// 权限判读
			if (in_array(ACTION_NAME, array('dist','undist','top','untop','lock','unlock','addCategory','deleteCategory'))) {
				// 置顶，精华，锁定，删除，帖子分类
				if(!$this->isadmin){
					$this->error('你没有权限');
				}
			} else if (in_array(ACTION_NAME, array('add','doAdd','edit','post','editPost'))) {
				// 发布，回复，编辑
				if(!$this->ismember){
					$this->error('抱歉，您不是该群成员');
				}
			}
			$this->assign('current','topic');
		}

		function index() {
			$dist = (isset($_GET['isdist']) && $_GET['isdist'] == 1) ? ' AND dist=1 ' : '';  //精华
			if ('my' == $_GET['cid']) {
				$cid = " AND uid={$this->mid} ";
				$this->assign('cid', $_GET['cid']);
			} else if ('dist' == $_GET['cid']) {
				$cid = " AND dist=1 ";
				$this->assign('cid', $_GET['cid']);
			} else {
				$cid = (is_numeric($_GET['cid']) && $_GET['cid'] > 0) ? " AND cid={$_GET['cid']} " : '';
				$this->assign('cid', intval($_GET['cid']));
			}

			// 帖子分类
			$category_list = $this->topic->categoryList($this->gid);
			foreach ($category_list as $k=>$v) {
				$_category_list[$v['id']] = $v;
				unset($category_list[$k]);
			}

			$search_key = $this->_getSearchKey('k','group_topic_search');
			$search_key = $search_key?" AND title LIKE '%{$search_key}%' ":'';

			$topiclist = $this->topic
							  ->order('top DESC,replytime DESC')
							  ->where('is_del=0 AND gid=' . $this->gid . $search_key . $dist . $cid)
							  ->findPage();
			// 附加帖子分类
			foreach ($topiclist['data'] as &$v) {
				$v['ctitle'] = $_category_list[$v['cid']]['title'] ? "[{$_category_list[$v['cid']]['title']}]" : '';
			}

			$this->assign('dist', $dist);
			$this->assign('category_list', $_category_list); // 帖子分类
			$this->assign('topiclist', $topiclist);
        	$this->setTitle("帖子 - " . $this->groupinfo['name']);
 			$this->display();
		}

		// 发表话题 编辑话题
		public function add()
		{
			$this->assign('category_list', $this->topic->categoryList($this->gid));
        	$this->setTitle("发新帖子 - " . $this->groupinfo['name']);
			$this->display();
		}

		// 添加内容
		public function doAdd()
		{
			if(isset($_POST['addsubmit']) && trim($_POST['addsubmit']) == 'do') {
				$title = getShort($_POST['title'], 30);
				if(empty($title)) $this->error('标题不能为空');

				$this->__checkContent($_POST['content'], 10, 5000);

				$topic['attach'] = $this->_setTopicAttach();	// 附件信息
				$topic['gid'] = $this->gid;
				$topic['uid'] = $this->mid;
				$topic['name'] = getUserName($this->mid);
				$topic['title'] = h(t($title));
				$topic['cid']   = intval($_POST['cid']);
				$topic['addtime'] = time();
				$topic['replytime'] = time();
				if($tid = D('Topic')->add($topic)) {
					$post['gid'] = $this->gid;
					$post['uid'] = $this->mid;
					$post['tid'] = $tid;
					$post['content'] = h($_POST['content']);
					$post['istopic'] = 1;
					$post['ctime'] = time();
					$post['ip'] = get_client_ip();
					$post_id = $this->post->add($post);

					// 微博
// 					$weibo_tpl_data = array('author'=>getUserName($topic['uid']),'title'=>$topic['title'],'url'=>U('group/Topic/topic',array('gid'=>$this->gid,'tid'=>$tid)));
// 					$weibo_tpl_data = model('Template')->parseTemplate('group_post_create_weibo', array('body'=>$weibo_tpl_data));
// 				    $weibo_data['gid']     = $this->gid;
// 					$weibo_data['content'] = $weibo_tpl_data['body'];
// 					D('GroupWeibo','group')->doSaveWeibo($this->mid, $weibo_data, 0, '', '', '');
					D('GroupFeed')->syncToFeed('我发布了一个群组帖子“'.t($_POST['title']).'”,详情请点击'.U('group/Topic/topic',array('tid'=>$tid,'gid'=>$this->gid)),$this->mid,0,0,$this->gid);
					// 积分
// 					X('Credit')->setUserCredit($this->mid, 'group_add_topic');

					$this->assign('jumpUrl',U('/Topic/topic',array('gid'=>$this->gid,'tid'=>$tid)));
					$this->success('发布帖子成功');
				}else{
					$this->error('发帖失败');
				}
			}
		}

		// 编辑话题
		public function edit()
		{
			// 权限判读 (管理员和创建者)
			$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
			$thread = $this->topic->getThread($tid);

			if (empty($thread))
				$this->error('帖子不存在');

			// 管理员或者帖子主人
			if(!($this->isadmin || $thread['uid'] == $this->mid))
				$this->error('无权限');

			if(isset($_POST['editsubmit']) && trim($_POST['editsubmit']) == 'do') {
				$title = h(t(getShort($_POST['title'], 30)));
				if(empty($title)) $this->error('标题不能为空');

				$this->__checkContent($_POST['content'], 10, 5000);
				$content = h($_POST['content']);

				// 附件信息
				$map['attach'] = $this->_setTopicAttach($thread['attach']);

				$map['title'] = $title;
				$map['cid']   = intval($_POST['cid']);
				$map['mtime'] = time();

				$this->topic->where('id=' . $tid)->save($map);

				$this->post->setField('content', $content, 'tid='.$tid." AND istopic=1");

				$this->assign('jumpUrl',U('group/Topic/topic',array('gid'=>$this->gid,'tid'=>$tid)));
				$this->success('编辑帖子成功');
			} else {
				$dirs = unserialize( $thread['attach'] );
				$dmap['id'] = array( 'in' , $dirs );
				$attachid = D('Dir')->where($dmap)->field('attachId')->findAll();
				$thread['attachids'] = getSubByKey ( $attachid , 'attachId' );
			}

			$this->assign('thread',$thread);
			$this->assign('category_list', $this->topic->categoryList($this->gid));
        	$this->setTitle("编辑帖子 - " . $this->groupinfo['name']);
			$this->display();
		}

		// 处理表单附件信息
		protected function _setTopicAttach($old_attach = '')
		{
				// 文件功能是否开启
				if ($this->groupinfo['openUploadFile']) {
					// 文件上传权限
					if ($this->groupinfo['whoUploadFile'] == 3 || ($this->groupinfo['whoUploadFile'] == 2 && $this->isadmin)) {
						// 添加附件
						if ($_POST['attach']) {
							if (count($_POST['attach']) > 3){
								$this->error('附件数量不能超过3个');
							}
							array_map('intval', $_POST['attach']);
							
							if ( $old_attach ){
								$old_attach = unserialize($old_attach);
								$oldmap['id'] = array('in' , $old_attach);
								$oldattachid = D('Dir')->where($oldmap)->field('attachId')->findAll();
								$oldattachid = getSubByKey( $oldattachid , 'attachId' );
								
								//需要添加的附件
								$attach = array_diff( $_POST['attach'] , $oldattachid );
							} else {
								$attach = $_POST['attach'];
							}
							$data = model('Attach')->getAttachByIds( $attach );
							$dirids = array();
							foreach ( $data as $d ){
								$attchement['gid'] = $this->gid;
								$attchement['uid'] = $this->mid;
								$attchement['attachId'] = $d['attach_id'];
								$attchement['name'] = $d['name'];
								$attchement['note'] = t($_POST['name']);
								$attchement['filesize'] = formatsize($d['size']);
								$attchement['filetype'] = $d['extension'];
								$attchement['fileurl'] = $d['save_path'] . $d['save_name'];
								$attchement['ctime'] = time();
								$dirids[] = D('Dir')->add( $attchement );
							}
							
							$dmap['id'] = array( 'in' , $dirids );
							D('Dir')->setField('is_del', 0, $dmap);
							
							
						}
// 						if ( $old_attach ){
							// 处理删除的附件的
// 							if ( count($_POST['attach']) > 0 ) {
// 								$dirids && $del_attach = array_diff($old_attach, $dirids);
// 							} else {
// 								$del_attach = $old_attach;
// 							}
							
// 							$del_attach && D('Dir')->remove($old_attach);
							
// 							$old = array_diff( $old_attach , $del_attach );
// 							$finalid = serialize(array_merge( $old , $dirids ));
// 						} else {
// 							$finalid = $dirids;
// 						}
						foreach ( $_POST['attach'] as $k=>&$v ){
							if (!$v){
								unset($_POST['attach'][$k]);
							}
						}
						$rmap['attachId'] = array ( 'in' , $_POST['attach'] );
						$finalid = D( 'Dir' )->where($rmap)->field('id')->findAll();
						$finalid = getSubByKey( $finalid , 'id') ;
						return $finalid ? serialize($finalid) : '';
					} else {
						return $old_attach;
					}
				} else {
					return $old_attach;
				}
		}

		//话题回复
		public function post()
		{
			//权限判读
			$tid = is_numeric($_POST['tid']) ? intval($_POST['tid']) : 0;

			if($tid > 0) {
				$topic = D('Topic')->field('id,uid,title,`lock`')->where("gid={$this->gid} AND id={$tid} AND is_del=0")->find();  //获取话题内容
				if (!$topic) {
					$this->error('帖子不存在或已被删除');
				} else if($topic['lock'] == 1) {
					$this->assign('jumpUrl', U('group/Topic/topic', array('gid'=>$this->gid, 'tid'=>$tid)));
					$this->error('帖子已被锁定，不可回复');
				}
				$this->__checkContent($_POST['content'], 5, 10000);

				$post['gid'] = $this->gid;
				$post['uid'] = $this->mid;
				$post['tid'] = $tid;
				$post['content'] = h($_POST['content']);
				$post['istopic'] = 0;
				$post['ctime'] = time();
				$post['ip'] = get_client_ip();
				if(isset($_POST['quote'])) {  //如果引用帖子
					$post['quote'] = isset($_POST['qid']) ? intval($_POST['qid']) : 0;	//引用帖子id
					$post_info = D('Post', 'group')->field('uid,istopic,content')->where("id={$post['quote']}")->find();
					if ($post_info['uid'] != $this->mid) {
						// 发送通知
// 						$notify_dao = service ( 'Notify' );
// 						$notify_data = array (
// 									 	'post' 	  => $post_info['istopic'] ? "的帖子“{$topic['title']}”并回复您" : "在帖子“{$topic['title']}”中的回复",
// 										'quote'   => strip_tags(getShort(html_entity_decode($post_info['content']), 30, '...')),
// 										'content' => strip_tags(getShort(html_entity_decode($post['content']), 60, '...')),
// 									   	'gid' 	  => $this->gid,
// 										'tid'	  => $topic['id'],
// 									   );
// 						$notify_dao->send($post_info['uid'], 'group_topic_quote', $notify_data, $this->mid);
// 	    				D('GroupUserCount')->addCount($post_info['uid'], 'bbs', $this->gid);
					}
				}

				$result = $this->post->add($post);  //添加回复
				if($result) {
					if ($topic['uid'] != $this->mid && $post_info['uid'] != $topic['uid']) {
						// 发送通知
// 						$notify_dao = service ( 'Notify' );
// 						$notify_data = array (
// 										'title'   => $topic['title'],
// 										'content' => strip_tags(getShort(html_entity_decode($post['content']), 60, '...')),
// 									   	'gid' 	  => $this->gid,
// 										'tid'	  => $topic['id'],
// 									   );
// 						$notify_dao->send($topic['uid'], 'group_topic_reply', $notify_data, $this->mid);
// 	    				D('GroupUserCount')->addCount($post_info['uid'], 'bbs', $this->gid);
					}

					$this->topic->setField('replytime', time(), 'id='.$tid);
					$this->topic->setInc('replycount', 'id='.$tid);
					// 积分
					X('Credit')->setUserCredit($this->mid, 'group_reply_topic');
				}
				$this->redirect('group/Topic/topic', array('gid'=>$this->gid,'tid'=>$tid));
			} else {
				$this->error('帖子参数错误');
			}
		}

		//编辑话题回复
		public function editPost()
		{
			//权限判读 (管理员和创建者)
			$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
			$post = $this->post->where('id='.$pid.' AND is_del=0')->find();

			//管理员或者帖子主人
			if(!$post) { $this->error('帖子回复不存在');}
			if( !($this->isadmin || $post['uid'] == $this->mid) ){$this->error('无权限');}

			if(isset($_POST['editsubmit']) && trim($_POST['editsubmit']) == 'do') {

				$this->__checkContent($_POST['content'], 10, 10000);
				$content = h($_POST['content']);

				$map['attach'] = !empty($_POST['attach']) ? serialize($_POST['attach']) : '';
				$map['content'] = $content;
				$res = $this->post->where('id='.$pid." AND istopic=0")->save($map);
				if ($res !== false) {
					$this->assign('jumpUrl', U('group/Topic/topic', array('gid'=>$this->gid,'tid'=>$post['tid'])));
				 	$this->success('修改成功');
				} else {
					$this->error('修改失败');
				}
			}

			$this->assign('post',$post);
			$this->setTitle($this->siteTitle['edit_topic']);
        	$this->setTitle("编辑帖子回复 - " . $this->groupinfo['name']);
			$this->display();
		}


		//搜索话题
		public function search() {
			$type = !empty($_POST['type']) ? $_POST['type'] : 'cort';

			if(isset($_POST['searchSubmit'])){
				$keywords = !empty($_POST['keywords']) ? t($_POST['keywords']) : '';

				if(!$keywords) $this->error('关键字太少');

				if($type=='name'){
					$where = 'gid='.$this->gid." AND is_del=0 AND name like '%$keywords%'";
					$topiclist = $this->topic->order('top DESC,replytime DESC')->where($where)->findPage(3);

					foreach($topiclist['data'] as $k => $v){
						$topiclist['data'][$k]['tid'] = $topiclist['data'][$k]['id'];
						$topiclist['data'][$k]['name'] = red($topiclist['data'][$k]['name'],$keywords);
						$topiclist['data'][$k]['content'] = D('Post')->getField('content','tid='.$v['id']." AND istopic=1");
					}
				}elseif($type == 'cort'){
			    	$topiclist=$this->topic->getSearch($keywords,$this->gid,'cort');
			    	foreach($topiclist['data'] as $k => $v){
						$topiclist['data'][$k]['title'] = red($topiclist['data'][$k]['title'],$keywords);
						$topiclist['data'][$k]['content'] = redContent($topiclist['data'][$k]['content'],$keywords);
					}
				}

				$this->assign('keywords',$keywords);
				$this->assign('topiclist',$topiclist);
			}
			$this->setTitle($this->siteTitle['search_topic']);
			$this->assign('type',$type);
        	$this->setTitle("帖子 - " . $this->groupinfo['name']);
 			$this->display();
		}

		// 话题显示
		public function topic()
		{
			$tid = intval($_GET['tid']) > 0 ? $_GET['tid'] : 0;

			if($tid == 0) $this->error('参数错误');
			$limit = 20;

			$this->topic->setInc('viewcount','id='.$tid);
			$thread = $this->topic->getThread($tid);     //获取主题
			// 判读帖子存不存在
			if(!$thread) {
				$this->assign('jumpUrl', U('group/Group/index', array('gid'=>$this->gid)));
				$this->error('帖子不存在');
			}
			// 帖子的分类
			$thread['ctitle'] = M('group_topic_category')->getField('title', "id={$thread['cid']} AND gid={$this->gid}");
			$thread['ctitle'] = $thread['ctitle'] ? "[{$thread['ctitle']}]" : '';

			// 附件信息
			if ($thread['attach']) {
				$_attach_map['id'] = array('IN', unserialize($thread['attach']));
				$thread['attach'] = D('Dir')->field('id,name,note,is_del')->where($_attach_map)->findAll();
			}
			$postlist = $this->post->order('istopic DESC')->where('is_del = 0 AND tid='.$tid)->findPage($limit);
			// 起始楼层计算
			$p = $_GET[C('VAR_PAGE')] ? intval($_GET[C('VAR_PAGE')]) : 1;
			$this->assign('start_floor', intval((1 == $p) ? (($p - 1) * $limit + 1) : (($p - 1) * $limit) ));
			
			$this->assign('topic', $thread);
			$this->assign('tid', $tid);
			$this->assign('postlist', $postlist);

			$this->assign('isCollect', D('Collect')->isCollect($tid,$this->mid));  //判断是否收藏

        	$this->setTitle("{$thread['title']} - 帖子 - {$this->groupinfo['name']}");
			$this->display();
		}

		// 收藏话题
		public function collect(){
			$tid = intval($_POST['tid']);
			$Collect = D('Collect');
			if($tid >0){
				$map['tid'] = $tid;
				$map['mid'] = $this->mid;
				$map['addtime'] = time();

				if($Collect->isCollect($tid,$this->mid)) { echo "-1"; exit();}
				if($Collect->add($map)){
					echo "1"; exit();
				}
			}
			echo "0"; exit();
		}

		// 取消收藏话题
		public function cancel_collect($gid,$tid){
			$tid = intval($_POST['tid']);
			if($tid >0){
				if(D('Collect')->where('tid='.$tid." AND mid=".$this->mid)->delete()){
					exit(1);
				}
			}
			exit(0);
		}

		// 精华
		public function dist()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title,dist')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title,dist')->where($map)->find();
			}

			$result = $this->topic->setField('dist', 1, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('设置为精华', $topicInfo);
				// 发送通知
				if (!is_array($topicInfo[0])) {
					$topicInfo[] = $topicInfo;
				}
				foreach ($topicInfo as $v) {
					if ($v['uid'] != $this->mid && !$v['dist']) {
// 						$notify_data = array (
// 										'title'   => $v['title'],
// 										'gid' 	  => $this->gid,
// 										'tid'	  => $v['id'],
// 									   );
// 						service ( 'Notify' )->send($v['uid'], 'group_topic_dist', $notify_data, $this->mid);
// 	    				D('GroupUserCount')->addCount($v['uid'], 'bbs', $this->gid);
					}
				}

				exit(json_encode(array('flag'=>'1', 'msg'=>'帖子设为精华成功')));
			}else{
				exit(json_encode(array('flag'=>'0', 'msg'=>'帖子设为精华失败')));
			}
		}

		// 取消精华
		public function undist()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
			}

			$result = $this->topic->setField('dist', 0, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('取消了精华', $topicInfo);

				//setScore($this->mid,'group_topic_cancel_dist');
				exit(json_encode(array('flag'=>'1','msg'=>'帖子取消精华成功')));
			}else{
				exit(json_encode(array('flag'=>'0','msg'=>'取消精华失败')));
			}
		}

		//置顶
		public function top()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title,top')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title,top')->where($map)->find();
			}

			$result = $this->topic->setField('top', 1, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('置顶', $topicInfo);
				// 发送通知
				if (!is_array($topicInfo[0])) {
					$topicInfo[] = $topicInfo;
				}
				foreach ($topicInfo as $v) {
					if ($v['uid'] != $this->mid && !$v['top']) {
// 						$notify_data = array (
// 										'title'   => $v['title'],
// 										'gid' 	  => $this->gid,
// 										'tid'	  => $v['id'],
// 									   );
// 						service ( 'Notify' )->send($v['uid'], 'group_topic_top', $notify_data, $this->mid);
// 	    				D('GroupUserCount')->addCount($v['uid'], 'bbs', $this->gid);
					}
				}

				exit(json_encode(array('flag'=>'1','msg'=>'帖子置顶成功')));
			}else{
				exit(json_encode(array('flag'=>'0','msg'=>'帖子置顶失败')));
			}
		}

		public function untop()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
			}

			$result = $this->topic->setField('top', 0, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('取消置顶', $topicInfo);

				//setScore($this->mid,'group_topic_cancel_top');
				exit(json_encode(array('flag'=>'1','msg'=>'取消置顶成功')));

			}else{
				exit(json_encode(array('flag'=>'0','msg'=>'取消置顶失败')));
			}
		}

		//锁定
		public function lock()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
			}

			$result = $this->topic->setField('lock', 1, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('锁定', $topicInfo);

				exit(json_encode(array('flag'=>'1','msg'=>'锁定成功')));

			}else{
				exit(json_encode(array('flag'=>'0','msg'=>'锁定失败')));
			}
		}

		public function unlock()
		{
			$tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($tid == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if (strpos($tid, ',')) {
				$map['id'] = array('IN',$tid);
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
			} else if(is_numeric($tid)) {
				$map = "id={$tid}";
				$topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
			}

			$result = $this->topic->setField('lock', 0, $map);

			if($result !== false) {
				//设置日志
				$this->_setOperationLog('解锁', $topicInfo);

				exit(json_encode(array('flag'=>'1','msg'=>'取消锁定成功')));

			}else{
				exit(json_encode(array('flag'=>'0','msg'=>'取消锁定失败')));
			}
		}

		//删除
		public function del()
		{
			$id = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
			if($id == '') exit(json_encode(array('flag'=>'0', 'msg'=>'tid错误')));

			if ($_POST['type'] == 'thread') {
				if (strpos($id, ',') && $this->isadmin) {
					$map['id'] = array('IN',$id);
					$map['gid'] = $this->gid;
					$topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
				} else if (is_numeric($id)) {
					$map['id']  = $id;
					$map['gid'] = $this->gid;
					$topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
					if (!$this->isadmin && $topicInfo['uid'] != $this->mid) {
						$this->error('你没有权限');
					}
				} else {
					$this->error('你没有权限');
				}
				//设置日志
				$this->_setOperationLog('删除', $topicInfo);

				$res = $this->topic->remove($id);

				if ($_POST['ajax'] == 1) {
					if ($res === false) {
						exit(json_encode(array('flag'=>'0','msg'=>'删除失败')));
					} else {
						exit(json_encode(array('flag'=>'1','msg'=>'删除成功')));
					}
				} else {
					$this->redirect('group/Topic/index', array('gid'=>$this->gid));
				}
			} else if ($_POST['type'] == 'post') {
				$post_info = $this->post->field('uid,tid')->where('id=' . $id)->find();           //获取要删除的帖子id
				if (!$this->isadmin && $post_info['uid'] != $this->mid) {
					$this->error('你没有权限');
				}
				$this->post->remove($id);           //删除回复

				//帖子回复数目减少1个
				$this->topic->setDec('replycount','id=' . $post_info['tid']);
				$this->redirect('group/Topic/topic', array('gid'=>$this->gid,'tid'=>$post_info['tid']));
			}
		}

		protected function _setOperationLog($operation, &$post_info)
		{
            $content =  '把 ' . getUserSpace($post_info['uid'], 'fn', '_blank', '@' . getUserName($post_info['uid']))
						 . ' 的帖子“<a href="' . U('group/Topic/topic', array('gid'=>$this->gid, 'tid'=>$post_info['id'])) . '" target="_blank">'
						 . $post_info['title'] . '</a>” ' . $operation;
			D('Log')->writeLog($this->gid, $this->mid, $content, 'topic');
            /*
			//设置日志
			if (!is_array($post_info[0])) {
				$post_info[] = $post_info;
			}
			foreach ($post_info as $v) {
				if (!$v['uid'] || !$v['title']) {
					continue;
				}
				$content =  '把 ' . getUserSpace($v['uid'], 'fn', '_blank', '@' . getUserName($v['uid']))
						 . ' 的帖子“<a href="' . U('group/Topic/topic', array('gid'=>$this->gid, 'tid'=>$v['id'])) . '" target="_blank">'
						 . $v['title'] . '</a>” ' . $operation;
				D('Log')->writeLog($this->gid, $this->mid, $content, 'topic');
			}
            */
		}
/*
		public function addShare_check(){

			$result = 1;
			$aimId = intval($_REQUEST['aimId']);

			$test = $this->api->share_isForbid($this->mid,9,$aimId);

			if($test==-1){
				$result = -2;
			}

			echo $result;
		}
		public function addShare(){
			$aimId = intval($_REQUEST['aimId']);
			$this->assign('aimId',$aimId);
			$group = D('Topic')->getThread($aimId,'title');

			$this->assign('title',$group['title']);
			$this->assign($group);
			$this->assign('mid',$this->mid);

			$this->display();
		}

		public function doaddShare(){
			$type['typeId'] = 9;
			$type['typeName'] = '话题';
			$type['alias'] = 'topic';

			$info = h($_REQUEST['info']);
			$aimId = intval($_REQUEST['aimId']);

			$data = D('Topic')->getThread($aimId);

			$data['cid'] = $this->groupinfo['cid0'];
			$data['catagory'] = group_getCategoryName($data['cid']);
			$data['groupName'] = $this->groupinfo['name'];

		    $intro = str_replace( "&amp;nbsp;","",t($data['content']));
		    $data['intro'] = $this->_getBlogShort($intro,120);
            unset($data['content']);
			//$data['title'] = h($_REQUEST['title']);
			$fids = $_REQUEST['fids'];

			$result = $this->api->share_addShare($type,$aimId,$data,$info,0,$fids);
			echo $result;
		}
*/

		// 引用
		public function quoteDialog(){
			$id = intval($_REQUEST['id']);
			//$tid = intval($_REQUEST['tid']);

			$postInfo = $this->post->where('id='.$id)->find();

			if(empty($postInfo))  $this->error('参数错误');

			$this->assign('postInfo',$postInfo);
			$this->assign('id',$id);
			$this->display();
		}

		protected function _getBlogShort($content,$length = 60) {
			$content	=	stripslashes($content);
			$content	=	strip_tags($content);
			$content	=	getShort($content,$length);
			return $content;
		}

		private function __checkContent($content, $mix = 5, $max = 5000)
		{
				$content_length = get_str_length($content, true);
				if (0 == $content_length) {
					$this->error('内容不能为空');
				} else if ($content_length < $mix) {
				 	$this->error('内容不能少于' . $mix . '个字');
				} else if ($content_length > $max) {
				 	$this->error('内容不能超过' . $max . '个字');
				}
		}

	     // 增加帖子分类
	     public function addCategory()
	     {
	     	$data['title'] = t($_POST['title']);
	     	$data['title'] = trim($data['title']);
	     	$gid = intval($_POST['gid']);
	     	if(empty($data['title'])){
	     		echo -2;
	     		exit;
	     	}
	     	if (M('group_topic_category')->getField('id', "title='{$data['title']}'  AND gid=$gid")) {
	     		echo -1;
	     		exit;
	     	}
	     	$data['gid'] = D('Group', 'group')->getField('id', 'id=' . intval($_POST['gid']) . ' AND status=1 AND is_del=0');
	     	$res = M('group_topic_category')->add($data);
	     	echo intval($res);
	     }

	     // 删除帖子分类
	     public function deleteCategory()
	     {
	     	$res = M('group_topic_category')->where('id=' . intval($_GET['cid']))->delete() ? 1 : 0;
	     	if ($res) {
	     		D('Topic')->setField('cid', 0, 'cid=' . intval($_GET['cid']));
	     		$this->assign('jumpUrl', U('group/Topic/index', array('gid'=>$this->gid)));
	     		$this->success('操作成功');
	     	} else {
	     		$this->error('操作失败');
	     	}
	     }

	}