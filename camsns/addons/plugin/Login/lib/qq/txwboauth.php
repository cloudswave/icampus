<?php 
class QqWeiboClient
{ 

    function __construct( $akey , $skey , $accecss_token , $accecss_token_secret ) 
    { 
        $this->oauth = new QqWeiboOAuth( $akey , $skey , $accecss_token , $accecss_token_secret );
    } 


	function get_ip() {
if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) 
{ 
$ip = $HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
} 
elseif ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) 
{ 
$ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
} 
elseif ($HTTP_SERVER_VARS["REMOTE_ADDR"]) 
{ 
$ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
} 
elseif (getenv("HTTP_X_FORWARDED_FOR")) 
{ 
$ip = getenv("HTTP_X_FORWARDED_FOR");
} 
elseif (getenv("HTTP_CLIENT_IP")) 
{ 
$ip = getenv("HTTP_CLIENT_IP");
} 
elseif (getenv("REMOTE_ADDR")) 
{ 
$ip = getenv("REMOTE_ADDR");
} 
else 
{ 
$ip = "Unknown"; 
} 
   return $ip;
}


    //广播大厅时间线
    function public_timeline($pos=0,$reqnum=20,$format='json') 
    { 
    	$param['format']=$format;
    	$param['pos']=$pos;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/public_timeline',$param); 
    } 

	//首页时间线
    function home_timeline($pageflag=0,$pagetime=0,$reqnum=20,$format='json') 
    { 
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/home_timeline',$param); 
    } 


    //其他用户发表时间线
	    function user_timeline($name,$pageflag=0,$pagetime=0,$reqnum=20,$format='json') 
    { 
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
		$param['name']=$name;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/user_timeline',$param); 
    } 

   // @提到我的微博时间线
   function  mentions_timeline($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/mentions_timeline',$param);    
      }

  //话题时间线
   function  ht_timeline($httext,$pageflag=1,$pageinfo='',$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
		$param['httext']=$httext;
    	$param['pageflag']=$pageflag;
    	$param['pageinfo']=$pageinfo;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/ht_timeline',$param);    
      }
  
  //我发表时间线
   function broadcast_timeline($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/broadcast_timeline',$param);    
      }

//特别收听的人发表时间线
   function special_timeline($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/statuses/special_timeline',$param);    
      }



//--------------------------------------------------------------------//
//***************************微博相关*********************************//
//--------------------------------------------------------------------//

//1.t/show 获取一条微博数据
    function t_show($id,$format='json') 
    { 
        $param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/t/show' ,$param); 
    } 

//2.t/add 发表一条微博
    function t_add($content='',$jing='',$wei='',$format='json') 
    { 
        $param['content'] =$content; 
        $param['format']=$format;
		$param['jing']=$jing;
		$param['wei']=$wei;
		$param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/t/add' ,$param); 
    } 

//3.t/del 删除一条微博
    function t_del($id,$format='json') 
    { 
        $param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/t/del' ,$param); 
    } 

//4.t/re_add 转播一条微博
    function t_re_add($reid,$content='',$jing='',$wei='',$format='json') 
    { 
        $param['content'] =$content; 
		$param['reid'] =$reid; 
        $param['format']=$format;
		$param['jing']=$jing;
		$param['wei']=$wei;
		$param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/t/re_add' ,$param); 
    } 

//5.t/reply 回复一条微博
    function t_reply($reid,$content='',$jing='',$wei='',$format='json') 
    { 
        $param['content'] =$content; 
		$param['reid'] =$reid; 
        $param['format']=$format;
		$param['jing']=$jing;
		$param['wei']=$wei;
		$param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/t/reply' ,$param); 
    } 

//6.t/add_pic 发表一条带图片的微博
    function t_add_pic($content='',$pic_data='',$jing='',$wei='',$format='json') 
    { 
        $param['content']=$content;
        $param['format']=$format;
        $param['pic']='@'. $pic_data;
		$param['jing']=$jing;
		$param['wei']=$wei;
        $param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/t/add_pic',$param,true); //采用multi form-data方式提交
    } 

