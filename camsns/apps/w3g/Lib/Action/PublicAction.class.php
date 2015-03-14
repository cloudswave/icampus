<?php
class PublicAction extends Action {

	private $_config;					// 注册配置信息字段
	private $_register_model;			// 注册模型字段
	private $_user_model;				// 用户模型字段
	private $_invite;					// 是否是邀请注册
	private $_invite_code;				// 邀请码

	/**
	 * 模块初始化，获取注册配置信息、用户模型对象、注册模型对象、邀请注册与站点头部信息设置
	 * @return void
	 */
	protected function _initialize()
	{
		$this->_invite = false;
		// 未激活与未审核用户
		if($this->mid > 0 && !in_array(ACTION_NAME, array('changeActivationEmail', 'activate', 'isEmailAvailable', 'isValidVerify'))) {
			$GLOBALS['ts']['user']['is_audit'] == 0 && ACTION_NAME != 'waitForAudit' && U('public/Register/waitForAudit', array('uid'=>$this->mid), true);
			$GLOBALS['ts']['user']['is_audit'] == 1 && $GLOBALS['ts']['user']['is_active'] == 0 && ACTION_NAME != 'waitForActivation' && U('public/Register/waitForActivation', array('uid'=>$this->mid), true);
		}
		// 登录后，将不显示注册页面
		// $this->mid > 0 && $GLOBALS['ts']['user']['is_init'] == 1 && redirect($GLOBALS['ts']['site']['home_url']);

		$this->_config = model('Xdata')->get('admin_Config:register');
		$this->_user_model = model('User');
		$this->_register_model = model('Register');
		$this->setTitle(L('PUBLIC_REGISTER'));
	}

	//刷新操作
	public function jump(){
		$url = $_GET['url'];
		$this->redirect($url);
	}

	public function isRegisterOpen()
	{
	    return strtolower(model('Xdata')->get('register:register_type')) == 'open';
	}

	public function login()
	{
		// 登录验证
		$passport = model('Passport');
		if ($passport->isLogged()) {
			$this->redirect(U('w3g/Index/index'));
		}
		//载入站点配置全局变量
		if($GLOBALS['ts']['site']['site_logo_w3g']==''){
			$w3gLogoUrl='img/logo.png';
		}else{
			$attach = model('Attach')->getAttachById($GLOBALS['ts']['site']['site_logo_w3g']);        
			$w3gLogoUrl = getImageUrl($attach['save_path'].$attach['save_name']); 
		}
		$this->assign('w3gLogoUrl',$w3gLogoUrl);
		// dump($w3gLogoUrl);exit();


		$this->assign('is_register_open', $this->isRegisterOpen() ? '1' : '0');
		$this->display();
	}

	
	public function doLogin() {
		$email = safe($_POST['email']);
		$password = safe($_POST['password']);
		$remember	= 1;
		if (empty($email) || empty($password)) {
			// $this->redirect(U('w3g/Public/login'), 3, '用户名和密码不能为空');
			echo '用户名或密码不能为空';
			exit();
		}
		if (!isValidEmail($email)) {
			// $this->redirect(U('w3g/Public/login'), 3, 'Email格式错误，请重新输入');
			echo 'Email格式错误，请重新输入';
			exit();
		}
		if ($user = model('Passport')->getLocalUser($email, $password)) { 
			// dump($user);
			if ($user['is_active'] == 0) {
				// $this->redirect(U('w3g/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
				echo '帐号尚未激活，请激活后重新登录';
				exit();
			}
			model('Passport')->loginLocal($email,$password,$remember);
			$this->setSessionAndCookie($user['uid'], $user['uname'], $user['email'], intval($_POST['remember']) === 1);
   			// $this->recordLogin($user['uid']);
            // model('Passport')->registerLogin($user, intval($_POST['remember']) === 1);
            echo '1';
            exit();
		}else {
			// $this->redirect(U('w3g/Public/login'), 3, '帐号或密码错误，请重新输入');
			echo '帐号或密码错误，请重新输入';
		}
	}

	//退出
	public function log_out() {
		model('Passport')->logoutLocal('');
		$this->redirect(U('w3g/Public/login'));
	}

	public function setSessionAndCookie($uid, $uname, $email, $remember = false) {
        $_SESSION['mid']    = $uid;
        $_SESSION['uname']  = $uname;
        $remember ?
			cookie('LOGGED_USER',jiami('thinksns.'.$uid),(3600*24*365)) :
			cookie('LOGGED_USER',jiami('thinksns.'.$uid),(3600*2));
    }

    //登录记录
    public function recordLogin($uid) {
        $data['uid']    = $uid;
        $data['ip']     = get_client_ip();
        $data['place']  = convert_ip($data['ip']);
        $data['ctime']  = time();
        M('login_record')->add($data);
    }

