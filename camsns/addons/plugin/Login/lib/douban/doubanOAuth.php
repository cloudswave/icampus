<?php
/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * Basic lib to work with Douban's OAuth beta. This is untested and should not
 * be used in production code. Douban's beta could change at anytime.
 *
 * Code based on:
 * Fire Eagle code - http://github.com/myelin/fireeagle-php-lib
 * twitterlibphp - http://github.com/poseurtech/twitterlibphp
 */

/* Load OAuth lib. You can find it at http://oauth.net */
require_once('OAuth.php');

/**
 * Douban OAuth class
 */
class DoubanOAuth {/*{{{*/
  /* Contains the last HTTP status code returned */
  private $http_status;

  /* Contains the last API call */
  private $last_api_call;

  /* Set up the API root URL */
  public static $TO_API_ROOT = "http://www.douban.com/service";

  /**
   * Set API URLS
   */
  function requestTokenURL() { return self::$TO_API_ROOT.'/auth/request_token'; }
  function authorizeURL() { return self::$TO_API_ROOT.'/auth/authorize'; }
  function authenticateURL() { return self::$TO_API_ROOT.'/auth/authenticate'; }
  function accessTokenURL() { return self::$TO_API_ROOT.'/auth/access_token'; }

  /**
   * Debug helpers
   */
  function lastStatusCode() { return $this->http_status; }
  function lastAPICall() { return $this->last_api_call; }

  /**
   * construct DoubanOAuth object
   */
  function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {/*{{{*/
    $this->sha1_method = new doubanOAuthSignatureMethod_HMAC_SHA1();
    $this->consumer = new doubanOAuthConsumer($consumer_key, $consumer_secret);
    if (!empty($oauth_token) || !empty($oauth_token_secret)) {
      $this->token = new doubanOAuthConsumer($oauth_token, $oauth_token_secret);
    } else {
      $this->token = NULL;
    }
  }/*}}}*/


  /**
   * Get a request_token from Douban
   *
   * @returns a key/value array containing oauth_token and oauth_token_secret
   */
  function getRequestToken() {/*{{{*/
    $r = $this->oAuthRequest($this->requestTokenURL());
	
    $token = $this->oAuthParseResponse($r);
    $this->token = new doubanOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }/*}}}*/

  /**
   * Parse a URL-encoded OAuth response
   *
   * @return a key/value array
   */
  function oAuthParseResponse($responseString) {
    $r = array();
    foreach (explode('&', $responseString) as $param) {
      $pair = explode('=', $param, 2);
      if (count($pair) != 2) continue;
      $r[urldecode($pair[0])] = urldecode($pair[1]);
    }
    return $r;
  }

  /**
   * Get the authorize URL
   *
   * @returns a string
   */
  function getAuthorizeURL($token) {/*{{{*/
    if (is_array($token)) $token = $token['oauth_token'];
    return $this->authorizeURL() . '?oauth_token=' . $token;
  }/*}}}*/
  /**
   * Get the authenticate URL
   *
   * @returns a string
   */
  function getAuthenticateURL($token) {/*{{{*/
    if (is_array($token)) $token = $token['oauth_token'];
    return $this->authenticateURL() . '?oauth_token=' . $token;
  }/*}}}*/

  /**
   * Exchange the request token and secret for an access token and
   * secret, to sign API calls.
   *
   * @returns array("oauth_token" => the access token,
   *                "oauth_token_secret" => the access secret)
   */
  function getAccessToken($token = NULL) {/*{{{*/
    $r = $this->oAuthRequest($this->accessTokenURL());
    $token = $this->oAuthParseResponse($r);
    $this->token = new doubanOAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }/*}}}*/

  /**
   * Format and sign an OAuth / API request
   */
  function oAuthRequest($url, $args = array(), $method = NULL) {/*{{{*/
    if (empty($method)) $method = empty($args) ? "GET" : "POST";
    $req = doubanOAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $args);
    $req->sign_request($this->sha1_method, $this->consumer, $this->token);
    switch ($method) {
    case 'GET': return $this->http($req->to_url());
    case 'POST': return $this->http($req->get_normalized_http_url(), $req->to_postdata());
    }
  }/*}}}*/

  /**
   * Make an HTTP request
   *
   * @return API results
   */
  function http($url, $post_data = null) {/*{{{*/
    $ch = curl_init();
    if (defined("CURL_CA_BUNDLE_PATH")) curl_setopt($ch, CURLOPT_CAINFO, CURL_CA_BUNDLE_PATH);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //////////////////////////////////////////////////
    ///// Set to 1 to verify Douban's SSL Cert //////
    //////////////////////////////////////////////////
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    if (isset($post_data)) {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    $response = curl_exec($ch);
    $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $this->last_api_call = $url;
    curl_close ($ch);
    return $response;
  }/*}}}*/
}/*}}}*/