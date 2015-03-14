<?php
require_once('google/Google_Client.php');
require_once('google/contrib/Google_Oauth2Service.php');
class google{
	var $error_code;
	var $obj;
	var $client;
	
	function __construct(){
		$client = new Google_Client();
		$client->setApplicationName($globals['ts']['site']['site_name']);
		$client->setClientId(GOOGLE_KEY);
		$client->setClientSecret(GOOGLE_SECRET);
		$client->setDeveloperKey(GOOGLE_ID);
		$client->setRedirectUri('http://t.ljwang.com/index.php?app=home&mod=public&act=displayAddons&type=gmail&addon=Login&hook=no_register_display');
		$this->client = $client;
		if(isset($_SESSION['google']['access_token']['oauth_token']))
			$this->client->setAccessToken($_SESSION['google']['access_token']['oauth_token']);
		$this->obj = new Google_Oauth2Service($this->client);
	}
	
	function getUrl($callback = null){
		if(is_null($callback))
			$callback = U('home/Pubic/gmailcallback');
		$this->client->setRedirectUri($callback);
		return $this->client->createAuthUrl();
	}
	
	function checkUser(){
		if(isset($_GET['code'])){
			$this->client->authenticate($_GET['code']);
			if($this->client->getAccessToken()){
				$_SESSION['google']['access_token']['oauth_token'] = $this->client->getAccessToken();
				$_SESSION['google']['access_token']['oauth_token_secret'] = $this->client->getAccessToken();
				$_SESSION['open_platform_type'] = 'google';
				return true;
			}
		}
		
		return false;
	
	}
	
	function userInfo(){
		$user = $this->obj->userinfo->get();
		return array('id'=>$user['id'],'uname'=>$user['name']);
	}
}