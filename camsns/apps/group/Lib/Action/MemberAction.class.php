<?php

class MemberAction extends BaseAction
{
	var $member;

	public function _initialize(){
		parent::_initialize();
		$this->member = D('Member');
		$this->assign('current','member');
        $this->setTitle("成员 - " . $this->groupinfo['name']);
	}

	//所有成员
	public function index() {
		if($_GET['order'] == 'new') {
			$order = 'ctime DESC';
			$this->assign('order', $_GET['order']);
		}elseif($_GET['order'] == 'visit'){
			$order = 'mtime DESC';
			$this->assign('order', $_GET['order']);
		}else{
			$order = 'level ASC';
			$this->assign('order', 'all');
		}

		$search_key = $this->_getSearchKey();
		if ($search_key) {
			
		} else {
			$memberInfo = $this->member->order($order)->where('gid=' . $this->gid . " AND status=1 AND level>0")->findPage(20);
		}
		foreach ($memberInfo['data'] as &$member) {
			$feedid = D('GroupFeed')->where("uid={$member['uid']} AND gid={$member['gid']} AND is_del=0")->order('publish_time DESC')->getField('feed_id');
			$feedid && $member['feed'] = D('GroupFeed')->getFeedInfo($feedid);
		}
		$uids = getSubByKey($memberInfo['data'], 'uid');	
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = model('Follow')->getFollowStateByFids($this->mid, $uids);
		$this->assign('follow_state', $follow_state);
		//dump($follow_state);exit;

		$this->assign('memberInfo',$memberInfo);
		$this->display();
	}
	/**
	 * 搜索团队内成员
	 */
	public function searchuser(){
		$key = t($_REQUEST['key']);
		$gid = intval( $_POST['gid'] );
		$list = D('Member')->where("name like '%".$key."%' and gid=".$gid." and uid!=".$GLOBALS['ts']['mid'])->field('uid')->limit(10)->findAll();
		$uids = getSubByKey( $list , 'uid' );
		foreach ( $uids as &$v ){
			$v = model( 'User' )->getUserInfo( $v );
		}
		$msg = array('status'=>1,'data'=>$uids);
		exit(json_encode($msg));
	}
}