<?php
class IndexAction extends Action {
        public $name;

        function _initialize() {
			//读取推荐列表
			$IsHotList = IsHotList();
			$this->assign('IsHotList',$IsHotList);
        }

    /*
     * 发起页
     */
        function addPoll() {
                $voteDao = D( 'Vote' );
                $date = date("Y-m-d-H",time()+getConfig('defaultTime'));
                $date_arr = explode("-", $date);

                $datex["year"] = $date_arr[0];
                $datex["month"] = $date_arr[1];
                $datex["day"] = $date_arr[2];
                $datex["hour"] = $date_arr[3];

                $this->assign("date", $datex);
            global $ts;
			$this->setTitle("{$ts['app']['app_alias']}");
                $this->display();
        }

        /**
         * add
         * 添加投票
         * @access public
         * @return void
         */
        function add() {

            $data['title']      = t(h($_POST['title']));
            if(t(h($_POST['date'])) == 'custom'){
            	$data['deadline'] = mktime($_POST['deadline']['hour'],0,0,$_POST['deadline']['month'],$_POST['deadline']['day'],$_POST['deadline']['year']);
            }else{
            	$data['deadline'] = time() + $_POST['date']*86400;
            }
            $data['uid']        = $this->mid;
            $data['explain']    = h($_POST['explain']);
            $data['type']       = intval($_POST['type']);
            $data['onlyfriend'] = intval($_POST['onlyfriend']);
            $data['cTime']      = time();
            $opt = $_POST['opt'];

            //投票表
            $voteDao = D("Vote");
            try{
                $result = $voteDao->addVote($data,$opt);
            }catch(ThinkException $e){
                $this->error($e->getMessage());
            }
            if($result){
                //$_SESSION['MyNewVote'] = 1;
                model('Credit')->setUserCredit($this->mid,'add_vote');
                $this->assign('jumpUrl', U('vote/Index/pollDetail',array('id'=>$result)));
                $this->ajaxData['url'] = U('vote/Index/pollDetail',array('id'=>$result));
                $this->ajaxData['id']  = $result;
                $this->ajaxData['title'] = keyWordFilter($data['title']);
                $this->ajaxData['opt']   = array_filter(keyWordFilter($opt));
                $this->ajaxData['deadline'] = $data['deadline'];
                // $this->success('添加投票成功');
                $this->ajaxReturn($this->ajaxData, '添加投票成功', 1);
            }else{
                $this->ajaxReturn($this->ajaxData, '添加投票成功', 1);
                // $this->error('添加失败');
            }
        }

        /**
         * index
         * 投票首页
         * @access public
         * @return void
         */
        function index() {
        	global $ts;

                $voteDao = D('Vote','vote');

				$order=NULL;

                switch( $_GET['order'] ) {
                        case 'new':    //最新排行
                                $order = 'cTime DESC';
                                $this->setTitle("最新{$ts['app']['app_alias']}");
                                break;
                        case 'following':    //关注的人的投票
                                $order = 'cTime DESC';
								$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
								$in_arr = $this->_getInArr($in_arr);
                                $map    = " uid IN $in_arr ";
                                $this->setTitle("我关注的人的{$ts['app']['app_alias']}");
                                break;
                        default:      //默认热门票数排行
                                $order = 'vote_num DESC';
                                $this->setTitle("热门{$ts['app']['app_alias']}");
                }

                $votes		= $voteDao->where($map)->order( $order )->findPage(getConfig('limitpage'));

                 // 搜索投票功能
                $k = t($_POST['k']);
                if($k){
                    $map['title'] = array('LIKE','%'.$k.'%');
                    $votes = M('Vote')->where($map)->findPage(getConfig('limitpage'));
                    $this->assign('searchkey',$k);
                }

                //选项
                $optDao = D("VoteOpt");
                foreach($votes['data'] as $k=>$v) {
                        $opts = $optDao->where("vote_id = {$v['id']}")->order("vote_id asc")->field("*")->limit( '0,2' )->findAll();
                        $votes['data'][$k]['opts'] = $opts;
                        $votes['data'][$k]['user_info'] = model('User')->getUserInfo($v['uid']);
                }
                //dump($votes);exit;
                // 搜索关键字
                $this->assign('searchkey',t($_POST['k']));
                $this->assign('votes',$votes);
                $this->display();
        }

