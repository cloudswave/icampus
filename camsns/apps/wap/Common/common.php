<?php 
// 格式化内容
function wapFormatContent($content, $url = false, $from_url = '') {
	$content = real_strip_tags($content);
	if($url){
		$content = preg_replace('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/ue', 
			"'<a href=\"'.U('wap/Index/urlalert').'&from_url={$from_url}&url='.urlencode('\\1').'\">\\1</a>\\2'", 
			$content);
	}
	$content = preg_replace_callback("/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is",replaceEmot,$content);
	$content = preg_replace_callback("/#([^#]*[^#^\s][^#]*)#/is",wapFormatTopic,$content);
	$content = preg_replace_callback("/@([\w\x{2e80}-\x{9fff}\-]+)/u",wapFormatUser,$content);
	return $content;
}

// 格式化评论
function wapFormatComment($content,$url=false, $from_url = '') {
	$content = real_strip_tags($content);
	if($url){
		$content = preg_replace('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/ue', 
			"'<a href=\"'.U('wap/Index/urlalert').'&from_url={$from_url}&url='.urlencode('\\1').'\">\\1</a>\\2'", 
			$content);
	}
    $content = preg_replace_callback("/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is",replaceEmot,$content);
    $content = preg_replace_callback("/@([\w\x{2e80}-\x{9fff}\-]+)/u",wapFormatUser,$content);
    return $content;
}

// 话题格式化回调
function wapFormatTopic($data) {
	return "<a href=".U('wap/Index/doSearch',array('key'=>t($data[1]))).">".$data[0]."</a>";
}

// 用户连接格式化回调
function wapFormatUser($name) {
	$info = D('User', 'home')->getUserByIdentifier($name[1], 'uname');
	if( $info ){
		return "<a href=".U('wap/Index/weibo',array('uid'=>$info['uid'])).">".$name[0]."</a>";
	}else{
		return "$name[0]";
	}
}

// 短地址
function getContentUrl($url) {
	return getShortUrl( $url[1] ).' ';
}
?>