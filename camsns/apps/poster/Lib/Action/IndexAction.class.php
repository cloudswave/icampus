<?php
    /**
     * GiftAction
     * 礼物控制层
     *
     * @uses
     * @package
     * @version
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
class IndexAction extends Action{
	    private $icopath="";
	    protected $app_alias;

	    public function _initialize(){
	    	//参数转义
	        $this->icopath = '__APP__/images/ico/';
	        $this->assign('icopath',$this->icopath);

	        global $ts;
	        $this->app_alias = $ts['app']['app_alias'];

	         // 推荐招贴数据
        	$list = M('poster')->where('recommend=1')->order('cTime DESC')->field('id,pid,type,uid,content,title,deadline,private,cover,cTime,recommend')->limit(10)->findAll();
        	$list = D('Poster','poster')->replace($list);
        	$this->assign('is_recommend_list',$list);
	    }

	    public function index(){
	    	$uid=array();
	        if($_GET['order']=='following'){
	        	 // $following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
	        	$following = model('Follow')->getFollowingsList($this->mid);
                 foreach($following['data'] as $v) {
                     $uid[] = $v['fid'];
                 }
                 $this->setTitle("我关注的人的{$this->app_alias}");
                 $this->__setAssign($uid);
	        }

	        if(!isset($_GET['order'])) {
	        	if( $_REQUEST['title'] ){
	        		$title = t ( $_REQUEST['title'] );
	        		$map['title'] = array('LIKE','%'.$title.'%');
	        		$_REQUEST['pid'] && $map['pid'] = intval ( $_REQUEST['pid'] );
	        		$_REQUEST['stid'] && $map['type'] = intval ( $_REQUEST['stid'] );
	        		$search_list =  M('poster')->where($map)->order('cTime DESC')->findPage(20);
	        		$search_list['data'] = D('Poster','poster')->replace($search_list['data']);
	        		$this->assign('searchkey' , $title );
	        		$this->assign( $search_list );
	        		$this->getPosterType ( D('Poster') );

	        	}else{
	        		$this->setTitle("最新{$this->app_alias}");
	        		$this->__setAssign($uid);
	        	}
	        }



	        if(isset($this->uid)){
	        	//分类关注
	        	    $_poster_type_follow=model('PosterTypeFollow')->getFollowList($this->uid);
	        	    //dump($poster_type_follow);
	        	    foreach($_poster_type_follow as $k=>$v){
					    $poster_type_follow[$v['id']]=$v['name'];   
					   /// unset($poster_type_follow[$k]); //删掉原有的键值        
					 
					}

					$_poster_smalltype_follow=model('PosterSmallTypeFollow')->getFollowList($this->uid);
					foreach($_poster_smalltype_follow as $k=>$v){
					    $poster_smalltype_follow[$v['id']]=$v['name'];
					   // unset($poster_smalltype_follow[$k]); //删掉原有的键值        
					 
					}
	        		$this->assign('poster_type_follow',$poster_type_follow);
                    $this->assign('poster_smalltype_follow',$poster_smalltype_follow);
	        }
	         //dump($poster_type_follow);
	        // var_dump(model('PosterTypeFollow')->getFollowList($this->uid));
	        $this->display();
	    }

        public function personal(){
            $this->__setAssign($this->uid);
            $this->assign('uid',$this->uid);
            $uname = getUserName($this->uid);
            $this->assign('name',$uname);
            if ( $this->mid != $this->uid && $this->uid ){
            	$this->setTitle($uname."的{$this->app_alias}");
            }else{
            	$this->setTitle("我的{$this->app_alias}");
            }

	        if(isset($this->uid)){
	        	//分类关注
	        	    $_poster_type_follow=model('PosterTypeFollow')->getFollowList($this->uid);
	        	    //dump($poster_type_follow);
	        	    foreach($_poster_type_follow as $k=>$v){
					    $poster_type_follow[$v['id']]=$v['name'];   
					   /// unset($poster_type_follow[$k]); //删掉原有的键值        
					 
					}

					$_poster_smalltype_follow=model('PosterSmallTypeFollow')->getFollowList($this->uid);
					foreach($_poster_smalltype_follow as $k=>$v){
					    $poster_smalltype_follow[$v['id']]=$v['name'];
					   // unset($poster_smalltype_follow[$k]); //删掉原有的键值        
					 
					}
	        		$this->assign('poster_type_follow',$poster_type_follow);
                    $this->assign('poster_smalltype_follow',$poster_smalltype_follow);
	        }

	        //dump($poster_type_follow);


            $this->display();
        }

	    public function addPosterSort(){
	    	$posterTypeDao = D('PosterType');
	    	$posterType = $posterTypeDao->getType();
	    	$this->assign('posterType',$posterType);
	    	$this->setTitle("发布{$this->app_alias}");
	    	$this->display();
	    }

	    public function posterDetail(){
	    	$posterTypeDao = D('PosterType');
	    	$poster = D('Poster');
	    	$id = intval($_GET['id']);
	    	if($id == 0){
	    		$this->error("错误的信息地址.请检查后再访问");
                exit;
	    	}

	    	$posterData     = $poster->getPoster($id,$this->mid);
	        if(!$posterData){
	        	$this->assign('jumpUrl', U('poster/Index/index'));
                $this->error("这个信息被删除或者不允许查看");
                exit;
            }

	    	$posterType = $posterTypeDao->getType($posterData['pid']);

	    	$posterTypeExtraField = $posterTypeDao->getExtraField($posterType['extraField']);
	    	unset($posterType['extraField']);

	    	if($posterData['uid'] == $this->mid){
	    		$posterData['name'] = '我';
	    		$this->assign('admin',1);
	    	}else{
	    		$posterData['name'] = getUserName($posterData['uid']);
	    	}
	    	$this->assign('poster',$posterData);
	    	$this->assign('uid',$posterData['uid']);
	    	$this->assign('extraField',$posterTypeExtraField);
	    	$this->assign('type',$posterType);

			//话题符号
    		$huati=$posterData['posterSmallType']==NULL?"":"#".$posterData['posterSmallType']."#";
    		$huati="#".$posterData['posterType']."#".$huati;

	    	 $this->assign('huati',$huati);	


            

            //dump($_SESSION);
            //自动分享微博
	     	if($_GET["autoShare"]){


				$_POST["content"]="";
				$_POST["body"]="我在".$huati."中发布了'". $posterData['title']."'。来看看吧0.0！";
				$_POST['source_url']=U('//posterDetail',array('id'=>$id));
				$_POST['type']=$posterData['attach_id']==NULL?"post":"postimage";
				
				$_POST['app_name']="public";
				$_POST['attach_id']=$posterData['attach_id'];

				//exit(dump($_POST));
				$this->My_PostFeed();//发微博

	    	}

	    	//自动弹框分享微博
	    	if($_GET['user_to_share']){
	    		$_SESSION['new_poster'] = $posterData['attach_id']?$posterData['attach_id']:1;
	    	}

           // dump($posterData);
           // //发私信提醒
         if($_GET['auto_to_sendMessage']){

             $FollowUserList=D('PosterTypeFollow')->getFollowUserList($posterData['pid']);
             $push_user_alias="";
             foreach ($FollowUserList as $key => $value) {
             	$d['to']=$value['uid'];
             	$d['content']="我在您关注的#".$posterData['posterType']."#中发布了'". $posterData['title']."'。来看看吧0.0->点击".U('//posterDetail',array('id'=>$id));
             	$d['attach_ids']=$posterData['attach_id'];
             	$this->my_doPostMessage($d);

             	$push_user_alias=$push_user_alias.$value['uid'].",";
             	//推送
             	if($key%1000==999||count($FollowUserList)-1==$key){
             		$_POST['n_title']="新的提醒";
	                $_POST['n_content']="有人在您关注的分类中发布了‘".$posterData['title']."’,来看看吧0.0";
	                $_POST['n_extras']=json_encode(array('go_url'=>SITE_URL,'app'=>'poster','posterDetail'=>$id));
	                $_POST['push_user_alias']=$push_user_alias;//推送所有人
	                $_POST['getPushResult']=0;
	                Addons::addonsHook("JPush","doAddPush",array(),true);

	                $push_user_alias="";//还原
             	}


             }

             $FollowUserList=D('PosterSmallTypeFollow')->getFollowUserList($posterData['type']);
             $push_user_alias="";
             foreach ($FollowUserList as $key => $value) {
             	$d['to']=$value['uid'];
             	$d['content']="我在您关注的#".$posterData['posterSmallType']."#中发布了'". $posterData['title']."'。来看看吧0.0->点击".U('//posterDetail',array('id'=>$id));
             	$d['attach_ids']=$posterData['attach_id'];
             	$this->my_doPostMessage($d);


             	//推送
                $push_user_alias=$push_user_alias.$value['uid'].",";
             	if($key%1000==999||count($FollowUserList)-1==$key){
             		$_POST['n_title']="新的提醒";
	                $_POST['n_content']="有人在您关注的分类中发布了‘".$posterData['title']."’,来看看吧0.0";
	                $_POST['n_extras']=json_encode(array('go_url'=>SITE_URL,'app'=>'poster','posterDetail'=>$id));
	                $_POST['push_user_alias']=$push_user_alias;//推送所有人
	                $_POST['getPushResult']=0;
	                Addons::addonsHook("JPush","doAddPush",array(),true);

	                $push_user_alias="";//还原
             	}
             }
            } 
	    	//exit;

	    	$this->display();
	    }

	    public function doDeletePoster(){
	    	$id = intval($_POST['id']);
	    	if(0 == $id){
	    		echo -3;
	    	}else{
	    		$poster = D('Poster');
	    		if($res = $poster->deletePoster($id,$this->mid)){
                    //积分
                    X('Credit')->setUserCredit($this->mid,'delete_poster');
	    		}
	    		echo $res;
	    	}
	    }
	    private function __setAssign($uid = null){
	       $poster = D('Poster');
	       $pid = intval($_GET['pid'])?intval($_GET['pid']):null;
	       $stid = intval($_GET['stid'])?intval($_GET['stid']):null;
           $posterData = $poster->getPosterList($pid,$stid,$uid);
           $this->getPosterType ($poster);
           foreach($posterData['data'] as $k=>&$v){
           		$v['user_info'] = model('User')->getUserInfo($v['uid']);
           }
           // dump($posterData);exit;
           $this->assign($posterData);
	    }

        private function getPosterType($poster){
            $posterTypeDao = D('PosterType');
            $posterSmallTypeDao = D('PosterSmallType');
	        $posterType = $posterTypeDao->getType();
	        foreach($posterType as $value){
	   	       $id = $value['id'];
	           if(isset($value['type']) && $id == intval($_GET['pid'])){
	        	  $posterSmallType = $posterSmallTypeDao->getPosterSmallType($value['type']);
	           }
	        }

           $posterSmallType = $this->getPosterCount($poster,$posterSmallType);
	       $this->assign('posterType',$posterType);
	       $this->assign('type',$posterSmallType);
        }



        private function getPosterCount($poster,$posterSmallType){
            $tableName = $poster->getTableName();
            //$otherWhere = $this->private;
            if(!empty($posterSmallType)){
                for($i=0;$i<count($posterSmallType);$i++){
                	//if(isset($otherWhere)){
                	//	$where = "type = {$posterSmallType[$i]['id']} AND ".$otherWhere;
                	//}else{
                		$where = "type = {$posterSmallType[$i]['id']}";
                	//}
                    $sql[] = "select '{$posterSmallType[$i]['id']}' as `id`,count(1) as count from  {$tableName} where {$where}";
                }
            }
            $sql = implode( ' union all ',$sql );
            $count = $poster->query($sql);
            $temp_array = array();
            foreach($count as $value){
            	$temp_array[$value['id']] = $value['count'];
            }
            $result = $posterSmallType;
            foreach ($result as &$value){
            	$value['count'] = $temp_array[$value['id']] ;
            }
            return $result;
        }


	   public function addPoster(){
	   	   $typeId = intval($_GET['typeId']);
	   	   if(empty($typeId))
	   	       $this->error('参数有误');

	       $posterTypeDao = D('PosterType');
	       $poster = $posterTypeDao->getType($typeId);
	       if(empty($poster)){
	           $this->error('参数有误');
	       }
	       $posterSmallTypeDao = D('PosterSmallType');
	       $posterSmallType = $posterSmallTypeDao->getPosterSmallType($poster['type']);
           $this->assign($poster);
           $this->assign('smallType',$posterSmallType);
           //初始化截止日期
           $this->assign('deadline',date("Y-m-d H:i:s",time()+90*24*3600));

           $this->setTitle("发布{$this->app_alias}");
	       $this->display();
	   }

	   public function editPoster(){
           $posterDao  = D('Poster');
           $id = intval( $_GET['id'] );
	   	   $posterData = $posterDao->getPoster($id,$this->mid);
	   	   //权限判断
		   if ( !CheckAuthorPermission($posterDao, $id) ){
		   		$this->error( '对不起,您没有操作权限！' );
		   }
	   	   $posterTypeDao = D('PosterType');
           $poster = $posterTypeDao->getType(intval($_GET['typeId']), intval($_GET['id']));
           if(empty($poster)){
               $this->error('参数有误');
           }
           $posterSmallTypeDao = D('PosterSmallType');
           $userInfo['areaid'] = $posterData['address_province'].','.$posterData['address_city'].','.$posterData['address_area'];
           $posterData['deadline'] && $posterData['deadline'] = date("Y-m-d H:i:s",$posterData['deadline']);
           $posterSmallType = $posterSmallTypeDao->getPosterSmallType($poster['type']);
           $this->assign('smallType',$posterSmallType);
           $this->assign('userInfo',$userInfo);
           $this->assign('poster',$posterData);
           $this->assign($poster);
           $this->display();
	   }

	   public function doEditPoster(){
            $dao = D('Poster');
            $condition['id']=intval($_POST['id']);
            //权限判断
            if ( !CheckAuthorPermission($dao, $condition['id']) ){
            	$this->error( '对不起,您没有操作权限！' );
            }
		    $map['title']      = t($_POST['title']);
	        $map['type']       = intval($_POST['type']);
	        $map['content']    = h($_POST['explain']);
	        $map['contact']    = t($_POST['contact']);

	        // $address = explode(',',$_POST['areaid']);
	        // $map['address_province'] = $address[0];
	        // $map['address_city'] = $address[1];
	        // $map['address_area'] = $address[2];
	        $map['area']=h($_POST['area']);
	        if($_POST['deadline']){
                $map['deadline'] = $deadline = $this->_paramDate( $_POST['deadline'] );
                $sendPosterTime =$dao->where('id='.intval($_POST['id']))->getField('cTime');
	        	$deadline < $sendPosterTime && $this->error( "结束时间不得小于发布时间" );
	        }else{
	        	$map['deadline'] = NULL;
	        }
	        // 检查详细介绍
	        if (get_str_length($map['content']) <= 0) {
	        	$this->error('详细介绍不能为空');
	        }

	        $map = $this->_extraField($map,$_POST);

	   //得到上传的图片
        $option = array();
        if($_FILES['cover']['size'] > 0) {
	        $options['userId'] = $this->mid;
	        $options['max_size'] = 2*1024*1024;//2MB
	        $options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
	        $options['attach_type'] = 'poster_cover';
	        $data['upload_type'] = 'image';
	        $cover  =   model('Attach')->upload($data,$options);
            if($cover['status']){
            	$map['cover'] = $cover['info'][0]['save_path'].$cover['info'][0]['save_name'];
            	$map['attach_id'] = $cover['info'][0]['attach_id'];
            }else{
            	$this->error($cover['info']);
            }
        }

	        //$map['private'] = isset($_POST['friend'])?$_POST['friend']:0;

	        $rs = $dao->where($condition)->save($map);
	        if(false !== $rs){

				//type=post&app_name=public&topicHtml=我分享了一个帖子“嵌入式系统”&source_url=http%3A%2F%2F192.168.24.214%2Fcamsns%2Findex.php%3Fapp%3Dposter%26mod%3DIndex%26act%3DposterDetail%26id%3D1
				// $_POST["content"]="";
				// $_POST["body"]="我在#".D('PosterSmallType')->getPosterSmallType($map['type'])."#中发布了". $map['title']."。";
				// $_POST['source_url']=U('//posterDetail',array('id'=>$condition['id']));
				// $_POST['type']="post";
				// $_POST['app_name']="poster";
				// $_POST['attach_id']=88;
				//exit(translatorGoogleAPI("好"));
				// echo post(U('public/Feed/My_PostFeed'),$_POST);
				// exit;
				// exit(post(U('public/Feed/My_PostFeed'),$_POST)); 
    // //             A('public/Feed/po');
                //exit($this->My_PostFeed());
                //$_SESSION['new_poster'] = $cover['info'] ? $cover['info'][0]['attach_id'] : 1;
	        	$this->assign('jumpUrl',U('//posterDetail',array('id'=>$condition['id'],'autoShare'=>false,'user_to_share'=>true,'auto_to_sendMessage'=>true)));
	        	$this->success("编辑成功");
	        	exit;
	        }else{
	        	$this->error('编辑失败');
	        }
	   }

	    private function _paramDate( $date ) {
	        $date_list = explode( ' ',$date );
	        list( $year,$month,$day ) = explode( '-',$date_list[0] );
	        list( $hour,$minute,$second ) = explode( ':',$date_list[1] );
	        return mktime( $hour,$minute,$second,$month,$day,$year );
	    }
	   public function doAddPoster(){
	   	$map['title']      = t(h($_POST['title']));
        $map['type']       = intval($_POST['type']);
        $map['pid']        = intval($_POST['pid']);
        $map['content']    = h($_POST['explain']);
        $map['contact']    = t($_POST['contact']);
        $map['uid']        = $this->mid;
        $map['cTime']      = time();
	    if($_POST['deadline']){
            $map['deadline'] = $deadline = $this->_paramDate( $_POST['deadline'] );
            $deadline < time() && $this->error( "结束时间不得小于发布时间" );
        }else{
            $map['deadline'] = NULL;
        }
        //$address = explode(',',$_POST['areaid']);
       // $map['address_province'] = $address[0];
        //$map['address_city'] = $address[1];
        //$map['address_area'] = $address[2];
        //
        $map['area']=h($_POST['area']);
        // 检查详细介绍
        if (get_str_length($map['content']) <= 0) {
        	$this->error('详细介绍不能为空');
        }

        $map = $this->_extraField($map,$_POST);
        //得到上传的图片
        $option = array();
        if($_FILES['cover']['size'] > 0) {
	        $options['userId'] = $this->mid;
	        $options['max_size'] = 2*1024*1024;//2MB
	        $options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
	        $options['attach_type'] = 'poster_cover';
	        $data['upload_type'] = 'image';
	        $cover  =   model('Attach')->upload($data,$options);
            if($cover['status']){
            	$map['cover'] = $cover['info'][0]['save_path'].$cover['info'][0]['save_name'];
            	$map['attach_id'] = $cover['info'][0]['attach_id'];
            }else{
            	$this->error($cover['info']);
            }
        }
        //$map['private'] = isset($_POST['friend'])?$_POST['friend']:0;
        $dao = D('Poster');
        $rs = $dao->add($map);
        if($rs){
            //发微薄
            //$_SESSION['new_poster'] = $cover['info'] ? $cover['info'][0]['attach_id'] : 1;
            //积分
            X('Credit')->setUserCredit($this->mid,'add_poster');
            // $this->success("发布成功，继续发布！");
            // 分享并 @关注的人

            //autoShare 自动发布微博 user_to_share 自动弹分享框
            $this->redirect('poster/Index/posterDetail',array('id'=>$rs,'autoShare'=>true,'user_to_share'=>false,'auto_to_sendMessage'=>true),0,'发布成功');
        }else{
            $this->error("发布失败");
        }
	   }

	   private function _extraField($map,$post){
	   	for($i=1;$i<6;$i++){
	   		if(isset($post['extra'.$i]) && !empty($post['extra'.$i])){
	   			if(is_array($post['extra'.$i])){
	   				$map['extra'.$i] =implode(',',$post['extra'.$i]);
	   			}else{
                    $map['extra'.$i] = $post['extra'.$i];
	   			}

	   		}
	   	}
	   	return $map;
	   }





	   /**
	 * 发布微博操作，用于AJAX
	 * @return json 发布微博后的结果信息JSON数据
	 */
	public function My_PostFeed()
	{
		// 返回数据格式
		//$_POST=$_GET;
		//dump($_POST);
		$return = array('status'=>1, 'data'=>'');
		// 用户发送内容
		$d['content'] = isset($_POST['content']) ? filter_keyword(h($_POST['content'])) : '';
		// 原始数据内容
		$d['body'] = filter_keyword($_POST['body']);

		//$this->success($d['body']);//测试
		// 安全过滤
		foreach($_POST as $key => $val) {
			$_POST[$key] = t($_POST[$key]);
		}
		$d['source_url'] = urldecode($_POST['source_url']);  //应用分享到微博，原资源链接
		// 滤掉话题两端的空白
		$d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);	
		// 附件信息
		$d['attach_id'] = trim(t($_POST['attach_id']), "|");
		if ( !empty($d['attach_id']) ){
			$d['attach_id'] = explode('|', $d['attach_id']);
			array_map( 'intval' , $d['attach_id'] );
		}
		// 发送微博的类型
		$type = t($_POST['type']);
		// 所属应用名称
		$app = isset($_POST['app_name']) ? t($_POST['app_name']) : APP_NAME;			// 当前动态产生所属的应用
		if(!$data = model('Feed')->put($this->uid, $app, $type, $d)) {
			$return = array('status'=>0,'data'=>model('Feed')->getError());
			//return json_encode($return);
		}
		// 发布邮件之后添加积分
		model ( 'Credit' )->setUserCredit ( $this->uid, 'add_weibo' );
		// 微博来源设置
		$data ['from'] = getFromClient ( $data ['from'], $data ['app'] );
		//$this->assign ( $data );
		// 微博配置
		$weiboSet = model ( 'Xdata' )->get ( 'admin_Config:feed' );
		//$this->assign ( 'weibo_premission', $weiboSet ['weibo_premission'] );
		$return ['data'] = $this->fetch ();
		// 微博ID
		$return ['feedId'] = $data ['feed_id'];
		$return ['is_audit'] = $data ['is_audit'];
		// 添加话题
		model ( 'FeedTopic' )->addTopic ( html_entity_decode ( $d ['body'], ENT_QUOTES, 'UTF-8' ), $data ['feed_id'], $type );
		// 更新用户最后发表的微博
		$last ['last_feed_id'] = $data ['feed_id'];
		$last ['last_post_time'] = $_SERVER ['REQUEST_TIME'];
		model ( 'User' )->where ( 'uid=' . $this->uid )->save ( $last );
		
		
		$isOpenChannel = model ( 'App' )->isAppNameOpen ( 'channel' );
		if (! $isOpenChannel) {
			//return json_encode($return);
		}
		// 添加微博到投稿数据中
		$channelId = t ( $_POST ['channel_id'] );
		
		// 绑定用户
		$bindUserChannel = D ( 'Channel', 'channel' )->getCategoryByUserBind ( $this->mid );
		if (! empty ( $bindUserChannel )) {
			$channelId = array_merge ( $bindUserChannel, explode ( ',', $channelId ) );
			$channelId = array_filter ( $channelId );
			$channelId = array_unique ( $channelId );
			$channelId = implode ( ',', $channelId );
		}
		// 绑定话题
		$content = html_entity_decode ( $d ['body'], ENT_QUOTES, 'UTF-8' );
		$content = str_replace ( "＃", "#", $content );
		preg_match_all ( "/#([^#]*[^#^\s][^#]*)#/is", $content, $topics );
		$topics = array_unique ( $topics [1] );
		foreach ( $topics as &$topic ) {
			$topic = trim ( preg_replace ( "/#/", '', t ( $topic ) ) );
		}
		$bindTopicChannel = D ( 'Channel', 'channel' )->getCategoryByTopicBind ( $topics );
		if (! empty ( $bindTopicChannel )) {
			$channelId = array_merge ( $bindTopicChannel, explode ( ',', $channelId ) );
			$channelId = array_filter ( $channelId );
			$channelId = array_unique ( $channelId );
			$channelId = implode ( ',', $channelId );
		}
		if (! empty ( $channelId )) {
			// 获取后台配置数据
			$channelConf = model('Xdata')->get('channel_Admin:index');
			$return['is_audit_channel'] = $channelConf['is_audit'];
			// 添加频道数据
			D ( 'Channel', 'channel' )->setChannel ( $data ['feed_id'], $channelId, false );
		}			

		//return json_encode($return);
	}




		/**
	 * 发送私信
	 * @return void
	 */
	public function my_doPostMessage($d) {
		$return = array('data'=>L('PUBLIC_SEND_SUCCESS'),'status'=>1);
		if (empty($d['to']) || !CheckPermission('core_normal','send_message')) {
			$return['data']=L('PUBLIC_SYSTEM_MAIL_ISNOT');
			$return['status'] = 0;
			//exit();
		}
		if(trim(t($d['content'])) == ''){
			$return['data'] = L('PUBLIC_COMMENT_MAIL_REQUIRED');
			$return['status'] = 0;
			//exit();
		}
		$d['to'] = trim(t($d['to']),',');
		$to_num = explode(',', $d['to']);
		if( sizeof($to_num)>10 ){
			$return['data'] = '';
			$return['status'] = 0;
			//exit();
		}
		!in_array($d['type'], array(MessageModel::ONE_ON_ONE_CHAT, MessageModel::MULTIPLAYER_CHAT)) && $d['type'] = null;
		$d['content'] = h($d['content']);
		// 图片附件信息
		if (trim(t($d['attach_ids'])) != '') {
			$d['attach_ids'] = explode('|', $d['attach_ids']);
			$d['attach_ids'] = array_filter($d['attach_ids']);
			$d['attach_ids'] = array_unique($d['attach_ids']);
		}
		$res = model('Message')->postMessage($d, $this->mid);
		if ($res) {
			//exit();
		}else {
			$return['status'] = 0;
			$return['data']   = model('Message')->getError();;
			//exit();
		}

	}
}
