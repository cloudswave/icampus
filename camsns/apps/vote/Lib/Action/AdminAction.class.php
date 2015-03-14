<?php

    /**
     * AdminAction 
     * 投票后台管理
     * @uses Action
     * @package 
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    // import('admin.Action.AdministratorAction');
    tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
    class AdminAction extends AdministratorAction{
        /**
         * vote 
         * VoteModel的实例化对象
         * @var mixed
         * @access private
         */
        private $vote;

        /**
         * smile 
         * Smile的实例化对象
         * @var mixed
         * @access private
         */
        private $smile;
        /**
         * config 
         * VoteConfig的实例化对象
         * @var mixed
         * @access private
         */
        private $config;

        private $category;
        /**
         * _initialize 
         * 初始化
         * @access public
         * @return void
         */
        public function _initialize(){
        	parent::_initialize();
            $this->vote  = D( 'Vote' );
        }
        /**
         * basic 
         * 基础设置管理
         * @access public
         * @return void
         */
        public function index(){
            $config   = model('Xdata')->lget('vote');
            $this->assign( $config );
            $this->display();
        }	
       /**
         * recycle 
         * 回收站
         * @access public
         * @return void
         */
/*         public function recycle(  ) {
			//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
			if ( !empty($_POST) ) {
				$_SESSION['vote_admin_search_recycle'] = serialize($_POST);
			}else if ( isset($_GET[C('VAR_PAGE')]) ) {
				$_POST = unserialize($_SESSION['vote_admin_search_recycle']);
			}else {
				unset($_SESSION['vote_admin_search_recycle']);
			}
			
        	$this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
            //姓名，uid,日志内容
            //$_POST['name']     && $map['name']    = t( $_POST['name'] );
            $_POST['uid']      && $map['uid']     = intval( t( $_POST['uid'] ) );
            $_POST['title']  && $map['title'] = array( 'like',"%".t( $_POST['title'] )."%" );
            //isset( $_POST['isHot'] )    && $map['isHot'] = intval( $_POST['isHot'] );

            //处理时间
            //$_POST['stime'] && $_POST['etime'] && $map['cTime'] = $this->vote->DateToTimeStemp(t( $_POST['stime'] ),t( $_POST['etime'] ) );

            //处理排序过程
            $order = 'cTime DESC';

            $map['status'] = 2;

           $list = $this->vote->where( $map )->order( $order )->findPage( 20 );
            $this->assign( $list );
            $this->display();
        }*/
        /**
         * recycleAction 
         * 回收站动作
         * @access public
         * @return void
         */
/*        public function recycleMan(  ){
            $act = $_REQUEST['type'];  //动作
            isset($_REQUEST['id']) && $map['id']  = array('in',$_REQUEST['id']);  //id

            switch( $act ){
                case "resume":  //恢复
                    $result = $this->vote->setField( 'status',1,$map );
                    break;
                case "delete"://彻底物理删除
                    if( empty( $map ) ){
                        echo -1;
                        exit();
                    }
                    $map['status'] = 2;
                    $result = $this->vote->where( $map )->delete();
                    break;
                case "allresume":  //全部恢复
                    $result = $this->vote->setField( 'status',1);
                    break;
                case "alldelete"://全部彻底物理删除
                    $map['status'] = 2;
                    $result = $this->vote->where( $map )->delete();
                    if( $result ){
                        $this->success( "删除成功" );
                    }
                    break;
                default:
                    echo -1;
                    exit;
                    $this->error( "error_no_action" );
            }

            if( $result ){
                if ( !strpos($_REQUEST['id'],",") ){
                    echo 2;            //说明只是删除一个
                }else{
                    echo 1;            //删除多个
                }
            }else{
                echo -1;
            }

        }*/
        public function filterUser($var){
            if( 0 != intval($var['uid']) )
                return false;
            return true;
        }

        /**
         * votelist 
         * 获得所有人的votelist
         * @access public
         * @return void
         */
        public function votelist (){
	        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
			if ( !empty($_POST) ) {
				$_SESSION['vote_admin_search'] = serialize($_POST);
			}else if ( isset($_GET[C('VAR_PAGE')]) ) {
				$_POST = unserialize($_SESSION['vote_admin_search']);
			}else {
				unset($_SESSION['vote_admin_search']);
			}
			
        	$this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
			
            //$_POST['name']     && $this->vote->name    = t( $_POST['name'] );
            $_POST['uid']      && $this->vote->uid     = intval( t( $_POST['uid'] ) );
            $_POST['title']  && $this->vote->title = array( 'like',"%".t( $_POST['title'] )."%" );
            isset($_POST['isHot']) && $_POST['isHot']!=''	&&	$this->vote->isHot = intval( $_POST['isHot'] );

            //处理时间
            //$_POST['stime'] && $_POST['etime'] && $this->vote->cTime = $this->vote->DateToTimeStemp(t( $_POST['stime'] ),t( $_POST['etime'] ) );

            //处理排序过程
            //$order = isset( $_POST['sorder'] )?t( $_POST['sorder'] )." ".t( $_POST['eorder'] ):"cTime DESC";
            $order = 'id DESC';
            $list  = $this->vote->getVoteList(null,null,$order);
            $this->assign( $_POST );
            $this->assign( $list );
            $this->display();
        }

        /**
         * doDeleteVote 
         * 删除mili
         * @access public
         * @return void
         */
        public function doDeleteVote(){
            $voteid = array( 'in',$_REQUEST['id']);//要删除的id.
            $result       = $this->vote->doDeleteVote($voteid);
            if( false !== $result){
                if ( !strpos($_REQUEST['id'],",") ){
                    echo 2;            //说明只是删除一个
                }else{
                    echo 1;            //删除多个
                }
            }else{
                echo -1;               //删除失败
            }
        }

        /**
         * doChangeBase 
         * 修改全局设置
         * @access public
         * @return void
         */
        public function doChangeBase (){
			$config = $_POST;			
            if( model('Xdata')->lput('vote',$config)){
            	$this->assign('jumpUrl', U('vote/Admin/index'));
            	$this->success('保存成功');
            }else{
                $this->error( "保存失败" );
            }
        }

        public function doChangeIsHot(){
            
        	$vote['id'] = array( 'in',$_REQUEST['id']);        //要推荐的id.
        	//$vote['id'] = array( 'in',$_POST['id']);        //要推荐的id.
            $act  = $_REQUEST['type'];  //推荐动作
			
            $result  = $this->vote->doIsHot($vote,$act);

            if( false !== $result){
                    echo 1;            //推荐成功
            }else{
                echo -1;               //推荐失败
            }
        }
    }