<?php
class GroupAction extends BaseAction
{

     function _initialize()
     {
        parent::_initialize();
        $this->assign('current','group');  //头部导航切换
     }

    //群首页
    public function index()
    {
        //关闭群微博时，自动跳转到群帖子页面；如果群帖子也没开启，自动跳转到群成员页面
        if($this->groupinfo['openWeibo']==0 && $this->groupinfo['openBlog']==1){
            redirect(U('group/Topic/index', array('gid' => $this->gid)));
        //如果群帖子也没开启，自动跳转到群成员页面
        }elseif($this->groupinfo['openWeibo']==0 && $this->groupinfo['openBlog']==0){
            redirect(U('group/Member/index', array('gid' => $this->gid)));
        }
        // $data['weibo_menu'] = array(
	       //  ''  => L('all'),
	       //  'original' => L('original'),
        // );
        //Addons::hook('home_index_weibo_tab', array(0 => & $data['weibo_menu'], 'menu' =>  & $data['weibo_menu'], 'position'=>'other'));

  //       //群组微博
  //       $strType = h($_GET['weibo_type']);
  //       $data['type'] = $strType;
  //       $data['list'] = D('WeiboOperate')->getHomeList($this->mid, $this->gid, $strType, '', 10);

		// $_last_weibo = reset($data ['list'] ['data']);
		// $data['lastId'] = $_last_weibo['id'];
		// $_since_weibo = end($data ['list'] ['data']);
		// $data['sinceId'] =  empty($_since_weibo['id']) ? 0 : $_since_weibo['id'];

  //       $this->assign($data);
        //dump($this->groupinfo);exit;
        // 加载微博筛选信息
        $d['feed_type'] = t($_REQUEST['feed_type']) ? t($_REQUEST['feed_type']) : '';
        $d['feed_key']  = t($_REQUEST['feed_key']) ? t($_REQUEST['feed_key']) : '';
        $this->assign($d);
		$this->setTitle($this->groupinfo['name'].' - '.$this->groupinfo['intro']);
        $this->display();
    }

    //查找微博话题
    public function search()
    {
        $data['search_key']    = $this->_getSearchKey('k','group_weibo_search');
        $data['type']          = t($_REQUEST['type']);
        $data['type'] = empty($data['type']) ? "" : $data['type'];
        $data['list']          = D('WeiboOperate', 'group')->doSearch( $data['search_key'], $this->gid, $data['type'] );
        //$data['hotTopic']        = D('WeiboTopic','weibo')->getHot();
        $data['search_key_id'] = D('WeiboTopic', 'group')->getTopicId($data['search_key'], $this->gid);
        //$data['followTopic']   = D('Follow','weibo')->getTopicList($this->mid);
        $this->assign($data);
		$this->setTitle('搜群组: '.$data['search_key']);
        $this->display('index');
    }

