<?php
/**
 * AdminAction
 * 相册管理
 * @uses Action
 * @package Admin
 * @version $2009-7-29$
 * @copyright 2009-2011 LiuXiaoqing
 * @author LiuXiaoqing <liuxiaoqing@thinksns.com>
 * @license ThinkSNS Version 1.6
 */
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {

	/**
	 * _initialize
	 * 初始化相册管理
	 * @access public
	 * @return void
	 */
	public function _initialize() {
		//管理权限判定
        parent::_initialize();
	}

	/**
	 * index
	 * 获取配置信息
	 * @access public
	 * @return void
	 */
    public function index(){
		//获取配置
        $config   = model('Xdata')->lget('photo');
		$this->assign($config);
		$this->display();
    }

	/**
	 * do_change_config
	 * 更改相册配置
	 * @access public
	 * @return void
	 */
	public function do_change_config() {
		//变量过滤 todo:更细致的过滤
		foreach($_POST as $k=>$v){
			$config[$k]	=	t($v);
		}
		$config['photo_file_ext'] = preg_replace("/bmp,||,bmp||bmp/",'',$config['photo_file_ext']);//过滤bmp
        if(model('Xdata')->lput('photo',$config)){
            $this->assign('jumpUrl', U('photo/Admin/index'));
			$this->success('设置成功！');
		}else{
			$this->error('设置失败！');
		}
	}

	/**
	 * photo_list
	 * 图片管理
	 * @access public
	 * @return void
	 */
    public function photo_list(){
		//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if ( !empty($_POST) ) {
			$_SESSION['vote_admin_search'] = serialize($_POST);
		}else if(!empty($_GET['albumId'])){
			$_SESSION['vote_admin_search'] = serialize($_GET);
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['vote_admin_search']);
		}else {
			unset($_SESSION['vote_admin_search']);
		}	
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');	

        $_POST['userId']    && $map['userId']    =   intval($_POST['userId']);
        $_POST['id']        && $map['id']        =   intval($_POST['id']);
        $_POST['albumId']   && $map['albumId']   =   intval($_POST['albumId']);
        $_GET['albumId']    && $map['albumId']   =   intval($_GET['albumId']);
        $_POST['name']      && $map['name']      =   array( 'like','%'.t( $_POST['name'] ).'%' );
        ($_POST['order']    && $order     		 =   'id '.t( $_POST['order'] )) || $order='id DESC';
        $_POST['limit']     && $limit            =   intval($_POST['limit']);

		$list	  = D('Photo')->where($map)->order($order)->findPage($limit);
		//获取相册名
		$albumDao = D('Album');
		foreach($list['data'] as $k=>$v){
			if(empty($albumNames[$v['albumId']])){
				$albumName            = $albumDao->field('name')->where("id={$v['albumId']}")->find();
				$albumNames[$v['albumId']] = getShort($albumName['name'],5);
			}
		}

		$this->assign($_POST);
		$this->assign($list);
		$this->assign('albumNames',$albumNames);
		$this->display();
    }

	/**
	 * album_list
	 * 专辑管理
	 * @access public
	 * @return void
	 */
    public function album_list(){
		//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if ( !empty($_POST) ) {
			$_SESSION['vote_admin_search'] = serialize($_POST);
		}else if(!empty($_GET['id'])){
			$_SESSION['vote_admin_search'] = serialize($_GET);
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['vote_admin_search']);
		}else {
			unset($_SESSION['vote_admin_search']);
		}	
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');	

        $_POST['userId']     && $map['userId']    =   intval(t( $_POST['userId']));
        $_POST['id']   		 && $map['id']        =   intval(t( $_POST['id']));
        $_GET['id'] 		 && $map['id']        =   intval(t($_GET['id']));
        $_POST['name']     	 && $map['name']      =   array( 'like','%'.t( $_POST['name'] ).'%' );
        ($_POST['order']     && $order     	      =   'id '.t( $_POST['order'] )) || $order='id DESC';
        $_POST['limit']    	 && $limit            =   intval( t( $_POST['limit'] ) );

		$list	=	D('Album')->where($map)->order($order)->findPage($limit);

		$this->assign($_POST);
		$this->assign($list);
		$this->display();
    }

	/**
	 * delete_photo
	 * 删除图片
	 * @access public
	 * @return void
	 */
	public function delete_photo() {
		$map['id']		=	t($_REQUEST['id']);
		$result	=	D('Album')->deletePhoto($map['id'],$this->mid,1);
		if($result){
			//删除成功
			if ( !strpos($_REQUEST['id'],",") ){
                echo 2;exit;         //说明只是删除一个
            }else{
                echo 1;exit;            //删除多个
            }
		}else{
			//删除失败
			echo "0";exit;
		}
	}

	/**
	 * delete_album
	 * 解锁图片或相册
	 * @access public
	 * @return void
	 */
	public function delete_album() {
		$map['id']		=	t($_REQUEST['id']);
		$result	=	D('Album')->deleteAlbum($map['id'],$this->mid,1);
		if($result){
			//删除成功
			if ( !strpos($_REQUEST['id'],",") ){
                echo 2;exit;         //说明只是删除一个
            }else{
                echo 1;exit;            //删除多个
            }
		}else{
			//删除失败
			echo "0";exit;
		}
	}
	 
	 public function doChangeIsHot(){            
        	$map['id'] = array( 'in',t($_REQUEST['id']));        //要推荐的id
            $act  = $_REQUEST['type'];  //推荐动作
			
			if( empty($map) ) {
				throw new ThinkException( "不允许空条件操作数据库" );
			}
			switch( $act ) {
				case "recommend":   //推荐
					$field = array( 'isHot','rTime' );
					$val = array( 1,time() );
					$result = D('Album')->setField( $field,$val,$map );
					break;
				case "cancel":   //取消推荐
					$field = array( 'isHot','rTime' );
					$val = array( 0,0 );
					$result = D('Album')->setField( $field,$val,$map );
					break;
	
			}
            if( false !== $result){
                echo 1;exit;       //推荐成功
            }else{
                echo -1;exit;      //推荐失败
            }
        }

	/**
	 * photo_recycle
	 * 图片回收站管理
	 * @access public
	 * @return void
	 */
