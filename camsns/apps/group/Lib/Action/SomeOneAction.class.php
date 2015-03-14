<?php
//某人的群组
class SomeOneAction extends BaseAction {

	public function _initialize() {
		parent::base();

		/*
		 * 右侧信息
		 */
    	// 加入的群组的数量
    	$join_group_count = D('Member')->where('(level>1 AND status=1)  AND uid=' . $this->mid)->count();
    	$this->assign('join_group_count', $join_group_count);

    	// 热门标签
    	$hot_tags_list = D('GroupTag')->getHotTags();
    	$this->assign('hot_tags_list', $hot_tags_list);

    	// 群组热门排行
    	$hot_group_list = D('Group')->getHotList();
    	//dump($hot_group_list);exit;
    	$this->assign('hot_group_list', $hot_group_list);
	}

	public function index()
	{
		$type = in_array($_GET['type'], array('join', 'manage', 'following')) ? $_GET['type'] : 'all';
		switch ($type) {
			case 'join': // 我管理的
				$group_list = D('Group')->myjoingroup($this->uid, 1);
        		$this->setTitle("我加入的群组");
				break;
			case 'manage': // 我加入的
				$group_list = D('Group')->mymanagegroup($this->uid, 1);
        		$this->setTitle("我管理的群组");
				break;
			case 'following': // 我关注的人的
				$db_prefix  = C('DB_PREFIX');
				$group_list = D('Group')->field('g.id,g.name,g.type,g.membercount,g.logo,g.cid0,g.ctime,g.status')
    							->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}user_follow AS f ON f.uid={$this->uid} AND g.uid=f.fid")
    							->where('g.status=1 AND g.is_del=0 AND f.fid<>\'\'')
    							->findPage();
        		$this->setTitle("我关注的人的群组");
				break;
			default:
				$group_list = D('Group')->getAllMyGroup($this->uid, 1);
        		$this->setTitle("我的群组");
		}
		
		$gids = getSubByKey( $group_list['data'] , 'id');
		$map['gid'] = array('in' , $gids);
		$map['uid'] = $this->mid;
		$usercounts = D('GroupUserCount')->where($map)->findAll();
		$gcount = array();
		foreach ( $usercounts as $v ){
			if ( $v['atme'] || $v['comment'] || $v['topic'] ){
				$gcount[ $v['gid'] ]['atme'] = $v['atme'];
				$gcount[ $v['gid'] ]['comment'] = $v['comment'];
				$gcount[ $v['gid'] ]['topic'] = $v['topic'];
			}
			
		}
		foreach ( $group_list['data'] as &$g ){
			$g['unread_usercount'] = $gcount[$g['id']];
		}
		$name = ($this->mid == $this->uid) ? '我' : getUserName($this->uid);
		$this->assign('current_uname', $name);
		$this->setTitle($name."的群组");
		$this->assign('type', $type);
		$this->assign('grouplist', $group_list);
		$this->assign('nav','mygroup');
		$this->display();
	}

	//话题
	function topic(){
		//加入的
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		$cond = '';
		if($type == 'post'){  //发表
			$cond = ' AND istopic=1 ';

		}elseif($type == 'reply'){  //回复
			$cond = ' AND istopic=0 ';
		}
		$postList = D('Post')->where('uid='.$this->uid." $cond AND is_del=0")->order('ctime DESC')->findPage();
		foreach($postList['data'] as $k=>$v){
			$postList['data'][$k] = gettopic($v['tid']);
		}
		$this->assign('type',$type);
		$this->assign('topicList',$postList);
		$this->display();
	}

}