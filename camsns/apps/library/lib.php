<?php
echo '123';
function fetch_page($site,$url,$params=false)
{
    $ch = curl_init();
    $cookieFile = $site . '_cookiejar.txt';
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE,$cookieFile);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch,   CURLOPT_SSL_VERIFYPEER,   FALSE);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    if($params)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
    curl_setopt($ch, CURLOPT_URL,$url);

    $result = curl_exec($ch);
    //file_put_contents('jobs.html',$result);
    return $result;
}
$html = fetch_page('210.42.38.33','http://210.42.38.33/login.aspx?ReturnUrl=/user/userinfo.aspx','key=value');
var_dump($html);
?>