<?php
date_default_timezone_set('Asia/Chongqing');
require_once 'douban/OAuth.php';
require_once 'douban/doubanOAuth.php';
class douban{
	private $_authorize_url;
	private $_douban_key;
	private $_douban_secret;
	var $error_code;
	function getError(){
		return $this->error_code;
	}
	public function __construct() {
		$this->_douban_key		= DOUBAN_KEY;
		$this->_douban_secret	= DOUBAN_SECRET;
	}
	public function getUrl($call_back = null) {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) )
			return false;
		if (is_null($call_back)) {
			$call_back = Addons::createAddonShow('Login','no_register_display',array('type'=>'douban','do'=>"bind"));
		}
		if ( empty($this->_authorize_url) ) {
			$client = new DoubanOAuth($this->_douban_key, $this->_douban_secret);
			$request_token = $client->getRequestToken();
			$this->_authorize_url = $client->getAuthorizeURL( $request_token ) . '&oauth_callback=' . urlencode($call_back);
		}
		$_SESSION['douban']['request_token'] = $request_token;
		return $this->_authorize_url;
	}
    public function checkUser() {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) || empty($_SESSION['douban']['request_token']['oauth_token']) || empty($_SESSION['douban']['request_token']['oauth_token_secret']) )
			return false;
		$client = new DoubanOAuth($this->_douban_key, $this->_douban_secret, $_SESSION['douban']['request_token']['oauth_token'], $_SESSION['douban']['request_token']['oauth_token_secret']);
		$access_token = $client->getAccessToken();
		if ( $access_token['oauth_token'] ) {
			$_SESSION['douban']['access_token'] = $access_token;
			$_SESSION['open_platform_type'] = 'douban';
			return true;
		}else {
			return false;
		}
	}
	// 用户资料
	public function userInfo() {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) || empty($_SESSION['douban']['access_token']['oauth_token']) || empty($_SESSION['douban']['access_token']['oauth_token_secret']) )
			return false;
		$client = new DoubanOAuth($this->_douban_key, $this->_douban_secret, $_SESSION['douban']['access_token']['oauth_token'], $_SESSION['douban']['access_token']['oauth_token_secret']);
		$res = $client->OAuthRequest('http://api.douban.com/people/%40me', array(), 'GET');
		$res = simplexml_load_string($res);
		$uid_and_icon 			= $this->__getUidAndIcon($res->link);
		$userInfo['id']			= $uid_and_icon['id'];
		$userInfo['uname']		= (string) $res->title;
		$userInfo['userface']	= $uid_and_icon['icon'];
		$userInfo['signature']	= (string)$res->content;
		$userInfo['location']	= (string)$res->children('http://www.douban.com/xmlns/')->location;
		
		return $userInfo;
	}
	private function __getUidAndIcon($res) {
		$uid_and_icon = array();
		foreach($res as $v) {
			$v = (array) $v;
			if ( $v['@attributes']['rel'] == 'icon') {
				$icon_url = $v['@attributes']['href'];
				$v['@attributes']['href'] = basename($v['@attributes']['href']);
				if ( false !== strpos($v['@attributes']['href'], '-') ) {
					$v['@attributes']['href'] = explode('-', $v['@attributes']['href']);
				}else {
					$v['@attributes']['href'] = explode('.', $v['@attributes']['href']);
				}
				$uid_and_icon['id']   = substr($v['@attributes']['href'][0], 1);
				$uid_and_icon['icon'] = $icon_url;
				break ;
			}
		}
		return $uid_and_icon;
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