//7.t/re_count 转播数  参数 $ids 的格式为: "第1条微博id,第2条微博id,第3条微博id,........."   最多30个
    function t_re_count($ids,$format='json') 
    { 
        $param['ids'] =$ids; 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/t/re_count' ,$param); 
    } 

//8.t/re_list 获取单条微博的转发或点评列表  Flag:标识0 转播列表，1点评列表 2 点评与转播列表
   function  t_re_list($flag,$rootid,$pageflag=0,$pagetime=0,$reqnum=20,$twitterid=0,$format='json')
	   {
    	$param['format']=$format;
		$param['flag']=$flag;
		$param['rootid']=$rootid;
		$param['twitterid']=$twitterid;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get('http://open.t.qq.com/api/t/re_list',$param);    
      }

//9.t/comment 点评一条微博
    function t_comment($reid,$content='',$jing='',$wei='',$format='json') 
    { 
        $param['content'] =$content; 
		$param['reid'] =$reid; 
        $param['format']=$format;
		$param['jing']=$jing;
		$param['wei']=$wei;
		$param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/t/comment' ,$param); 
    } 

//--------------------------------------------------------------------//
//********************       帐户相关       **************************//
//--------------------------------------------------------------------//

//1.User/info获取自己的详细资料
    function user_info($format='json') 
    { 
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/user/info' ,$param); 
    } 
    
//2.user/update 更新用户信息
    function user_update($nick='',$introduction='',$sex=0,$year=1980,$month=1,$day=1,$countrycode=1,$provincecode=1,$citycode=1,$format='json') 
    { 
       $param['nick'] =$nick; 
		$param['introduction'] =$introduction; 
       $param['sex']=$sex;
		$param['year']=$year;
		$param['month']=$month;
		$param['day']=$day;
		$param['countrycode']=$countrycode;
		$param['provincecode']=$provincecode;
		$param['citycode']=$citycode;
		 $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/user/update' ,$param); 
    } 
//3.user/update_head 更新用户头像信息
    function user_update_head($pic,$format='json') 
    { 
       $param['pic'] =$pic; 
       $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/user/update_head' ,$param,true); 
    } 

//4.user/other_info 获取其他人资料
    function user_other_info($name,$format='json') 
    {   
		$param['name'] =$name;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/user/other_info' ,$param); 
    } 



//--------------------------------------------------------------------//
//********************      关系链相关      **************************//
//--------------------------------------------------------------------//


//1.friends/fanslist 我的听众列表
    function f_fanslist($startindex=0,$reqnum=30,$format='json') 
    {   
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/fanslist' ,$param); 
    } 

//2.friends/idollist 我收听的人列表
    function f_idollist($startindex=0,$reqnum=30,$format='json') 
    {   
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/idollist' ,$param); 
    } 

//3.friends/blacklist 黑名单列表
    function f_blacklist($startindex=0,$reqnum=30,$format='json') 
    {   
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/blacklist' ,$param); 
    } 

//4.friends/speciallist 特别收听列表
    function f_speciallist($startindex=0,$reqnum=30,$format='json') 
    {   
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/speciallist' ,$param); 
    } 


//5.friends/add  收听某个人
    function f_add($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/add' ,$param); 
    } 

//6.friends/del  取消收听某个人
    function f_del($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/del' ,$param); 
    } 

//7.friends/addspecial 特别收听某个
    function f_add_s($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/addspecial' ,$param); 
    } 

//8.friends/delspecial 取消特别收听某个
    function f_del_s($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/delspecial' ,$param); 
    } 

//9.friends/addblacklist 添加某个用户到黑名单
    function f_add_black($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/addblacklist' ,$param); 
    } 
//10.friends/delblacklist 从黑名单释放某用户
    function f_del_black($name='',$format='json') 
    { 
        $param['name'] =$name; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/friends/delblacklist' ,$param); 
    } 

//11.friends/check 检测是否是我的粉丝或偶像  flag 0:粉丝  1：偶像
    function f_check($names='',$flag=0,$format='json') 
    { 
        $param['names'] =$names; 
        $param['flag'] =$flag; 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/friends/check' ,$param); 
    } 

