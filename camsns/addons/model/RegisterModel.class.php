<?php
/**
 * 注册模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class RegisterModel extends Model {

	private $_config;																	// 注册配置字段
	private $_user_model;																// 用户模型对象字段
	private $_error;																	// 错误信息字段
	private $_email_reg = '/[_a-zA-Z\d\-\.]+(@[_a-zA-Z\d\-\.]+\.[_a-zA-Z\d\-]+)+$/i';		// 邮箱正则规则
	private $_name_reg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u";							// 昵称正则规则

	/**
	 * 初始化操作，获取注册配置信息；实例化用户模型对象 
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->_config = model('Xdata')->get('admin_Config:register');
		$this->_user_model = model('User');
	}

	/**
	 * 验证邀请邮件内容的正确性
	 * @param string $email 邀请邮箱的信息
	 * @param string $old_email 原始邮箱的信息
	 * @return boolean 是否验证成功
	 */
	public function isValidEmail_invite($email, $old_email = null) {
		$res = preg_match($this->_email_reg, $email, $matches) !== 0;
		if(!$res) {
			$this->_error = L('PUBLIC_EMAIL_TIPS');			// 无效的Email地址
		} else if(!empty($this->_config['email_suffix'])) {
			$res = in_array($matches['1'], explode(',', $this->_config['email_suffix']));
			// !$res && $this->_error =L('PUBLIC_EMAIL_SUFFIX_FORBIDDEN');			// 邮箱后缀不允许注册
			!$res && $this->_error = '该邮箱后缀不允许注册';			// 邮箱后缀不允许注册
		}
		if($res && ($email != $old_email) && $this->_user_model->where('`email`="'.mysql_escape_string($email).'"')->find()) {
			$this->_error = L('PUBLIC_ACCOUNT_REGISTERED');			// 该用户已注册
			$res = false;
		}

		return (boolean)$res;
	}

	/**
	 * 验证邮箱内容的正确性
	 * @param string $email 输入邮箱的信息
	 * @param string $old_email 原始邮箱的信息
	 * @return boolean 是否验证成功
	 */
	public function isValidEmail($email, $old_email = null) {
		$res = preg_match($this->_email_reg, $email, $matches) !== 0;
		if(!$res) {
			$this->_error = L('PUBLIC_EMAIL_TIPS');			// 无效的Email地址
		} else if(!empty($this->_config['email_suffix'])) {
			$res = in_array($matches['1'], explode(',', $this->_config['email_suffix']));
			// !$res && $this->_error = $matches['1'].L('PUBLIC_EMAIL_SUFFIX_FORBIDDEN');				// 邮箱后缀不允许注册
			!$res && $this->_error = '该邮箱后缀不允许注册';				// 邮箱后缀不允许注册
		}
		if($res && ($email != $old_email) && $this->_user_model->where('`email`="'.mysql_escape_string($email).'"')->find()) {
			$this->_error = L('PUBLIC_EMAIL_REGISTER');			// 该Email已被注册
			$res = false;
		}

		return (boolean)$res;
	}

	/**
	 * 验证昵称内容的正确性
	 * @param string $name 输入昵称的信息
	 * @param string $old_name 原始昵称的信息
	 * @return boolean 是否验证成功
	 */
	public function isValidName($name, $old_name = null) {
		// 默认不准使用的昵称
		$protected_name = array('name', 'uname', 'admin', 'profile', 'space');
		$site_config = model('Xdata')->get('admin_Config:site');
		!empty($site_config['sys_nickname']) && $protected_name = array_merge($protected_name, explode(',', $site_config['sys_nickname']));
		$res = preg_match($this->_name_reg, $name) !== 0;
		if($res) {
			$length = get_str_length($name);
			$res = ($length >= 2 && $length <= 10);
		} else {
			$this->_error = '仅支持中英文，数字，下划线';
			$res = false;
			return $res;
		}
		// 预保留昵称
		if(in_array($name, $protected_name)) {
			$this->_error = L('PUBLIC_NICKNAME_RESERVED');				// 抱歉，该昵称不允许被使用
			$res = false;
			return $res;
		}
		if(!$res) {
			$this->_error = L('PUBLIC_NICKNAME_LIMIT', array('nums'=>'2-10'));			// 昵称长度必须在2-10个汉字之间
			return $res;
		}
		if(($name != $old_name) && $this->_user_model->where('`uname`="'.mysql_escape_string($name).'"')->find()) {
			$this->_error = L('PUBLIC_ACCOUNT_USED');				// 该用户名已被使用
			$res = false;
		}
		// 敏感词
		if (filter_keyword($name) !== $name) {
			$this->_error = '抱歉，该昵称包含敏感词不允许被使用';
			return false;
		}

		return $res;
	}

	/**
	 * 验证密码内容的正确性
	 * @param string $pwd 密码信息
	 * @param string $repwd 确认密码信息
	 * @return boolean 是否验证成功
	 */
	public function isValidPassword($pwd, $repwd) {
		$res = true;
		$length = strlen($pwd);
		if($length < 6) {
			$this->_error = L('PUBLIC_PASSWORD_TIPS');			// 密码太短了，最少6位
			$res = false;
		} else if ($length > 15) {
			$this->_error = '密码太长了，最多15位';
			$res = false;
		} else if($pwd !== $repwd) {
			$this->_error = L('PUBLIC_PASSWORD_UNSIMILAR');		// 新密码与确认密码不一致
			$res = false;
		}

		return $res;
	}
	
	/**
	 * 审核用户
	 * @param array $uids 用户UID数组
	 * @param integer $type 类型，0表示取消审核，1表示通过审核
	 * @return boolean 是否审核成功
	 */
	public function audit($uids, $type=1) {
		// 处理数据
		!is_array($uids) && $uids = explode(',', $uids);
		$uids = array_unique(array_filter(array_map('intval', $uids)));
		// 审核指定用户
		$map['uid'] = array('IN', $uids);
		$result = $this->_user_model->where($map)->setField('is_audit', $type);
		model('User')->cleanCache($uids);
		if(!$result) {
			$this->_error = L('PUBLIC_REVIEW_FAIL');		// 审核失败
			return false;
		} else {
			if($type == 0) {
				$this->_error = L('PUBLIC_CANCEL_REVIEW_SUCCESS');		// 取消审核成功
				// 发送取消审核邮件
				foreach($uids as $touid) {
					model('Notify')->sendNotify($touid, 'audit_error');
				}
				return true;
			}
			
			// 发送通过审核邮件
			foreach ($uids as $uid) {
				$this->sendActivationEmail($uid,'audit_ok');
			}
			$this->_error = L('PUBLIC_REVIEW_SUCCESS');		// 审核成功
			return true;
		}
	}

	/**
	 * 给指定用户发送激活账户邮件
	 * @param integer $uid 用户UID
	 * @param string $node 邮件模板类型
	 * @return boolean 是否发送成功
	 */
	public function sendActivationEmail($uid, $node ='register_active') {
		$map['uid'] = $uid;
		$user_info = $this->_user_model->where($map)->find();

		if(!$user_info) {
			$this->_error = L('PUBLI_USER_NOTEXSIT');			// 用户不存在
			return false;
		} else if($user_info['is_audit']) {
			if($user_info['is_active'] == 1){
				$config['activeurl'] = $GLOBALS['ts']['site']['home_url'];
			}else{
				$code = $this->getActivationCode($user_info);
				$config['activeurl'] = U('public/Register/activate', array('uid'=>$uid, 'code'=>$code));
			}
			$config['name'] = $user_info['uname']; 	
			model('Notify')->sendNotify($uid, $node, $config);
			$this->_error = '发送成功';		// 系统已将一封激活邮件发送至您的邮箱，请立即查收邮件激活帐号
			return true;
		} else {
			$this->_error = !$user_info['is_audit'] ? L('PUBLIC_ACCOUNT_REVIEW_FAIL') : L('PUBLIC_ACCOUNT_ACTIVATED_SUCCESSFULLY');		// 您的帐号未通过审核，恭喜，帐号已成功激活
			return false;
		}
	}

	/**
	 * 激活指定用户
	 * @param integer $uid 用户UID
	 * @param string $code 激活码
	 * @return boolean 是否激活成功
	 */
	public function activate($uid, $code) {
		$map['uid'] = $uid;
		$user_info = $this->_user_model->where($map)->find();

		$res = ($code == $this->getActivationCode($user_info));
		if($res && !$user_info['is_active']) {
			$res = $this->_user_model->where($map)->save(array('is_active'=>1));
			$this->_user_model->cleanCache($uid);
		}

		if($res) {
			$this->_error = L('PUBLIC_ACCOUNT_ACTIVATED_SUCCESSFULLY');		// 恭喜，帐号已成功激活
			return true;
		} else {
			$this->_error = L('PUBLIC_ACTIVATE_USER_FAIL');			// 激活用户失败
			return false;
		}
	}

	/**
	 * 获取激活码
	 * @param array $user_info 用户的相关信息
	 * @return string 激活码
	 */
	public function getActivationCode($user_info) {
		return md5($user_info['login'].$user_info['password'].$user_info['login_salt']);
	}

	/**
	 * 初始化用户账号
	 * @param integer $uid 用户UID
	 * @return boolean 是否成功初始化用户账号
	 */
	public function initUser($uid) {
		$map['uid'] = $uid;
		$user_info  = $this->_user_model->where($map)->find();
		$user_info['is_active'] && $res = $this->_user_model->where($map)->save(array('is_init' => 1));
		// 清除用户缓存
		$this->_user_model->cleanCache($uid);
		
		if($res) {
			$this->_error = L('PUBLIC_ACCOUNT_INITIALIZE_SUCCESS');			// 帐号初始化成功
			return true;
		} else {
			$this->_error = L('PUBLIC_ACCOUNT_INITIALIZE_FAIL');			// 帐号初始化失败
			return false;
		}
	}

	/**
	 * 获取最后的错误信息
	 * @return string 最后的错误信息
	 */
	public function getLastError() {
		return $this->_error;
	}

	/**
	 * 修改指定用户的注册邮箱
	 * @param integer $uid 用户ID
	 * @param string $email 邮箱地址
	 * @return boolean 是否更改邮箱成功
	 */
	public function changeRegisterEmail($uid, $email) {
		$map['uid'] = $uid;
		$map['is_active'] = 0;
		$data['login'] = $email;
		$data['email'] = $email;
		$res = $this->_user_model->where($map)->save($data);
		$res = (boolean)$res;
		if($res) {
			$this->_error = '更换邮箱成功';
			$this->_user_model->cleanCache($uid);
		} else {
			$this->_error = '更换邮箱失败';
		}
		return $res;
	}

	/**
	 * 指定用户初始化完成
	 * @param integer $uid 用户ID
	 * @return boolean 是否初始化成功
	 */
	public function overUserInit($uid) {
		$map['uid'] = $uid;
		$data['is_init'] = 1;
		$res = $this->_user_model->where($map)->save($data);
		$res = (boolean)$res;
		if($res) {
			// 获取用户信息
			$receiverInfo = model('User')->getUserInfo($uid);
			// 获取发起邀请用户ID
			$inviteUid = model('Invite')->where("code='{$receiverInfo['invite_code']}'")->getField('inviter_uid');
			// 邀请人积分操作
            model('Credit')->setUserCredit($inviteUid, 'invite_friend');
			// 相互关注操作
			model('Follow')->doFollow($uid, intval($inviteUid));
			model('Follow')->doFollow(intval($inviteUid), $uid);
		    // 清除用户缓存
		    $this->_user_model->cleanCache($uid);
			// 发送通知
			$config['name'] = $receiverInfo['uname'];
			$config['space_url'] = $receiverInfo['space_url'];
			model('Notify')->sendNotify($inviteUid, 'register_invate_ok', $config);
			$registerConfig = model('Xdata')->get('admin_Config:register');
			if($registerConfig['welcome_email']){
				model('Notify')->sendNotify($uid, 'register_welcome', $config);
			}
		}
		// 清除用户缓存
		$this->_user_model->cleanCache($uid);
		return $res;
	}
}