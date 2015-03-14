<?php
/**
 * IndexAction
 * 活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {
	private $appName;
    private $event;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
		//应用名称
		global $ts;
		$this->appName = $ts['app']['app_alias'];
        //设置活动的数据处理层
        $this->event = D( 'Event','event' );
        //读取推荐列表
        $is_hot_list = $this->event->getHotList();
        $this->assign('is_hot_list',$is_hot_list);
        // 活动分类
        $cate = D( 'EventType' )->getType();
        $this->assign( 'category',$cate );
    }

    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
		$order = NULL;
        switch( $_GET['order'] ) {
        	case 'new':    //最新排行
       			$order = 'cTime DESC';
       			$this->setTitle('最新' . $this->appName);
                break;
            case 'following':    
                //关注的人的活动
				// $following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
				// foreach($following as $v) {
				// 	$in_arr[] = $v['fid'];
				// }
                // $map['uid'] = array('in',$in_arr);
            // 关注的活动
                $map['uid'] = $this->mid;
                $map['action'] = 'attention';
                $eventIds = M('event_user')->where($map)->field('eventId')->findAll();unset($map);
                foreach ($eventIds as $key => $value) {
                    $ids[$key] = $value['eventId'];
                }
                $map['id'] = array('IN',$ids);
                $this->setTitle('我关注的' . $this->appName);
                break;
	         default:      //默认热门排行
                $order = 'joinCount DESC,attentionCount DESC,cTime DESC';
                $this->setTitle('热门' . $this->appName);
        }

        //查询
        $title = t($_POST['title']);
        if ($_POST['title']) {
        	$map['title'] = array( 'like',"%".t($_POST['title'])."%" );
        	$this->assign('searchkey',$title);
        	$this->setTitle('搜索' . $this->appName);
        }
        if ($_GET['cid']) {
        	$map['type']  = intval($_GET['cid']);
        	$this->setTitle('分类浏览');
        }

        $result  = $this->event->getEventList($map,$order,$this->mid,$_GET['order']);
		$this->assign($result);
        $this->display();
    }

    /**
     * personal
     * 个人列表
     * @access public
     * @return void
     */
    public function personal() {
    	if ($this->uid == $this->mid)
    		$name = '我';
    	else
    		$name = getUserName($this->uid);

        switch( $_GET['action'] ) {
            case 'join':    //参与的
                $map_join['action'] = 'joinIn';
                $map_join['status'] = 1;
                $map_join['uid']    = $this->uid;
                $eventIds  = D('EventUser')->field('eventId')->where($map_join)->findAll();
                foreach($eventIds as $v) {
                    $in_arr[] = $v['eventId'];
                }
                $map['id'] = array('in',$in_arr);
                $this->setTitle("{$name}参与的{$this->appName}");
                break;
            case 'att':    //关注的
                $map_att['action'] = 'attention';
                $map_att['status'] = 1;
                $map_att['uid']    = $this->uid;
                $eventIds  = D('EventUser')->field('eventId')->where($map_att)->findAll();
                foreach($eventIds as $v) {
                    $in_arr[] = $v['eventId'];
                }
                $map['id'] = array('in',$in_arr);
                $this->setTitle("{$name}关注的{$this->appName}");
                break;
         	default:      //发起的
                $map['uid'] = $this->uid;
                $this->setTitle("{$name}发起的{$this->appName}");
        }
        $result  = $this->event->getEventList($map,'id DESC',$this->mid);
        $this->assign($result);
        $this->assign('name', $name);
        $this->display();
    }

    /**
     * addEvent
     * 发起活动
     * @access public
     * @return void
     */
    public function addEvent() {
        $this->_createLimit($this->mid);

        $typeDao = D( 'EventType' );
        $this->assign('type',$typeDao->getType());
        $this->setTitle('发起' . $this->appName);
        $this->display();
    }
	/**
     * _creatLimit
     * 条件限制判断
     * @access public
     * @return void
     */
    private function _createLimit($uid){
		$config = event_getConfig();
		if(!$config['canCreate']){
			$this->error('禁止发起'.$this->appName);
		}
    	if($config['credit']){
			$userCredit = model('Credit')->getUserCredit($uid);
    		if($userCredit['credit'][$config['credit_type']]['value']<$config['credit']){
    			$this->error($userCredit['credit'][$config['credit_type']]['alias'].'小于'.$config['credit'].'点，不允许发起'.$this->appName);
    		}
    	}
        $timeLimit = $config['limittime'];
    	if($timeLimit){
    	   $regTime = M('User')->getField('ctime',"uid={$uid}");
    	   $difference = (time()-$regTime)/3600;
    	   if($difference<$timeLimit){
    	       $this->error('帐号注册时间小于'.$config['limittime'].'小时，不允许发起'.$this->appName);
    	   }
    	}
    }
    /**
     * doAddEvent
     * 添加活动
     * @access public
     * @return void
     */
    public function doAddEvent() {
        $this->_createLimit($this->mid);

        $map['title']      = t($_POST['title']);
        $map['address']    = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['limitCount']));
        $map['type']       = intval($_POST['type']);
        $map['explain']    = h($_POST['explain']);
        $map['contact']    = t($_POST['contact']);
        $map['deadline']   = $this->_paramDate($_POST['deadline']);
        $map['sTime']      = $this->_paramDate($_POST['sTime']);
        $map['eTime']      = $this->_paramDate($_POST['eTime']);
        $map['uid']        = $this->mid;
        //$map['name']       = getUserName($this->mid);
        if(!t($_POST['title'])){
            $this->error("活动标题不能为空");
        }
        if(!t($_POST['address'])){
            $this->error("活动地址不能为空");
        }
        if(intval($_POST['type']) == 0){
            $this->error("请选择活动分类");
        }
        if( $map['sTime'] > $map['eTime'] ) {
            $this->error( "结束时间不得早于开始时间" );
        }
		if( $map['sTime'] < mktime(0, 0, 0, date('M'), date('D'), date('Y')) ) {
            $this->error( "开始时间不得早于当前时间" );
        }
        if( $map['deadline'] < time() ) {
            $this->error( "报名截止时间不得早于当前时间" );
        }
        if( $map['deadline'] > $map['eTime'] ) {
        	$this->error('报名截止时间不能晚于结束时间');
        }

		$string=iconv("UTF-8","GBK", t($map['explain']));
        $length = strlen($string);
        if($length < 20){
        	$this->error('介绍不得小于20个字符');
        }
        	
        //处理省份，市，区
        list( $opts['province'],$opts['city'],$opts['area'] ) = explode(" ",safe($_POST['city']));

        //得到上传的图片
        $data['attach_type'] =  'event';
        $data['upload_type'] =  'image';
        $cover = model('attach')->upload($data);

        //处理选项
        $opts['cost']        = intval( $_POST['cost'] );
        $opts['costExplain'] = t( $_POST['costExplain'] );
        $opts['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $friend              = isset( $_POST['friend'] )?1:0;
        $allow               = isset( $_POST['allow'] )?1:0;
        $opts['opts']        = array( 'friend'=>$friend,'allow'=>$allow );
        if( $addId = $this->event->doAddEvent($map, $opts, $cover)) {
            $cover['status'] && $attachid = $cover['info'][0]['attach_id'];
        	model('Feed')->syncToFeed('我发布了一个新活动“'.t($_POST['title']).'”,详情请点击'.U('event/Index/eventDetail',array('id'=>$addId,'uid'=>$this->mid)),$this->mid,$attachid,$from);
            model('Credit')->setUserCredit($this->mid,'add_event');
			// $this->assign('jumpUrl',U('/Index/eventDetail',array('id'=>$addId,'uid'=>$this->mid)));
            // $this->success($this->appName.'添加成功');
            $res['id'] = $addId;
            $res['uid'] = $this->mid;
            exit($this->ajaxReturn($res, $this->appName.'发布成功', 1));
        }else{
			$this->error($this->appName.'添加失败');
		}
    }

    /**
     * doAction
     * 参与活动
     * @access public
     * @return void
     */
    public function doAction() {
        $data['id']   = intval( $_POST['id'] );
        $data['uid']  = $this->mid;
        $allow        = intval( $_POST['allow'] );
        $data['action'] = t( $_POST['action'] );
        $this->event->setMid( $this->mid );
        //检测id和uid是否为0
        if( false == $this->checkUrl( $data ) ) {
            echo -4;
        }
        // 判断活动人数是否已满不能参加
        $limitCount = $this->event->where( 'id ='.$data['id'] )->field('limitCount,eTime')->find();
        if($limitCount['limitCount'] <= 0){
            if($allow){
                echo -5;
            }
        }
        if($limitCount['eTime'] < time()){
            echo -6;
        }
        if($allow){
            echo $this->event->doAddUser( $data,$allow );
        }
        if(!$allow){
            echo $this->event->doAddUser( $data,$allow );
        }
        // echo  111;
    }

    /**
     * doAction
     * 取消参加
     * @access public
     * @return void
     */
    public function doDelAction() {
        $data['id']     = intval( $_POST['id'] );
        $data['uid']    = $this->mid;
        $allow          = intval( $_POST['allow'] );
        $data['action'] = t( $_POST['action'] );
        //检测id和uid是否为0
        if( false == $this->checkUrl( $data ) ) {
            echo -4;
            return;
        }
        echo trim($this->event->doDelUser( $data ));

    }

    /**
     * eventDetail
     * 活动详细页
     * @access public
     * @return void
     */
    public function eventDetail() {
        $id   = intval( $_GET['id'] );
        $uid  = intval( $_GET['uid'] );
        $test = array( $id,$uid );
        //检测id和uid是否为0
        if( false == $this->checkUrl( $test ) ) {
            $this->assign('jumpUrl',U('event/Index/index'));
            $this->error( "错误的访问页面，请检查链接" );
        }

        $this->event->setMid( $this->mid );
        if($result = $this->event->getEventContent( $id,$uid )) {
            // 图片大小控制
            $result['cover']     = getCover($result['coverId'],200,200);
        	//计算待审核人数
	        if( $this->mid == $result['uid'] )
	            $result['verifyCount'] = D( 'EventUser' )->where( "status = 0 AND action='joinIn' AND eventId ={$result['id']}" )->count();
            $this->assign($result);
            $this->assign('event', $result);
            $attentionUids = getSubByKey($result['attention'],'uid');
            $memberUids = getSubByKey($result['member'],'uid');
            // $uids = array_unique(array_merge(array($result['uid']),$attentionUids,$memberUids));

            // if($result['uid'] == $this->mid){
            //     $uids = $this->mid;
            // }
            $this->assign('user_info',model('User')->getUserInfoByUids($uids));
            $this->assign('user_info',model('User')->getUserInfoByUids($result['uid']));
            $this->setTitle($result['title'].' - '.$result['time'].' - '.$result['city'].' - '.$result['address'].' - '.$result['type']);
            $this->display();
        }else {
            $this->assign('jumpUrl',U('event/Index/index'));
            $this->error( '错误的访问页面，请检查链接' );
        }
    }

    /**
     * member
     * 活动成员
     * @access public
     * @return void
     */
    public function member() {
        $id = intval( $_GET['id'] );
        //检查url参数
        if( false == $this->checkUrl( array( $id ) ) ) {
            $this->error( "错误的访问页面，请检查链接" );
        }

        //检查id是否存在
        if( false == $event = $this->event->where( 'id='.$id )->field( 'uid,id,title,joinCount,attentionCount,optsId' )->find() )
            $this->error( $this->appName.'已删除或取消' );

        $this->assign( $event );

        //计算待审核人数
        if( $this->mid == $event['uid'] )
            $verifyCount = D( 'EventUser' )->where( "status = 0 AND action='joinIn' AND eventId ={$event['id']}" )->count();
        $this->assign( 'verifyCount',$verifyCount );

        //获得action对应的成员
        switch( $_GET['action'] ) {
            case "att":
                $map['action'] = 'attention';
                $map['status'] = 1;
                break;
            case "join":
                $map['action'] = 'joinIn';
                $map['status'] = 1;
                break;
            case 'verify':
                $map['action'] ='joinIn';
                $map['status'] = 0;
                break;
            default:
                $map['action'] = array( 'in',"'admin','attention','joinIn'" );
                $map['status'] = 1;
        }
        $map['eventId'] = $event['id'];
        //取得成员列表
        $result = $this->event->getMember($map,$event['uid']);
        $this->assign( $result );
        $this->setTitle('成员列表');
        $this->display();
    }

    /**
     * edit
     * 编辑活动
     * @access public
     * @return void
     */
    public function edit(  ) {
        $id = intval( $_GET['id'] );
        $uid = $this->event->where( 'id='.$id )->getField( 'uid' );
        if( $uid != $this->mid ) {
            $this->error( '您没有权限编辑这个'.$this->appName ) ;
        }

        $typeDao = D( 'EventType' );
        $this->event->setMid( $this->mid );
        if($result = $this->event->getEventContent( $id,$uid )) {
            $this->assign( $result );
            $this->display('edit');
        }else {
            $this->error( '错误的访问页面，请检查链接' );
        }

    }

    /**
     * doEditEvent
     * 修改活动
     * @access public
     * @return void
     */
    public function doEditEvent() {
        $id['id'] = intval( $_POST['id'] );
        //判断作者
        if ( !CheckAuthorPermission( D('Event') , $id['id'] ) ){
        	$this->error( '对不起，您没有权限进行该操作' );
        }
        $id['optsId'] = intval( $_POST['optsId'] );
        $map['title']      = t($_POST['title']);
        $map['address']    = t($_POST['address']);
        $map['limitCount'] = intval(t( $_POST['limitCount'] ));
        $map['type']       = intval($_POST['type']);
        $map['explain']    = h($_POST['explain']);
        $map['contact']    = t($_POST['contact']);
        $map['deadline'] = $deadline = $this->_paramDate( $_POST['deadline'] );
        $map['sTime']    = $stime = $this->_paramDate($_POST['sTime']);
        $map['eTime']    = $etime = $this->_paramDate($_POST['eTime']);

        if(!t($_POST['title'])){
            $this->error("活动标题不能为空");
        }
        if(!t($_POST['address'])){
            $this->error("活动地址不能为空");
        }
        if(intval($_POST['type']) == 0){
            $this->error("请选择活动分类");
        }
        if( $stime > $etime) {
            $this->error( "结束时间不得早于开始时间" );
        }
        if( $deadline > $etime) {
            $this->error( "报名截止时间不能晚于结束时间" );
        }

        $string=iconv("UTF-8","GBK", t($map['explain']));
        $length = strlen($string);
        if($length < 20){
            $this->error('活动介绍不得小于20个字符');
        }

        //处理省份，市，区
        list( $opts['province'],$opts['city'],$opts['area'] ) = explode( " ",safe($_POST['city']));

        //得到上传的图片
        $config     =   event_getConfig();
 		$options['userId']		=	$this->mid;
		$options['max_size']    =   $config['photo_max_size'];
		$options['allow_exts']	=	$config['photo_file_ext'];
       
        if(!empty($_FILES['cover']['tmp_name'])){
            if( !is_image_file($_FILES['cover']['name']) ){
                $this->error( "封面不是图片文件" );
                exit;
            }
            $data['attach_type'] =  'event';
            $data['upload_type'] =  'image';
            $cover = model('attach')->upload($data);
        }

        //处理选项
        $opts['cost']        = intval( $_POST['cost'] );
        $opts['costExplain'] = t( $_POST['costExplain'] );
        $friend              = isset( $_POST['friend'] )?1:0;
        $allow                = isset( $_POST['allow'] )?1:0;
        $opts['opts']        = array( 'friend'=>$friend,'allow'=>$allow );

        if( $this->event->doEditEvent($map, $opts, $cover, $id )) {
        	// $this->assign('jumpUrl',U('//eventDetail',array('id'=>$id['id'],'uid'=>$this->mid)));
         //    $this->success($this->appName.'修改成功！');
            $res['id'] = intval( $_POST['id'] );
            $res['uid'] = $this->mid;
            return $this->ajaxReturn($res, $this->appName.'发布成功', 1);
        }
    }

    /**
     * doEndAction
     * 结束活动
     * @access public
     * @return void
     */
    public function doEndAction() {
        $id = $_POST['id'];
        $this->event->setMid( $this->mid );

        //检查安全性，防止非管理员访问
        $uid = $this->event->where( 'id='.$id )->getField( 'uid' );
        if( $uid != $this->mid ){
            echo -1;
        }
        if($this->event->where( 'id='.$id )->setField( 'eTime',time() )){
            echo 1;
        }else{
            echo 0;
        }
        // echo $this->event->doEditData( time(),$id );
    }

    /**
     * doAgreeAction
     * 同意申请
     * @access public
     * @return void
     */
    public function doAgreeAction() {
        $data['id']      = intval( $_POST['id'] );
        $data['eventId'] = intval( $_POST['eventId'] );
        $data['uid']     = intval( $_POST['uid'] );
        $join_uid = intval($_POST['join_uid']);
        $result = $this->event->getEventContent( $data['eventId'],$data['uid'] );
        $map['eventId']  = intval($_POST['eventId']);
        $map['status']   = 0;
        $user   = M('event_user')->where($map)->findAll();
        $userCount = count($user);

        //检查操作权限
        if( $this->mid != D('Event')->getField('uid',"id={$data['eventId']}") ){
            echo  -4;
            return;
        }
        if($result['lc'] <=0 && $result['lc'] != '无限制'){
            echo -5;
            return ;
        }
        //检测id和uid是否为0
        if( false == $this->checkUrl( $data ) ) {
            echo -4;
            return;
        }
        $res = $this->event->doArgeeUser( $data);
        if($res){
            model('Credit')->setUserCredit($join_uid,'join_event');
        }
        echo trim($res);
    }

    /**
     * doAdminAction
     * 删除成员
     * @access public
     * @return void
     */
    public function doAdminAction() {
        $admin          = t( $_POST['admin'] );
        $data['uid']    = intval( $_POST['uid'] );      //被操作的用户
        $data['id']     = intval( $_POST['eventId'] );  //被操作的活动
        $data['action'] = t( $_POST['action'] );    //被操作的用户的动作

        //检查操作权限
        if( $this->mid != D('Event')->getField('uid',"id={$data['id']}") ){
        	echo  -4;
        	return;
        }

        //检查链接合法性
        if( !$this->checkUrl( $data ) ) {
            echo -4;
            return;
        }
        switch ( $admin ) {
            case 'user':   //成员管理
                echo $this->event->doDelUser( $data );
                return;
                break;
            default:
        //TODO 更多的操作
        }

    }

        /**
         * doDeleteEvent
         * 删除活动
         * @access public
         * @return void
         */
        public function doDeleteEvent(){
            $eventid['id']  = intval($_REQUEST['id']);    //要删除的id.
            $eventid['uid'] = $this->mid;
            if ( !CheckAuthorPermission( D('Event') , $eventid['id'] ) ){
            	echo 0;exit;
            }
            
            $result         = $this->event->doDeleteEvent($eventid);
            if( false != $result){
                echo 1;
            }else{
                echo 0;               //删除失败
            }
        }

    /**
     * 分享活动
     */
    public function ShareEvent(){
        $eventId = intval($_POST['eventId']);
        // 判断活动是否结束
        $limitCount = $this->event->where( 'id ='.$eventId )->field('limitCount,eTime')->find();
        if($limitCount['eTime'] < time()){
            echo -6;
        }else{
            $eventDetail = $this->event->find($eventId);
            if(model('Feed')->shareToFeed('我分享了一个活动“'.$eventDetail['title'].'”,详情请点击'.U('event/Index/eventDetail',array('id'=>$eventId,'uid'=>$eventDetail['uid'])),$this->mid,$eventDetail['coverId'],0)){
                echo 1;
            }else{
                echo 0;
            }  
        }
        
    }

    /**
     * _paramDate
     * 解析日期
     * @param mixed $date
     * @access private
     * @return void
     */
    private function _paramDate( $date ) {
        $date_list = explode( ' ',safe($date) );
        list( $year,$month,$day ) = explode( '-',$date_list[0] );
        list( $hour,$minute,$second ) = explode( ':',$date_list[1] );
        return mktime( $hour,$minute,$second,$month,$day,$year );
    }

    /**
     * checkUrl
     * 检查url参数是否合法
     * @param array $data
     * @access public
     * @return void
     */
    public function checkUrl(array $data ) {
        $count1 = count( $data );
        $count2 = count( array_filter( $data ));
        if( $count2 < $count1 ) {
            return false;
        }else {
            return true;
        }
    }

    /**
     * upload
     * 上传图片
     * @access public
     * @return void
     */
    /*public function upload() {
        $eventId = intval($_GET['eventId']);
        //检查空
        if( empty($eventId) || 0 === $eventId ) {
            $this->error( '没有传入' );
        }

        //检查是否有有这个活动
        if( false === $event = $this->event->where( 'id='.$eventId )->field( 'id,title,uid' )->find() ) {
            $this->error( '没有您请求的页面，请检查链接' );
        }

        //检查是否访问者有权限上传图片
        $action = $this->event->hasMember( $this->mid,$eventId );

        switch ( event_getConfig( 'membel' ) ) {
            case 0:
                ('attention' == $action['action'] || false == $action || 1 != $action['status']) && $this->error( "只允许{$this->appName}参与者上传照片" );
                break;
            case 1;
                ('admin' != $action['action'] || false == $action) && $this->error( "只允许{$this->appName}创建者者上传照片" );
                break;
            default:
                $this->error( '错误的配置信息' );
        }
        $this->assign( $event );

        $this->display();

    }*/

    /**
     * upload
     * flash上传图片
     * @access public
     * @return void
     */
    /*public function flash() {
        $eventId = intval($_GET['eventId']);
        //检查空
        if( empty($eventId) || 0 === $eventId ) {
            $this->error( '没有传入' );
        }

        //检查是否有有这个活动
        if( false === $event = $this->event->where( 'id='.$eventId )->field( 'id,title,uid' )->find() ) {
            $this->error( '没有您请求的页面，请检查链接' );
        }

        //检查是否访问者有权限上传图片
        $action = $this->event->hasMember( $this->mid,$eventId );

        switch ( event_getConfig( 'membel' ) ) {
            case 0:
                ('attention' == $action['action'] || false == $action || 1 != $action['status']) && $this->error( "只允许{$this->appName}参与者上传照片" );
                break;
            case 1;
                ('admin' != $action['action'] || false == $action) && $this->error( "只允许{$this->appName}创建者者上传照片" );
                break;
            default:
                $this->error( '错误的配置信息' );
        }
        $this->assign( $event );

        $this->display();
    }*/

    /**
     * upload_muti_pic
     * 普通上传图片
     * @access public
     * @return void
     */
    /*public function upload_muti_pic(  ) {
    //上传图片
        $cover = $this->api->attach_upload( 'event_photo' );

        $dao   = D( 'EventPhoto' );
        $dao->setMid( $this->mid );

        $data  = array();
        //存储图片
        if( true === $cover['status'] &&
            $result = $dao->addPhoto( $cover['info'] ,              //相册信息
            intval( $_POST['eventId'] ),  //活动Id
            $this->my_name)                        //用户信息
        ) {
            $this->success( '添加成功' );
        }else {
            $cover['status']?$this->error( '添加失败' ):$this->error( $cover['info'] );
        }

    }*/

    /**
     * upload_single_pic
     * 执行单照片上传
     * @access public
     * @return void
     */
   /*public function upload_single_pic() {
    //上传图片
        $photos = $this->api->attach_upload( 'event_photo' );
        $dao   = D( 'EventPhoto' );
        $dao->setMid( $this->mid );

        if($photos['status']  &&
            $result = $dao->addPhoto( $photos['info'] ,              //相册信息
            1,//intval( $_POST['eventId'] ),  //活动Id
            $this->my_name)                        //用户信息)
        ) {
            echo "Flash requires that we output something or it won't fire the uploadSuccess event";
        }else {
            echo "There was a problem with the upload";
            exit(0);
        }
    }*/

    /**
     * photos
     * 相册列表
     * @access public
     * @return void
     */
    /*public function photos() {
        $id = t($_GET['id']);
        $uid = t($_GET['uid']);
        //检查合法性
        if (false === $this->checkUrl( array( $id,$uid ) ))
            $this->error( "错误的地址，请检查链接" );
        //检查是否有这个活动
        if( false === $result = $this->event->where( 'id='.$id.' AND uid='.$uid )->find() ) {
            $this->error( "没有您提交的{$this->appName}" );
        }
        //获得相片
        $photos = D( 'EventPhoto' )->where( 'eventId = '.$id )->order('id DESC')->findPage(20);
        //组装链接地址
        foreach( $photos['data'] as &$value ) {
            $value['path']  = sprintf( '%s/thumb.php?&w=130&h=87&url=%s%s%s',SITE_URL,UPLOAD_URL,$value['filepath'],$value['filename'] );
        }
        $this->assign( $result );
        $this->assign( $photos );
        $this->display();

    }*/


    //显示一张照片
    /*public function photo() {

        $id		=	intval($_REQUEST['id']);
        $aid	=	intval($_REQUEST['aid']);
        $uid	=	intval($_REQUEST['uid']);
        $eventId = intval( $_REQUEST['eid'] );

        //$type	=	t($_REQUEST['type']);	//照片来源类型，来自某相册，还是其它的

        //判断来源类型
        //if(!empty($type) && !in_array($type,array('album','mAll','fAll'))){
        //$this->error('错误的链接！');
        //}
        //$this->assign('type',$type);

        //获取照片信息
        $photo	=	D('EventPhoto')->where(" id='$id' AND eventId='$eventId' ")->find();
        $this->assign('photo',$photo);
        //验证照片信息是否正确
        if(!$photo) {
            $this->error('照片不存在或已被删除！');
        }

        //获取所有照片数据
        $photos	=	D('EventPhoto')->where( " eventId = '$eventId'" )->findAll();
        $this->assign('photos',$photos);


        //获取活动信息
        $event = D( 'Event' )->where( "id = '$eventId'" )->find();
        $event['cover'] = $temp_cover?UPLOAD_URL.$temp_cover:C( 'TS_URL' ).'/public/theme_default/images/hdpic1.gif';

        $this->assign( $event );


        //获取上一页 下一页 和 预览图
        if($photos) {
            foreach($photos as $v) {
                $photoIds[]	=	intval($v['id']);
            }
            $photoCount	=	count($photoIds);

            //颠倒数组，取索引
            $pindex		=	array_flip($photoIds);

            //当前位置索引
            $now_index	=	$pindex[$id];

            //上一张
            $pre_index	=	$now_index-1;
            if( $now_index <= 0 ) {
                $pre_index	=	$photoCount-1;
            }
            $pre_photo	=	$photos[$pre_index];

            //下一张
            $next_index	=	$now_index+1;
            if( $now_index >= $photoCount-1 ) {
                $next_index	=	0;
            }
            $next_photo	=	$photos[$next_index];

            //预览图的位置索引
            $start_index	=	$now_index - 2;
            if( ($photoCount+1-$now_index)<2) {
                $start_index	=	($photoCount+1-5);
            }
            if($start_index<0) {
                $start_index	=	0;
            }

            //取出预览图列表 最多5个
            $preview_photos	=	array_slice($photos,$start_index,5);
        }else {
            $this->error('照片列表数据错误！');
        }

        $this->assign('photoCount',$photoCount);
        $this->assign('now',$now_index+1);
        $this->assign('pre',$pre_photo);
        $this->assign('next',$next_photo);
        $this->assign('previews',$preview_photos);

        unset($pindex);
        unset($photos);
        unset($album);
        unset($preview_photos);
        $this->display();
    }*/

    /*public function editPhoto() {
        $id   = intval( $_POST['id'] );
        $name = t($_POST['name']);
        if( D( 'EventPhoto' )->editName( $id,$name ) ) {
            echo 1;
        }else {
            echo 0;
        }
    }*/
}
