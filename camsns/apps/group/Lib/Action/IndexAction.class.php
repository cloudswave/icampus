<?php
class IndexAction extends BaseAction
{
	protected $group;
	public function _initialize()
	{
		parent::base();
		$this->group = D('Group');
		/*
		 * 右侧信息
		 */
		 if (in_array((ACTION_NAME), array('find', 'search', 'add'))) {
	    	// 加入的群组的数量
	    	$join_group_count = D('Member')->where('(level>1 AND status=1)  AND uid=' . $this->mid)->count();
	    	$this->assign('join_group_count', $join_group_count);

	    	// 热门标签
	    	$hot_tags_list = D('GroupTag')->getHotTags();
	    	$this->assign('hot_tags_list', $hot_tags_list);

	    	// 群组热门排行
	    	$hot_group_list = $this->group->getHotList();
	    	$this->assign('hot_group_list', $hot_group_list);
		} else if (in_array((ACTION_NAME), array('index','message', 'post', 'replied', 'comment', 'atme', 'bbsNotify'))) {
		    //检查所有模板里面需要检查项
		    $check['inIndex'] = in_array(ACTION_NAME,array('index','message'));
		    $check['newly'] = $check['inIndex']?on:off;
		    $check['postly']      = ACTION_NAME  == 'post'?"on":"off";
		    $check['replied']     = ACTION_NAME  == "replied"?"on":"off";
            $check['commentes']   = ACTION_NAME  == 'comment'?"on":"off";
            $check['bbsNotifyes'] = ACTION_NAME == 'bbsNotify'?'on':"off";
            $check['atmes']        = ACTION_NAME  == 'atme'?'on':'off';

		    $this->assign($check);
			$this->assign('userCount', D('GroupUserCount', 'group')->getUnreadCount($this->mid));
		}


	}