    //查看微博详细
    public function detail()
    {
        	if(empty($_GET['feed_id'])){
			$this->error(L('PUBLIC_INFO_ALREADY_DELETE_TIPS'));
		}

		// 获取用户信息
		$user_info = model('User')->getUserInfo($this->uid);
		
		$feedInfo = D('GroupFeed')->get(intval($_GET['feed_id']));
			if(!$feedInfo) $this->error('该微博不存在或已被删除');
			if(intval($_GET['uid']) != $feedInfo['uid']) $this->error('参数错误');
			if($feedInfo['is_audit'] == '0' && $feedInfo['uid']!=$this->mid){
				$this->error('此微博正在审核');exit();
			}
			if($feedInfo['is_del'] == '1'){
				$this->error(L('PUBLIC_NO_RELATE_WEIBO'));exit();
			}
			
			$weiboSet = model('Xdata')->get('admin_Config:feed');
	        $a['initNums']         = $weiboSet['weibo_nums'];
	        $a['weibo_type']       = $weiboSet['weibo_type'];
	        $a['weibo_premission'] = $weiboSet['weibo_premission'];
			$this->assign($a);
			switch ( $feedInfo['app'] ){
        		case 'weiba':
        			$feedInfo['from'] = getFromClient(0 , $feedInfo['app'] , '微吧');
        			break;
        		default:
        			$feedInfo['from'] = getFromClient( $feedInfo['from'] , 'public');
        			break;
        	}
			//$feedInfo['from'] = getFromClient( $feedInfo['from'] , $feedInfo['app']);
			$this->assign('feedInfo',$feedInfo);
		//seo
		$feedContent = unserialize($feedInfo['feed_data']);
        $seo= model('Xdata')->get("admin_Config:seo_feed_detail");
        $replace['content'] = $feedContent['content'];
        $replace['uname'] = $feedInfo['user_info']['uname'];
        $replaces = array_keys($replace);
         foreach($replaces as &$v){
            $v = "{".$v."}";
         }
        $seo['title'] = str_replace($replaces,$replace,$seo['title']);
        $seo['keywords'] = str_replace($replaces,$replace,$seo['keywords']);
        $seo['des'] = str_replace($replaces,$replace,$seo['des']);
        !empty($seo['title']) && $this->setTitle($seo['title']);
        !empty($seo['keywords']) && $this->setKeywords($seo['keywords']);
        !empty($seo['des']) && $this->setDescription($seo['des']);

			
		$this->display();
    }
    /**
     * 删除微博操作，用于AJAX
     * @return json 删除微博后的结果信息JSON数据
     */
    public function removeFeed() {
    	$return = array('status'=>0,'data'=>L('PUBLIC_DELETE_FAIL'));			// 删除失败
    	$feed_id = intval($_POST['feed_id']);
    	$feed = D('GroupFeed')->getFeedInfo($feed_id);
    	// 不存在时
    	if(!$feed){
    		exit(json_encode($return));
    	}
    	// 非作者时
    	if($feed['uid']!=$this->mid){
    		// 没有管理权限不可以删除
    		if(!CheckPermission('core_admin','feed_del')){
    			exit(json_encode($return));
    		}
    		// 是作者时
    	}else{
    		// 没有前台权限不可以删除
    		if(!CheckPermission('core_normal','feed_del')){
    			exit(json_encode($return));
    		}
    	}
    	// 执行删除操作
    	$return = D('GroupFeed')->doEditFeed($feed_id, 'delFeed', '',$this->mid);
    	// 删除失败或删除成功的消息
    	$return['data'] = ($return['status'] == 0) ? L('PUBLIC_DELETE_FAIL') : L('PUBLIC_DELETE_SUCCESS');
    	// 批注：下面的代码应该挪到FeedModel中
    	// 删除话题相关信息
//     	$return['status'] == 1 && model('FeedTopic')->deleteWeiboJoinTopic($feed_id);
    	// 删除@信息
    	D('GroupAtme')->setAppName('group')->setAppTable('group_feed')->deleteAtme(null, $feed_id, null);
    	exit(json_encode($return));
    }
    // 加入该群
    public function  joinGroup()
    {
        if (isset($_POST['addsubmit'])) {
            $level = 0;
            $incMemberCount = false;
            if ($this->is_invited) {
                M('group_invite_verify')->where("gid={$this->gid} AND uid={$this->mid} AND is_used=0")->save(array('is_used'=>1));
                if (0 === intval($_POST['accept'])) {
                    // 拒绝邀请
                    exit;
                } else {
                    // 接受邀请加入
                    $level = 3;
                    $incMemberCount = ture;
                }
            } else if ($this->groupinfo['need_invite'] == 0) {
                // 直接加入
                $level = 3;
                $incMemberCount = ture;
            } else if ($this->groupinfo['need_invite'] == 1) {
                // 需要审批，发送私信到管理员
                $level = 0;
                $incMemberCount = false;
                // 添加通知
                $toUserIds = D('Member')->field('uid')->where('gid='.$this->gid.' AND (level=1 or level=2)')->findAll();
                foreach ($toUserIds as $k=>$v) {
                    $toUserIds[$k] = $v['uid'];
                }

                $message_data['title']   = "申请加入群组 {$this->groupinfo['name']}";
                $message_data['content'] = "你好，请求你批准加入“{$this->groupinfo['name']}” 群组，点此"
                                         ."<a href='".U('group/Manage/membermanage', array('gid'=>$this->gid,'type'=>'apply'))."' target='_blank'>"
                                         . U('group/Manage/membermanage', array('gid'=>$this->gid,'type'=>'apply')) . '</a>进行操作。';
                $message_data['to']      = $toUserIds;
                $res = model('Message')->postMessage($message_data,  $this->mid);

            }

            $result = D('Group')->joinGroup($this->mid, $this->gid, $level, $incMemberCount, $_POST['reason']);   //加入
            S('Cache_MyGroup_'.$this->mid,null);
            exit;
        }

        parent::base();

        $this->assign('joinCount', D('Member')->where("uid={$this->mid} AND level>1")->count());
        $member_info = D('Member')->field('level')->where("gid={$this->gid} AND uid={$this->mid}")->find();
        $this->assign('isjoin', $member_info['level']);  // 是否加入过或加入情况
        $this->display();
    }

    //退出该群对话框
    function quitGroupDialog() {
        $this->assign('gid',$this->gid);
        $this->display();
    }

