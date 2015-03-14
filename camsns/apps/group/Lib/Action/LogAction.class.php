<?php

class LogAction extends BaseAction {
	var $log;
	 public function _initialize(){
	 	parent::_initialize();
	 	
	 	if (!$this->isadmin)
			$this->error('您没有权限');

	 	$this->log = D('Log');
	 	$this->assign('current','log');  //头部导航切换
	 }

	 //所有日志
	 public function index()
	 {
	 	$map['gid']  = $this->gid;
	 	if ($_GET['type'])
	 		$map['type'] = $_GET['type'];
	 	$log_list = $this->log->where($map)->order('id DESC')->findPage();

	 	$this->assign('on', 	 $_GET['type'] ? $_GET['type'] : 'all');
	 	$this->assign('logList', $log_list);
	 	$this->display();
	 }

	 //贴子日志
	 function topic() {
	 	$logList = $this->log->where('gid='.$this->gid." AND type='topic'")->order('id DESC')->findPage();

	 	$this->assign('logList',$logList);
	 	$this->assign('on','topic');
	 	$this->display('index');
	 }

	 //成员日志
	 function member() {
	 	$logList = $this->log->where('gid='.$this->gid." AND type='member'")->order('id DESC')->findPage();


	 	$this->assign('logList',$logList);
	 	$this->assign('on','member');
	 	$this->display('index');
	 }

	 //设置日志

	 function setting() {

	 	$logList = $this->log->where('gid='.$this->gid." AND type='setting'")->order('id DESC')->findPage();


	 	$this->assign('logList',$logList);
	 	$this->assign('on','setting');
	 	$this->display('index');
	 }


}


?>