	// URL重定向
	function redirect($url,$time=0,$msg='') {
		//多行URL地址支持
		$url = str_replace(array("\n", "\r"), '', $url);
		if(empty($msg))
		$msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			// redirect
			if(0===$time) {
				header("Location: ".$url);
			}else {
				header("refresh:{$time};url={$url}");
				// 防止手机浏览器下的乱码
				$str = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$str .= $msg;
			}
		}else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time!=0)
			$str   .=   $msg;
		}
		$this->assign('msg', $str);

		$this->display('redirect');
	}


	// 访问正常版
	public function w3gToNormal() {
		$_SESSION['wap_to_normal'] = '1';
		cookie('wap_to_normal', '1', 3600*24*365);
		redirect(U('public'));
	}

	public function register() {
	    // if (!$this->isRegisterOpen())
	        // redirect(U('/Public/login'), 3, '站点未开放注册');

	    $this->assign($_GET);
	    $this->display();
	}


	//用来传递头像地址
	public function ava(){
		if(!isset($_GET['uid'])){
			exit;
		}
		$data['user_id'] = intval($_GET['uid']);
		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile['avatar_small']);
		$this->display();
	}


	public function doRegister() {
	    $service = model('Register');
		$email = $service->isValidEmail($_POST['email']);
		$uname = $service->isValidName($_POST['uname']);
		$password = $service->isValidPassword($_POST['password'],$_POST['password']);
		if(!$email && !$uname && !$password){
			echo $service->getLastError();
		}else{
			if ($user = model('Passport')->getLocalUser($email, $password)) {
				if ($user['is_active'] == 0) {
					redirect(U('w3g/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
				}
			}
			$invite = t($_POST['invate']);
			$inviteCode = t($_POST['invate_key']);
			$email = t($_POST['email']);
			$uname = t($_POST['uname']);
			$sex = 1 == $_POST['sex'] ? 1 : 2;
			$password = trim($_POST['password']);
			$repassword = trim($_POST['repassword']);

			$login_salt = rand(11111, 99999);
			$map['uname'] = $uname;
			$map['sex'] = $sex;
			$map['login_salt'] = $login_salt;
			$map['password'] = md5(md5($password).$login_salt);
			$map['login'] = $map['email'] = $email;
			$map['reg_ip'] = get_client_ip();
			$map['ctime'] = time();
			$map['first_letter'] = getFirstLetter($uname);
			$map['is_init'] = 1;


			// 审核状态： 0-需要审核；1-通过审核
			$map['is_audit'] = $this->_config['register_audit'] ? 0 : 1;
			// 需求添加 - 若后台没有填写邮件配置，将直接过滤掉激活操作
			$isActive = $this->_config['need_active'] ? 0 : 1;
			if ($isActive == 0) {
				$emailConf = model('Xdata')->get('admin_Config:email');
				if (empty($emailConf['email_host']) || empty($emailConf['email_account']) || empty($emailConf['email_password'])) {
					$isActive = 1;
				}
			}
			$map['is_active'] = $isActive;
			$map['first_letter'] = getFirstLetter($uname);
			//如果包含中文将中文翻译成拼音
			if ( preg_match('/[\x7f-\xff]+/', $map['uname'] ) ){
				//昵称和呢称拼音保存到搜索字段
				$map['search_key'] = $map['uname'].' '.model('PinYin')->Pinyin( $map['uname'] );
			} else {
				$map['search_key'] = $map['uname'];
			}
			$uid = $this->_user_model->add($map);
			// dump($uid);
			if($uid) {
				// 添加积分
				model('Credit')->setUserCredit($uid,'init_default');
				// 如果是邀请注册，则邀请码失效
				if($invite) {
					// 验证码使用
					$receiverInfo = model('User')->getUserInfo($uid);
					// 添加用户邀请码字段
					model('Invite')->setInviteCodeUsed($inviteCode, $receiverInfo);
					//给邀请人奖励
					model('User')->where('uid='.$uid)->setField('invite_code', $inviteCode);
				}

				// 添加至默认的用户组
				$userGroup = model('Xdata')->get('admin_Config:register');
				$userGroup = empty($userGroup['default_user_group']) ? C('DEFAULT_GROUP_ID') : $userGroup['default_user_group'];
				model('UserGroupLink')->domoveUsergroup($uid, implode(',', $userGroup));

				// //注册来源-第三方帐号绑定
				// if(isset($_POST['other_type'])){
				// 	$other['type'] = t($_POST['other_type']);
				// 	$other['type_uid'] = t($_POST['other_uid']);	
				// 	$other['oauth_token'] = t($_POST['oauth_token']);
				// 	$other['oauth_token_secret'] = t($_POST['oauth_token_secret']);
				// 	$other['uid'] = $uid;
				// 	D('login')->add($other);
				// }

				// //判断是否需要审核
				// if($this->_config['register_audit']) {
				// 	$this->redirect('w3g/Register/waitForAudit', array('uid' => $uid));
				// } else {
				// 	if(!$isActive){
				// 		$this->_register_model->sendActivationEmail($uid);
				// 		$this->redirect('w3g/Register/waitForActivation', array('uid' => $uid));
				// 	}else{
						D('Passport')->loginLocal($email,$password);
						// $this->assign('jumpUrl', U('w3g/Index/login'));
						// $this->success('恭喜您，注册成功');
						echo '1';
				// 	}
				// }

			} else {
				// $this->error(L('PUBLIC_REGISTER_FAIL'));			// 注册失败
				echo '0';
			}


			
		}

	 //    $uid     = $service->register($email, $uname, $password, true);
	 //    if (!$uid){
	 //        redirect(U('/Public/register', $_POST), 3, $service->getLastError());
	 //    }else{
	 //        //redirect(U('/Public/login'), 1, '注册成功');
		// 	if ($user = model('Passport')->getLocalUser($email, $password)) {
		// 		if ($user['is_active'] == 0) {
		// 			redirect(U('wap/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
		// 		}

		// 		$result = model('Passport')->registerLogin($user);
		// 		redirect(U('wap/Index/index'));
		// 	} else {
		// 		redirect(U('wap/Public/login'), 3, '帐号或密码错误，请重新输入');
		// 	}
		// }
	}
}