    //退出该群
    function quitGroup() {
        if(iscreater($this->mid,$this->gid) || !$this->ismember) { echo '0';exit;} //$this->error('你没有权限'); //群组不可以退出
        $res = D('Member')->where("uid={$this->mid} AND gid={$this->gid}")->delete();  //用户退出
        if($res){
        	$map['uid'] = $this->mid;
        	$map['gid'] = $this->gid;
        	D('GroupUserCount')->where($map)->delete();
            D('Group')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
            // 积分操作
            X('Credit')->setUserCredit($this->mid, 'quit_group');
            S('Cache_MyGroup_'.$this->mid,null);
            echo '1';
            exit;
        }
    }


    //删除该群
    function delGroup() {
        if (md5(strtoupper($_POST['verify'])) != $_SESSION['verify']) {
            exit('验证码错误');
        }
        if(!iscreater($this->mid,$this->gid))  exit('你没有权限');
        D('Group')->remove($this->gid);
        S('Cache_MyGroup_'.$this->mid, NULL);
        exit('1');
    }


    //删除群组对话框
    function delGroupDialog() {

        $this->assign('gid',$this->gid);
        $this->display();
    }

    function addShare_check(){

        $result = 1;

        $aimId = intval($_REQUEST['aimId']);
        $this->assign('aimId',$aimId);

        $test = $this->api->share_isForbid($this->mid,8,$aimId);

        if($test==-1){
            $result = -2;
        }

        echo $result;
    }
    /**
     * 分享控制
     * @return void
     */
    public function shareFeed(){
    	$shareInfo['sid'] = intval($_GET['sid']);
    	$shareInfo['stable'] = t($_GET['stable']);
    	$shareInfo['initHTML']  = h($_GET['initHTML']);
    	$shareInfo['curid'] 	= t($_GET['curid']);
    	$shareInfo['curtable']  = t($_GET['curtable']);
    	$shareInfo['appname']	= t($_GET['appname']);
    	$shareInfo['cancomment'] = intval($_GET['cancomment']);
    	$shareInfo['is_repost'] = intval($_GET['is_repost']);
    	if(empty($shareInfo['stable']) || empty($shareInfo['sid'])){
    		echo L('PUBLIC_TYPE_NOEMPTY'); exit();
    	}
    	if(!$oldInfo = model('Source')->getSourceInfo($shareInfo['stable'],$shareInfo['sid'],false,$shareInfo['appname'])){
    		echo L('PUBLIC_INFO_SHARE_FORBIDDEN');exit();
    	}
    	empty($shareInfo['appname']) && $shareInfo['appname'] = $oldInfo['app'];
    	if($shareInfo['appname'] != '' && $shareInfo['appname'] != 'public'){
    		addLang($shareInfo['appname']);
    	}
    	if(empty($shareInfo['initHTML']) && !empty($shareInfo['curid'])){
    		//判断是否为转发的微博
    		if($shareInfo['curid'] != $shareInfo['sid'] && $shareInfo['is_repost']==1){
    			
    			$curInfo = model('Source')->getSourceInfo($shareInfo['curtable'],$shareInfo['curid'],false,$shareInfo['appname']);
    			$userInfo = $curInfo['source_user_info'];
    			// if($userInfo['uid'] != $this->mid){	//分享其他人的分享，非自己的
    			$shareInfo['initHTML'] = ' //@'.$userInfo['uname'].'：'.$curInfo['source_content'];
    			// }
    			$shareInfo['initHTML'] = str_replace(array("\n", "\r"), array('', ''), $shareInfo['initHTML']);
    		}
    	}
    	if ( !CheckPermission('core_normal','feed_comment') ){
    		$shareInfo['cancomment'] = 0;
    	}
    	$shareInfo['shareHtml'] =  !empty($oldInfo['shareHtml'])  ?  $oldInfo['shareHtml'] : '';
    	$weiboSet = model('Xdata')->get('admin_Config:feed');
    	$canShareFeed = in_array('repost',$weiboSet['weibo_premission']) ? 1  : '0';
    	$this->assign('canShareFeed',$canShareFeed);
    	$this->assign('initNums',$weiboSet['weibo_nums']);
    	$this->assign('shareInfo',$shareInfo);
    	$this->assign('oldInfo',$oldInfo);
    	$this->display();
    }
    /**
     * 分享/转发微博操作，需要传入POST的值
     * @return json 分享/转发微博后的结果信息JSON数据
     */
    public function doShareFeed()
    {
    	if ( !$this->ismember ){
    		$return = array('status'=>0,'data'=>'抱歉，您不是该群成员');
    		exit(json_encode($return));
    	}
    	// 获取传入的值
    	$post = $_POST;
    	// 安全过滤
    	foreach($post as $key => $val) {
    		$post[$key] = t($post[$key]);
    	}
    	// 判断资源是否删除
    	if(empty($post['curid'])) {
    		$map['feed_id'] = $post['sid'];
    	} else {
    		$map['feed_id'] = $post['curid'];
    	}
    	$map['is_del'] = 0;
    	$isExist = D('GroupFeed')->where($map)->count();
    	if($isExist == 0) {
    		$return['status'] = 0;
    		$return['data'] = '内容已被删除，转发失败';
    		exit(json_encode($return));
    	}
    	// 过滤内容值
    	$post['body'] = filter_keyword(h($post['body']));
    	// 进行分享操作
    	$return = D('GroupShare')->shareFeed($post, 'share');
    	if($return['status'] == 1) {
    		$app_name = $_POST['app_name'];
    
    		// 添加积分
//     		if($app_name == 'public'){
//     			model('Credit')->setUserCredit($this->uid,'forward_weibo');
//     			//微博被转发
//     			$suid =  D('GroupFeed')->where($map)->getField('uid');
// //     			model('Credit')->setUserCredit($suid,'forwarded_weibo');
//     		}
//     		if($app_name == 'weiba'){
// //     			model('Credit')->setUserCredit($this->uid,'forward_topic');
//     			//微博被转发
// //     			$suid =  D('GroupFeed')->where('feed_id='.$map['feed_id'])->getField('uid');
// //     			model('Credit')->setUserCredit($suid,'forwarded_topic');
//     		}
    			
    		$this->assign($return['data']);
    		// 微博配置
    		$weiboSet = model('Xdata')->get('admin_Config:feed');
    		$this->assign('weibo_premission', $weiboSet['weibo_premission']);
    		$return['data'] =  $this->fetch('PostFeed');
    	}
    	exit(json_encode($return));
    }
    /**
     * 分享信息
     * @return mix 分享状态和提示
     */
    public function shareMessage()
    {
    	$post = $_POST;
    	// 安全过滤
    	foreach($post as $key => $val) {
    		$post[$key] = t($post[$key]);
    	}
    	// 判断资源是否存在
    	// 判断资源是否删除
    	if(empty($post['curid'])) {
    		$map['feed_id'] = $post['sid'];
    	} else {
    		$map['feed_id'] = $post['curid'];
    	}
    	$map['is_del'] = 0;
    	$isExist = D('GroupFeed')->where($map)->count();
    	if($isExist == 0) {
    		$return['status'] = 0;
    		$return['data'] = '内容已被删除，分享失败';
    		exit(json_encode($return));
    	}
    	// 过滤数据，安全性
    	foreach($post as $key => $val) {
    		$post[$key] = t($post[$key]);
    	}
    
    	exit(json_encode(D('GroupShare')->shareMessage($post)));
    }
    function addShare(){
        $aimId = intval($_REQUEST['aimId']);
        $this->assign('aimId',$aimId);
        $group = D('group')->where("id='$aimId'")->field('name')->find();

        $this->assign('name',$group['name']);
        $this->assign($group);
        $this->assign('mid',$this->mid);
        $this->display();
    }