	public function index(){
		$map['status'] = 1;
		$map['is_del'] = 0;
		$list = D('Group')->where($map)->order('ctime desc')->findPage();
		$cids = getSubByKey( $list['data'] , 'cid0' );
		$cmap['id'] = array('in' , $cids );
		$cateinfos = D('Category')->where($cmap)->field('id,title')->findAll();
		$cnames = array();
		foreach ( $cateinfos as $cate ){
			$cnames[$cate['id']] = $cate['title'];
		}
		foreach ( $list['data'] as $k=>&$v ){
			$v['logo'] = logo_path_to_url( $v['logo'] );
			$v['catename'] = $cnames[$v['cid0']];
		}
		$this->_indexRight();
		$this->assign($list);
		$this->display();
// 	    $this->message();
	}
	/**
	 * 首页右侧数据查询
	 */
	private function _indexRight(){
		$list = D('Category')->order('pid')->findAll();
		$catelist = array();
		foreach ( $list as $v ){
			if ( $v['pid'] && $catelist[$v['pid']] ){
				$catelist[$v['pid']]['child'][] = $v;
			} else {
				$catelist[$v['id']] = $v;
			}
		}
		$hotlist = D('Group', 'group')->getHotList();
		foreach ( $hotlist as &$hv ) {
			$hv['short_name'] = getShort( $hv['name'] , 10);
			$hv['logo'] = logo_path_to_url( $hv['logo'] );
		}
		$this->assign( 'hotlist' , $hotlist );
		$this->assign( 'catelist' , $catelist );
	}
	
	
    public function message()
    {



        $order = in_array($_GET['order'], array('group', 'ctime')) ? $_GET['order'] : 'group';
        $my_group = D('Member')->field('gid')
        					   ->where("uid={$this->mid} AND status=1 AND level>0")
        					   ->order('level ASC,ctime DESC')
        					   ->findAll();
		// 无任何群组，则跳转一次到发现页
        if (!$my_group && !cookie('new_group_user')) {
        	cookie('new_group_user', $this->mid);
        	$this->redirect('group/Index/find');
        	exit;
        }

        switch ($order) {
        	case 'ctime':	// 按照时间查看
        		foreach ($my_group as $v) {
        			$_gids[] = $v['gid'];
        		}
        		$index_list = D('WeiboOperate', 'group')->doSearchTopic('gid IN (' . implode(',', $_gids) . ')', 'ctime DESC', $this->mid);
        		// 群组名称
        		$index_list_gids = getSubByKey($index_list['data'], 'gid');
        		$map	   = array();
        		$map['id'] = array('IN', $index_list_gids);
        		$group_name = $this->group->field('id,name')->where($map)->findAll();
        		foreach ($group_name as $v) {
        			$_group_name[$v['id']] = $v['name'];
        		}
        		foreach ($index_list['data'] as &$v) {
        			$v['group_name'] = $_group_name[$v['gid']];
        		}
        		break;
        	default:	// 按照群组查看
        		foreach ($my_group as $v) {
        			$_gids[] = $v['gid'];
        		}
        		// 群组基本信息
        		$map	  	   = array();
        		$map['id'] 	   = array('IN', $_gids);
        		$map['status'] = 1;
        		$map['is_del'] = 0;
        		$my_group_info = $this->group->field('id,name,logo,membercount')->where($map)->findAll();
        		foreach ($my_group_info as $v) {
        			$index_list[$v['id']]['group_info'] = $v;
        			// 最新微博
        			$index_list[$v['id']]['weibo_list'] = D('GroupWeibo')->field('weibo_id,gid,uid,content,ctime')
			        												     ->where("gid={$v['id']} AND isdel=0")
			        												     ->order('weibo_id DESC')->limit(3)->findAll();
        		}
        		// 今日微博统计
        		$today = mktime(0,0,0,date("m"),date("d"),date("Y"));
        		$new_weibo_count = D('GroupWeibo')->field('gid,count(gid) AS count')->where('gid IN (' . implode(',', $_gids) . ') AND isdel=0 AND ctime>' . $today)->group('gid')->findAll();
        		foreach ($new_weibo_count as $v) {
        			$index_list[$v['gid']]['new_weibo_count'] = $v['count'];
        		}
        		// 今日新帖
        		$new_topic_count = D('Topic')->field('gid,count(gid) AS count')->where('gid IN (' . implode(',', $_gids) . ') AND is_del=0 AND addtime>' . $today)->group('gid')->findAll();
        		foreach ($new_topic_count as $v) {
        			$index_list[$v['gid']]['new_topic_count'] = $v['count'];
        		}
        		// 今日文件
        		$new_file_count = D('Dir')->field('gid,count(gid) AS count')->where('gid IN (' . implode(',', $_gids) . ') AND is_del=0 AND ctime>' . $today)->group('gid')->findAll();
        		foreach ($new_file_count as $v) {
        			$index_list[$v['gid']]['new_file_count'] = $v['count'];
        		}
        		// 今日新成员统计
        		$new_member_count = D('Member')->field('gid,count(gid) AS count')->where('gid IN (' . implode(',', $_gids) . ') AND level>1 AND ctime>' . $today)->group('gid')->findAll();
        		foreach ($new_member_count as $v) {
        			$index_list[$v['gid']]['new_member_count'] = $v['count'];
        		}
        		break;
        }

        $check['byGroup'] = $order == 'group';
        $check['byCtime'] = $order == 'ctime';
        $this->assign($check);
        $this->assign('order',  $order);
        $this->assign('index_list', $index_list);
        $this->setTitle("最近更新");
        $this->display("index");
    }

    function post()
    {
    	$index_list = D('WeiboOperate', 'group')->doSearchTopic('uid=' . $this->mid, 'ctime DESC', $this->mid);
        // 群组名称
        $index_list_gids = getSubByKey($index_list['data'], 'gid');
        $map	   = array();
        $map['id'] = array('IN', $index_list_gids);
        $group_name = $this->group->field('id,name')->where($map)->findAll();
        foreach ($group_name as $v) {
        	$_group_name[$v['id']] = $v['name'];
        }
        foreach ($index_list['data'] as &$v) {
        	$v['group_name'] = $_group_name[$v['gid']];
        }

        $this->assign('index_list', $index_list);
        $this->setTitle("我发布的");
        $this->display('index');
    }

