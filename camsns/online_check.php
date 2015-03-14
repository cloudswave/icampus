<?php
//exit;
define('SITE_PATH',dirname(__FILE__));
date_default_timezone_set('PRC');
error_reporting(0);
session_start();
//$encrypt	=	1;
//exit;
/* ===================================== 配置部分 ========================================== */

$check_time		=	300;	//10分钟检查一次
$online_time	=	1800;	//统计30分钟的在线用户

$app			=	t($_GET['app'])?t($_GET['app']):'public';
$mod			=	t($_GET['mod'])?t($_GET['mod']):'Index';
$act			=	t($_GET['act'])?t($_GET['act']):'index';
$action			=	$app."/".$mod."/".$act;
$uid			=	isset($_GET['uid'])?intval($_GET['uid']):0;
$uname			=	t($_GET['uname'])?t($_GET['uname']):'guest';
$agent			=	getBrower();
$ip				=	getClientIp();
$refer			=	addslashes($_SERVER['HTTP_REFERER']);
$isGuest		=	($uid==-1 || $uid==0)?1:0;
$isIntranet		=	(substr($ip,0,2)=='10.')?1:0;
$cTime			=	time();
$ext			=	'';

//全局配置
$config		=	require(SITE_PATH.'/config/config.inc.php');

//数据库配置
$db_config		=	!empty($config['ONLINE_DB']) ? array_merge($config,$config['ONLINE_DB']) : $config;


$dbconfig	=	array();
$dbconfig['DB_TYPE']	=	$db_config['DB_TYPE'];
$dbconfig['DB_HOST']	=	$db_config['DB_HOST'];
$dbconfig['DB_NAME']	=	$db_config['DB_NAME'];
$dbconfig['DB_USER']	=	$db_config['DB_USER'];
$dbconfig['DB_PWD']		=	$db_config['DB_PWD'];
$dbconfig['DB_PORT']	=	$db_config['DB_PORT'];
$dbconfig['DB_PREFIX']	=	$db_config['DB_PREFIX'];
$dbconfig['DB_CHARSET']	=	$db_config['DB_CHARSET'];



$db	=	new Db($dbconfig);


