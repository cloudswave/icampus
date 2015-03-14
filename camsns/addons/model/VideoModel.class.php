<?php
class VideoModel {

    public function __construct() {
    }

    public function getVideoInfo($link) {
        $link = t($link);
        $parseLink = parse_url($link);
        if(preg_match("/(youku.com|youtube.com|qq.com|ku6.com|sohu.com|sina.com.cn|tudou.com|yinyuetai.com)$/i", $parseLink['host'], $hosts)) {
            $flashinfo = $this->_video_getflashinfo($link, $hosts[1]);
        }
        if ($flashinfo['flash_url']) {
            //$flashinfo['host']      = $hosts[1];
            $flashinfo['video_url'] = $link;
            return $flashinfo;
        }else{
            return false;
        }
    }

    public function _weiboTypePublish($type_data){
    	$link = $type_data;
    	$parseLink = parse_url($link);
    	if(preg_match("/(youku.com|youtube.com|qq.com|ku6.com|sohu.com|sina.com.cn|tudou.com|yinyuetai.com)$/i", $parseLink['host'], $hosts)) {
    		$flashinfo = $this->_video_getflashinfo($link, $hosts[1]);
    	}
    	if ($flashinfo['flash_url']) {
    		$typedata['flashvar']  = $flashinfo['flash_url'];
    		$typedata['flashimg']  = $flashinfo['image_url'];
    		$typedata['host']      = $hosts[1];
    		$typedata['source']    = $type_data;
    		$typedata['title']     = $flashinfo['title'];
    	}
    	return $typedata;	 
    }

	//此代码需要持续更新.视频网站有变动.就得修改代码.
    public function _video_getflashinfo($link, $host) {
        $return='';
		if(extension_loaded("zlib")){
			$content = file_get_contents("compress.zlib://".$link);//获取
        }

		if(!$content)
			$content = file_get_contents($link);//有些站点无法获取

		if('youku.com' == $host)
        {
			// 2012/3/7 修复优酷链接图片的获取
            preg_match('/http:\/\/profile\.live\.com\/badge\/\?[^"]+/i', $content, $share_url);
            preg_match('/id\_(.+)\.html/', $share_url[0], $flashvar);
            preg_match('/screenshot=([^"&]+)/', $share_url[0], $img);
            preg_match('/title=([^"&]+)/', $share_url[0], $title);
            if (!empty($title[1])) {
                $title[1] = urldecode($title[1]);
            } else {
                preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            }
            $img[1] = str_ireplace('ykimg.com/0', 'ykimg.com/1', $img[1]);
            $flash_url = 'http://player.youku.com/player.php/sid/'.$flashvar[1].'/v.swf';
        }
        elseif('ku6.com' == $host)
        {
			// 2012/3/7 修复ku6链接和图片抓去
            preg_match("/\/([\w\-\.]+)\.html/",$link,$flashvar);
			//preg_match("/<span class=\"s_pic\">(.*?)<\/span>/i",$content,$img);
			preg_match("/cover: \"(.+?)\"/i", $content, $img);
            preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = 'http://player.ku6.com/refer/'.$flashvar[1].'/v.swf';
        }
        elseif('tudou.com' == $host && strpos($link,'www.tudou.com/albumplay')!==false) {
            preg_match("/albumplay\/([\w\-\.]+)\//",$link,$flashvar);
            preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            preg_match("/pic: \"(.+?)\"/i", $content, $img);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = 'http://www.tudou.com/a/'.$flashvar[1].'/&autoPlay=true/v.swf';
        }
        elseif('tudou.com' == $host && strpos($link,'www.tudou.com/programs')!==false) {
            //dump(auto_charset($content,'GBK','UTF8'));
            preg_match("/programs\/view\/([\w\-\.]+)\//",$link,$flashvar);
            preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            preg_match("/pic: \'(.+?)\'/i", $content, $img);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = 'http://www.tudou.com/v/'.$flashvar[1].'/&autoPlay=true/v.swf';
        }
        elseif('tudou.com' == $host && strpos($link,'www.tudou.com/listplay')!==false) {
            //dump(auto_charset($content,'GBK','UTF8'));
            preg_match("/listplay\/([\w\-\.]+)\//",$link,$flashvar);
            preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            preg_match("/pic:\"(.+?)\"/i", $content, $img);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = 'http://www.tudou.com/l/'.$flashvar[1].'/&autoPlay=true/v.swf';
        }
        elseif('tudou.com' == $host && strpos($link,'douwan.tudou.com')!==false) {
            //dump(auto_charset($content,'GBK','UTF8'));
            preg_match("/code=([\w\-\.]+)$/",$link,$flashvar);
            preg_match("/title\":\"(.+?)\"/i",$content,$title);
            preg_match("/itempic\":\"(.+?)\"/i", $content, $img);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = 'http://www.tudou.com/v/'.$flashvar[1].'/&autoPlay=true/v.swf';
        }
        elseif('youtube.com' == $host) {
			preg_match('/http:\/\/www.youtube.com\/watch\?v=([^\/&]+)&?/i',$link,$flashvar);
            preg_match("/<link itemprop=\"thumbnailUrl\" href=\"(.+?)\">/i", $content, $img);
            preg_match("/<title>(.*?)<\/title>/", $content, $title);
            $flash_url = 'http://www.youtube.com/embed/'.$FLASHVAR[1];
        }
        elseif('sohu.com' == $host) {
            preg_match("/og:videosrc\" content=\"(.+?)\"/i", $content, $flashvar);
            preg_match("/og:title\" content=\"(.+?)\"/i", $content, $title);
            preg_match("/og:image\" content=\"(.+?)\"/i", $content, $img);
            $title[1] = iconv("GBK","UTF-8",$title[1]);
            $flash_url = $flashvar[1];
        }
        elseif('qq.com' == $host) {
            preg_match("/vid:\"(.+?)\",/i", $content, $flashvar);
            preg_match('/itemprop=\"image\" content=\"(.+?)\"/i', $content, $img);
            preg_match("/<title>(.*?)<\/title>/", $content, $title);
            $flash_url = 'http://static.video.qq.com/TPout.swf?vid='.$flashvar[1].'&auto=1';
        }
        elseif('sina.com.cn' == $host)
        {
            preg_match("/swfOutsideUrl:\'(.+?)\'/i", $content, $flashvar);
            preg_match("/pic\:[ ]*\'(.*?)\'/i",$content,$img);
            preg_match("/<title>(.*?)<\/title>/i",$content,$title);
            $flash_url = $flashvar[1];

        }
        elseif('yinyuetai.com' == $host)
        {
            preg_match("/video\/([\w\-]+)$/",$link,$flashvar);
            preg_match("/<meta property=\"og:image\" content=\"(.*)\"\/>/i",$content,$img);
            preg_match("/<meta property=\"og:title\" content=\"(.*)\"\/>/i",$content,$title);
            $flash_url = 'http://player.yinyuetai.com/video/player/'.$flashvar[1].'/v_0.swf';
			$base = base64_encode(file_get_contents($img[1]));
            $img[1] = 'data:image/jpeg;base64,'.$base;
        }

        $return['title'] = t($title[1]);
        $return['flash_url'] = t($flash_url);
        $return['image_url'] = t($img[1]);
        return $return;
    }

    // 运行服务，系统服务自动运行
    public function run() {
        return;
    }
}