<?php
/**
 * 邀请控制器
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class InviteAction extends Action {

	private $_invite_model;
	private $_invite_config;
	private $_register_config;

	public function _initialize() {
		// 获取后台配置
		$this->_register_config = model('Xdata')->get('admin_Config:register');
		$registerType = $this->_register_config['register_type'];
		if(!in_array($registerType, array('open', 'invite'))) {
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
				exit($this->ajaxReturn(null, '您没有邀请权限', 0));
			} else {
				exit(redirect(U('public/Index/index')));
			}
		}
		$this->_invite_model = model('Invite');
	}

	/**
	 * 邀请页面 - 页面
	 * @return void
	 */
	public function invite()
	{
		if( !CheckPermission('core_normal','invite_user') ){
			$this->error('对不起，您没有权限进行该操作！');
		}
		// 获取选中类型
		$type = isset($_GET['type']) ? t($_GET['type']) : 'email';
		$this->assign('type', $type);
		// 获取不同列表的相关数据
		switch($type) {
			case 'email':
				$this->_getInviteEmail();
				break;
			case 'link':
				$this->_getInviteLink();
				break;
		}
		$userInfo = model('User')->getUserInfo($this->mid);
		$this->assign('invite', $userInfo);
		$this->assign('config', model('Xdata')->get('admin_Config:register'));
		// 获取后台积分配置
		$creditRule = model('Credit')->getCreditRules();
		foreach ($creditRule as $v) {
			if ($v['name'] === 'core_code') {
				$applyCredit = abs($v['score']);
				break;
			}
		}
		$this->assign('applyCredit', $applyCredit);
		// 后台配置邀请数目
		$inviteConf = model('Xdata')->get('admin_Config:invite');
		$this->assign('emailNum', $inviteConf['send_email_num']);

		$this->display();
	}

	/**
	 * 邮箱邀请相关数据
	 * @return void
	 */
	private function _getInviteEmail()
	{
		// 获取邮箱后缀
		$config = model('Xdata')->get('admin_Config:register');
		$this->assign('emailSuffix', $config['email_suffix']);
		// 获取已邀请用户信息
		$inviteList = $this->_invite_model->getInviteUserList($this->mid, 'email');
		$this->assign('inviteList', $inviteList);
		// 获取有多少可用的邀请码
		$count = $this->_invite_model->getAvailableCodeCount($this->mid, 'email');
		$this->assign('count', $count);
	}

	/**
	 * 链接邀请相关数据
	 * @return void
	 */
	private function _getInviteLink()
	{
		// 获取邀请码列表
		$codeList = $this->_invite_model->getInviteCode($this->mid, 'link');
		$this->assign('codeList', $codeList);
		// 获取已邀请用户信息
		$inviteList = $this->_invite_model->getInviteUserList($this->mid, 'link');
		$this->assign('inviteList', $inviteList);
		// 获取有多少可用的邀请码
		$count = $this->_invite_model->getAvailableCodeCount($this->mid, 'link');
		$this->assign('count', $count);
	}

	/**
	 * 邀请页面 - 弹窗
	 * @return void
	 */
	public function inviteBox()
	{
		$userInfo = model('User')->getUserInfo($this->mid);
		$this->assign('invite', $userInfo);
		$this->assign('config', model('Xdata')->get('admin_Config:register'));
		$this->display();	
	}

	/**
	 * 邀请操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doInvite()
	{
		if( !CheckPermission('core_normal','invite_user') ){
			return false;
		}
		$email = t($_POST['email']);
		$detial = !isset($_POST['detial']) ? L('PUBLIC_INVATE_MESSAGE',array('uname'=>$GLOBALS['ts']['user']['uname'])) : h($_POST['detial']);			// Hi，我是 {uname}，我发现了一个很不错的网站，我在这里等你，快来加入吧。
		$map['inviter_uid'] = $this->mid;
		$map['ctime'] =	time();
		// 发送邮件邀请
		$result = model('Invite')->doInvite($email, $detial, $this->mid);
		$this->ajaxReturn(null, model('Invite')->getError(), $result);
	}

	/**
	 * 验证邮箱地址是否可用
	 * @return json 验证后的相关数据
	 */
	public function checkInviteEmail()
	{
		$email = t($_POST['email']);
		$result = model('Register')->isValidEmail($email);
		$this->ajaxReturn(null, model('Register')->getLastError(), $result);
	}

	/**
	 * 获取邀请码接口
	 * @return json 操作后的相关数据
	 */
	public function applyInviteCode()
	{
		// 获取相关数据
		$uid = intval($_POST['uid']);
		$type = t($_POST['type']);
		$result = $this->_invite_model->applyInviteCode($uid, $type);
		$res = array();
		if($result) {
			$res['status'] = true;
			$res['info'] = '邀请码领取成功';
		} else {
			$res['status'] = false;
			$res['info'] = '邀请码领取失败';
		}

		exit(json_encode($res));
	}
}