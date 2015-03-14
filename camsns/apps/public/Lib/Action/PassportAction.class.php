<?php
/**
 * PassportAction 通行证模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class PassportAction extends Action 
{
	var $passport;

	/**
	 * 模块初始化
	 * @return void
	 */
	protected function _initialize() {
		$this->passport = model('Passport');
	}

	/**
	 * 通行证首页
	 * @return void
	 */
	public function index() {
		// 如果设置了登录前的默认应用
		// U('welcome','',true);
		// 如果没设置
		$this->login();
	}

	/**
	 * 默认登录页
	 * @return void
	 */
	public function login(){
		// 添加样式
		$this->appCssList[] = 'login.css';
		if(model('Passport')->isLogged()){
			U('public/Index/index','',true);
		}

		// 获取邮箱后缀
		$registerConf = model('Xdata')->get('admin_Config:register');
		$this->assign('emailSuffix', explode(',', $registerConf['email_suffix']));
		$this->assign( 'register_type' , $registerConf['register_type']);
		$data= model('Xdata')->get("admin_Config:seo_login");
        !empty($data['title']) && $this->setTitle($data['title']);
        !empty($data['keywords']) && $this->setKeywords($data['keywords']);
        !empty($data['des'] ) && $this->setDescription ( $data ['des'] );
		
		$login_bg = getImageUrlByAttachId( $this->site ['login_bg'] );
		if(empty($login_bg))
			$login_bg = APP_PUBLIC_URL . '/image/body-bg2.jpg';
        
        $this->assign('login_bg', $login_bg);
        
		$this->display('login');
	}

		/**
	 * 默认登录页
	 * @return void
	 */
	public function isLogin(){
        $callback = $_GET['callback'];
		if(model('Passport')->isLogged()){
			$result = array('status'=>true,'info'=>"自动登陆成功");
			//U('public/Index/index','',true);
		}else{
			$result = array('status'=>false,'info'=>"请重新登录");
		}

		$data['home_url']=$GLOBALS['ts']['site']['home_url'];
		$result['data']=$data;

        echo $callback.'('.json_encode($result).')';
        exit();
		//$this->ajaxReturn($data,$result['info'],$result['status']);
	}
	
	/**
	 * 快速登录
	 */
	public function quickLogin(){
		$registerConf = model('Xdata')->get('admin_Config:register');
		$this->assign( 'register_type' , $registerConf['register_type']);
		$this->display();
	}

	/**
	 * 用户登录
	 * @return void
	 */
	public function doLogin() {
		$login 		= addslashes($_POST['login_email']);
		$password 	= trim($_POST['login_password']);
		$remember	= intval($_POST['login_remember']);
		$result 	= $this->passport->loginLocal($login,$password,$remember);

		if(!$result){
			$status = 0; 
			$info	= $this->passport->getError();
			$data 	= 0;
		}else{
			$user = $this->passport->getLocalUser($login, $password);
			$status = 1;
			$info 	= $this->passport->getSuccess();
			$data 	= ($GLOBALS['ts']['site']['home_url'])?$GLOBALS['ts']['site']['home_url']:0;
			$dat["home_url"]=$data;
			$dat["uid"]=$user['uid'];
			//设置设备align JPush推送用
			
		}
		$this->ajaxReturn($dat,$info,$status);
	}

	/**
	 * 用户登录
	 * @return void
	 */
	public function doLoginJsonp() {
		 $callback = $_GET['callback'];
		$login 		= addslashes($_GET['login_email']);
		$password 	= trim($_GET['login_password']);
		$remember	= intval($_GET['login_remember']);
		$result 	= $this->passport->loginLocal($login,$password,$remember);

		if(!$result){
			$status = 0; 
			$info	= $this->passport->getError();
			$dat 	= 0;
		}else{
			$user = $this->passport->getLocalUser($login, $password);
			$status = 1;
			$info 	= $this->passport->getSuccess();
			$data 	= ($GLOBALS['ts']['site']['home_url'])?$GLOBALS['ts']['site']['home_url']:0;
			$dat["home_url"]=$data;
			$dat["uid"]=$user['uid'];
			//设置设备align JPush推送用
			
		}

		$res['status']=$status;
		$res['info']=$info;
		$res['data']=$dat;
	
	    echo $callback.'('.json_encode($res).')';
        exit();
	}

	
	/**
	 * 注销登录
	 * @return void
	 */
	public function logout() {
		$this->passport->logoutLocal();
		$this->assign('jumpUrl',U('public/Passport/login'));
		$this->success('退出成功！');
	}

	/**
	 * 找回密码页面
	 * @return void
	 */
	public function findPassword() {

		// 添加样式
		$this->appCssList[] = 'login.css';

		$this->display();
	}

	/**
	 * 通过安全问题找回密码
	 * @return void
	 */
	public function doFindPasswordByQuestions() {
		$this->display();
	}

	/**
	 * 通过Email找回密码
	 */
	public function doFindPasswordByEmail() {
		$_POST["email"]	= t($_POST["email"]);
		if(!$this->_isEmailString($_POST['email'])) {
			$this->error(L('PUBLIC_EMAIL_TYPE_WRONG'));
		}

		$user =	model("User")->where('`email`="'.$_POST["email"].'"')->find();
        if(!$user) {
        	$this->error('找不到该邮箱注册信息');
        } 

        $result = $this->_sendPasswordEmail($user);
		if($result){
			$this->success('发送成功，请注意查收邮件');
		}else{
			$this->error('操作失败，请重试');
		}
	}

	/**
	 * 找回密码页面
	 */
	private function _sendPasswordEmail($user) {
		if($user['uid']) {
	    	$this->appCssList[] = 'login.css';		// 添加样式
	        $code = md5($user["uid"].'+'.$user["password"].'+'.rand(1111,9999));
	        $config['reseturl'] = U('public/Passport/resetPassword', array('code'=>$code));
	        //设置旧的code过期
	        D('FindPassword')->where('uid='.$user["uid"])->setField('is_used',1);
	        //添加新的修改密码code
	        $add['uid'] = $user['uid'];
	        $add['email'] = $user['email'];
	        $add['code'] = $code;
	        $add['is_used'] = 0;
	        $result = D('FindPassword')->add($add);
	        if($result){
	    		model('Notify')->sendNotify($user['uid'], 'password_reset', $config);
				return true;
			}else{
				return false;
			}
	    }
	}

	public function doFindPasswordByEmailAgain(){
		$_POST["email"]	= t($_POST["email"]);
		$user =	model("User")->where('`email`="'.$_POST["email"].'"')->find();		
        if(!$user) {
        	$this->error('找不到该邮箱注册信息');
        } 

        $result = $this->_sendPasswordEmail($user);
		if($result){
			$this->success('发送成功，请注意查收邮件');
		}else{
			$this->error('操作失败，请重试');
		}
	}

	/**
	 * 通过手机短信找回密码
	 * @return void
	 */
	public function doFindPasswordBySMS() {
		$this->display();
	}

	/**
	 * 重置密码页面
	 * @return void
	 */
	public function resetPassword() {
		$code = t($_GET['code']);
		$this->_checkResetPasswordCode($code);
		$this->assign('code', $code);
		$this->display();
	}

	/**
	 * 执行重置密码操作
	 * @return void
	 */
	public function doResetPassword() {
		$code = t($_POST['code']);
		$user_info = $this->_checkResetPasswordCode($code);

		$password = trim($_POST['password']);
		$repassword = trim($_POST['repassword']);
		if(!model('Register')->isValidPassword($password, $repassword)){
			$this->error(model('Register')->getLastError());
		}

		$map['uid'] = $user_info['uid'];
		$data['login_salt'] = rand(10000,99999);
		$data['password']   = md5(md5($password) . $data['login_salt']);
		$res = model('User')->where($map)->save($data);
		if ($res) {
			D('find_password')->where('uid='.$user_info['uid'])->setField('is_used',1);
			model('User')->cleanCache($user_info['uid']);
			$this->assign('jumpUrl', U('public/Passport/login'));
			//邮件中会包含明文密码，很不安全，改为密文的
			$config['newpass'] = $this->_markPassword($password); //密码加星号处理
			model('Notify')->sendNotify($user_info['uid'],'password_setok',$config);
			$this->success(L('PUBLIC_PASSWORD_RESET_SUCCESS'));
		} else {
			$this->error(L('PUBLIC_PASSWORD_RESET_FAIL'));
		}
	}

	/**
	 * 检查重置密码的验证码操作
	 * @return void
	 */
	private function _checkResetPasswordCode($code) {
		$map['code'] = $code;
		$map['is_used'] = 0;
		$uid = D('find_password')->where($map)->getField('uid');
		if(!$uid){
			$this->assign('jumpUrl',U('public/Passport/findPassword'));
			$this->error('重置密码链接已失效，请重新找回');
		}
		$user_info = model('User')->where("`uid`={$uid}")->find();

		if (!$user_info) {
			$this->redirect = U('public/Passport/login');
		}

		return $user_info;
	}

	/*
	 * 验证安全邮箱
	 * @return void
	 */
	public function doCheckEmail() {
		$email = t($_POST['email']);
		if($this->_isEmailString($email)){
			die(1);			
		}else{
			die(0);
		}
	}

	/*
	 * 正则匹配，验证邮箱格式
	 * @return integer 1=成功 ""=失败
	 */
	private function _isEmailString($email) {
		return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
	}

	/*
	 * 替换密码为星号
	 * @return integer 1=成功 ""=失败
	 */
	private function _markPassword($str){
	    $c = strlen($str)/2;
	    return preg_replace('|(?<=.{'.(ceil($c/2)).'})(.{'.floor($c).'}).*?|',str_pad('',floor($c),'*'),$str,1);
	}
}