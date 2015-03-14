<?php
require_once('BaseModel.class.php');
//    Import( '@.Unit.Common' );
/**
 * BlogModel
 * 迷你日志Model层。操作相关迷你日志的数据业务逻辑
 * @package Model::Blog
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class BlogModel extends BaseModel {
/**
 * _type
 * 日志的种类。默认为0
 * @var float
 * @access public
 */
    public $_type = 0;
    public $cuid = 0;
    public $config = null;

    /**
     * limit
     * 每页显示多少条
     * @var float
     * @access public
     */

    public function _initialize() {
      //初始化只搜索status为0的。status字段代表没被删除的
      $this->status = 1;
      //获取配置
      $this->config = D('AppConfig','blog')->getConfig();
      parent::_initialize();
    }

    public function setCount($appid,$count) {
        $map['id'] = $appid;
        $map2['commentCount'] = $count;
        return $this->where($map)->save($map2);
    }
    /**
     * getBlogList
     * 通过userId获取到用户列表
     * 通过用户Id获取用户心情
     * @param array|string|int $userId
     * @param array|object $options 查询参数
     * @access public
     * @return object|array
     */
    public function getBlogList($map = null, $field=null, $order = null, $limit, $uid) {
      //处理where条件
      $map = $this->merge($map);
      $limit = !empty($limit) ? $limit : 20;
      // 连贯查询.获得数据集
      if(!empty($uid)) {
        $map['_string'] = ' private = 0 ';
        // 获取博主关注的UID
        $blogAuthorUids = model('Follow')->where('uid='.$uid)->field('fid')->findAll();
        $blogAuthorUids = getSubByKey($blogAuthorUids, 'fid');
        if(!empty($blogAuthorUids)) {
          $authorMap = implode(',', $blogAuthorUids);

          if(!empty($map['_string'])) {
            $map['_string'] .= ' OR (uid IN ('.$authorMap.') AND private = 2)';
          } else {
            $map['_string'] .= '(uid IN ('.$authorMap.') AND private = 2)';
          }
        }

        // 仅仅对自己可见
        if(!empty($map['_string'])) {
          $map['_string'] .= ' OR (uid = '.$uid.' AND private = 4)';
        } else {
          $map['_string'] .= ' (uid = '.$uid.' AND private = 4)';
        }
        // 仅对粉丝可见
        
        if(!empty($map['_string'])) {
          $map['_string'] .= ' OR uid='.$uid;
        } else {
          $map['_string'] .= ' uid='.$uid;
        }
      }
      
      $result = $this->where($map)->field($field)->order($order)->findPage($limit);
      //对数据集进行处理
      $data = $result['data'];
      $data = $this->replace($data); //本类重写父类的replace方法。替换日志的分类和追加日志的提及到的人
      $result['data'] = $data;

      return $result;
    }

    /**
     * getBlogContent
     * 重写父类的getBlogContent
     * @param mixed $id
     * @param mixed $how
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function getBlogContent( $id,$how =null,$uid = null  ) {
        $result         = parent::getBlogContent( $id,$how,$uid );
        if(false == $result) return false;
        $result['role']  = $this->checkCommentRole( $result['canableComment'],$uid,$this->cuid );
        $result['title'] = t( $result['title'] );
        $result['attach'] = unserialize($result['attach']);
        return $result;
    }

    public function setUid($value) {
        $this->cuid = $value;
    }
    /**
     * getMentionBlog
     * 获取提到我的好友的帖子数据
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function getMentionBlog( $uid = null ) {
        return false;

    }

    public function getCategory( $uid ) {
        $category        = self::factoryModel( 'Category' );
        if( isset( $uid ) ) {
            $categorycontent = $category->getUserCategory($uid);
        }else {
            $categorycontent = $category->getCategory();
        }
        return $categorycontent;
    }

    /**
     * checkCommentRole
     * 检查是否可以评论
     * @param mixed $role 评论权限
     * @param mixed $userId 日志所有者
     * @access protected
     * @return void
     */
    private function checkCommentRole( $role,$userId,$mid ) {
        if( $userId == $mid ) {
            return 1;
        }
        switch ( $role ) {
            case 1:  //全站可评论
                return 1;
            case 2:  //好友可评论
                if( $this->api->friend_areFriends($mid,$userId) ) {
                    return 1;
                }else {
                    return 2;
                }
            case 3:  //关闭评论
                return 3;
        }
    }
  public function getIsHot() {  //获取推荐日志...重复//TS_2.0
    //处理where条件
	    $map['isHot'] = 1;
	    $map['status']= 1;
	    $order        = 'rTime DESC';

	        //连贯查询.获得数据集

	    $hotlist = $this->where( $map )->order( $order )->findAll();
        //对数据集进行处理
        //$data           = $result['data'];
        //$data           = $this->replace( $data ); //本类重写父类的replace方法。替换日志的分类和追加日志的提及到的人
        //$data           = intval( $this->config->replay ) ? $this->appendReplay($data):$data;//追加回复
		//dump ($data);
        return $hotlist;
    }

    // 获取日志的数据
    public function getAllData($order, $uid) {
      //TODO 根据条件决定排序方式,尚有优化空间
      $temp_order_map = $this->getOrderMap($order);
      //根据以上处理条件获取数据集
      // $temp_order_map['map']['private'] = 0;
      $result = $this->getBlogList($temp_order_map['map'],null,$temp_order_map['order'], null, $uid);
      $result['category'] = $this->getCategory();
      return $result;
    }

    public function getFollowsBlog($mid){
      $followlist = model("Follow")->getFollowingListAll($mid,null);
  		foreach($followlist as $key=>$value) {
      	 $folist[$key]=$value['fid'];
      }
  		$map['uid']  = array('in',$folist);
  		// $map['private'] = 0;
  		$order = 'cTime DESC';
      $result = $this->getBlogList($map,null,$order,null,$mid);
      $result['category'] = $this->getCategory();
      return $result;
    }

    private function getOrderMap($order){
           switch( $order ) {
                case 'hot':    //推荐阅读
                    $map['isHot'] = 1;
                    $order        = 'rTime DESC';
                    break;
                case 'new':    //最新排行
                    $order = 'cTime DESC';
                    break;
                case 'popular':    //人气排行
                    // $order        = '((readCount/100)*(commentCount/100)*(cTime/3600)) DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    $order = 'readCount DESC, commentCount DESC , cTime DESC';
                    break;
                case 'read':   //阅读排行
                    $order = 'readCount DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    break;
                case 'comment':   //评论排行
                    $order = 'commentCount DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    break;

                default:      //默认时间排行
                    $order = 'cTime DESC';
            }
            // $map['private'] = array('neq',2);
            $result['map'] = $map;
            $result['order'] = $order;
            return $result;
    }
    /**
     * getBlogCategoryCount
     * 根据uid获得日志分类的日志数
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function getBlogCategoryCount( $uid ) {
        $sql = "SELECT count( 1 ) as count, category
                    FROM `{$this->tablePrefix}blog`
                    WHERE `category` IN (
                                          SELECT `id`
                                          FROM {$this->tablePrefix}blog_category
                                          WHERE `uid` = 0 OR `uid` = {$uid}
                                      ) AND `uid` = {$uid} AND `status` = 1
                                          GROUP BY category
            ";
        $result = $this->query( $sql );
        return $result;
    }
    public function getBlogCategory($uid,$cateId) {
        $list = $this->getCategory($uid);
        $result = $this->getBlogCount($uid,$list);
        if(isset( $cateId ) && !self::_checkCategory( $cateId,$list )) return false;
        return $result;
    }

    public function getBlogCount($uid,$list) {
        $result = $list;
        $count = $this->getBlogCategoryCount( $uid );
        //重组数据
        $count_arr = array();
        foreach ( $count as $value ) {
            $count_arr[$value['category']] = $value['count'];
        }
        foreach ($result as &$value) {
            $value['count'] = $count_arr[$value['id']] ? $count_arr[$value['id']] : 0;
        }
        return $result;
    }
    /**
     * _checkCategory
     * 检查分类是否合法
     * @param mixed $cateId
     * @param mixed $category
     * @static
     * @access private
     * @return void
     */
    private static function _checkCategory($cateId,$category ) {
        $temp = array();
        foreach( $category as $value ) {
            $temp[] = $value['id'];
        }
        return in_array($cateId,$temp);
    }

    /**
     * doDeleteBlog
     * 删除Mili日志，检查配置DELETE=true,则真实删除。如果DELETE=false，则是状态为1;
     * @param array|string $map 删除条件
     * @access public
     * @return void
     */
    public function doDeleteBlog( $map = null,$uid=null ) {
    //获得配置信息
        $config    = $this->config['delete'];

        //获得删除条件
        $condition = $this->merge( $map );

        //检测uid是否合法
        $mid = $this->where( $condition )->getField( 'uid' );
        //监测管理员
        $isAdmin = model('UserGroup')->isAdmin($uid);
        if( isset($uid) && $uid != $mid && !$isAdmin) {
            return false;
        }
        if(!isset($uid)){
        	$uid = $mid;
        }
        //判断是否合法。不允许删除整个表
        if( !isset( $condition ) && empty( $condition ) )
            throw new ThinkException( "不允许删除整个表" );
        //如果配置文件中delete的值为true则真是删除，如果delete=false,则设置status＝2;

        if( false == $config ) {
            $result = $this->where( $condition )->setField( 'status',2 );
            $count = $this->where( 'uid ='.$uid.' AND status <> 2' )->count();
        }else {
            $reuslt = $this->where( $condition )->delete();
            $count = $this->where( 'uid ='.$uid)->count();
        }
//        setScore($uid,'delete_blog');
//      //  修改空间中的计数
//      $this->api	=	new TS_API();
//     $this->opts = $this->api->option_get();
//      $result = $this->api->space_changeCount( 'blog',$count );

        return $result;
    }

    /**
     * changeCount
     * 修改日志的浏览数
     * @param mixed $blogid
     * @access public
     * @return void
     */
    public function changeCount( $blogid ) {
        $sql = "UPDATE {$this->tablePrefix}blog
                    SET readCount=readCount+1,hot = commentCount*readCount+round(readCount/(commentCount+1),0)
                    WHERE id='$blogid' LIMIT 1 ";
        $result = $this->execute($sql);
        if ( $result ) {
            return true;

        }
        return false;
    }

    /**
     * fileAway
     * 归档查询
     *
     * @param string|array $findTime 查询时间。
     * @param mixed $condition 查询条件
     * @param Model $object 查询对象
     * @param mixed $limit 查询limit
     * @access public
     * @return void
     */
    public function fileAway($findTime ,$condition = null) {
    //如果是数组。进行的解析不同
        if( is_array( $findTime) ) {
            $start_temp   = $this->paramData( strval($findTime[0] ));
            $end_temp     = $this->paramData( strval($findTime[1] ));

            $start        = $start_temp[0];
            $end          = $end_temp[1];
        }else {
            $findTime  = strval( $findTime );
            $paramTime = self::paramData( $findTime );
            $start     = $paramTime[0];
            $end       = $paramTime[1];
        }
        $this->cTime = array( 'between', array( $start,$end ) );
        //如果查询时没有设置其它查询条件，就只是按时间来进行归档查询
        $map = $this->merge( $condition );
        //查询条件赋值
        $result = $this->where( $map )->field( '*' )->order( 'cTime DESC' )->findPage( $this->config['limitpage']);
        $result['data'] = $this->replace( $result['data'] );//追加内容

        //追加用户名
        return $result;
    }
    /**
     * doAddBlog
     * 添加日志
     * @param mixed $map 日志内容
     * @param mixed $feed 是否发送动态
     * @access public
     * @return void
     */
    public function doAddBlog ($map,$import) {
    	$map['private']		= '0';
        $map['cTime']        = isset( $map['cTime'] )?$map['cTime']:time();
        $map['mTime']        =$map['cTime'];
        $map['type']  		 = isset( $map['type'])?$map['type']:$this->_type;
        $map['private_data'] = md5($map['password']);
        $map['category_title'] = M('blog_category')->where("`id`={$map['category']}")->getField('name');
        $content 			 = $map['content'];// 用于发通知截取
        $map['content'] 	 = t(h($map['content']));

        unset( $map['password'] );
        $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;//解析提到的好友
        unset( $map['mention'] );

        $map    = $this->merge( $map );
        $addId  = $this->add( $map );

        $temp = array_filter( $friendsId );
        //$appid = A('Index')->getAppId();
        //添加日志提到的好友
        if( !empty( $friendsId ) && !empty($temp) ) {
            $mention = self::factoryModel( 'mention' );
            $result  = $mention->addMention( $addId,$temp );
            for($i =0 ;$i<count($temp);$i++){
                  setScore($map['uid'], 'mention');
            }

            //发送通知给提到的好友

            $body['content']     = getBlogShort(t($content),40);
            $url                 = sprintf( "%s/Index/show/id/%s/mid/%s",'{'.$appid.'}',$addId,$map['uid'] );
            $title_data['title']   = sprintf("<a href='%s'>%s</a>",$url,$map['title']);
            $this->doNotify( $temp,"blog_mention",$title_data,$body,$url );
        }
        if( !$addId ) {
            return false;
        }
        //获得配置信息
        $config    = $this->config['delete'];
        if( $config ) {
        //修改空间中的计数
            $count = $this->where( 'uid ='.$map['uid'] )->count();
        }else {
        //修改空间中的计数
            $count = $this->where( 'uid ='.$map['uid'].' AND status <> 2' )->count();
        }
        //$this->api->space_changeCount( 'blog',$count );

        //发送动态
        if( $import ) {
        //$title['title']   = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",__APP__,$addId,$map['uid'],$map['title']);
            $title['title']   = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",'{SITE_URL}',$addId,$map['uid'],$map['title']);
            $title['title'] = stripslashes($title['title']);
            //setScore($map['uid'],'add_blog');
//            $body['content'] = getBlogShort($this->replaceSpecialChar(t($map['content'])),80);
            $body['content'] = $this->getBlogShort($this->replaceSpecialChar(t($map['content'])),80);
            $body['title'] = stripslashes($body['title']);
            //$this->doFeed("blog",$title,$body);
        }else {
            //setScore($map['uid'],'add_blog');
            $result['appid'] = $addId;
            $result['title'] = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",'{SITE_URL}',$addId,$map['uid'],$map['title']);
            return $result;
        }


        return $addId;
    }