//12.friends/user_fanslist 其他用户的听众列表
    function f_user_fanslist($name,$startindex=0,$reqnum=30,$format='json') 
    {   $param['name'] =$name;
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/user_fanslist' ,$param); 
    } 

//13.friends/user_idollist 其他用户的听众列表
    function f_user_idollist($name,$startindex=0,$reqnum=30,$format='json') 
    {   $param['name'] =$name;
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/user_idollist' ,$param); 
    } 

//4.friends/user_speciallist  其他用户的特别收听列表
    function f_user_speciallist($name,$startindex=0,$reqnum=30,$format='json') 
    {   $param['name'] =$name;
		$param['startindex'] =$startindex;
		$param['reqnum'] =$reqnum;
        $param['format']=$format;
        return $this->oauth->get('http://open.t.qq.com/api/friends/user_speciallist' ,$param); 
    } 


//--------------------------------------------------------------------//
//********************       私信相关       **************************//
//--------------------------------------------------------------------//

//1.private/add 发一条私信
    function pm_add($name,$content='',$jing='',$wei='',$format='json') 
    { 
        $param['content'] =$content; 
		$param['name'] =$name; 
        $param['format']=$format;
		$param['jing']=$jing;
		$param['wei']=$wei;
		$param['clientip']=$this->get_ip();
        return $this->oauth->post( 'http://open.t.qq.com/api/private/add' ,$param); 
    } 
//2.private/del 删除一条私信
    function pm_del($id,$format='json') 
    { 
		$param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/private/del' ,$param); 
    } 

//3.private/recv 获取私信收件箱列表
    function pm_recv($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get( 'http://open.t.qq.com/api/private/recv' ,$param); 
    } 

//4.private/send 获取私信收件箱列表
    function pm_send($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get( 'http://open.t.qq.com/api/private/send' ,$param); 
    } 


//--------------------------------------------------------------------//
//********************       搜索相关       **************************//
//--------------------------------------------------------------------//

//1.Search/user 搜索用户
    function search_user($keyword,$page=1,$pagesize=10,$format='json')
	   {
    	$param['format']=$format;
    	$param['page']=$page;
    	$param['pagesize']=$pagesize;
    	$param['keyword']=$keyword;
        return $this->oauth->get( 'http://open.t.qq.com/api/search/user' ,$param); 
    } 

//2.Search/t 搜索微博
    function search_t($keyword,$page=1,$pagesize=10,$format='json')
	   {
    	$param['format']=$format;
    	$param['page']=$page;
    	$param['pagesize']=$pagesize;
    	$param['keyword']=$keyword;
        return $this->oauth->get( 'http://open.t.qq.com/api/search/t' ,$param); 
    } 

//3.Search/userbytag 搜索微博
    function search_by_tag($keyword,$page=1,$pagesize=10,$format='json')
	   {
    	$param['format']=$format;
    	$param['page']=$page;
    	$param['pagesize']=$pagesize;
    	$param['keyword']=$keyword;
        return $this->oauth->get( 'http://open.t.qq.com/api/search/userbytag' ,$param); 
    } 




//--------------------------------------------------------------------//
//********************       标签相关       **************************//
//--------------------------------------------------------------------//

//1.tag/add 添加标签
    function tag_add($tag,$format='json') 
    { 
		$param['tag'] =$tag; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/tag/add' ,$param); 
    } 
//2.tag/del  删除标签
    function tag_del($tagid,$format='json') 
    { 
		$param['tagid'] =$tagid; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/tag/del' ,$param); 
    } 


//--------------------------------------------------------------------//
//********************       热度趋势       **************************//
//--------------------------------------------------------------------//

//1.trends/ht 话题热榜
    function hts($type=3,$pos=0,$reqnum=20,$format='json') 
    { 
		$param['type'] =$type; 
		$param['pos'] =$pos; 
		$param['reqnum'] =$reqnum; 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/trends/ht' ,$param); 
    } 

