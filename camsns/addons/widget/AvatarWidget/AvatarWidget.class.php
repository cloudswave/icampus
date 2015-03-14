<?php
   /**
	* 头像上传组件
	* @example {:W('Avatar',array('avatar'=>$user_info,'defaultImg'=>$user_info['avatar_big'],'callback'=>'gotoStep3'))}
	* @version TS3.0
	*/
class AvatarWidget extends Widget {
    
    /**
     * @param array avatar 用户信息
     * @param string defaultImg 头像地址
     * @param string callback 回调方法
     */
	public function render($data) {
		$var['password']   = time();
		$var['defaultImg'] = 'noavatar/big.jpg';
		$var['uploadUrl']  = urlencode(U('public/Account/doSaveUploadAvatar')); 
		// 获取附件配置信息
		$attachConf = model('Xdata')->get('admin_Config:attach');
		$var['attach_max_size'] = $attachConf['attach_max_size'];

 		is_array($data) && $var = array_merge($var,$data);

		$content = $this->renderFile(dirname(__FILE__)."/default.html", $var);
		
		return $content;
	}

	/**
	 * 输出新头像
	 */
	public function getflashHtml(){
		$password   = time();
		$userinfo   = model('User')->getUserInfo($GLOBALS['ts']['mid']);
		$defaultImg = $userinfo['avatar_big'];
		$uploadUrl  = urlencode(U('public/Account/doSaveUploadAvatar')); 
		echo ' <embed src="'.THEME_PUBLIC_URL.'/image/face.swf" quality="high" wmode="opaque" 
			FlashVars="uploadServerUrl='.$uploadUrl.'&defaultImg='.$defaultImg.'" 
			pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" 
			type="application/x-shockwave-flash" width="610" height="560"></embed>';
		exit(); 		
	}
}