    /*
     * 我的投票
     */
        function my() {
			global $ts;

                $voteUserDao =       D("VoteUser");
                $voteDao     = D( 'Vote' );

                $map["uid"]  =       $this->mid;
                if( isset( $_GET['action'] ) && 'add' == $_GET['action'] ) {
                //我发布的
                	$votes = $voteDao->where( $map )->order( 'id DESC' )->findPage(getConfig('limitpage'));
                    $this->setTitle("我发起的{$ts['app']['app_alias']}");
                }else{
                //我参与的
                    $map   = "uid = {$this->mid} AND opts <> ''";
                    $temp  = $voteUserDao->where( $map )->field('distinct(vote_id)')->findAll();
                    $votes = array();
                    foreach( $temp as $value ) {
                    	$void[] = $value['vote_id'];
                    }
                    $where['id']   = array( 'in',$void );
                    $votes         = $voteDao->where( $where )->order( 'id DESC' )->findPage(getConfig('limitpage'));
                    $this->setTitle("我参与的{$ts['app']['app_alias']}");
                }
				//选项
                $optDao = D("VoteOpt");
                foreach($votes['data'] as $k=>$v) {
                        $opts = $optDao->where("vote_id = {$v['id']}")->order("id asc")->field("*")->limit( '0,2' )->findAll();
                        $votes['data'][$k]['opts'] = $opts;
                        $votes['data'][$k]['user_info'] = model('User')->getUserInfo($v['uid']);
                }
                $this->assign('votes',$votes);
                $this->display();
        }


        /**
         * personal
         * 某个人的投票页
         * @access public
         * @return void
         */
        function personal() {
                $xxx = intval( $this->uid );
                if( empty( $xxx ) || 0 == $xxx ) {
                        $this->error( "意外错误，无法找到该用户的投票页。" );
                        exit;
                }

				$data=array('user_id'=>$xxx,);
                $vote = api('User')->data($data)->show();
                if( false == $vote['uname'] ) {
                        $this->error( "被删除或被屏蔽的不存在用户ID" );
                        exit;
                }
                $this->assign( 'uid',$xxx );
                $this->assign( 'vote',$vote );

                //投票
                $voteDao = D('Vote');
                $time = time();
                if( isset( $_GET['action'] ) && 'add' == $_GET['action'] ) {
                //发表的
                        $where = " uid = $xxx ";
                }else{
                //参与的
                        $map     =  "uid = $xxx AND opts <> ''";
                        $userDao = D( 'VoteUser' );
                        $temp    = $userDao->where( $map )->field('distinct(vote_id)')->findAll();
                        $void   = array();
                        foreach( $temp as $value ) {
                        	$void[] = $value['vote_id'];
                 		}
                        $where['id']   = array('in',$void);
                 }

                $votes		= $voteDao->where( $where )->order( 'id desc' )->findPage(getConfig('limitpage'));

                //选项
                $optDao = D("VoteOpt");
                foreach($votes['data'] as $k=>$v) {
                        $opts = $optDao->where("vote_id = {$v['id']}")->order("id asc")->field("*")->limit( '0,2' )->findAll();
                        //  echo $optDao->getLastSql();return;
                        $votes['data'][$k]['opts'] = $opts;
                }

                $this->assign('votes',$votes);

                $this->display();
        }


