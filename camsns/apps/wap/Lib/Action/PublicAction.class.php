<?php
class PublicAction extends Action {

	public function login() {
		// 登录验证
		$passport = model('Passport');
		if ($passport->isLogged()) {
			redirect(U('wap/Index/index'));
		}
		
	    $this->assign('is_register_open', $this->isRegisterOpen() ? '1' : '0');

		$this->display();
	}

	public function doLogin() {
		$email = safe($_POST['email']);
		$password = safe($_POST['password']);
		$remember	= intval($_POST['login_remember']);

		if (empty($email) || empty($password)) {
			// redirect(U('wap/Public/login'), 3, '用户名和密码不能为空');
			echo '用户名和密码不能为空';
		}
		if (!isValidEmail($email)) {
			// redirect(U('wap/Public/login'), 3, 'Email格式错误，请重新输入');
			echo 'Email格式错误，请重新输入';
		}
		if ($user = model('Passport')->getLocalUser($email, $password)) {
			if ($user['is_active'] == 0) {
				// redirect(U('wap/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
				echo '帐号尚未激活，请激活后重新登录';		
			}
			model('Passport')->loginLocal($email,$password,$remember);
            // model('Passport')->registerLogin($user, intval($_POST['remember']) === 1);
            redirect(U('wap/Index/index'));
            // echo '1';
		} else {
			// redirect(U('wap/Public/login'), 3, '帐号或密码错误，请重新输入');
			echo '帐号或密码错误，请重新输入';
		}
	}

	public function logout() {
		model('Passport')->logoutLocal();
		redirect(U('wap/Public/login'));
	}

	// 访问正常版
	public function wapToNormal() {
		$_SESSION['wap_to_normal'] = '1';
		cookie('wap_to_normal', '1', 3600*24*365);
		redirect(U('public'));
	}

	public function isRegisterOpen() {
	    return strtolower(model('Xdata')->get('register:register_type')) == 'open';
	}

	public function register() {
	    if (!$this->isRegisterOpen())
	        redirect(U('/Public/login'), 3, '站点未开放注册');

	    $this->assign($_GET);
	    $this->display();
	}

	public function doRegister() {
		
		$email = safe($_POST['email']);

		$uname = safe($_POST['uname']);
		
		$password = safe($_POST['password']);

		$repassword = safe($_POST['re_password']);

	    if ($password != $repassword)
	        redirect(U('/Public/register', $_POST), 3, '两次的密码不符');

	    $service = model('UserRegister');
	    $uid     = $service->register($email, $uname, $password, true);
	    if (!$uid){
	        redirect(U('/Public/register', $_POST), 3, $service->getLastError());
	    }else{
	        //redirect(U('/Public/login'), 1, '注册成功');
			if ($user = model('Passport')->getLocalUser($email, $password)) {
				if ($user['is_active'] == 0) {
					redirect(U('wap/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
				}

				$result = model('Passport')->registerLogin($user);
				redirect(U('wap/Index/index'));
			} else {
				redirect(U('wap/Public/login'), 3, '帐号或密码错误，请重新输入');
			}
		}
	}
}