    function replied()
    {
    	$weibo_count = D('WeiboComment', 'group')->field('COUNT(DISTINCT(weibo_id)) AS count')->where("uid={$this->mid} AND isdel=0")->find();
    	$index_list  = D('WeiboComment', 'group')->field('DISTINCT(weibo_id)')->where("uid={$this->mid} AND isdel=0")->findPage(20, $weibo_count['count']);
    	foreach ($index_list['data'] as $v) {
    		$_weibo_ids[] = $v['weibo_id'];
    	}
    	$weibo_list = D('WeiboOperate', 'group')->doSearchTopic('weibo_id IN (' . implode(',', $_weibo_ids) . ')', 'ctime DESC', $this->mid);
    	$index_list['data'] = $weibo_list['data'];
        // 群组名称
        $index_list_gids = getSubByKey($index_list['data'], 'gid');
        $map	   = array();
        $map['id'] = array('IN', $index_list_gids);
        $group_name = $this->group->field('id,name')->where($map)->findAll();
        foreach ($group_name as $v) {
        	$_group_name[$v['id']] = $v['name'];
        }
        foreach ($index_list['data'] as &$v) {
        	$v['group_name'] = $_group_name[$v['gid']];
        }

        $this->assign('index_list', $index_list);
        $this->setTitle("我评论的");
        $this->display('index');
    }

    // 群内评论
    public function comment()
    {
    	D('GroupUserCount')->setZero($this->mid, 'comment');
    	$type = $_GET['type'] == 'send' ? 'send' : 'receive';
    	//$from_app = $_GET['from_app'] == 'other' ? 'other' : 'weibo';
    	$from_app = 'weibo';
    	$comment_list  = D('WeiboComment','group')->getCommentList($type, 'all', $this->mid);

    	$this->assign('comment_list', $comment_list);
    	$this->assign('from_app', $from_app);
    	$this->assign('type', $type);
        $this->setTitle('群内评论');
    	$this->display();
    }

    // @到我的微博
    public function atme()
    {
    	D('GroupUserCount')->setZero($this->mid, 'atme');
        $index_list = D('WeiboOperate','group')->getAtme($this->mid);

        $group_map['id'] = array('IN', array_unique(getSubByKey($index_list['data'], 'gid')));
        $group_info = D('Group', 'group')->field('id,name')->where($group_map)->findAll();
        $group_names = array();
        foreach ($group_info as $value) {
        	$group_names[$value['id']] = $value['name'];
        }

        $this->assign('index_list', $index_list);
        $this->assign('group_names', $group_names);
        $this->setTitle('群内@到我的');
    	$this->display('index');
    }

