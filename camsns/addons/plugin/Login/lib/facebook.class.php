<?php
//使用V2版本的客户端,支持Oauth2.0
require_once('facebook/facebook.php');
class facebook{
	var $loginUrl;
	var $baidu;

	function getUrl($redirectUri){
		$facebook = new FacebookAPI(array('appId' => FACEBOOK_KEY, 'secret' => FACEBOOK_SECRET));
		$loginUrl = $facebook->getLoginUrl();
		return $loginUrl;
	}

	//用户资料
	function userInfo(){
		$facebook = new FacebookAPI(array('appId' => FACEBOOK_KEY, 'secret' => FACEBOOK_SECRET));
		$user = $facebook->getUser();
		if($user){
			$profile = $facebook->api('/me');	
		}	
		//dump($user);
		//dump($profile);
		//exit;
		$user['id']         =  $user['uid'];
		$user['uname']       = $user['uname'];
		$user['province']    = 0;
		$user['city']        = 0;
		$user['location']    = '';
		$user['userface']    = $profile['portrait'];
		$user['sex']         = $profile['sex'];
		return $user;
	}

	//验证用户
	function checkUser(){
		$redirect_uri = Addons::createAddonShow('Login','no_register_display',array('type'=>'facebook','do'=>"bind"));
		$facebook = new FacebookAPI(array('appId' => FACEBOOK_KEY, 'secret' => FACEBOOK_SECRET));
		$token = $facebook->getAccessTokenFromCode($_GET['code'],$redirect_uri);
		//dump($token);
		//exit;
		// if($user){
		// 	$_SESSION['baidu']['access_token']['oauth_token'] = $access_token;
		// 	$_SESSION['baidu']['access_token']['oauth_token_secret'] = $refresh_token;
		// 	$_SESSION['baidu']['isSync'] = 0;
		// 	$_SESSION['baidu']['uid'] = $user['uid'];
		// 	$_SESSION['open_platform_type'] = 'baidu';
		// 	return $user;
		// }else{
		// 	return false;
		// }
	}

	//发布一条微博
	public function update($text,$opt){
		return true;
	}

	//上传一个照片，并发布一条微博
	public function upload($text,$opt,$pic){
		return true;
	}

	//转发一条微博
    public function transpond($transpondId,$reId,$content='',$opt=null){
		return true;
	}

	//保存数据
	public function saveData($data){
		return true;
	}
}