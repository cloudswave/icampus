<?php
//获取应用配置
function event_getConfig($key=NULL){
	$config = model('Xdata')->lget('event');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=100;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=10;//换算为秒

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}

//获取活动封面存储地址
function getCover($coverId,$width=100,$height=100){
	if($coverId > 0)
		$cover = model('Attach')->where("attach_id=$coverId")->find();
	if($cover){
		$cover	=	getImageUrl($cover['save_path'].$cover['save_name'],$width,$height,true);
	}else{
		$cover	=	SITE_URL.'/apps/event/_static/images/hdpic1.gif';
	}
	return $cover;
}

//根据存储路径，获取图片真实URL
function event_get_photo_url($savepath) {
	return DATA_PATH . '/uploads/'.$savepath;
}

/**
 * getEventShort 
 * 去除标签，截取blog的长度
 * @param mixed $content 
 * @param mixed $length 
 * @access public
 * @return void
 */
function getEventShort($content,$length = 40) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}