<?php
/**
 * IndexAction
 * blog的Action.接收和过滤网页传参
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {
        private $filter;
        private $blog;
        private $lastblog;
        private $config;
        private static $friends=array();
        protected $app = null;

        /**
         * __initialize
         * 初始化
         * @access public
         * @return void
         */
        public function _initialize() {
        	//parent::_initialize();
            $this->app = $GLOBALS['ts']['app'];
			//设置日志Action的数据处理层
            $this->blog  = D('Blog','blog');
            $this->follow= D('Follow','blog');
            $this->config= D('AppConfig','blog')->getConfig();
            $this->assign($this->config);
            $isAdmin = model('UserGroup')->isAdmin($this->mid);
            $this->assign('isAdmin',$isAdmin);
        }
        // 日志统计数
        public function commentCount($list){
            foreach ($list['data'] as $key => $value) {
                $map['app'] = 'public';
                $map['table'] = 'blog';
                $map['row_id'] = $value['id'];
                $commentCount = M('comment')->where($map)->count();
                $list['data'][$key]['commentCount'] = $commentCount;
                D('blog')->where('id='.$value['id'])->setField('commentCount' , $commentCount);
            }
            return $list;
        }
        /**
         * index
         * 好友的日志
         * @access public
         * @return void
         */
        public function index() {
			$list = $this->blog->getAllData('popular', $this->mid);
            $uids = array_unique(getSubByKey($list['data'],'uid'));
            $this->assign('user_info',model('User')->getUserInfoByUids($uids));
//             $list = $this->commentCount($list);//评论数统计

			$relist= $this->blog->getIsHot();
			$this->assign('relist',$relist);
			$this->assign( 'uid',$this->mid );
			$this->assign( 'order',t($_GET['order']) );
			$this->assign( $list );
			$this->assign( 'all','true' );
			$this->setTitle("热门{$this->app['app_alias']}");
			$this->display();
        }

        /**
         * search
         * 搜索日志
         * @access public
         * @return void
         */
        public function search() {
			$keyword	=	h($_GET['key']);
			//获得日志数据集,自动获得当前登录用户的好友日志
			$map['title']  = array('like',"%{$keyword}%");
			if($keyword)
				$list = $this->blog->getBlogList($map,'*','cTime desc',10,$this->mid);
            foreach ($list['data'] as $key => $value) {
                $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
            }
            $this->assign('user_info',$getUserInfo);

			$relist= $this->blog->getIsHot();
			$this->assign('relist',$relist);
			$this->assign( 'api',$this->api);
			$this->assign( 'uid',$this->mid );
			$this->assign( $list );
			$this->assign( 'all','true' );
			$this->setTitle("搜索文章: ".$keyword);
			$this->display();
        }

        /**
         * my
         * 我的日志
         * @access public
         * @return void
         */
        public function my() {
        	//获得日志数据集
            $outline = D( 'BlogOutline' );
            $list    = isset( $_GET['outline'] )?
            	$outline->getList( $this->mid ): //草稿箱
            	$this->__getBlog( $this->mid,'*','cTime desc' ); //我的日志
//             // 归档数据
//             if($_GET['date']){
//                 $list = $this->blog->getDataByDate($_GET['date'],$this->mid);
//             }

            foreach($list['data'] as $k => $v) {
            	if ( empty($v['category_title']) && !empty($v['category']) )
            		$list['data'][$k]['category_title'] = M('blog_category')->where('id='.$v['category'])->getField('name');
            }
            //归档数据
            $url = isset( $_GET['cateId'] )? 'Index&act=my&cateId='.intval($_GET['cateId']):'Index&act=my';
            $file_away = $this->_getWiget( $url,$this->mid );

            //获得分类的计数
            $category = $this->__getBlogCategoryCount($this->mid);

            //草稿箱计数
            $outline = D( 'BlogOutline' )->where( 'uid ='.$this->mid )->count();

            //检查是否可以查看全部日志
            $this->__checkAllModel();
			$relist= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            //获得归档传输数据
            $this->assign( 'oc',$outline );
            $this->assign( 'file_away',$file_away );
            $this->assign('category',$category);
            $this->assign( $list );
            
            $this->setTitle("我的{$this->app['app_alias']}");
            $this->display('index');
        }

        public function news() {
	        //检查是否可以查看这个页面
			if( $this->__checkAllModel() ) {
    			$list = $this->blog->getAllData('new', $this->mid);
//                 $list = $this->commentCount($list);//评论数统计
                foreach ($list['data'] as $key => $value) {
                    $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
                }
                $this->assign('user_info',$getUserInfo);
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',t($_GET['order']) );
                $this->assign( $list );
                $this->assign( 'all','true' );
                $this->setTitle("最新{$this->app['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
            }
        }
        public function followsblog() {
        	//检查是否可以查看这个页面
        	$mid=$this->mid;
            if( $this->__checkAllModel() ) {
            	$list = $this->blog->getFollowsBlog($this->mid);
//                 $list = $this->commentCount($list);//评论数统计

                foreach ($list['data'] as $key => $value) {
                    $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
                }
                $this->assign('user_info',$getUserInfo);

                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',t($_GET['order']) );
                $this->assign( $list );
                $this->assign( 'all','true' );
                $this->setTitle("我的关注的{$this->app['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
			}
        }
        private function __checkAllModel() {
        	return true;

        	//获取配置，是否可以查看全部的日志
            if( $this->blog->getConfig( 'all' ) ) {
            	$this->assign( 'all','true' );
                return true;
            }
            return false;
        }


        /**
         * show
         * 日志显示页
         * @access public
         * @return void
         */
        public function show() {
			
            unset($_SESSION['blog_use_widget_share']);
            //获得日志id
            $id = intval($_GET['id']);
            $this->blog->setUid( $this->mid );

            //全站日志
            if( $this->blog->getConfig( 'all' ) ) {
                 $this->assign( 'all','true' );
            }


            //日志所有者
            $bloguid = intval($_GET['mid']);


            //获得日志的详细内容,第二参数通知是当前还是上一篇下一篇
            isset( $_GET['action'] ) && $how = t($_GET['action']);
            $list     = $this->blog->getBlogContent($id,$how,$bloguid);
            $list['user_info'] = model('User')->getUserInfo($list['uid']);
            //dump($list);exit;
			if($this->mid != $bloguid){
				$relationship = getFollowState($this->mid, $bloguid);
				if($list['private'] == 4){
                    $this->assign('jumpUrl', U('blog/Index/index'));
					$this->error('本日志仅主人自己可见');
				}elseif($list['private'] == 2 && $relationship=='unfollow'){
                    $this->assign('jumpUrl', U('blog/Index/index'));
					$this->error('本日志仅主人的粉丝可见');
				} else if ($list['private'] == 5 && model('Friend')->identifyFriend($this->mid, $bloguid) != FriendModel::ARE_FRIENDS) {
                    $this->assign('jumpUrl', U('blog/Index/index'));
					$this->error('本日志仅主人朋友可见');
				}
			}

            //Converts special HTML entities back to characters.
            $list['content'] = htmlspecialchars_decode($list['content']);
            $isExist = $this->blog->where('id='.$id)->find();
            //检测是否有值。不允许非正常id访问
            if( false == $list || empty($isExist) || $isExist['status'] == 2) {
            		$this->assign('jumpUrl',U('blog/Index'));
                    $this->error( '日志不存在或者已删除！' );
            }
             //Converts special HTML entities back to characters.
            $list['content'] = htmlspecialchars_decode($list['content']);

            //获得正确的当前日志ID
            $id = $list['id'];
            // 关注关系
            $this->assign( 'relationship', $relationship );

            //检测密码
            if (isset($_POST['password'])) {
                if(md5(t($_POST['password'])) == $list['private_data']) {
                        // Cookie::set($id.'password',md5(t($_POST['password'])));
                        cookie($id.'password',md5(t($_POST['password'])));
                        $list['private'] = 0;
                }
            } else {
                // if( 3 == $list['private'] && Cookie::get($id.'password') == $list['private_data']) {
                if( 3 == $list['private'] && cookie($id.'password') == $list['private_data']) {
                        $list['private'] = 0;
                }
            }

            //不是日志所有人读日志才会刷新阅读数.只有非日志发表人才进行阅读数刷新
            if( !empty( $bloguid ) && $this->mid != $bloguid ) {
                $options = array( 'id'=>$id,'uid'=>$this->mid,'type'=>APP_NAME,'lefttime'=>"30" );
                //浏览计数，防刷新
                //if(  browseCount( APP_NAME,$id,$this->mid,'30') ) {
                        $this->blog->changeCount( $id );
                //}
            }


                //获取发表人的id
            $name          = $this->blog->getOneName( $bloguid );

            //他人日志渲染特殊的变量和数据
            if( $this->mid != $bloguid ) {
            //查看这篇日志，访问者是否推荐过
                $recommend = D( 'Blog' )->checkRecommend( $this->mid,$list['id'] );

                //如果是其它人的日志。需要获得最新的10条日志
                $bloglist  = $this->blog->getBlogTitle( $list['uid'] );
                $this->assign( 'bloglist',$bloglist );
                //$this->assign( 'recommend',$recommend );
            }

            //渲染公共变量
            $relist= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            $this->assign( $list );
            $this->assign( 'blog', $list );
            $this->assign( 'guest',$this->mid );
            $this->assign( 'name',$name['name'] );
            $this->assign( 'uid',$bloguid );
            $this->assign('isOwner', $this->mid == $bloguid ? '1' : '0');
            $this->assign('isAdmin',model('UserGroup')->isAdmin($this->mid));
            
            $this->setTitle(getUserName($list['uid']).'的文章: '.$list['title']);
            $this->display('blogContent');
        }

        /**
         * personal
         * 个人的日志列表
         * @access public
         * @return void
         */
        public function personal() {
        //获得日志数据集
                $uid   = intval($_GET['uid']);
                if($uid <= 0)
                	$this->error('参数错误');

                //获得blog的列表
                $list             = $this->__getBlog($uid,'*','cTime desc');

                //获得分类的计数
                $category = $this->__getBlogCategoryCount($uid);

                //归档数据
                $url       = isset( $GET['cateId'] )?
                    'Index/personal/uid/'.$uid.'/cateId/'.intval($_GET['cateId']):
                    'Index/personal/uid/'.$uid;
                $file_away = $this->_getWiget( $url,$uid);

                //组装数据
                $this->assign( 'file_away',$file_away );
                $this->assign('api',$this->api);

                $this->assign('category',$category);
                $name = getUserName($uid);
                $this->assign('name', $name);
                $this->assign( $list );

                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->setTitle($name . '的' .$this->app['app_alias']);
                $this->display('index');
        }

        private function __getBlogCategoryCount($uid) {
                $cateId = null;
                if(isset($_GET['cateId'])) {
                    $cateId = intval($_GET['cateId']);
                }
                $category = $this->blog->getBlogCategory($uid,$cateId);
                // if(!$category) {
                //         $this->error(L('参数错误'));
                //         exit;
                // }
                return $category;
        }

        /**
         * doDeleteblog
         * 删除blog
         * @access public
         * @return void
         */
        public function doDeleteblog(  ) {

                $this->blog->id = $_REQUEST['id']; //要删除的id;
                $result         = $this->blog->doDeleteblog(null,$this->mid);

                if( false != $result) {
					model('Credit')->setUserCredit($this->mid,'delete_blog');
                    redirect( U('blog/Index/my') );
                }else {
                    $this->error( "删除日志失败" );
                }
        }

        /**
         * deleteCategory
         * 删除分类
         * @access public
         * @return void
         */
        public function deleteCategory(  ) {
                $data['id'] = intval($_POST['id']);
                if( 0 === $data['id'] )
                        return false;

                //删除分类和将分类的日志转移到其它分类里
                isset( $_POST['toCate'] ) && !empty( $_POST['toCate'] ) && $toCate   = $_POST['toCate'];

                $category   = D( 'BlogCategory' );
                return $category->deleteCategory( $data,$toCate,$this->blog );
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addBlog() {

                $category  = $this->blog->getCategory($this->mid);
                $savetime  = $this->blog->getConfig( 'savetime' );

                //表情控制
                $smile     = array();
                $smileType = $this->opts['ico_type'];
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                //$smileList = $this->getSmile($smileType);
                //$smilePath = $this->getSmilePath($smileType);
                $this->assign( 'smileList',$smileList );
                $this->assign( 'smilePath',$smilePath );
                $this->assign( 'savetime',$savetime );
                $this->assign( 'blog_category',$category );
                
                $this->setTitle("发表{$this->app['app_alias']}");
                $this->display();
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addAjaxBlog() {
				$use = intval($_POST['used']);
                $category  = $this->blog->getCategory($this->mid);
                $savetime  = $this->blog->getConfig( 'savetime' );

                //表情控制
                $smile     = array();
                $smileType = $this->opts['ico_type'];
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'savetime',$savetime );
                $this->assign( 'category',$category );
                if($use){
                	$this->display('addAjaxBlog_used');
                }else{
                	 $this->display();
                }

        }

        public function edit() {
                $category = $this->blog->getCategory($this->mid);
                $this->assign( 'blog_category',$category );
                $id = intval($_GET['id']);
                $isAdmin = model('UserGroup')->isAdmin($this->mid);
                if( $_GET['edit'] ) {
                        $outline = D( 'BlogOutline' );
                        //检查是否存在这篇日志
                        if( false == $list = $outline->getBlogContent( $id,null,$_GET['mid']))
                                $this->error( L( 'error_no_blog' ) );
                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if( $list['uid'] != $this->mid ) {
                                $this->error( L( 'error_no_role' ) );
                        }

                        $list['saveId'] = $list['id'];
                        unset( $list['id'] );

                        //定义连接
                        $link = __URL__."&act=doAddBlog";
                        unset ( $list['friendId'] );
                //编辑新的日志
                }else {
                        $link = __URL__."&act=doUpdate";
                        $dao = $this->blog;

                        if( false == $list = $this->blog->getBlogContent( $id,null,$_GET['mid'] ))
                                $this->error( L( 'error_no_blog' ) );

                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if(!$isAdmin && ($list['uid'] != $this->mid ))
                                $this->error( L( 'error_no_role' ) );
                }


				$relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'link',$link );
                $this->assign( $list );
                $this->display();
        }

        /**
         * doAddblog
         * 添加blog
         * @access public
         * @return void
         */
        public function doAddBlog() {
            $title = text(h($_POST['title']));

        	if(empty($title)) {
            	$this->error( "请填写标题" );
            }
        		
            if( mb_strlen($title, 'UTF-8') > 25 ) {
				$this->error( "标题不得大于25个字符" );
            }

            $content = text(html_entity_decode(h(t($_POST['content']))));
            //检查是否为空
            if( empty($_POST['content']) || empty( $content )  ) {
                    $this->error( "日志内容不能为空" );
            }

            //得到发日志人的名字
            $userName = $this->blog->getOneName( $this->mid );

            //处理发日志的数据
            $data = $this->__getPost();
            $data['cTime'] = time();
            $data['mTime'] = time();
            $category_name = M('blog_category')->where('id ='.t($_POST['category']))->find();
            $data['category_title'] = $category_name['name'];
            
            //添加日志
            $images = matchImages($data['content']);
            $images[0] && $data['cover'] = $images[0];
            $add = $this->blog->add($data);
            $blogId = mysql_insert_id();
            //如果是有自动保存的数据。删除自动保存数据
            if( isset( $_POST['saveId'] ) && !empty( $_POST['saveId'] ) ) {
                    $BlogOutline = D( 'BlogOutline' );
                    $BlogOutline->where( 'id = '.$_POST['saveId'] )->delete();
            }

            if( $add ) {
				X('Credit')->setUserCredit($this->mid,'add_blog');
				$html = '【'.text($data['title']).'】'.getShort($content,80).U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid));
				$image  = $images[0]?$images[0]:false;
				$this->ajaxData = array('url'=>U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid)),
					'id' =>$add,
				    'html'=>$html,
				    'image'=>$image,
					'title'=>t($_POST['title']),
				);
    //             $this->assign('jumpUrl', U('blog/Index/show',array('id'=>$blogId,'mid'=>$this->mid)));
				// $this->success('发表成功');
                $res['id'] = $blogId;
                $res['mid'] = $this->mid;
                exit($this->ajaxReturn($res, '发布成功', 1));
            }else {
                $this->error( "添加失败" );
            }
        }

        /**
         * doUpdate
         * 执行更新日志动作
         * @access public
         * @return void
         */
        public function doUpdate() {
        		if (empty($_POST['title'])) {
                    $this->error( "请填写标题" );
                }
        		if (mb_strlen($_POST['title'], 'UTF-8') > 25 ) {
                	$this->error( "标题不能大于25个字符" );
                }
                $content = text(html_entity_decode(h(t($_POST['content']))));

                if( empty($_POST['content']) || empty( $content ) ) {
                    $this->error( "日志内容不能为空" );
                }

                $userName = $this->blog->getOneName( intval($_POST['uid']) );

                $id       = intval($_POST['id']);
                //检查更新合法化
                if(!model('UserGroup')->isAdmin($this->mid) && ($this->blog->where( 'id = '.$id )->getField( 'uid' ) != $this->mid )) {
                        $this->error( L('error_no_role') );
                }
                $data = $this->__getPost();
                $data['content'] = $data['content']; 
                $images = matchImages($data['content']);
                $data['cover'] = $images[0];
                $save = $this->blog->doSaveBlog($data,$id);

                if ($save) {
                    // redirect(U('blog/Index/show', array('id'=>$id, 'mid'=>$this->mid)));
                    $res['id'] = $id;
                    $res['mid'] = intval($_POST['uid']);
                    exit($this->ajaxReturn($res, '发布成功', 1));
                } else {
                    $this->error( "修改失败" );
                }
        }

        private function __getPost() {
        		//得到发日志人的名字
                $userName = $this->blog->getOneName( intval($_POST['uid']) );
                $data['name']     = $userName['name'];
                $data['content']  = safe($_POST['content']);
                $data['uid']      = isset($_POST['uid']) ?intval($_POST['uid']) : $this->mid;
                $data['category'] = intval($_POST['category']);
                $data['password'] = text($_POST['password']);
                $data['mention']  = $_POST['fri_ids'];
                $data['title']    = !empty($_POST['title']) ?text($_POST['title']):"无标题";
                $data['private']  = intval($_POST['private']);
                $data['canableComment'] = intval(t($_POST['cc']));

                //处理attach数据
                $data['attach']         = serialize($this->__wipeVerticalArray($_POST['attach']));
                if(empty($_POST['attach']) || !isset($_POST['attach'])) {
                        $data['attach'] = null;
                }
                return $data;
        }

        private function __wipeVerticalArray($array) {
                $result = array();
                foreach($array as $key=>$value) {
                        $temp = explode('|', $value);
                        $result[$key]['id'] = $temp[0];
                        $result[$key]['name'] = $temp[1];
                }
                return $result;

        }

        /**
         * autoSave
         * 自动保存
         * @access public
         * @return void
         */
        public function autoSave(  ) {
                $content = trim(str_replace('&amp;nbsp;','',t($_POST['content'])));
                //检查是否为空
                if( empty($_POST['content']) || empty( $content )  ) {
                        $this->error( "日志内容不能为空" );
                        exit();
                }

                $add="";
                $userName = $this->blog->getOneName( $this->mid );

                //处理数据
                $data['name']     = $userName['name'];
                $data['content']  = $_POST['content'];
                $data['uid']      = $this->mid;
                $data['category'] = $_POST['category'];
                $data['password'] = $_POST['password'];
                $data['mention']  = $_POST['mention'];
                $data['title']    = !empty($_POST['title']) ?$_POST['title']:"无标题";
                $data['private']  = intval($_POST['private']);
                $data['canableComment'] = intval(t($_POST['cc']));
                if( isset( $_POST['updata'] ) ) {
                //更新数据，而不是添加新的草稿
                        $add = intval(trim($_POST['updata']));
                        $result = $this->blog->updateAuto( $data,$add );
                }else {
                //自动保存
                        $add = $this->blog->autoSave($data);
                }
                if( $add || $result) {
                        echo date('Y-m-d h:i:s',time()).",".$add;
                }else {
                        echo -1;
                }
        }

        /**
         * outline
         * 草稿箱
         * @access public
         * @return void
         */
        public function outline(  ) {
                $this->assign( $list );
                $this->display();
        }

        /**
         * deleteOutline
         * 删除
         * @access public
         * @return void
         */
        public function deleteOutline(  ) {
                if( empty($_POST['id']) ) {
                        echo -1;
                        return;
                }


                $map['id'] = array( "in",array_filter( explode( ',' , $_POST['id'] ) ));
                $outline = D( 'BlogOutline' );
                //检查合法性
                if( $outline->where( $map )->getField( 'uid' ) != $this->mid ) {
                        echo -1;
                }

                if( $result = $outline->where( $map )->delete() ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * admin
         * 个人管理页面
         * @access public
         * @return void
         */
        public function admin() {
        	//获得分类名称
        	//获得分类下的日志数
            $category   = $this->__getBlogCategoryCount( $this->mid );
            $relist		= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            $this->assign( 'category',$category );
            
            $this->setTitle("{$this->app['app_alias']}管理");
            $this->display();
        }


        /**
         * deleteCateFrame
         * 删除分类时，转移其中的日志
         * @access public
         * @return void
         */
        public function deleteCateFrame(  ) {
                $id       = intval($_GET['id']);
                $category = $this->blog->getCategory( $this->mid );
                foreach( $category as $key=>$value ) {
                        if( $value['id'] == $id)
                                unset( $category[$key] );
                }
                $this->assign( 'category',$category );
                $this->display();

        }

        /**
         * addCategory
         * 添加分类
         * @access public
         * @return void
         */
        public function addCategory() {
                $data['name'] = h(t($_POST['name']));
                $data['uid']  = $this->mid;
                $data['name'] = keyWordFilter(h(t($_POST['name'])));

                $category   = D( 'BlogCategory' );
                $result = $category->addCategory($data,$this->blog);
        }

        public function addCategorys() {
                $this->display();
        }

        //检测分类名是否存在
        public function isCategoryExist() {
            $name = t($_POST['name']);
            $list = M("BlogCategory")->where(array('name'=>$name,'uid'=>$this->mid))->getField("name");
            if($list){
                echo 1;//已存在
            }else{
                echo 0;
            }
        }

        public function filterCategory() {
            $category = t($_POST['name']);
            echo keyWordFilter($category);
        }

        /**
         * editCategory
         * 修改分类
         * @access public
         * @return void
         */
        public function editCategory() {
        	foreach($_POST['name'] as $k => $v){
                $_POST['name'][$k] = h(t($v));
                if(!$_POST['name'][$k]){
                    $this->error('分类名不能为空');
                }
            }
        		

        	if ( count($_POST['name']) != count(array_unique($_POST['name'])) )
        		$this->error('分类名不允许重复, 请重新输入');

			$category = D( 'BlogCategory' );
            $result   = $category->editCategory( $_POST['name'] );

            // 更新日志信息
            foreach ($_POST['name'] as $k => $v) {
            	M('blog')->where("`category`='{$k}'")->setField('category_title', $v);
            }

            $this->assign('jumpUrl', U('blog/Index/admin'));
            $this->success('保存成功');
        }

        /**
         * TODO 删除
         * recommend
         * 推荐操作
         * @access public
         * @return void
         */
        public function recommend(  ) {
                $name          = $this->blog->getOneName($this->mid);
                $map['blogid'] = $_POST['id'];
                $map['uid']    = $this->mid;
                $map['name']   = $name['name'];
                $map['type']   = "recommend";
                $action        = $_POST['act'];

                //添加推荐和推荐人数据。并且更新日志的推荐数
                if( $result = D( 'Blog' )->addRecommendUser( $map,$action ) ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * TODO 删除
         */
        public function commentSuccess() {
        //$post = str_replace('\\', '', stripslashes($_POST['data']));
                $result = json_decode(stripslashes($_POST['data']));  //json被反解析成了stdClass类型
                $count = $this->__setBlogCount($result->appid);

                //发送两条消息
                $data = $this->__getNotifyData($result);
                $this->api->comment_notify('blog',$data,$this->appId);
                echo $count;
        }

        /**
         * TODO 删除
         */
        private function __getNotifyData($data) {
        //发送两条消息
                $result['toUid'] = $data->toUid;
                $need  = $this->blog->where('id='.$data->appid)->field('uid,title')->find();


                $result['uids'] =$need['uid'];
                $result['url'] = sprintf('%s/Index/show/id/%s/mid/%s','{'.$this->appId.'}',$data->appid,$result['uids']);
                $result['title_body']['comment'] = $data->comment;
                $result['title_data']['title'] = sprintf("<a href='%s'>%s</a>",$result['url'],$need['title']);
                $result['title_data']['type']  = "日志";
                // if(empty($data->toUid) && $this->mid != $need['uid'] && $data->quietly == 0){
                //     $title['title'] = $result['title_data']['title'];
                //     $uid = $result["uids"];
                //     $title['user'] = '<a href="__TS__/space/'.$uid.'">'.getUserName($uid)."</a>";
                //     $body['comment'] = $data->comment;
                //     $this->blog->doFeed('blog_comment',$title,$body);
                // }
                return $result;
        }

        /**
         * TODO 删除
         */
        public function deleteSuccess() {
                $id = $_POST['id'];
                echo $this->__setBlogCount($id);;
        }

        /**
         * TODO 删除
         */
        private function __setBlogCount($id) {
                $count = $this->api->comment_getCount('blog',$id);
                $result = $this->blog->setCount($id,$count);
                return $count;
        }

        /**
         * fileAway
         * 获取归档查询的数据
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function fileAway($uid,$cateId = null) {
                $findTime           = t($_GET['date']); //获得传入的参数
                $this->blog->status = 1;
                $this->blog->uid    = $uid;
                isset( $cateId ) && $this->blog->category = intval($cateId);
                return $this->blog->fileAway( $findTime ) ;
        }

        /**
         * __getblog
         * 获得blog列表
         * @param int|array|string $uid uid
         * @access private
         * @return void
         */
        private function __getBlog ($uid=null,$field=null,$order=null,$limit=null) {
        	//将数字或者数字型字符串转换成整型
            is_numeric( $uid ) && $uid = intval( $uid );

            //归档
            if( isset( $_GET['date'] ) ) {
                    return $this->fileAway( $uid, $_GET['cateId'] );
            }

            //分类
            if( isset( $_GET['cateId'] ) ) {
                    $this->blog->category = intval($_GET['cateId']);
                    $this->assign( 'cateId', intval($_GET['cateId']) );
            }

            //给blog对象的uid属性赋值
            if( isset( $uid ) ) {
            	$map['uid']   = $uid;
                if ($uid != $this->mid) {
                	$relationship	=	getFollowState($uid,$this->mid);
					if($relationship=='eachfollow'||$relationship=='havefollow'){
						$map['private']	= array('in',array(0,2));
					// } else if (model('Friend')->identifyFriend($this->uid, $this->mid) == FriendModel::ARE_FRIENDS) {
					// 	$map['private']	= array('in',array(0,5));
					}else{
						$map['private']	= 0;
					}
                }
            }else {
                    $gid = intval($_GET['gid']);
                   // $friends = $this->api->friend_getGroupUids($gid);
                    if(empty($friends)) return false;
                    $map['uid']  = array( "in",$friends);
                    $this->blog->private = array('neq',2);
            }
            if(!$limit){

            }
            return $this->blog->getBlogList ($map, $field, $order);
        }

        /**
         * _getWiget
         * 获得需要传递给widget的数据
         * @param mixed $link
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function _getWiget($link,$uid) {
                $condition['uid'] = $uid;
                if( empty( $uid) )
                        unset( $condition);
                $map['fileaway']  = L( 'fileaway' );
                $map['link']      = $link;
                $map['condition'] = $condition ;
                $map['limit']     = $this->blog->getConfig( 'fileawaypage' );
                $map['tableName'] = C('DB_PREFIX').'_blog';
                $map['APP']       = __APP__;
                return $map;
        }
        /**
         * _checkCategory
         * 检查分类是否合法
         * @param mixed $cateId
         * @param mixed $category
         * @static
         * @access private
         * @return void
         */
        private static function _checkCategory( $cateId,$category ) {
                $temp = array();
                foreach( $category as $value ) {
                        $temp[] = $value['id'];
                }
                return in_array($cateId,$temp);
        }
        private function _checkUser( $uid ) {
                $result = $this->api->user_getInfo($uid,'id');
                return $result;
        }
}