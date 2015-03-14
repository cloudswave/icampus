<?php
/**
 * 勋章馆
 * @author Stream
 *
 */
class MedalAction extends Action{
	public function __construct(){
		parent::__construct();
		if( !CheckTaskSwitch() ){
			$this->error( '该页面不存在！' );
		}
	}
	public function index(){
		$type = $_GET['type'] ? intval( $_GET['type'] ) : 1;
		$uid = $_GET['uid'] ? intval ( $_GET['uid'] ) : $GLOBALS['ts']['mid'];
		
		if ( $type == 1 ){
			$user = model( 'User' )->getUserInfo($uid);
			$medals = $user['medals'];
			if ( $medals ){
				$map['id'] = array( 'in' , getSubByKey( $medals , 'id') );
				$list = model( 'Medal' )->getList( $map , 10 );
			} else {
				$list['count'] = 0;
			}
			$this->assign( 'face' , $user['avatar_middle'] );
			$this->assign( 'spaceurl' , $user['space_url'] );
			$this->assign( 'uname' , $user['uname'] );
			$this->assign( 'uid' , $user['uid'] );
		} else {
			$list = model( 'Medal' )->getList('',10);
		}
		$isme = $uid == $this->mid ? true : false;
		$this->assign( 'isme' , $isme );
		
		$lastpage = $list['nowPage'] - 1;
		$nextpage = $list['nowPage'] + 1;
		
		$showlast = true;
		if ( $lastpage <= 0 ){
			$showlast = false;
		}
		$shownext = true;
		if ( $nextpage > $list['totalPages']){
			$shownext = false;
		}
		$this->assign( 'lpage' , $lastpage );
		$this->assign( 'npage' , $nextpage );
		$this->assign( 'showlast' , $showlast );
		$this->assign( 'shownext' , $shownext );
		$this->assign( 'type' , $type );
		$this->assign( $list );
		$this->display();
	}
	/**
	 * 勋章详细
	 */
	public function showdetail(){
		$id = intval ( $_GET['id'] );
		$type = intval ( $_GET['type'] );
		if ( $id ){
			if ( $type == 1 ){
				$umedal = D('medal_user')->where('uid='.$GLOBALS['ts']['mid'].' and medal_id='.$id)->field('`desc`,ctime')->find();
				$desc = $umedal['desc'];
				$ctime = $umedal['ctime'];
			}
			$medal = model( 'Medal' )->where('id='.$id)->find();
			if ( $medal ){
				$src = explode('|', $medal['src']);
				$medal['src'] = getImageUrl ( $src[1] );
				$desc && $medal['desc'] = $desc;
				$ctime && $medal['ctime'] = date('Y-m-d H:i:s',$ctime);
				$this->assign( 'medal' , $medal );
				$this->display();
			}
		}
	}
}
?>