    function doaddShare(){
        $type['typeId'] = 8;
        $type['typeName'] = '群组';
        $type['alias'] = 'group';

        $info = h($_REQUEST['info']);
        $aimId = intval($_REQUEST['aimId']);

        $field = 'uid,name,logo,cid0,membercount';
        $data = D('group')->where("id='$aimId'")->field($field)->find();
        $data['logo'] = group_get_photo_url($data['logo']);
        $data['catagory'] = D('Category')->where("id=".$data['cid0'])->getField('title');

        //$data['name'] = h($_REQUEST['name']);
        $fids = $_REQUEST['fids'];


        $result = $this->api->share_addShare($type,$aimId,$data,$info,0,$fids);
        echo $result;
    }
    /**
     * 我的评论中，回复弹窗页面
     */
    public function reply() {
    	$var = $_GET;
    
    	$var['initNums'] = model('Xdata')->getConfig('weibo_nums', 'feed');
    	$var['commentInfo'] = D('GroupComment')->getCommentInfo($var['comment_id'], false);
    	$var['canrepost']  = $var['commentInfo']['table'] == 'feed'  ? 1 : 0;
    	$var['cancomment'] = 1;
    	// 获取原作者信息
    	$rowData = D('GroupFeed')->get(intval($var['commentInfo']['row_id']));
    	$appRowData = D('GroupFeed')->get($rowData['app_row_id']);
    	$var['user_info'] = $appRowData['user_info'];
    	// 微博类型
    	$var['feedtype'] = $rowData['type'];
    	// $var['cancomment_old'] = ($var['commentInfo']['uid'] != $var['commentInfo']['app_uid'] && $var['commentInfo']['app_uid'] != $this->uid) ? 1 : 0;
    	$var['initHtml'] = L('PUBLIC_STREAM_REPLY').'@'.$var['commentInfo']['user_info']['uname'].' ：';		// 回复
    
    	$this->assign($var);
    	$this->display();
    }

}