/*    public function photo_recycle(){

		$map['isDel']	=	1;

		$list		=	D('Photo')->order('id DESC')->where($map)->findPage(20);

		$this->assign($list);
		$this->display();
    }*/

	/**
	 * album_recycle
	 * 相册回收站管理
	 * @access public
	 * @return void
	 */
/*    public function album_recycle(){

		$map['isDel']	=	1;

		$list		=	D('Album')->order('id DESC')->where($map)->findPage(20);

		$this->assign($list);
		$this->display();
    }*/

	/**
	 * restore_photo
	 * 恢复图片
	 * @access public
	 * @return void
	 */
/*	public function restore_photo() {
		$map['id']		=	t($_REQUEST['id']);

		$result	=	D('Album')->restorePhoto($map['id'],$this->mid,1);
		if($result){
			//删除成功
			//同步个人空间图片数
			$photoCount	=	D('Photo')->where("userId='$this->mid' ")->count();
			$this->api->space_changeCount( 'photo',$photoCount );

			echo "1";exit;
		}else{
			//删除失败
			echo "0";exit;
		}
	}*/

	/**
	 * restore_album
	 * 恢复图片
	 * @access public
	 * @return void
	 */
/*	public function restore_album() {
		$map['id']		=	t($_REQUEST['id']);

		$result	=	D('Album')->restoreAlbum($map['id'],$this->mid,1);
		if($result){
			//删除成功
			//同步个人空间图片数
			$photoCount	=	D('Photo')->where("userId='$this->mid' ")->count();
			$this->api->space_changeCount( 'photo',$photoCount );

			echo "1";exit;
		}else{
			//删除失败
			echo "0";exit;
		}
	}
*/
	/**
	 * clean_photo
	 * 彻底清除回收站的图片
	 * @access public
	 * @return void
	 */
/*    public function clean_photo(){

		$ids	=	t($_REQUEST['id']);

		//解析ID成数组
		if(!is_array($ids)){
			$aids	=	explode(',',$ids);
		}

		$map['id']		=	array('in',$aids);
		$map['isDel']	=	1;

		$result		=	D('Photo')->where($map)->delete();

		if($result){
			echo "1";exit;
		}else{
			//删除失败
			echo "0";exit;
		}
    }*/

	/**
	 * clean_album
	 * 彻底清除回收站的相册
	 * @access public
	 * @return void
	 */
/*    public function clean_album(){

		$ids	=	t($_REQUEST['id']);

		//解析ID成数组
		if(!is_array($ids)){
			$aids	=	explode(',',$ids);
		}

		$map['id']		=	array('in',$aids);
		$map['isDel']	=	1;

		$result		=	D('Album')->where($map)->delete();

		if($result){
			echo "1";exit;
		}else{
			//删除失败
			echo "0";exit;
		}
    }*/
}
?>