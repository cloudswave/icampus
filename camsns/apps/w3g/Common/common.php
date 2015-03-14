<?php 
// 格式化内容
function wapFormatContent($content, $url = false, $from_url = '') {
    $content = htmlspecialchars_decode($content);
    $content = real_strip_tags($content);
    if($url || true){
        // $content = preg_replace('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/ue', 
        //     "'<a class=\"c_a\" href=\"'.U('w3g/Index/urlalert').'&from_url={$from_url}&url='.urlencode('\\1').'\">\\1</a>\\2'",$content);
       $content = str_replace('[SITE_URL]', SITE_URL, $content);
       $content = preg_replace_callback('/((?:https?|mailto|ftp):\/\/([^\x{2e80}-\x{9fff}\s<\'\"“”‘’，。}]*)?)/u', '_parse_url2', $content);
       //dump($content);
    }

    //表情处理
    $content = preg_replace_callback("/(\[.+?\])/is",_w3g_parse_expression,$content);
    $content = preg_replace_callback("/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is",replaceEmot,$content);
    $content = preg_replace_callback("/#([^#]*[^#^\s][^#]*)#/is",wapFormatTopic,$content);
    $content = preg_replace_callback("/@([\w\x{2e80}-\x{9fff}\-]+)/u",wapFormatUser,$content);
   // dump($content);
    return $content;
}

// 格式化评论
function wapFormatComment($content,$url=false, $from_url = '') {
    $content = real_strip_tags($content);
    if($url){
        $content = preg_replace('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/ue', 
            "'<a  class=\"c_a\" href=\"'.U('w3g/Index/urlalert').'&from_url={$from_url}&url='.urlencode('\\1').'\">\\1</a>\\2'", 
            $content);
    }
    $content = preg_replace_callback("/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is",replaceEmot,$content);
    $content = preg_replace_callback("/@([\w\x{2e80}-\x{9fff}\-]+)/u",wapFormatUser,$content);
    return $content;
}

// 话题格式化回调
function wapFormatTopic($data) {
    return "<a class='c_a' href=".U('w3g/Index/doSearch',array('key'=>t($data[1]))).">".$data[0]."</a>";
}

// 用户连接格式化回调
function wapFormatUser($name) {
    $info = D('User', 'home')->getUserByIdentifier($name[1], 'uname');
    if( $info ){
        return "<a class='c_a' href=".U('w3g/Index/weibo',array('uid'=>$info['uid'])).">".$name[0]."</a>";
    }else{
        return "$name[0]";
    }
}

// 短地址
function getContentUrl($url) {
    return getShortUrl( $url[1] ).' ';
}


function is_iphone() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("iphone","ipad","ipod");
    $is_iphone = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_iphone = true;
            break;
        }
    }
    return $is_iphone;
}

function is_android() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("android");
    $is_android = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_android = true;
            break;
        }
    }
    return $is_android;
}

/**
 * 表情替换 [格式化微博与格式化评论专用]
 * @param array $data
 */
function _w3g_parse_expression($data) {
    if(preg_match("/#.+#/i",$data[0])) {
        return $data[0];
    }
    $allexpression = model('Expression')->getAllExpression();
    $info = $allexpression[$data[0]];
    if($info) {
        return preg_replace("/\[.+?\]/i","<img src='".__THEME__."/image/expression/miniblog/".$info['filename']."' />",$data[0]);
    }else {
        return $data[0];
    }
}
?>