<?php
/**
 * 读取推荐列表
 * @return array 推荐列表数组
 */
function photo_IsHotList () {
	$lists = D('Album', 'photo')->where('isHot="1"')->order('rTime DESC')->limit(5)->findAll();
	return $lists;
}
/**
 * 获取相册封面
 * @param integer $albumId 相册ID
 * @param array $album 相册详细信息
 * @param integer $width 相册封面宽度，默认为140
 * @param integer $height 相册封面高度，默认为140
 * @return string 相册封面图片地址
 */
function get_album_cover ($albumId, $album = '', $width = 140, $height = 140) {
	// 获取相册详细信息
	if (empty($album) || $albumId != $album['id']) {
		$album = D('Album', 'photo')->find($albumId);
	}
	// 根据隐私情况，判断相册封面
	if ($album['privacy'] == 4 && (md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId']) != $_COOKIE['album_password_'.$album['id']])) {
		// 密码可见
		$cover = APP_PUBLIC_URL."/images/photo_mima.gif";
	} else if ($album['privacy'] == 3) {
		// 主人可见
		$cover = APP_PUBLIC_URL."/images/photo_zrkj.gif";
	} else if ($album['privacy'] == 2) {
		// 显示相册只有他关注的人可见
		$cover = APP_PUBLIC_URL."/images/photo_hykj.gif";
	} else {
		// 图片封面
		if (intval($album['photoCount']) > 0 && !empty($album['coverImagePath'])) {
			$cover = getImageUrl($album['coverImagePath'], 200, 200, true);
		} else if (intval($album['photoCount']) == 0) {
			$cover = APP_PUBLIC_URL."/images/photo_zwzp.gif";
		} else {
			// 无设置封面 且有照片 则默认最新一张为封面
			$firstImg = D('Photo', 'photo')->field('savepath')->where("albumId={$album['id']}")->order('`order` DESC,id DESC')->find();
			$cover = getImageUrl($firstImg['savepath'], 200, 200, true);
		}			
	}

	return $cover;
}
/**
 * 根据存储路径，获取图片真实URL
 * @param string $savepath 保存路径
 * @return string 存储的图片路径
 */
function get_photo_url ($savepath, $width = 200, $height = 200, $cut = true) {
	return getImageUrl($savepath, $width, $height, $cut);
}

//获取照隐私
function get_privacy($privacy) {
	//根据隐私情况，显示相册隐私
	if($privacy==4){
		//持密码可见
		return '持密码可见';
	}elseif($privacy==3){
		//仅主人可见
		return '仅主人可见';
	}elseif($privacy==2){
		//仅朋友可见
		return '仅主人关注的人可见';
	}else{
		//任何人都可见
		return '任何人都可见';
	}
}

//获取照隐私
function get_privacy_code($privacy) {
	//根据隐私情况，显示相册隐私
	if($privacy==4){
		//持密码可见
		return 'password';
	}elseif($privacy==3){
		//仅主人可见
		return 'self';
	}elseif($privacy==2){
		//仅我关注的人可见
		return 'following';
	}else{
		//任何人都可见
		return 'everyone';
	}
}
/**
 * 获取应用配置参数
 * @param string $key 指定的配置KEY值
 * @return mixed(array|string) 应用配置参数
 */
function photo_getConfig ($key = null) {
	$config = model('Xdata')->lget('photo');
	$config['album_raws'] || $config['album_raws'] = 6;
	$config['photo_raws'] || $config['photo_raws'] = 8;
	$config['photo_preview'] == 0 || $config['photo_preview'] = 1;
	$config['photo_max_limit'] = $config['photo_max_size'];
	($config['photo_max_size'] = floatval($config['photo_max_size']) * 1024 * 1024) || $config['photo_max_size'] = -1;
	$config['photo_file_ext'] || $config['photo_file_ext'] = 'jpeg,gif,jpg,png';
	$config['max_flash_upload_num'] || $config['max_flash_upload_num'] = 10;
	$config['open_watermark']==0 || $config['open_watermark'] = 1;
	$config['watermark_file'] || $config['watermark_file'] = 'public/images/watermark.png';
	if ($key == null) {
		return $config;
	} else {
		return $config[$key];	
	}
}