    // 群内其他消息
    public function bbsNotify()
    {
    	D('GroupUserCount')->setZero($this->mid, 'bbs');
		$list = X('Notify')->get('receive=' . $this->mid . ' AND type LIKE "group\_topic\_%"', 10);
		// 解析表情
		foreach($list['data'] as $k => $v) {
			$list['data'][$k]['title'] = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['title']);
			$list['data'][$k]['body']  = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['body']);
			$list['data'][$k]['other'] = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['other']);
		}
		//$this->assign('userCount', X('Notify')->getCount($this->mid));
		$this->assign($list);
		$this->setTitle('群内帖子消息');
    	$this->display('notify');
    }

    // 发现群组
    public function newIndex()
    {
		U('group/Index/index','', true);
    }

    // 可能感兴趣的群组
    public function interesting()
    {
		$group_list =  $this->group->interestingGroup($this->mid);
		if($group_list['count']==0){
			exit;
		}

		$this->assign('next_page', ($group_list['nowPage'] < $group_list['totalPages'])?($group_list['nowPage'] + 1):'1');
		$this->assign('now_page', $group_list['nowPage']);
		$this->assign('group_list', $group_list['data']);
    	$this->display();
    }

	//群的创建
	function add()
	{
		if (0 == $this->config['createGroup']) {
			// 系统后台配置关闭创建
			$this->error('群组创建已关闭');
		} else if ($this->config['createMaxGroup'] <= $this->group->where('is_del=0 AND uid=' . $this->mid)->count()) {
			// 系统后台配置要求，如果超过，则不可以创建
			$this->error('你不可以再创建了，超过系统规定数目');
		}

		$this->_getSearchKey();

		$this->assign('reTags', D('GroupTag')->getHotTags('recommend'));
        $this->setTitle("创建群组");
		$this->display();
	}

	public function code(){
		if (md5(strtoupper($_POST['verify'])) == $_SESSION['verify']) {
			echo 1;
		}else{
			echo 0;
		}
	}

	//做创建操作
	public function doAdd()
	{
		if (0 == $this->config['createGroup']) {
			// 系统后台配置关闭创建
			$this->error('群组创已经关闭');
		} else if ($this->config['createMaxGroup'] <= $this->group->where('is_del=0 AND uid='.$this->mid)->count()) {
			//系统后台配置要求，如果超过，则不可以创建
			$this->error('你不可以再创建了，超过系统规定数目');
		}

		if (trim($_POST['dosubmit'])) {
			//检查验证码
			if (md5(strtoupper($_POST['verify'])) != $_SESSION['verify']) {
				$this->error('验证码错误');
			}

			$group['uid']   = $this->mid;
			$group['name']  = h(t($_POST['name']));
			$group['intro'] = h(t($_POST['intro']));
			$group['cid0']  = intval($_POST['cid0']);
			// intval($_POST['cid1']) > 0	&& $group['cid1']  = intval($_POST['cid1']);
			$cid1 = D('Category','group')->_digCateNew($_POST);
			intval($cid1) > 0 && $group['cid1'] = intval($cid1);

			if (!$group['name']) {
				$this->error('群组名称不能为空');
			} else if (get_str_length($_POST['name']) > 30) {
				$this->error('群组名称不能超过30个字');
			}

			if (D('Group','group')->where(array('name'=>$group['name']))->find()) {
				$this->error('这个群组名称已被占用');
			}

			if (get_str_length($_POST['intro']) > 200) {
				$this->error('群组简介请不要超过200个字');
			}
// 			if (!preg_replace("/[,\s]*/i", '' ,$_POST['tags']) || count(array_filter(explode(',', $_POST['tags']))) > 5) {
// 				$this->error('标签不能为空或者不要超过五个');
// 			}

			$group['type']  = $_POST['type'] == 'open'?'open':'close';

			$group['need_invite']  = intval($this->config[$group['type'] . '_invite']);  //是否需要邀请
			$group['brower_level'] = $_POST['type'] == 'open'?'-1':'1'; //浏览权限

			$group['openWeibo'] = intval($this->config['openWeibo']);
			$group['openUploadFile'] = intval($this->config['openUploadFile']);
			$group['openBlog'] = intval($this->config['openBlog']);
			$group['whoUploadFile'] = intval($this->config['whoUploadFile']);
			$group['whoDownloadFile'] = intval($this->config['whoDownloadFile']);
			$group['openAlbum'] = intval($this->config['openAlbum']);
			$group['whoCreateAlbum'] = intval($this->config['whoCreateAlbum']);
			$group['whoUploadPic'] = intval($this->config['whoUploadPic']);
			$group['anno'] = intval($_POST['anno']);
			$group['ctime'] = time();

			if (1 == $this->config['createAudit']) {
				$group['status'] = 0;
			}

	        // 群组LOGO
			$options['allow_exts']	=	'jpg,gif,png,jpeg,bmp';
			$options['max_size'] = 2 * 1024 *1024;
			$options['attach_type'] = 'group_logo';
			$data['upload_type'] = 'image';
	        $info	=	model('Attach')->upload($data,$options);
		    if($info['status']) {
			    $group['logo'] = $info['info'][0]['save_path'] . $info['info'][0]['save_name'];
		    }else{
		    	$group['logo'] = 'default.gif';
		    }

		    $gid = $this->group->add($group);

			if($gid) {
				// 积分操作
				X('Credit')->setUserCredit($this->mid,'add_group');
				
				// 把自己添加到成员里面
				$this->group->joingroup($this->mid, $gid, 1, $incMemberCount=true);

				// 添加群组标签
				D('GroupTag')->setGroupTag($_POST['tags'], $gid);

				

				S('Cache_MyGroup_'.$this->mid,null);

				if (1 == $this->config['createAudit']) {
					$this->assign('jumpUrl',U('group/SomeOne/index', array('uid'=>$this->mid,'type'=>'manage')));
					$this->success('创建成功，请等待审核');
				} else {
					$this->assign('jumpUrl',U('group/Invite/create', array('gid'=>$gid,'from'=>'create')));
					$this->success('创建成功');
				}
			} else {
				$this->error('创建失败');
			}
		} else {
			$this->error('创建失败');
		}
	}



	//最新话题
	function newtopic(){
		$this->assign('mymanagegroup',$this->group->mymanagegroup($this->mid));  //我管理的群组
		$this->assign('myjoingroup',$this->group->myjoingroup($this->mid));    //我加入的群组

		$this->assign('newTopic',$this->group->getnewtopic($this->mid));         //最新话题 自己加入的群组和自己创建的
		$this->setTitle($this->siteTitle['my_group_new_topic']);
		$this->display();
	}

	function allTopic(){
		$type = isset($_GET['type']) && in_array($_GET['type'],array('post','reply','collect')) ? $_GET['type'] : '';


		if($type == 'post'){ //发表的话题
			$value = D('Post')->field('tid')->order('ctime DESC')->where('istopic=1 AND is_del=0 AND uid='.$this->mid)->findPage();
			$this->setTitle($this->siteTitle['newTopic_my_post']);
		}elseif($type == 'reply'){ //回复话题
			$value = D('Post')->field('distinct(tid) as tid')->order('ctime DESC')->where('istopic=0 AND is_del=0 AND uid='.$this->mid)->findPage();
			$this->setTitle($this->siteTitle['newTopic_my_reply']);
		}elseif($type == 'collect'){
			$value = D('Collect')->order('addtime DESC')->field('tid')->where('is_del=0')->findPage();
			$this->setTitle($this->siteTitle['newTopic_my_collect']);
		}
		else{  //所有话题
			$value = D('Topic')->order('isrecom DESC,replytime DESC')->field('id as tid')->where('is_del=0')->findPage();
			$this->setTitle($this->siteTitle['newTopic_all']);
		}


		$this->assign('value',$value);
		$this->assign('type',$type);
		$this->display();
	}


	//首页发布话题
	function issue(){
		//获取我所有的群组

		if($myAllGroup){
			$this->assign('myAllGroup',$myAllGroup);
			$this->setTitle($this->siteTitle['issue_topic']);
			$this->display();
		}else{  //如果没有群组，则跳转到创建页面
			$url = __APP__."/Index/add";
			$this->assign('jumpUrl',$url);
			$this->error('你还没有创建群组，请你先创建群组！');
		}
	}



	//最新动态
	function myjoinfeed() {
		$myJoinFeed = $this->group->getMyJoinGroup($this->mid);

		$this->assign('myJoinFeed',$myJoinFeed);
		$this->display();
	}


	// 好友群组
	function flist() {
		$group = $page = '';
		$groupdata = $this->group->friendjoingroup($this->mid);
		if($groupdata) {
			list($group,$page) = $groupdata;
		}

		$this->assign('group',$group);
		$this->assign('page',$page);
		$this->setTitle($this->siteTitle['my_friend_group']);

		$this->display();
	}

	// 搜索群组
	function search() {
		$search_key = $this->_getSearchKey();

		$db_prefix  = C('DB_PREFIX');
		if ($search_key) {
			$tag_id = M('tag')->getField('tag_id', "tag_name='{$search_key}'");
			$map = "g.is_del=0 AND (g.name LIKE '%{$search_key}%' OR g.intro LIKE '%{$search_key}%'";
			if ($tag_id) {
				$map .= ' OR t.tag_id=' . $tag_id;
				$tag_id_score = "+IF(t.tag_id={$tag_id},2,0)";
			}
			$map .= ')';
			$group_count = $this->group->field('COUNT(DISTINCT(g.id)) AS count')
	    							   ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
	    							   ->where($map)
	    							   ->find();
			$group_list = $this->group->field('DISTINCT(g.id),g.name,g.intro,g.logo,g.cid0,g.cid1,g.membercount,g.ctime')
	    							  ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
	    							  ->where($map)
	    							  ->order("IF(LOCATE('{$search_key}',g.name),4,0)+IF(LOCATE('{$search_key}',g.intro),1,0){$tag_id_score} DESC")
	    							  ->findPage(20, $group_count['count']);
		} else if(intval($_GET['cid']) > 0) {
			// 当前分类
			$current_category = D('Category')->field('id,title,pid')->where('id=' . intval($_GET['cid']))->find();
			$this->assign('current_cid', $current_category['id']);
			$map = 'is_del=0';
			// 判断是否未最小分类
			$isMinCate = D('Category')->where('pid='.intval($_GET['cid']))->count();
			if($isMinCate == 0 && $current_category['pid'] > 0) {
				$map .= ' AND cid1=' . $current_category['id'];
				$top_cid = $current_category['pid'];
				// 当前顶级分类
				$topCateInfo = D('Category')->field('id,title')->where("id={$current_category['pid']}")->find();
				$this->assign('top_category', $topCateInfo);
				// 面包屑
				$this->assign('top_path', D('Category')->getPathWithCateId($topCateInfo['id']));
			} else {
				// 获取所有分类下的所有cid
				$allPid = D('Category')->getAllCateIdWithPid($current_category['id']);
				array_push($allPid, $current_category['id']);
				if(!empty($allPid)) {
					$map .= ' AND cid1 IN ('.implode(',', $allPid).')';
				}
				$map .= ' AND cid0=' . $current_category['id'];
				$top_cid = $current_category['id'];
				// 当前顶级分类
				$this->assign('top_category', $current_category);
				// 面包屑
				$this->assign('top_path', D('Category')->getPathWithCateId($current_category['id']));
			}
			// 当前顶级分类的子分类列表
			$son_categorys = D('Category')->field('id,title')->where("pid='{$top_cid}'")->findAll();
			$this->assign('son_categorys', $son_categorys);

			$group_list = $this->group->field('id,name,intro,logo,cid0,cid1,membercount,ctime')
	    							  ->where($map)
	    							  ->findPage();
		}

		foreach ($group_list['data'] as $v) {
			$_cids[] = $v['cid0'];
			$_cids[] = $v['cid1'];
			$_gids[] = $v['id'];
		}
		D('GroupTag')->setGroupTagObjectCache($_gids);

		foreach ($group_list['data'] as &$group) {
            // 群分类
            $group['cname0'] = D('Category')->getField('title', array('id'=>$group['cid0']));
            $group['cname1'] = D('Category')->getField('title', array('id'=>$group['cid1']));
            // 群标签
            $_tags = array();
            $tags  = D('GroupTag')->getGroupTagList($group['id']);
            foreach ($tags as $tag) {
            	$href = U('group/Index/search', array('k'=>urlencode($tag['tag_name'])));
            	$_tags[] = ($tag['tag_name'] == $search_key)?"<a href=\"{$href}\" class=\"cRed\">{$tag['tag_name']}</a>":"<a href=\"{$href}\">{$tag['tag_name']}</a>";
            }
            $group['tags']   = implode('<span class="cGray2"> | </span> ', $_tags);

			if ($search_key) {
				$group['name']	 = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['name']);
				$group['intro']	 = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['intro']);
			}
		}

		$this->assign('group_list', $group_list);
        $this->setTitle("群组搜索");
    	$this->display();
    }
}