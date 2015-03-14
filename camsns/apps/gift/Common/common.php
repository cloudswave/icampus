<?php
//获取应用配置参数
function gift_getConfig($key=NULL){
	$config = model('Xdata')->lget('gift');
	$config['credit'] || $config['credit']='score';
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];	
	}
}
//获取称谓
function getUserWo($uid,$mid = null) {

    if($uid == $mid) return "我";

    $uid  =  abs(intval($uid));
    $info =  M('user')->field( 'sex' )->find($uid);
    return $info['sex']?"他":"她";

}
//获取礼物状态：禁用\启用
function getStatus($status,$imageShow=true)
{
    switch($status) {
    	case 0:
            $showText   = '禁用';
            $showImg    = '<IMG SRC="__APP__/images/locked.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="禁用">';
            break;
        case 1:
        default:
            $showText   =   '正常';
            $showImg    =   '<IMG SRC="__APP__/images/ok.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="正常">';

    }
    return ($imageShow===true)? auto_charset($showImg) : $showText;
}
/**
* __realityImage
* 获取礼品图片真实地址
* @param  $giftInfo['img'],$giftInfo['name'] 礼品信息
* @return  图片标签;
*/			
function realityImage($img,$name=''){
	return sprintf('<img src="'.realityImageURL($img).'" alt="%s">',$name);
}
/**
* __realityImage
* 获取礼品图片真实URL
* @param  $giftInfo['img'] 礼品信息
* @return  URL;
*/			
function realityImageURL($img){
	$imgURL = sprintf('%s/apps/gift/Tpl/default/Public/gift/%s', SITE_URL, $img);//默认的礼物图片地址
	if(file_exists(sprintf('./apps/gift/Tpl/default/Public/gift/%s',$img))){
		return $imgURL;
	}else{//若默认里没有则返回自定义的礼物图片地址
		return sprintf('%s/data/upload/gift/%s', SITE_URL, $img);
	}
}