    /*
     * 某个投票的详情
      */
        function pollDetail() {

				if($_SESSION['MyNewVote'] == 1) {
        			$this->assign('MyNewVote', intval($_SESSION['MyNewVote']));
        			unset($_SESSION['MyNewVote']);
        	    }

                $id = intval($_GET["id"]);
                if( empty( $id ) || 0 == $id ) {
                    $this->assign('jumpUrl', U('vote/Index/index'));
                    $this->error( "非法访问投票页面" );
                    exit;
                }

                //投票详情
                $vote = D("Vote")->find($id);
                if( false == $vote ) {
                    $this->assign('jumpUrl', U('vote/Index/index'));
                    $this->error( "浏览的投票不存在或者被删除" );
                }
				$vote[name]=getUserName($vote['uid']);
                $vote['user_info'] = model('User')->getUserInfo($vote['uid']);
                $this->assign("vote", $vote);
                $this->setTitle($vote['title']);
                //$this->assign( "api",$this->api );

                //投票选项
                $vote_opts = D("VoteOpt")->where("vote_id = $id")->order("id asc")->findAll();
                $this->assign("vote_opts", $vote_opts);

                //投票的参与者
                $test = D( 'VoteUser' );
                $vote_users = $test->where("vote_id = $id AND opts<>'' ")->findAll();

                //检查是否已投票
                $has_vote     = false;
                //检查投票情况
                $empty_friend = false;
                $temp_uid     = array();
                $join = getConfig('join');
                if( "" == $vote_users[0] ) {
                        $empty_friend = true;
                }else {
                        foreach( $vote_users as &$value ) {
                                if( getFollowState( $this->mid,$value['uid'])=='havefollow') {
                                        $value['following'] = true;
                                }else if( $this->mid == $vote['uid'] || $this->uid == $value['uid'] ) {
                                        $value['admin'] = true;
                                }else {
                                    if( 'following' === $join) {
                                        $value['Show'] = false;
                                	}else {
                                        $value['Show'] = true;
                               		}
                                }
                                $notShow[] = ($value['Show'] || $value['admin'] || $value['following']);
                                $temp_uid[] = $value['uid'];
                        }
                        $temp = array_filter( $notShow );
                        if( empty( $temp ) )
                                $empty_friend = true;

                        $has_vote  = ( in_array($this->mid,$temp_uid)) ?true:false;
                }
                $this->assign( 'has_vote',$has_vote );
                $this->assign( "empty_friend",$empty_friend );
                //$this->assign("vote_users", $vote_users);

                //投票的百分比
                foreach($vote_opts as $v) {
                        $nums[] = (int)($v['num']);
                        $total += (int)($v['num']);
                }
                foreach($nums as $v) {
                        $pers[] = round(((float)$v/(float)$total)*100,0);
                }

                $this->assign('vote_pers',$pers);
                $this->display();
        }
	/*
     * 投票的参与者
      */
	    public function voteUsers(){
            $id = intval($_POST["id"]);
            if( empty( $id ) || 0 == $id ) {
                $this->error( "非法访问投票情况" );
                exit;
            }
            $join = getConfig('join');
        	$vote_users = D( 'VoteUser' )->where("vote_id = $id AND opts<>'' ")->order('id DESC')->findPage(8);
			foreach( $vote_users['data'] as &$value ) {
                if( getFollowState( $this->mid,$value['uid'])!='unfollow') {
                     $value['following'] = true;
                }else if( $this->mid == $value['uid'] || $this->uid == $value['uid'] ) {
                     $value['admin'] = true;
                }else {
                     if( 'following' === $join) {
                     	$value['Show'] = false;
                     }else {
                        $value['Show'] = true;
                     }
                 }
             }
             $this->assign('vote_users', $vote_users);
             $this->display();
	    }

    /*
     * 投票
      */
        function vote() {

        //用户投票信息
                $voteUserDao = D("VoteUser");

                $vote_id      = intval($_POST["vote_id"]);
                //检查ID是否合法
                if( empty($vote_id) || 0 == $vote_id ) {
                        $this->error( "错误的投票ID" );
                        exit;
                }
                //先看看投票期限过期与否
                $voteDao      = D( "Vote" );
                $the_vote     = $voteDao->where("id=$vote_id")->find();
                $vote_user_id = $the_vote['uid'];
                $deadline     = $the_vote['deadline'];
                if( $deadline <= time() ) {
                        echo -3;
                        return;
                }
                //再看看投过没
                $count = $voteUserDao->where( "vote_id=$vote_id AND uid=$this->mid AND opts <>''" )->count();
                if($count>0) {
                        echo -1;
                        return;
                }
                //如果没投过，就添加
                $data["vote_id"] = $vote_id;
                $data["uid"] = $this->mid;
                $data["opts"]    = rtrim(t($_POST["opts"]),",");
                $data["cTime"]   = time();

                $addid = $voteUserDao->add($data);

                //投票选项信息的num+1
                $dao = D("VoteOpt");

                $opts_ids = rtrim(t($_POST["opts_ids"]),",");
                $opts_ids = explode(",",$opts_ids);

                foreach($opts_ids as $v) {
                        $v = intval($v);
                        $dao->setInc("num","id=$v");
                }

                //投票信息的vote_num+1
                D("Vote")->setInc("vote_num","id=$vote_id");

				if($the_vote['uid']!=$this->mid){
					model('Credit')->setUserCredit($the_vote['uid'],'joined_vote')
						       ->setUserCredit($this->mid,'join_vote');
				}

                echo 1;
        }


        /**
         * deleteVote
         * 删除投票
         * @access public
         * @return void
         */
        function deleteVote() {
                $id = intval($_POST['id']);
                //								越权判断
                if( empty( $id ) || 0 == $id || !CheckAuthorPermission(D('Vote'),$id) ) {
                        echo -1;
                        return ;
                }

                if( D( 'Vote','vote' )->doDeleteVote( $id )) {
					model('Credit')->setUserCredit($this->mid,'delete_vote');
                    echo 1;
                }else {
                    echo -1;
                }
        }

