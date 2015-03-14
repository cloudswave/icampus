<?php
class NoticeAction extends BaseAction{
	public function _initialize(){
		parent::_initialize();
		$this->assign('current','notice');
	}
	public function atme(){
// 		//获取未读@Me的条数
// 		$this->assign('unread_atme_count',D('GroupUserCount')->where('uid='.$this->mid." and `key`='unread_atme'")->getField('value'));
		// 拼装查询条件
		$map['uid'] = $this->mid;
		$map['gid'] = $this->gid;
		// !empty($_GET['t']) && $map['table'] = t($_GET['t']);
		if(!empty($_GET['t'])) {
			$table = t($_GET['t']);
			switch($table) {
				case 'feed':
					$map['app'] = 'Public';
					break;
			}
		}
		// 设置应用名称与表名称
		$app_name = isset($_GET['app_name']) ? t($_GET['app_name']) : 'group';
		// 获取@Me微博列表
		$at_list = D('GroupAtme')->setAppName($app_name)->getAtmeList($map);
		// dump($at_list);exit;
		// 添加Widget参数数据
		foreach($at_list['data'] as &$val) {
			if($val['source_table'] == 'comment') {
				$val['widget_sid'] = $val['sourceInfo']['source_id'];
				$val['widget_style'] = $val['sourceInfo']['source_table'];
				$val['widget_sapp'] = $val['sourceInfo']['app'];
				$val['widget_suid'] = $val['sourceInfo']['uid'];
				$val['widget_share_sid'] = $val['sourceInfo']['source_id'];
			} else if($val['is_repost'] == 1) {
				$val['widget_sid'] = $val['source_id'];
				$val['widget_stype'] = $val['source_table'];
				$val['widget_sapp'] = $val['app'];
				$val['widget_suid'] = $val['uid'];
				$val['widget_share_sid'] = $val['app_row_id'];
				$val['widget_curid'] = $val['source_id'];
				$val['widget_curtable'] = $val['source_table'];
			} else {
				$val['widget_sid'] = $val['source_id'];
				$val['widget_stype'] = $val['source_table'];
				$val['widget_sapp'] = $val['app'];
				$val['widget_suid'] = $val['uid'];
				$val['widget_share_sid'] = $val['source_id'];
			}
			// 获取转发与评论数目
			if($val['source_table'] != 'comment') {
				$feedInfo = D('GroupFeed')->get($val['widget_sid']);
				$val['repost_count'] = $feedInfo['repost_count'];
				$val['comment_count'] = $feedInfo['comment_count'];
			}
			//解析数据成网页端显示格式(@xxx  加链接)
			$val['source_content'] = parse_html($val['source_content']);
		}
		// 获取微博设置
		$weiboSet = model('Xdata')->get('admin_Config:feed');
		$this->assign($weiboSet);
		// 用户@Me未读数目重置
		D('GroupUserCount')->setGroupZero($this->mid, $this->gid , 'atme',  0);
		$this->setTitle(L('PUBLIC_MENTION_INDEX'));
		$userInfo = model('User')->getUserInfo($this->mid);
		$this->setKeywords('@提到'.$userInfo['uname'].'的消息');
		$this->assign('hashtab','atme');
		$this->assign($at_list);
		$this->display();
	}
	public function comment(){
		// 安全过滤
		$type = t($_GET['type']);
		if($type == 'send') {
			$keyword = '发出';
			$map['uid'] = $this->uid;
			$this->assign('hashtab','send');
		} else {
			// 微博配置
			$weiboSet = model('Xdata')->get('admin_Config:feed');
			$this->assign('weibo_premission', $weiboSet['weibo_premission']);
			$keyword = '收到';
			//获取未读评论的条数
			$this->assign('unread_comment_count',model('UserData')->where('uid='.$this->mid." and `key`='unread_comment'")->getField('value'));
			// 收到的
			$map['_string'] = " (to_uid = '{$this->uid}' OR app_uid = '{$this->uid}') AND uid !=".$this->uid;
			$this->assign('hashtab','receive');
			D('GroupUserCount')->setGroupZero($this->mid, $this->gid , 'comment',  0);
		}
		// 类型描述术语 TODO:放到统一表里面
		$d['tabHash'] = array(
				'feed'	=> L('PUBLIC_WEIBO')			// 微博
		);
		
		$d['tab'] = model('Comment')->getTab($map);
		$this->assign($d);
		
		// 安全过滤
		$t = t($_GET['t']);
		!empty($t) && $map['table'] = $t;
		$list = D('GroupComment')->setAppName(t($_GET['app_name']))->getCommentList($map,'comment_id DESC',null,true);
		foreach($list['data'] as $k=>$v){
			if($v['sourceInfo']['app']=='weiba'){
				$list['data'][$k]['sourceInfo']['source_body'] = str_replace($v['sourceInfo']['row_id'], $v['comment_id'], $v['sourceInfo']['source_body']);
			}
		}
		$this->assign('list', $list);
		//dump($list);exit;
		$this->setTitle($keyword.'的评论');					// 我的评论
		$userInfo = model('User')->getUserInfo($this->mid);
		$this->setKeywords($userInfo['uname'].$keyword.'的评论');
		$this->display();
	}
}