function getBlogShort($content,$length = 60) {
	$content	=	real_strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

    public function doSaveBlog( $map,$blogid ) {
        $map['mTime'] = isset( $map['cTime'] )?$map['cTime']:time();
        $map['type']  = isset( $map['type'])?$map['type']:$this->_type;
        $map['category_title'] = M('blog_category')->where("`id`={$map['category']}")->getField('name');
        $map['private_data'] = md5($map['password']);
        // $map['content'] 	 = t(h($map['content']));

        unset( $map['password'] );
        //添加blog相关好友
        $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;
        unset( $map['mention'] );
        $map    = $this->merge( $map );

        if( !empty( $friendsId ) ) {
            $mention = self::factoryModel( 'mention' );
            $result  = $mention->updateMention( $blogid,$friendsId );
        }
        $addId  = $this->where( 'id = '.$blogid )->save( $map );

        if( !$result && !empty( $friendsId ) ) {
            return false;
        }

        return $addId;

    }

    /**
     * updateAuto
     * 更新日志的列表
     * @param mixed $map
     * @param mixed $id
     * @access public
     * @return void
     */
    public function updateAuto( $map,$id ) {
        $outline = self::factoryModel( 'outline' );
        return $outline->doUpdateOutline( $map,$id );

    }
    /**
     * autosave
     * 自动保存
     * @param mixed $map
     * @access public
     * @return void
     */
    public function autosave( $map ) {
        $outline = self::factoryModel( 'outline' );
        return $outline->doAddOutline( $map );
    }
    /**
     * getConfig
     * 获取配置
     * @param mixed $index
     * @access public
     * @return void
     */
    public function getConfig( $index ) {
        $config = $this->config[$index];
        return $config;
    }


    /**
     * unsetConfig
     * 删除配置
     * @param mixed $index
     * @param mixed $group
     * @access public
     * @return void
     */
    public function unsetConfig( $index , $group = null ) {
        if( isset( $group ) ) {
            unset( $this->config->$group->$index );
        }else {
            unset( $this->config->$index );
        }
        return $this;
    }

    /**
     * DateToTimeStemp
     * 时间换算成时间戳返回
     * @param mixed $stime
     * @param mixed $etime
     * @access public
     * @return void
     */
    public function DateToTimeStemp( $stime,$etime ) {
        $stime = strval( $stime );
        $etime = strval( $etime );

        //如果输入时间是YYMMDD格式。直接换算成时间戳
        if( isset( $stime[7] ) && isset( $etime[7] ) ) {
        //开始时间
            $syear  = substr( $stime,0,4 );
            $smonth = substr( $stime,4,2 );
            $sday   = substr( $stime,6,2 );
            $stime  = mktime( 0, 0, 0, $smonth,$sday,$syear );

            //结束时间
            $eyear  = substr( $etime,0,4 );
            $emonth = substr( $etime,4,2 );
            $eday   = substr( $etime,6,2 );
            $etime  = mktime( 0, 0, 0, $emonth,$eday,$eyear );

            return array( 'between',array( $stime,$etime ) );
        }

        //如果输入时间是YYYYMM格式
        $start_temp   = $this->paramData( $stime );
        $end_temp     = $this->paramData( $etime );
        $start        = $start_temp[0];
        $end          = $end_temp[1];

        return array( 'between',array( $start,$end ) );
    }

    public function getBlogTitle( $uid ) {
        $map['uid'] = $uid;
        $map = $this->merge( $map );
        return $this->where( $map )->field( 'title,id' )->order( 'cTime desc' )->limit( "0,10" )->findAll();
    }

    /**
     * checkGetSubscribe
     * 检查和返回以注册过的订阅源
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function checkGetSubscribe( $uid ) {
        $subscribe  = $this->factoryModel( 'subscribe' );
        $map['uid'] = $uid;
        $source_id  = $subscribe->getSourceId( $map );

        unset( $map );

        $source    = $this->factoryModel( 'source' );
        if( empty($source_id))
            return false;
        $map['id'] = array( 'in',$source_id );
        $result    = $source->getSource( $map );

        //重组数据,根据服务名和用户名重组链接
        foreach ( $result as &$value ) {
            switch( $value['service'] ) {
                case "163":
                    $link = "http://%s.blog.163.com/rss/";
                    break;
                case "sohu":
                    $link = "http://%s.blog.sohu.com/rss";
                    break;
                case "baidu":
                    $link = "http://hi.baidu.com/%s/rss/";
                    break;
                case "sina":
                    $link = "http://blog.sina.com.cn/rss/%s.xml";
                    break;
                case "msn":
                    $link = "http://%s.spaces.live.com/feed.rss";
                    break;
                default:
                    $link = $value['service'];
            //throw new ThinkException( "系统异常" );
            }
            $value['link'] = sprintf( $link,$value['username'] );
        //unset ( $value['service'] );
        //unset( $value['username'] );
        }
        return $result;
    }

    /**
     * doIsHot
     * 设置推荐
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsHot( $map,$act ) {
        if( empty($map) ) {
            throw new ThinkException( "不允许空条件操作数据库" );
        }
        switch( $act ) {
            case "recommend":   //推荐
                $field = array( 'isHot','rTime' );
                $val = array( 1,time() );
                $result = $this->setField( $field,$val,$map );
                break;
            case "cancel":   //取消推荐
                $field = array( 'isHot','rTime' );
                $val = array( 0,0 );
                $result = $this->setField( $field,$val,$map );
                break;

        }
        return $result;
    }

    /**
     * replace
     * 对数据集进行追加处理
     * @param array $data 数据集
     * @param array $mention 需要被追加的值
     * @access protected
     * @return void
     */
    protected function replace( $data,$mentiondata = null ) {
        $result         = $data;
        $categoryname   = $this->getCategory(null);  //获取所有的分类


        //TODO 配置信息,截取字数控制

        foreach ( $result as &$value ) {
            if(3 == $value['private']) {
               // if(Cookie::get($value['id'].'password') == $value['private_data']) {
               //     $value['private'] = 0;
               // }   Change
            }
            $value['content']  = str_replace( "&amp;nbsp;","",h($value['content']));
//            $value['category'] = array(
//                "name" => $categoryname[$value['category']]['name'],
//                "id"   => $value['category']); //替换日志类型

            //日志截断
            $short = $this->config->titleshort == 0 ? 4000: $this->config->titleshort;
            
            $suffix = (StrLenW( $value['content'] ) > $short) ? $this->config->suffix : '';
            $value['content'] = getBlogShort( $value['content'], $short ) . $suffix;

            //日志标题
            $value['title'] = stripslashes( $value['title'] );
        }
        return $result;
    }


    /**
     * changeType
     * 将数组中的数据转换成指定类型
     * @param mixed $data
     * @param mixed $type
     * @access private
     * @return void
     */
    private static function changeType( $data , $type ) {
        $result = $data;

        switch( $type ) {
            case 'int':
                $method = "intval";
                break;
            case 'string':
                $method = "strtval";
                break;
            default:
                throw new ThinkException( '暂时只能转换数组和字符串类型' );
        }
        foreach ( $result as &$value ) {
            is_numeric( $value ) && $value = $method( $value );
        }
        return $result;
    }


    private function replaceSpecialChar($code) {
        $code = str_replace("&amp;nbsp;", "", $code);

        $code = str_replace("<br>", "", $code);

        $code = str_replace("<br />", "", $code);

        $code = str_replace("<P>",  "", $code);

        $code = str_replace("</P>","",$code);

        return trim($code);
    }
    /**
     * _orderDate
     * 解析日志排序时间区段
     * @param mixed $options
     * @access private
     * @return void
     */
    private function _orderDate( $options ) {
        if('all' == $options) return array('lt',time());
        $now_year  = intval( date( 'Y',time() ) );
        $now_month = intval( date( 'n',time() ) );
        $now_day   = intval( date( 'j',time() ) );

        //定义偏移量
        $month = self::_getExcursion($options, 'month');
        $year = self::_getExcursion($options, 'year');
        $day = self::_getExcursion($options, 'day');

        //换算时间戳
        $toDate = mktime( 0,0,0,$now_month-$month,$now_day-$day,$now_year-$year );
        //返回数组型数据集
        return array( "between",array( $toDate,time() ) );
    }
    private static function _getExcursion($options,$field){
        $excursion = array(
                            'one'   => array('month'=>1),
                           'three' => array('month'=>3),
                           'half'  => array('month'=>6),
                           'year'  => array('year'=>1),
                           'oneDay'=> array('day'=>1),
                           'threeDay'=>array('day'=>3),
                           'oneWeek'=>array('day'=>7),
                           );
        return isset($excursion[$options][$field])?$excursion[$options][$field]:0;
    }

        /**
         * addRecommendUser 
         * 添加日志推荐
         * @param mixed $map 
         * @param mixed $action 
         * @param mixed $obj 
         * @access public
         * @return void
         */
        public function addRecommendUser( $map,$action ){
            //推荐
            if( 'recommend' == $action  ){
                $this->add( $map );
                $sql = "UPDATE {$this->tablePrefix}blog
                        SET recommendCount = recommendCount + 1
                        WHERE `id` = {$map['blogid']}
                    ";
            //取消推荐
            }else{
                $this->where($map)->delete( );
                $sql = "UPDATE {$this->tablePrefix}blog
                        SET recommendCount = recommendCount - 1
                        WHERE `id` = {$map['blogid']}
                    ";
            }
            $result = $this->execute( $sql ) ;
            return $result;

        }
        
    public function checkRecommend( $uid,$blogid ){
      $map['uid']    = $uid;
      $map['type']   = 'recommend';
      $map['blogid'] = $blogid;
      return $this->where( $map )->find();
    }
    // 获取归档数据
    public function getDataByDate($date,$uid,$limit){
      $date = $date;
      $year = substr($date,0,4);
      $month = substr($date,-2);
      $month1 = ($month+1)%12;
      if(!$month1){ $month1 = 12;}
      if($month == 12){ $year = $year + 1; }
      $sTime = mktime(0,0,0,$month,1,$year);
      $eTime = mktime(0,0,0,$month1,1,$year);
      $map['cTime'] = array('between',array($sTime,$eTime));
      $map['uid'] = $uid;
      $limit = !empty($limit) ? $limit : 20;
      $list = $this->where($map)->order('cTime DESC')->findPage($limit);
      return $list;
    }
}