        /**
         * addOpt
         * 添加候选项
         * @access public
         * @return void
         */
        function addOptTab() {
			$id = intval($_GET['id']);
            if( empty( $id ) || 0 == $id ) {
                  echo -1;
                  return;
            }
            $this->assign('id',$id);
			$this->display();
		}
        function addOpt() {
                $id = intval( $_POST['id'] );
                
                								//越权判断
                if( empty( $id ) || 0 == $id || !CheckAuthorPermission(D('Vote'),$id)) {
                        echo -1;
                        return;
                }
                $map['name'] = t( $_POST['name'] );
                $map['vote_id'] = $id;
                //查找这个投票的所有选项
                $voteDao = D( 'VoteOpt' );
                $vote_opt = $voteDao->where( "vote_id = {$id}" )->field()->findAll();
                //没有找到对应的选项。这一个投票项不存在
                if( false == $vote_opt ) {
                        echo -2;
                        return;
                }else {
                //找到后，对比新添加的name,如果相同。。返回错误提示
                        foreach( $vote_opt as $value ) {
                                if( $map['name'] == $value['name'] ) {
                                        echo -3;
                                        return;
                                }
                        }

                        //将新的选项添加
                        if( $result = $voteDao->add( $map ) ) {
                                echo 1;
                                return;
                        }else {
                                echo 0;
                                return;
                        }
                }


        }
		function optList(){
			$id = intval($_POST['id']);
			$vote_opts = D("VoteOpt")->where("vote_id = $id")->order("id asc")->findAll();
            $this->assign("vote_opts", $vote_opts);
			//检查是否过期
			$vote = M('vote')->field('deadline,type')->find($id);
            $this->assign("vote", $vote);
			//检查是否已投票
            $has_vote     = false;
			$result = M('vote_user')->where(" uid={$this->mid} and vote_id={$id} and opts<>'' ")->count();
			if($result>0)$has_vote=true;
            $this->assign("has_vote", $has_vote);
			//投票的百分比
            foreach($vote_opts as $v) {
                $nums[] = (int)($v['num']);
                $total += (int)($v['num']);
            }
            foreach($nums as $v) {
                $pers[] = round(((float)$v/(float)$total)*100,0);
            }
			$this->assign('vote_pers',$pers);

			$this->display();
		}

		/**
         * editDate
         * 修改结束时间
         * @access public
         * @return void
         */
		public function editDateTab(){
			$id = intval($_GET['id']);
			if( empty( $id ) || 0 == $id  ) {
				echo -1;
				return;
            }
			$voteinfo = D('Vote')->where(" id={$id} ")->find();
			$this->assign('id',$id);
			$this->assign('deadline',$voteinfo['deadline']);
			$this->display();
		}

        public function editDate() {
                $id = intval( $_POST['id'] );
                								//越权判断
                if( empty( $id ) || 0 == $id || !CheckAuthorPermission(D('Vote'),$id)) {
                        echo -1;
                        return;
                }
                $map['id'] = $id;
                //查找这个投票的信息
                $voteDao = D( 'Vote' );
                $vote_opt = $voteDao->where( "id = {$id}" )->field()->find();
                //没有找到对应的选项。这一个投票项不存在
                if( false == $vote_opt ) {
                        echo -2;
                        return;
                }else {

                        $deadline = mktime($_POST["hour"],0,0,$_POST["month"],$_POST["day"],$_POST["year"]);

                        if($deadline < time()) {
                                echo -3;
                                return;
                        }
                        $save['deadline'] = $deadline;

                        //将新的选项添加
                        if( $result = $voteDao->where( $map )->save( $save ) ) {
                                echo 1;
                                return;
                        }else {
                                echo 0;
                                return;
                        }
                }
        }

        function _getInArr($in_arr) {

                $in_str = "(";
                foreach($in_arr as $key=>$v) {
                        $in_str .= $v['fid'].",";
                }
                $in_str = rtrim($in_str,",");
                $in_str .= ")";
                return $in_str;

        }

        /**
         * _orderDate
         * 解析排序时间区段
         * @param mixed $options
         * @access private
         * @return void
         */
        private function _orderDate( $options ) {
                $time = explode('-',date( 'Y-n-j',time() ));
                list( $now_year,$now_month,$now_day ) = $time;
                //定义偏移量
                $month = 0;
                $year = 0;
                $day = 0;
                switch ( $options ) {
                        case 'all': //所有日志
                                return array( 'lt',time() );
                                break;
                        case 'one': //一个月以内的日志
                                $month = 1;
                                break;
                        case 'three': //3个月以内的日志
                                $month = 3;
                                break;
                        case 'half': //6个月以内的日志
                                $month = 6;
                                break;
                        case 'year': //一年以内的日志
                                $year  = 1;
                                break;
                        case "oneDay":
                                $day = 1; //一天以内的
                                break;
                        case "threeDay":
                                $day = 3; //三天以内的
                                break;
                        case "oneWeek":
                                $day = 7; //一周以内
                                break;
                }
                //换算时间戳
                $toDate = mktime( 0,0,0,$now_month-$month,$now_day-$day,$now_year-$year );
                //返回数组型数据集
                return array( "between",array( $toDate,time() ) );
        }
}
?>
