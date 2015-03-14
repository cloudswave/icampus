<?php
abstract class WeiboOAuth
{
    /**
     * Contains the last HTTP status code returned.
     *
     * @ignore
     */
    public $http_code;
    /**
     * Contains the last API call.
     *
     * @ignore
     */
    public $url;
    /**
     * Set up the API root URL.
     *
     * @ignore
     */
    /**
     * Set timeout default.
     *
     * @ignore
     */
    public $timeout = 30;
    /**
     * Set connect timeout.
     *
     * @ignore
     */
    public $connecttimeout = 30;
    /**
     * Verify SSL Cert.
     *
     * @ignore
     */
    public $ssl_verifypeer = FALSE;
    /**
     * Respons format.
     *
     * @ignore
     */
    public $format = 'json';
    /**
     * Decode returned json data.
     *
     * @ignore
     */
    public $decode_json = TRUE;
    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     */
    public $http_info;
    /**
     * Set the useragnet.
     *
     * @ignore
     */
    public $useragent = 'Sae T OAuth v0.2.0-beta2';
    /* Immediately retry the API call if the response was not successful. */
    //public $retry = TRUE;
    /**
     * construct WeiboOAuth object
     */
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        } else {
            $this->token = NULL;
        }
    }
    /**
     * Get a request_token from Weibo
     *
     * @return array a key/value array containing oauth_token and oauth_token_secret
     */
    function getRequestToken($oauth_callback = NULL) {
        $parameters = array();
        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }
        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }
    /**
     * Get the authorize URL
     *
     * @return string
     */
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_Weibo)) {
            return $this->authorizeURL() . "?oauth_token={$token}";
        } else {
            return $this->authenticateURL() . "?oauth_token={$token}";
        }
    }
    function getAuthorizeJSON($token, $sign_in_with_Weibo = TRUE , $userid,$passwd){
       if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_Weibo)) {
            $info = $this->http($this->authorizeURL()."?oauth_token={$token}&oauth_callback=json&userId={$userid}&passwd={$passwd}", "get");
        } else {
            $info = $this->http( $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=json&userId={$userid}&passwd={$passwd}","get");
        }
        return $info;
    }
    /**
     * Exchange the request token and secret for an access token and
     * secret, to sign API calls.
     *
     * @return array array("oauth_token" => the access token,
     *                "oauth_token_secret" => the access secret)
     */
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false) {
        $parameters = array();
        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }
        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }
    /**
     * GET wrappwer for oAuthRequest.
     *
     * @return mixed
     */
    function get($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }
    /**
     * POST wreapper for oAuthRequest.
     *
     * @return mixed
     */
    function post($url, $parameters = array() , $multi = false) {
        $response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }
    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    function delete($url, $parameters = array()) {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }
    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     */
    function oAuthRequest($url, $method, $parameters , $multi = false) {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method) {
        case 'GET':
            return $this->http($request->to_url(), 'GET');
        default:
            return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi );
        }
    }
    /**
     * Http请求接口
     *
     * @param string $url
     * @param string $method 支持 GET / POST / DELETE
     * @param string $postfields
     * @param boolean $multi false:普通post true: 文件上传
     * @return string
     */
    function http($url, $method, $postfields = NULL , $multi = false)
    {
        //for test ,print all send params
//      ksort($params);
//      print_r($params);
        
        $method = strtoupper($method);
        $postdata = '';
        $urls = @parse_url($url);
        $httpurl = $urlpath = $urls['path'] . ($urls['query'] ? '?' . $urls['query'] : '');
        if( !$multi )
        {
            if ($postfields)
            {
                $postdata = $postfields;
                $httpurl = $httpurl . (strpos($httpurl, '?') ? '&' : '?') . $postdata;
            }
            else
            {
            }
        }
        
        $host = $urls['host'];
        $port = $urls['port'] ? $urls['port'] : 80;
        $version = '1.1';
        if($urls['scheme'] === 'https')
        {
            $port = 443;
        }
        $headers = array();
        if($method == 'GET')
        {
            $headers[] = "GET $httpurl HTTP/$version";
        }
        else if($method == 'DELETE')
        {
            $headers[] = "DELETE $httpurl HTTP/$version";
        }
        else
        {
            $headers[] = "POST $urlpath HTTP/$version";
        }
        $headers[] = 'Host: ' . $host;
        $headers[] = 'User-Agent: OpenSDK-OAuth';
        $headers[] = 'Connection: Close';
        if($method == 'POST')
        {
            if($multi)
            {
                $headers[]= 'Content-Type: multipart/form-data; boundary=' . OAuthUtil::$boundary;
                $postdata = $postfields;
            }
            else
            {
                $headers[]= 'Content-Type: application/x-www-form-urlencoded';
            }
        }
        $ret = '';
        $fp = fsockopen($host, $port, $errno, $errstr, 5);
        if(! $fp)
        {
            $error = 'Open Socket Error';
            return '';
        }
        else
        {
            if( $method != 'GET' && $postdata )
            {
                $headers[] = 'Content-Length: ' . strlen($postdata);
            }
            $this->fwrite($fp, implode("\r\n", $headers));
            $this->fwrite($fp, "\r\n\r\n");
            if( $method != 'GET' && $postdata )
            {
                $this->fwrite($fp, $postdata);
            }
            while(! feof($fp))
            {
                $ret .= fgets($fp, 1024);
            }
            fclose($fp);
            //skip headers
            $pos = strpos($ret, "\r\n\r\n");
            if($pos)
            {
                $rt = trim(substr($ret , $pos+1));
                $responseHead = trim(substr($ret, 0 , $pos));
                $responseHeads = explode("\r\n", $responseHead);
                $httpcode = explode(' ', $responseHeads[0]);
                $this->_httpcode = $httpcode[1];
                if(strpos( substr($ret , 0 , $pos), 'Transfer-Encoding: chunked'))
                {
                    $response = explode("\r\n", $rt);
                    $t = array_slice($response, 1, - 1);
                    return implode('', $t);
                }
                return $rt;
            }
        }
        return $ret;
    }
    private function fwrite($handle,$data)
    {
        fwrite($handle, $data);
    }
    /**
     * Get the header info to store.
     *
     * @return int
     */
    function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }
    /**
     * Debug helpers
     */
    function lastStatusCode() { return $this->http_status; }
    function lastAPICall() { return $this->last_api_call; }
}