//--------------------------------------------------------------------//
//********************       数据更新       **************************//
//--------------------------------------------------------------------//
//1.info/update 查看数据更新条数
    function info_update($op=0,$type=5,$format='json') 
    { 
		$param['op'] =$op; 
		if($op==1){
		$param['type'] =$type;
		}
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/info/update' ,$param); 
    } 

//--------------------------------------------------------------------//
//********************       数据收藏       **************************//
//--------------------------------------------------------------------//
//1.fav/addt 收藏一条微博
    function fav_add_t($id,$format='json') 
    { 
		$param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/fav/addt' ,$param); 
    } 
//2.fav/delt 删除一条收藏
    function fav_del_t($id,$format='json') 
    { 
		$param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/fav/delt' ,$param); 
    } 
//3.fav/list_t 获取收藏的微博列表
    function fav_list_t($pageflag=0,$pagetime=0,$reqnum=20,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get( 'http://open.t.qq.com/api/fav/list_t' ,$param); 
    } 
//4.fav/addht 收藏话题
    function fav_add_ht($id,$format='json') 
    { 
		$param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/fav/addht' ,$param); 
    } 
//5.fav/delt 删除一条话题收藏 
    function fav_del_ht($id,$format='json') 
    { 
		$param['id'] =$id; 
        $param['format']=$format;
        return $this->oauth->post( 'http://open.t.qq.com/api/fav/delht' ,$param); 
    } 
//6.fav/list_ht 获取收藏的话题列表
    function fav_list_ht($pageflag=0,$pagetime=0,$reqnum=15,$format='json')
	   {
    	$param['format']=$format;
    	$param['pageflag']=$pageflag;
    	$param['pagetime']=$pagetime;
    	$param['reqnum']=$reqnum;
        return $this->oauth->get( 'http://open.t.qq.com/api/fav/list_ht' ,$param); 
    } 

//--------------------------------------------------------------------//
//********************       话题相关       **************************//
//--------------------------------------------------------------------//
//1.ht/ids 根据话题名称查话题ID
    function ht_ids($httexts,$format='json')
	   {
    	$param['httexts']=$httexts;
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/ht/ids' ,$param); 
    } 
//2.ht/info 根据话题名称查话题ID
    function ht_info($ids,$format='json')
	   {
    	$param['ids']=$ids;
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/ht/info' ,$param); 
    } 
//--------------------------------------------------------------------//
//********************       other          **************************//
//--------------------------------------------------------------------//
//other/kownperson   我可能认识的人
function kownperson($ip=false,$format='json')
	{
    	$param['ip']=$this->get_ip();
        $param['format']=$format;
		return $this->oauth->get( 'http://open.t.qq.com/api/other/kownperson' ,$param); 
    }

	//发表微博

	//我的信息
    function getinfo($format='json') 
    { 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/user/info' ,$param); 
    } 
    
  	//发表带图片微博







   //获取自己信息(sina api 同步)
	 function verify_credentials($format='json') 
   { 
        $param['format']=$format;
        return $this->oauth->get( 'http://open.t.qq.com/api/user/info',$param); 
    } 




   
} 


class QqWeiboOAuth extends WeiboOAuth
{    
    public $host = "http://open.t.qq.com/"; 

    /** 
     * Set API URLS 
     */ 
    /** 
     * @ignore 
     */ 
    function accessTokenURL()  { return 'http://open.t.qq.com/cgi-bin/access_token'; } 
    /** 
     * @ignore 
     */ 
    function authorizeURL()    { return 'http://open.t.qq.com/cgi-bin/authorize'; } 
    /** 
     * @ignore 
     */ 
    
   /*
    function requestTokenURL() { return 'http://api.t.sina.com.cn/oauth/request_token'; }
    */ 
   function requestTokenURL() { return 'http://open.t.qq.com/cgi-bin/request_token'; } 
    

    /** 
     * Debug helpers 
     */ 
    /** 
     * @ignore 
     */ 
    function lastStatusCode() { return $this->http_status; } 
    /** 
     * @ignore 
     */ 
    function lastAPICall() { return $this->last_api_call; } 
} 