//记录在线统计.
if($_GET['action']=='trace'){
    
    
	/* ===================================== step 1 record track ========================================== */
		
	$sql	=	"INSERT INTO ".$config['DB_PREFIX']."online_logs 
				(day,uid,uname,action,refer,isGuest,isIntranet,ip,agent,ext)
				VALUES ( CURRENT_DATE,'$uid','$uname','$action','$refer','$isGuest','$isIntranet','$ip','$agent','$ext');";
    
              
	$result	=	$db->execute("$sql");
	

	/* ===================================== step 2 update hits ========================================== */
	
	//memcached更新.写入全局点击量.每个应用的点击量.每个版块的点击量.

	/* ===================================== step 3 update heartbeat ========================================== */
	

	if( ( cookie('online_update') + $check_time ) < $cTime ){
	   
	//刷新用户在线时间
		//设置10分钟过期
		cookie('online_update',$cTime,7200);

		//$_SESSION['online_pageviews']	=	0;

		//判断是否存在记录.
		if($uid>0){
			$where	=	"WHERE (uid='$uid')";
		}else{
			$where	=	"WHERE (uid=0 AND ip='$ip')";
		}
		$sql	=	"SELECT uid FROM ".$config['DB_PREFIX']."online ".$where;
       
		$result	=	$db->query("$sql");
		//如果没有记录.添加记录.
		if($result){

			$sql	=	"UPDATE ".$config['DB_PREFIX']."online SET activeTime=$cTime,ip='$ip' ".$where;
			$result	=	$db->execute("$sql");	
		}else{

			$sql	=	"INSERT INTO ".$config['DB_PREFIX']."online (uid,uname,app,ip,agent,activeTime) VALUES ('$uid','{$uname}','$app','$ip','$agent',$cTime);";
			$result	=	$db->execute("$sql");
		}
     
	}
    if($result){
       echo 'var onlineclick = "ok";';
    }
}
/* ===================================== 公共部分 ========================================== */
// 获取客户端IP地址
function getClientIp() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
		$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
		$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
		$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
		$ip = $_SERVER ['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return addslashes($ip);
}
// 过滤非法html标签
function t($text) {
    //过滤标签
    $text = nl2br($text);
    $text = real_strip_tags($text);
    $text = addslashes($text);
    $text = trim($text);
    return addslashes($text);
}
function real_strip_tags($str, $allowable_tags="") {
    $str = stripslashes(htmlspecialchars_decode($str));
    return strip_tags($str, $allowable_tags);
}
// 获取用户浏览器型号。新加浏览器，修改代码，增加特征字符串.把IE加到12.0 可以使用5-10年了.
function getBrower(){
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {  
	    $browser = 'Maxthon'; 
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {  
	    $browser = 'IE12.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {  
	    $browser = 'IE11.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {  
	    $browser = 'IE10.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {  
	    $browser = 'IE9.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {  
	    $browser = 'IE8.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {  
	    $browser = 'IE7.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {  
	    $browser = 'IE6.0';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {  
	    $browser = 'NetCaptor';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {  
	    $browser = 'Netscape';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {  
	    $browser = 'Lynx';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {  
	    $browser = 'Opera';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {  
	    $browser = 'Google';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {  
	    $browser = 'Firefox';  
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {  
	    $browser = 'Safari'; 
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'iphone') || strpos($_SERVER['HTTP_USER_AGENT'], 'ipod')) {  
	    $browser = 'iphone';
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'ipad')) {  
	    $browser = 'iphone';
	} elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'android')) {  
	    $browser = 'android';
	} else {  
	    $browser = 'other';  
	}
	return addslashes($browser);
}
// 浏览器友好的变量输出
function dump($var) {
	ob_start();
	var_dump($var);
	$output = ob_get_clean();
	if(!extension_loaded('xdebug')) {
		$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
		$output = '<pre style="text-align:left">'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
	}
    echo($output);
}
// 设置cookie
function cookie($name,$value='',$option=null)
{
    // 默认设置
    $config = array(
        'prefix' => $GLOBALS['config']['COOKIE_PREFIX'], // cookie 名称前缀
        'expire' => $GLOBALS['config']['COOKIE_EXPIRE'], // cookie 保存时间
        'path'   => '/',   // cookie 保存路径
        'domain' => '', // cookie 有效域名
    );

    // 参数设置(会覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option)) {
            $option = array('expire'=>$option);
        }else if( is_string($option) ) {
            parse_str($option,$option);
        }
        $config =   array_merge($config,array_change_key_case($option));
    }

    // 清除指定前缀的所有cookie
    if (is_null($name)) {
       if (empty($_COOKIE)) return;
       // 要删除的cookie前缀，不指定则删除config设置的指定前缀
       $prefix = empty($value)? $config['prefix'] : $value;
       if (!empty($prefix))// 如果前缀为空字符串将不作处理直接返回
       {
           foreach($_COOKIE as $key=>$val) {
               if (0 === stripos($key,$prefix)){
                    setcookie($_COOKIE[$key],'',time()-3600,$config['path'],$config['domain']);
                    unset($_COOKIE[$key]);
               }
           }
       }
       return;
    }
    $name = $config['prefix'].$name;

 
    if (''===$value){
        //return isset($_COOKIE[$name]) ? unserialize($_COOKIE[$name]) : null;// 获取指定Cookie
        return isset($_COOKIE[$name]) ? ($_COOKIE[$name]) : null;// 获取指定Cookie
    }else {
        if (is_null($value)) {
            setcookie($name,'',time()-3600,$config['path'],$config['domain']);
            unset($_COOKIE[$name]);// 删除指定cookie
        }else {
            // 设置cookie
            $expire = !empty($config['expire'])? time()+ intval($config['expire']):0;
            //setcookie($name,serialize($value),$expire,$config['path'],$config['domain']);
           
            setcookie($name,($value),$expire,$config['path'],$config['domain'],false,true);

            //$_COOKIE[$name] = ($value);
        }
    }
}
/**
 +------------------------------------------------------------------------------
 * ThinkPHP 简洁模式数据库中间层实现类
 * 只支持mysql
 +------------------------------------------------------------------------------
 */
class Db
{

    static private $_instance	= null;
    // 是否显示调试信息 如果启用会在日志文件记录sql语句
    public $debug				= false;
    // 是否使用永久连接
    protected $pconnect         = false;
    // 当前SQL指令
    protected $queryStr			= '';
    // 最后插入ID
    protected $lastInsID		= null;
    // 返回或者影响记录数
    protected $numRows			= 0;
    // 返回字段数
    protected $numCols			= 0;
    // 事务指令数
    protected $transTimes		= 0;
    // 错误信息
    protected $error			= '';
    // 当前连接ID
    protected $linkID			=   null;
    // 当前查询ID
    protected $queryID			= null;
    // 是否已经连接数据库
    protected $connected		= false;
    // 数据库连接参数配置
    protected $config			= '';
    // SQL 执行时间记录
    protected $beginTime;
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config 数据库配置数组
     +----------------------------------------------------------
     */
    public function __construct($config=''){
        if ( !extension_loaded('mysql') ) {
            echo('not support mysql');
        }
        $this->config   =   $this->parseConfig($config);
    }

    /**
     +----------------------------------------------------------
     * 连接数据库方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect() {
        if(!$this->connected) {
            $config =   $this->config;
            // 处理不带端口号的socket连接情况
            $host = $config['hostname'].($config['hostport']?":{$config['hostport']}":'');
            if($this->pconnect) {
                $this->linkID = mysql_pconnect( $host, $config['username'], $config['password']);
            }else{
                $this->linkID = mysql_connect( $host, $config['username'], $config['password'],true);
            }
            if ( !$this->linkID || (!empty($config['database']) && !mysql_select_db($config['database'], $this->linkID)) ) {
                echo(mysql_error());
            }
            $dbVersion = mysql_get_server_info($this->linkID);
            if ($dbVersion >= "4.1") {
                //使用UTF8存取数据库 需要mysql 4.1.0以上支持
                mysql_query("SET NAMES 'UTF8'", $this->linkID);
            }
            //设置 sql_model
            if($dbVersion >'5.0.1'){
                mysql_query("SET sql_mode=''",$this->linkID);
            }
            // 标记连接成功
            $this->connected    =   true;
            // 注销数据库连接配置信息
            unset($this->config);
        }
    }

    /**
     +----------------------------------------------------------
     * 释放查询结果
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function free() {
        mysql_free_result($this->queryID);
        $this->queryID = 0;
    }

    /**
     +----------------------------------------------------------
     * 执行查询 主要针对 SELECT, SHOW 等指令
     * 返回数据集
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($str='') {
        $this->connect();
        if ( !$this->linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        //释放前次的查询结果
        if ( $this->queryID ) {    $this->free();    }
        $this->Q(1);
        $this->queryID = mysql_query($this->queryStr, $this->linkID);
        $this->debug();
        if ( !$this->queryID ) {
            if ( $this->debug )
                echo($this->error());
            else
                return false;
        } else {
            $this->numRows = mysql_num_rows($this->queryID);
            return $this->getAll();
        }
    }

    /**
     +----------------------------------------------------------
     * 执行语句 针对 INSERT, UPDATE 以及DELETE
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sql指令
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($str='') {
        $this->connect();
        if ( !$this->linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        //释放前次的查询结果
        if ( $this->queryID ) {    $this->free();    }
        $this->W(1);
        $result =   mysql_query($this->queryStr, $this->linkID) ;
        $this->debug();
        if ( false === $result) {
            if ( $this->debug )
                echo($this->error());
            else
                return false;
        } else {
            $this->numRows = mysql_affected_rows($this->linkID);
            $this->lastInsID = mysql_insert_id($this->linkID);
            return $this->numRows;
        }
    }


    /**
     +----------------------------------------------------------
     * 获得所有的查询数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getAll() {
        if ( !$this->queryID ) {
            echo($this->error());
            return false;
        }
        //返回数据集
        $result = array();
        if($this->numRows >0) {
            while($row = mysql_fetch_assoc($this->queryID)){
                $result[]   =   $row;
            }
            mysql_data_seek($this->queryID,0);
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * 关闭数据库
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function close() {
        if (!empty($this->queryID))
            mysql_free_result($this->queryID);
        if ($this->linkID && !mysql_close($this->linkID)){
            echo($this->error());
        }
        $this->linkID = 0;
    }

    /**
     +----------------------------------------------------------
     * 数据库错误信息
     * 并显示当前的SQL语句
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = mysql_error($this->linkID);
        if($this->queryStr!=''){
            $this->error .= "\n [ SQL语句 ] : ".$this->queryStr;
        }
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * SQL指令安全过滤
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQL字符串
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escape_string($str) {
        $res = @mysql_escape_string($str);
        $res === false && $res = $str;
        return $res;
    }

   /**
     +----------------------------------------------------------
     * 析构方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct()
    {
        // 关闭连接
        $this->close();
    }

    /**
     +----------------------------------------------------------
     * 取得数据库类实例
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @return mixed 返回数据库驱动类
     +----------------------------------------------------------
     */
    public static function getInstance($db_config='')
    {
		if ( self::$_instance==null ){
			self::$_instance = new Db($db_config);
		}
		return self::$_instance;
    }

    /**
     +----------------------------------------------------------
     * 分析数据库配置信息，支持数组和DSN
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param mixed $db_config 数据库配置信息
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function parseConfig($_db_config='') {
		// 如果配置为空，读取配置文件设置
		$db_config = array (
			'dbms'		=>   $_db_config['DB_TYPE'],
			'username'	=>   $_db_config['DB_USER'],
			'password'	=>   $_db_config['DB_PWD'],
			'hostname'	=>   $_db_config['DB_HOST'],
			'hostport'	=>   $_db_config['DB_PORT'],
			'database'	=>   $_db_config['DB_NAME'],
			'dsn'		=>   $_db_config['DB_DSN'],
			'params'	=>   $_db_config['DB_PARAMS'],
		);
        return $db_config;
    }

    /**
     +----------------------------------------------------------
     * 数据库调试 记录当前SQL
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function debug() {
        // 记录操作结束时间
        if ( $this->debug )    {
            $runtime    =   number_format(microtime(TRUE) - $this->beginTime, 6);
            Log::record(" RunTime:".$runtime."s SQL = ".$this->queryStr,Log::SQL);
        }
    }

    /**
     +----------------------------------------------------------
     * 查询次数更新或者查询
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $times
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function Q($times='') {
        static $_times = 0;
        if(empty($times)) {
            return $_times;
        }else{
            $_times++;
            // 记录开始执行时间
            $this->beginTime = microtime(TRUE);
        }
    }

    /**
     +----------------------------------------------------------
     * 写入次数更新或者查询
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $times
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function W($times='') {
        static $_times = 0;
        if(empty($times)) {
            return $_times;
        }else{
            $_times++;
            // 记录开始执行时间
            $this->beginTime = microtime(TRUE);
        }
    }

    /**
     +----------------------------------------------------------
     * 获取最近一次查询的sql语句
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastSql() {
        return $this->queryStr;
    }

}//类定义结束
?>