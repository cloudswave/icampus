<?php
/**
 * 微博模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class FeedModel extends Model {

	protected $tableName = 'feed';
	protected $fields = array('feed_id','uid','type','app','app_row_id','app_row_table','publish_time','is_del','from','comment_count','repost_count','comment_all_count','digg_count','is_repost','is_audit','_pk'=>'feed_id');

	public $templateFile = '';			// 模板文件

	/**
	 * 添加微博
	 * @param integer $uid 操作用户ID
	 * @param string $app 微博应用类型，默认为public
	 * @param string $type 微博类型，
	 * @param array $data 微博相关数据
	 * @param integer $app_id 应用资源ID，默认为0
	 * @param string $app_table 应用资源表名，默认为feed
	 * @param array  $extUid 额外用户ID，默认为null
	 * @param array $lessUids 去除的用户ID，默认为null
	 * @param boolean $isAtMe 是否为进行发送，默认为true
	 * @return mix 添加失败返回false，成功返回新的微博ID
	 */
	public function put($uid, $app = 'public', $type = '', $data = array(), $app_id = 0, $app_table = 'feed', $extUid = null, $lessUids = null, $isAtMe = true, $is_repost = 0) {
		if(isSubmitLocked()){
			$this->error = '发布内容过于频繁，请稍后再试';
			return false;
		}
		// 判断数据的正确性
		if(!$uid || $type == '') {
			$this->error = L('PUBLIC_ADMIN_OPRETING_ERROR');
			return false;
		}
		if ( strpos( $type , 'postvideo' ) !== false ){
			$type = 'postvideo';
		}
		//微博类型合法性验证 - 临时解决方案
		if ( !in_array( $type , array('post','repost','postvideo','postfile','postimage','weiba_post','weiba_repost') )){
			$type = 'post';
		}
		//应用类型验证 用于分享框 - 临时解决方案
		if ( !in_array( $app , array('w3g','public','weiba','tipoff') ) ){
			$app = 'public';
			$type = 'post';
			$app_table = 'feed';
		}
		
		$app_table = strtolower($app_table);
		// 添加feed表记录
		$data['uid'] = $uid;
		$data['app'] = $app;
		$data['type'] = $type;
		$data['app_row_id'] = $app_id;
		$data['app_row_table'] = $app_table;
		$data['publish_time'] = time();
		$data['from'] = isset($data['from']) ? intval($data['from']) : getVisitorClient();
		$data['is_del'] = $data['comment_count'] = $data['repost_count'] = 0;
		$data['is_repost'] = $is_repost;
		//判断是否先审后发
		$weiboSet = model('Xdata')->get('admin_Config:feed');
        $weibo_premission = $weiboSet['weibo_premission'];
		if(in_array('audit',$weibo_premission) || CheckPermission('core_normal','feed_audit')){
			$data['is_audit'] = 0;
		}else{
			$data['is_audit'] = 1;
		}
		// 微博内容处理
        if(Addons::requireHooks('weibo_publish_content')){
        	Addons::hook("weibo_publish_content",array(&$data));
        }else{
        	$content = $this->formatFeedContent($data['body']);
        	$data['body'] = $content['body'];
        	$data['content'] = $content['content'];
        }

        //分享到微博的应用资源，加入原资源链接
        $data['body'] .= $data['source_url'];
        $data['content'] .= $data['source_url'];
		
        // 微博类型插件钩子
		// if($type){
		// 	$addonsData = array();
		// 	Addons::hook("weibo_type",array("typeId"=>$type,"typeData"=>$type_data,"result"=>&$addonsData));
		// 	$data = array_merge($data,$addonsData);
		// }
        if( $type == 'postvideo' ){
        	$typedata = model('Video')->_weiboTypePublish( $_POST['videourl'] );
        	if ( $typedata && $typedata['flashvar'] && $typedata['flashimg'] ){
        		$data = array_merge( $data , $typedata );
        	} else {
        		$data['type'] = 'post';
        	}
        		
        }	
		// 添加微博信息
		$feed_id =  $this->data($data)->add();
		if(!$feed_id) return false;
		if(!$data['is_audit']){
			$touid = D('user_group_link')->where('user_group_id=1')->field('uid')->findAll();
			foreach($touid as $k=>$v){
				model('Notify')->sendNotify($v['uid'], 'feed_audit');
			}
		}
		// 目前处理方案格式化数据
		$data['content'] = str_replace(chr(31), '', $data['content']);
		$data['body'] = str_replace(chr(31), '', $data['body']);
		// 添加关联数据
		$feed_data = D('FeedData')->data(array('feed_id'=>$feed_id,'feed_data'=>serialize($data),'client_ip'=>get_client_ip(),'feed_content'=>$data['body']))->add();

		// 添加微博成功后
		if($feed_id && $feed_data) {
			//锁定发布
			lockSubmit();
			//微博发布成功后的钩子
			//Addons::hook("weibo_publish_after",array('weibo_id'=>$feed_id,'post'=>$data));

			// 发送通知消息 - 重点 - 需要简化把上节点的信息去掉.
			if($data['is_repost'] == 1) {
				// 转发微博
				$isAtMe && $content = $data['content'];									// 内容用户
				$extUid[] = $data['sourceInfo']['transpond_data']['uid'];				// 资源作者用户
				if($isAtMe && !empty($data['curid'])) {
					// 上节点用户
					$appRowData = $this->get($data['curid']);
					$extUid[] = $appRowData['uid'];
				}	
			} else {
				// 其他微博
				$content = $data['content'];
				//更新最近@的人
				model( 'Atme' )->updateRecentAt( $content );								// 内容用户
			}
			// 发送@消息
			model('Atme')->setAppName('Public')->setAppTable('feed')->addAtme($content, $feed_id, $extUid, $lessUids);

			$data['client_ip'] = get_client_ip();
			$data['feed_id'] = $feed_id;
			$data['feed_data'] = serialize($data);
			// 主动创建渲染后的缓存
			$return = $this->setFeedCache($data);
			$return['user_info'] = model('User')->getUserInfo($uid);
			$return['GroupData'] = model('UserGroupLink')->getUserGroupData($uid);   //获取用户组信息
			$return['feed_id'] = $feed_id;
			$return['app_row_id'] = $data['app_row_id'];
			$return['is_audit'] = $data['is_audit'];
			// 统计数修改
			model('UserData')->setUid($uid)->updateKey('feed_count', 1);
			// if($app =='public'){ //TODO 微博验证条件
				model('UserData')->setUid($uid)->updateKey('weibo_count', 1);
			// }
			if(!$return) {
				$this->error = L('PUBLIC_CACHE_FAIL');				// Feed缓存写入失败
			}
			return $return;
		} else {
			$this->error = L('PUBLIC_ADMIN_OPRETING_ERROR');		// 操作失败
			return false;
		}
	}

	/**
	 * 截取微博内容，将微博中的URL替换成{ts_urlX}进行字符数目统计
	 * @param string $content 微博内容
	 * @param string $weiboNums 微博截取数目，默认为0
	 * @return array 格式化后的微博内容，body与content
	 */
	public function formatFeedContent ($content, $weiboNums = 0) {
    	// 拼装数据，如果是评论再转发、回复评论等情况，需要额外叠加对话数据
		$content = str_replace(SITE_URL, '[SITE_URL]', preg_html($content));
		// 格式化微博信息 - URL
		$content = preg_replace_callback('/((?:https?|mailto|ftp):\/\/([^\x{2e80}-\x{9fff}\s<\'\"“”‘’，。}]*)?)/u', '_format_feed_content_url_length', $content);
		$replaceHash = $GLOBALS['replaceHash'];
		unset($GLOBALS['replaceHash']);
		// 获取用户发送的内容，仅仅以//进行分割
		$scream = explode('//', $content);
		// 截取内容信息为微博内容字数 - 重点
		$feedNums = 0;
		if (empty($weiboNums)) {
			$feedConf = model('Xdata')->get('admin_Config:feed');
			$feedNums = $feedConf['weibo_nums'];
		} else {
			$feedNums = $weiboNums;
		}
		$body = array();
		// 还原URL操作
		$patterns = array_keys($replaceHash);
		$replacements = array_values($replaceHash);
		foreach($scream as $value) {
			$tbody[] = $value;
			$bodyStr = implode('//', $tbody);
			if(get_str_length(ltrim($bodyStr)) > $feedNums) {
				break;
			}
			$body[] = str_replace($patterns, $replacements, $value);
			unset($bodyStr);
		}
		$data['body'] = implode('//', $body);
		// 获取用户发布内容
		$scream[0] = str_replace($patterns, $replacements, $scream[0]);
		$data['content'] = trim($scream[0]);

		return $data;
	}

	/**
	 * 获取指定微博的信息
	 * @param integer $feed_id 微博ID
	 * @return mix 获取失败返回false，成功返回微博信息
	 */
	public function get($feed_id) {
		$feed_list = $this->getFeeds(array($feed_id));
		if(!$feed_list) {
			$this->error = L('PUBLIC_INFO_GET_FAIL');			// 获取信息失败
			return false;
		} else {
			return $feed_list[0];
		}
	}

	/**
	 * 获取指定微博的信息，用于资源模型输出???
	 * @param integer $id 微博ID
	 * @param boolean $forApi 是否提供API数据，默认为false
	 * @return array 指定微博数据
	 */
	public function getFeedInfo($id, $forApi = false) {
		$data = model( 'Cache' )->get( 'feed_info_'.$id );
		if ( $data !== false && ($forApi === false || ($forApi === true && isset($data['iscoll'])) ) ){
			return $data;
		}

		$map['a.feed_id'] = $id;
		
		// //过滤已删除的微博 wap 版收藏
		// if($forApi){
		// 	$map['a.is_del'] = 0;
		// }
		
		$data = $this->where($map)
					 ->table("{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id ")
					 ->find();

		$fd = unserialize($data['feed_data']);	

		$userInfo = model('User')->getUserInfo($data['uid']);
		$data['ctime'] = date('Y-m-d H:i',$data['publish_time']);
		$data['content'] = $forApi ? parseForApi($fd['body']):$fd['body'];
		$data['uname'] = $userInfo['uname'];
		$data['user_group'] = $userInfo['api_user_group'];
		$data['avatar_big'] = $userInfo['avatar_big'];
		$data['avatar_middle'] = $userInfo['avatar_middle'];
		$data['avatar_small']  = $userInfo['avatar_small'];
		unset($data['feed_data']);
		
		// 微博转发
		if($data['type'] == 'repost'){
			$data['transpond_id'] = $data['app_row_id'];
			$data['transpond_data'] = $this->getFeedInfo($data['transpond_id'], $forApi);
		}

		// 附件处理
		if(!empty($fd['attach_id'])) {
			$data['has_attach'] = 1;
			$attach = model('Attach')->getAttachByIds($fd['attach_id']);
			foreach($attach as $ak => $av) {
				$_attach = array(
							'attach_id'   => $av['attach_id'],
							'attach_name' => $av['name'],
							'attach_url'  => getImageUrl($av['save_path'].$av['save_name']),
							'extension'   => $av['extension'],
							'size'		  => $av['size']
						);
				if($data['type'] == 'postimage') {
					$_attach['attach_small'] = getImageUrl($av['save_path'].$av['save_name'], 100, 100, true);
 					$_attach['attach_middle'] = getImageUrl($av['save_path'].$av['save_name'], 550);
				}
				$data['attach'][] = $_attach;
			}
		} else {
			$data['has_attach'] = 0;
		}

		if( $data['type'] == 'postvideo' ){
			$data['host'] = $fd['host'];
			$data['flashvar'] = $fd['flashvar'];
			$data['source'] = $fd['source'];
			$data['flashimg'] = $fd['flashimg'];
			$data['title'] = $fd['title'];
		}

		$data['feedType'] = $data['type'];
		
		// 是否收藏微博
		if($forApi) {
			$data['iscoll'] = model('Collection')->getCollection($data['feed_id'],'feed');
			if(empty($data['iscoll'])) {
				$data['iscoll']['colled'] = 0;
			} else {
				$data['iscoll']['colled'] = 1;
			}
		}

		// 微博详细信息
		$feedInfo = $this->get($id);
		$data['source_body'] = $feedInfo['body'];
		$data['api_source'] = $feedInfo['api_source'];
		//一分钟缓存
		model( 'Cache' )->set( 'feed_info_'.$id , $data , 60);
		if($forApi){
			$data['content'] = real_strip_tags($data['content']);
			unset($data['is_audit'],$data['from_data'],$data['app_row_table'],$data['app_row_id']);
			unset($data['source_body']);
		}

		//dump($data);
		return $data;			  	
	}

	/**
	 * 获取微博列表
	 * @param array $map 查询条件
	 * @param integer $limit 结果集数目，默认为10
	 * @param string $order 排序字段
	 * @return array 微博列表数据
	 */
	public function getList($map, $limit = 10 , $order = null, $max=null) {
		$order = !empty($order)?$order:'feed_id DESC';
		$feedlist = $this->field('feed_id')->where($map)->order($order);
		if($max>0){
			$feedlist = $this->findPage($limit,$max);
		}else{
			$feedlist = $this->findPage($limit);
		} 
		$feed_ids = getSubByKey($feedlist['data'], 'feed_id');
		$feedlist['data'] = $this->getFeeds($feed_ids);
		return $feedlist;
	}

	/**
	 * 获取指定用户所关注人的所有微博，默认为当前登录用户
	 * @param string $where 查询条件
	 * @param integer $limit 结果集数目，默认为10
	 * @param integer $uid 指定用户ID，默认为空
	 * @param integer $fgid 关组组ID，默认为空
	 * @return array 指定用户所关注人的所有微博，默认为当前登录用户
	 */
	public function getFollowingFeed($where = '', $limit = 10, $uid = '', $fgid = '', $max = null ) {
		$fgid = intval($fgid);
		$uid = intval($uid);
		$buid = empty($uid) ? $_SESSION['mid'] : $uid;
		$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}user_follow AS b ON a.uid=b.fid AND b.uid = {$buid}";
		// 加上自己的信息，若不需要屏蔽下语句
		$_where = !empty($where) ? "(a.uid = '{$buid}' OR b.uid = '{$buid}') AND ($where)" : "(a.uid = '{$buid}' OR b.uid = '{$buid}')";
		// 若填写了关注分组
		if(!empty($fgid)) {
			$table .=" LEFT JOIN {$this->tablePrefix}user_follow_group_link AS c ON a.uid = c.fid AND c.uid ='{$buid}' ";
			$_where .= " AND c.follow_group_id = ".intval($fgid);
		}
		$feedlist = $this->table($table)->where($_where)->field('a.feed_id')->order('a.feed_id DESC');
		//2013-10-01 为了提高效率增加一项改进，可以设置查看的微博总数，默认10000条
		if($max > 0 ){
			$feedlist = $this->findPage($limit,$max);
		}else{
			$feedlist = $this->findPage($limit);
		}
		$feed_ids = getSubByKey($feedlist['data'], 'feed_id');
		$feedlist['data'] = $this->getFeeds($feed_ids);

		return $feedlist;
	}

	/**
	 * 获取指定用户收藏的微博列表，默认为当前登录用户
	 * @param array $map 查询条件
	 * @param integer $limit 结果集数目，默认为10
	 * @param integer $uid 指定用户ID，默认为空
	 * @return array 指定用户收藏的微博列表，默认为当前登录用户
	 */
	public function getCollectionFeed($map, $limit = 10, $uid = '') {
		$map['uid'] = empty($uid) ? $_SESSION['mid'] : $uid;
		$map['source_table_name'] = 'feed';
		$table = "{$this->tablePrefix}collection";
		$feedlist = $this->table($table)->where($map)->field('source_id AS feed_id')->order('source_id DESC')->findPage($limit);
		$feed_ids = getSubByKey($feedlist['data'],'feed_id');
		$feedlist['data'] = $this->getFeeds($feed_ids);

		return $feedlist;
	}

	/**
	 * 获取指定用户所关注人的微博列表
	 * @param array $map 查询条件
	 * @param integer $uid 用户ID
	 * @param string $app 应用名称
	 * @param integer $type 应用类型
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户所关注人的微博列表
	 */
	public function getFollowingList($map,$uid, $app, $type, $limit = 10) {
		// 读取列表
		$map['_string'] = "uid IN (SELECT fid FROM {$this->tablePrefix}user_follow WHERE uid={$uid}) OR uid={$uid}";
		!empty($app) && $map['app'] = $app;
		!empty($type) && $map['type'] = $type;
		if ( $map['type'] == 'post' ){
			unset($map['type']);
			$map['is_repost'] = 0;
		}
		$feedlist = $this->field('feed_id')->where($map)->order("publish_time DESC")->findPage($limit);
		if(!$feedlist) {
			$this->error = L('PUBLIC_INFO_GET_FAIL');			// 获取信息失败
			return false;
		}
		$feed_ids = getSubByKey($feedlist['data'], 'feed_id');
		$feedlist['data'] = $this->getFeeds($feed_ids);

		return $feedlist;
	}

	/**
	 * 查看指定用户的微博列表
	 * @param array $map 查询条件
	 * @param integer $uid 用户ID
	 * @param string $app 应用类型
	 * @param string $type 微博类型
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户的微博列表数据
	 */
	public function getUserList($map, $uid, $app, $type, $limit = 10) {
		if(!$uid) {
			$this->error = L('PUBLIC_WRONG_DATA');				// 获取信息失败
			return false;
		}
		!empty($app) && $map['app'] = $app;
		!empty($type) && $map['type'] = $type;
		if( $map['type'] == 'post' ){
			unset($map['type']);
			$map['is_repost'] = 0;
		}
		$map['uid'] = $uid;
		$list = $this->getList($map, $limit); 

		return $list;
	}

	/**
	 * 获取指定用户的最后一条微博数据
	 * @param array $uids 用户ID
	 * @return array 指定用户的最后一条微博数据
	 */
	public function  getLastFeed($uids) {
		if(empty($uids)) {
			return false;
		}

		!is_array($uids) && $uids = explode(',', $uids);
		$map['uid'] = array('IN', $uids);
		$map['is_del'] = 0;
		$feeds = $this->where($map)->field('MAX(feed_id) AS feed_id,uid')->group('uid')->getAsFieldArray('feed_id');
		$feedlist = $this->getFeeds($feeds);
		$r = array();
		foreach($feedlist as $v) {
			if(!empty($v['sourceInfo'])) {
				$r[$v['uid']] = array('feed_id'=>$v['feed_id'],'title'=>getShort(t($v['sourceInfo']['shareHtml']), 30, '...'));
			} else {
				$t = unserialize($v['feed_data']);
				$r[$v['uid']] = array('feed_id'=>$v['feed_id'],'title'=>getShort(t($t['body']), 30, '...'));
			}
		}

		return $r;
	}

	/**
	 * 获取给定微博ID的微博信息
	 * @param array $feed_ids 微博ID数组
	 * @return array 给定微博ID的微博信息
	 */
	public function getFeeds($feed_ids) {
		$feedlist = array();
		$feed_ids = array_filter(array_unique($feed_ids));
		
		// 获取数据
		if(count($feed_ids) > 0) {
			$cacheList = model('Cache')->getList('fd_', $feed_ids);
		} else {
			return false;
		}

		// 按照传入ID顺序进行排序
		foreach($feed_ids as $key => $v) {
			if($cacheList[$v]) {
				$feedlist[$key] = $cacheList[$v]; 
			} else {
				$feed = $this->setFeedCache(array(), $v);
				$feedlist[$key] = $feed[$v];
			}
		}

		//dump($feedlist);
		return $feedlist;
	}

	/**
	 * 清除指定用户指定微博的列表缓存
	 * @param array $feed_ids 微博ID数组，默认为空
	 * @param integer $uid 用户ID，默认为空
	 * @return void
	 */
	public function cleanCache($feed_ids = array(), $uid = '') {
		if(!empty($uid)) {
			model('Cache')->rm('fd_foli_'.$uid);
			model('Cache')->rm('fd_uli_'.$uid);
		}
		if(empty($feed_ids)) {
			return true;
		}
		if(is_array($feed_ids)) {
			foreach($feed_ids as $v) {
				model('Cache')->rm('fd_'.$v);
				model('Cache')->rm('feed_info_'.$v);
			}
		} else {
			model('Cache')->rm('fd_'.$feed_ids);
			model('Cache')->rm('feed_info_'.$feed_ids);
		}
	}

	/**
	 * 更新指定微博的缓存
	 * @param array $feed_ids 微博ID数组，默认为空
	 * @param string $type 操作类型，默认为update
	 * @return bool true
	 */
	public function updateFeedCache($feed_ids, $type = 'update') {
		if($type == 'update') {
			$this->getFeeds($feed_ids);
		} else {
			foreach($feed_ids as $v) {
				model('Cache')->rm('fd_'.$v);
			}
		}

		return true;
	}

	/**
	 * 生成指定微博的缓存
	 * @param array $value 微博相关数据
	 * @param array $feed_id 微博ID数组
	 */
	private function setFeedCache($value = array(), $feed_id = array()) {
		if(!empty($feed_id)) {
			!is_array($feed_id) && $feed_id = explode(',', $feed_id);
			$map['a.feed_id'] = array('IN', $feed_id);
			$list = $this->where($map)
						 ->field('a.*,b.client_ip,b.feed_data')
						 ->table("{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id")
						 ->findAll();

			$r = array();
			foreach($list as &$v) {
				// 格式化数据模板
				$parseData = $this->__paseTemplate($v);
				$v['info'] = $parseData['info'];
				$v['title'] = $parseData['title'];
				$v['body'] = $parseData['body'];
				$v['api_source'] = $parseData['api_source'];
				$v['actions'] = $parseData['actions'];
				$v['user_info'] = $parseData['userInfo'];
				$v['GroupData'] = model('UserGroupLink')->getUserGroupData($v['uid']);
 				model('Cache')->set('fd_'.$v['feed_id'], $v);			// 1分钟缓存
				$r[$v['feed_id']] = $v;
			}

			return $r;
		} else {
			// 格式化数据模板
			$parseData = $this->__paseTemplate($value);
			$value['info'] = $parseData['info'];
			$value['title'] = $parseData['title'];
			$value['body'] = $parseData['body'];
			$value['api_source'] = $parseData['api_source'];
			$value['actions'] = $parseData['actions'];
			$value['user_info'] = $parseData['userInfo'];
			$value['GroupData'] = model('UserGroupLink')->getUserGroupData($value['uid']);
 			model('Cache')->set('fd_'.$value['feed_id'], $value);		// 1分钟缓存
			return $value;
		}
	}

	/**
	 * 解析微博模板标签
	 * @param array $_data 微博的原始数据
	 * @return array 解析微博模板后的微博数据
	 */
	private function __paseTemplate($_data) {
		// 获取作者信息
		$user = model('User')->getUserInfo($_data['uid']);
		// 处理数据
		$_data['data'] = unserialize($_data['feed_data']);
		// 模版变量赋值
		$var = $_data['data'];
		if(!empty($var['attach_id'])) {
			$var['attachInfo'] = model('Attach')->getAttachByIds($var['attach_id']);
			foreach($var['attachInfo'] as $ak => $av) {
				$_attach = array(
							'attach_id'   => $av['attach_id'],
							'attach_name' => $av['name'],
							'attach_url'  => getImageUrl($av['save_path'].$av['save_name']),
							'extension'   => $av['extension'],
							'size'		  => $av['size']
						);
				if($_data['type'] == 'postimage') {
					$_attach['attach_small'] = getImageUrl($av['save_path'].$av['save_name'], 100, 100, true);
 					$_attach['attach_middle'] = getImageUrl($av['save_path'].$av['save_name'], 550);
				}
				$var['attachInfo'][$ak] = $_attach;
			}
		}
		if ( $_data['type'] == 'postvideo' && !$var['flashimg']){
			$var['flashimg'] = '__THEME__/image/video.png';
		}
		$var['uid'] = $_data['uid'];
		$var["actor"] = "<a href='{$user['space_url']}' class='name' event-node='face_card' uid='{$user['uid']}'>{$user['uname']}</a>";
		$var["actor_uid"] = $user['uid'];
		$var["actor_uname"]	= $user['uname'];
		$var['feedid'] = $_data['feed_id'];   
		//微吧类型微博用到
		// $var["actor_groupData"] = model('UserGroupLink')->getUserGroupData($user['uid']);
		//需要获取资源信息的微博：所有类型的微博，只要有资源信息就获取资源信息并赋值模版变量，交给模版解析处理
		if(!empty($_data['app_row_id'])) {
			empty($_data['app_row_table']) && $_data['app_row_table'] = 'feed';
			$var['sourceInfo'] = model('Source')->getSourceInfo($_data['app_row_table'], $_data['app_row_id'], false, $_data['app']);
			$var['sourceInfo']['groupData'] = model('UserGroupLink')->getUserGroupData($var['sourceInfo']['source_user_info']['uid']);
		}
		// 解析Feed模版
		$feed_template_file = APPS_PATH.'/'.$_data['app'].'/Conf/'.$_data['type'].'.feed.php';
		if(!file_exists($feed_template_file)){
			$feed_template_file = APPS_PATH.'/public/Conf/post.feed.php';
		}
		$feed_xml_content = fetch($feed_template_file, $var);
		$s = simplexml_load_string($feed_xml_content);
		if(!$s){
			return false;
		}
		$result = $s->xpath("//feed[@type='".t($_data['type'])."']");
		$actions = (array) $result[0]->feedAttr;
		//输出模版解析后信息
		$return["userInfo"]  = $user;
		$return["actor_groupData"] = $var["actor_groupData"];
		$return['title'] = trim((string) $result[0]->title);
	    $return['body'] =  trim((string) $result[0]->body);
		// $return['sbody'] = trim((string) $result[0]->sbody);
	    $return['info'] =  trim((string) $result[0]['info']);
	    //$return['title'] =  parse_html($return['title']); 
	    $return['body']  =  parse_html($return['body']);
	    $return['api_source'] = $var['sourceInfo'];
		// $return['sbody'] =  parse_html($return['sbody']); 
	    $return['actions'] = $actions['@attributes'];
	    //验证转发的原信息是否存在
	    if(!$this->_notDel($_data['app'],$_data['type'],$_data['app_row_id'])) {
	    	$return['body'] = L('PUBLIC_INFO_ALREADY_DELETE_TIPS');				// 此信息已被删除〜
	    }
		return $return;
	}

	/**
	 * 判断资源是否已被删除
	 * @param string $app 应用名称
	 * @param string $feedtype 动态类型
	 * @param integer $app_row_id 资源ID
	 * @return boolean 资源是否存在
	 */
	private function _notDel($app, $feedtype, $app_row_id) {
		// TODO:该方法为完成？
		// 非转发的内容，不需要验证
		if(empty($app_row_id)){
			return true;
		}
		return true;
	}

	/**
	 * 获取所有微博节点列表 - 预留后台查看、编辑微博模板文件
	 * @param boolean $ignore 从微博设置里面获取，默认为false
	 * @return array 所有微博节点列表
	 */
	public function getNodeList($ignore = false) {
		if(false===($feedNodeList=S('FeedNodeList'))){
			//应用列表
			$apps = C('DEFAULT_APPS');
			$appList = model('App')->getAppList();
			foreach($appList as $app){
				$apps[] = $app['app_name'];
			}
	  		//获得所有feed配置文件
	        require_once ADDON_PATH.'/library/io/Dir.class.php';
	        $dirs = new Dir(SITE_PATH,'*.feed.php');
	        foreach($apps as $app){
	        	$app_config_path = SITE_PATH.'/apps/'.$app.'/Conf/';
	        	$dirs->listFile($app_config_path,'*.feed.php');
	        	$files = $dirs->toArray();        	
	        	if(is_array($files) && count($files)>0){
	        		foreach($files as $file){
	        			$feed_file['app'] = $app;
	        			$feed_file['filename']= $file['filename'];
	        			$feed_file['pathname']= $file['pathname'];
	        			$feed_file['mtime']= $file['mtime'];
	        			$feedNodeList[] = $feed_file;
	        		}
	        	}
	        }
	        S('FeedNodeList',$feedNodeList);
        }
        return $feedNodeList;
        // $xml = simplexml_load_file( $this->_getFeedXml() );
		// $feed = $xml->feedlist->feed;
		// $list = array();
		// foreach($feed as $key => $v) {
		// 	$app = (string)$v['app'];
		// 	$type = (string)$v['type'];
		// 	$list[$app][] = array(
		// 		'app'=>$app,
		// 		'type'=>$type,
		// 		'info'=>(string)$v['info']
		// 	);
		// }
		// return $list;
	}

	/**
	 * 获取微博模板的XML文件路径
	 * @param boolean $set 是否重新生成微博模板XML文件
	 * @return string 微博模板的XML文件路径
	 */
	public function _getFeedXml($set = false) {
		if($set || !file_exists(SITE_PATH.'/config/feeds.xml')) {
			$data = D('feed_node')->findAll();
			$xml = "<?xml version='1.0' encoding='UTF-8'?>
					<root>
					<feedlist>";
			foreach($data as $v) {
				$xml.="
				<feed app='{$v['appname']}' type='{$v['nodetype']}' info='{$v['nodeinfo']}'>
				".htmlspecialchars_decode($v['xml'])."
				</feed>";
			}
			$xml .= "</feedlist>
					</root>";
				
			file_put_contents(SITE_PATH.'/config/feeds.xml', $xml);
			chmod(SITE_PATH.'/config/feeds.xml', 0666);
		}
		
		return SITE_PATH.'/config/feeds.xml';
	}

	/**
	 * 微博操作，彻底删除、假删除、回复
	 * @param integer $feed_id 微博ID
	 * @param string $type 微博操作类型，deleteFeed：彻底删除，delFeed：假删除，feedRecover：恢复
	 * @param string $title 日志内容，目前没没有该功能
	 * @param string $uid 删除微博的用户ID（区别超级管理员）
	 * @return array 微博操作后的结果信息数组
	 */
	public function doEditFeed($feed_id, $type, $title ,$uid = null) {
		$return = array('status'=>'0');
		if(empty($feed_id)) {
			//$return['data'] = '微博ID不能为空！';
		} else {
			$map['feed_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
			$save['is_del'] = $type =='delFeed' ? 1 : 0;

			if($type == 'deleteFeed') {
				$feedArr = is_array($feed_id) ? $feed_id : explode(',', $feed_id);
				// 取消微博收藏
				foreach ($feedArr as $sid) {
					$feed = $this->where('feed_id='.$sid)->find();
					model('Collection')->delCollection($sid, 'feed', $feed['uid']);
				}
				// 彻底删除微博
				$res = $this->where($map)->delete();
				// 删除微博相关信息
				if($res) {
					$this->_deleteFeedAttach($feed_id, 'deleteAttach');
				}
			} else {
				$ids = !is_array($feed_id) ? array($feed_id) : $feed_id;
				$feedList = $this->getFeeds($ids);
				$res = $this->where($map)->save($save);
				if($type == 'feedRecover'){
					// 添加微博数
					foreach($feedList as $v) {
						model('UserData')->setUid($v['user_info']['uid'])->updateKey('feed_count', 1);
						model('UserData')->setUid($v['user_info']['uid'])->updateKey('weibo_count', 1);
					}
					$this->_deleteFeedAttach($ids, 'recoverAttach');
				} else {
					// 减少微博数
					foreach($feedList as $v) {
						model('UserData')->setUid($v['user_info']['uid'])->updateKey('feed_count', -1);
						model('UserData')->setUid($v['user_info']['uid'])->updateKey('weibo_count', -1);
					}
					$this->_deleteFeedAttach($ids, 'delAttach');
					// 删除频道相应微博
					$channelMap['feed_id'] = array('IN', $ids);
					D('channel')->where($channelMap)->delete();
				}
				$this->cleanCache($ids); 		// 删除微博缓存信息
				// 资源微博缓存相关微博
				$sids = $this->where('app_row_id='.$feed_id)->getAsFieldArray('feed_id');
				$this->cleanCache($sids);
			}
			// 删除评论信息
			$cmap['app'] = 'Public';
			$cmap['table'] = 'feed';
			$cmap['row_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
			$commentIds = model('Comment')->where($cmap)->getAsFieldArray('comment_id');
			model('Comment')->setAppName('Public')->setAppTable('feed')->deleteComment($commentIds);
			if($res) {
				// TODO:是否记录日志，以及后期缓存处理
				$return = array('status'=>1);
				//添加积分
				model('Credit')->setUserCredit($uid,'delete_weibo');
			}
		}

		return $return;
	}

	/**
	 * 删除微博相关附件数据
	 * @param array $feedIds 微博ID数组
	 * @param string $type 删除附件类型
	 * @return void
	 */
	private function _deleteFeedAttach($feedIds, $type)
	{
		// 查询微博内是否存在附件
		$feeddata = $this->getFeeds($feedIds);
		$feedDataInfo = getSubByKey($feeddata, 'feed_data');
		$attachIds = array();
		foreach($feedDataInfo as $value) {
			$value = unserialize($value);
			!empty($value['attach_id']) && $attachIds = array_merge($attachIds, $value['attach_id']);
		}
		array_filter($attachIds);
		array_unique($attachIds);
		!empty($attachIds) && model('Attach')->doEditAttach($attachIds, $type, '');
	}

	/**
	 * 审核通过微博
	 * @param integer $feed_id 微博ID
	 * @return array 微博操作后的结果信息数组
	 */
	public function doAuditFeed($feed_id){
		$return = array('status'=>'0');
		if(empty($feed_id)) {
			$return['data'] = '请选择微博！';
		} else {
			$map['feed_id'] = is_array($feed_id) ? array('IN', $feed_id) : intval($feed_id);
			$save['is_audit'] = 1;
			$res = $this->where($map)->save($save);
			if($res) {
				$return = array('status'=>1);
			}
			
			//更新缓存
			$this->cleanCache($feed_id);
		}	
		return $return;
	}
	
	/*** 搜索引擎使用 ***/
	/**
	 * 搜索微博
	 * @param string $key 关键字
	 * @param string $type 搜索类型，following、all、space
	 * @param integer $loadId 载入微博ID，从此微博ID开始搜索
	 * @param integer $limit 结果集数目
	 * @param boolean $forApi 是否返回API数据，默认为false
	 * @return array 搜索后的微博数据
	 */
	public function searchFeed($key, $type, $loadId, $limit, $forApi = false,$feed_type) {
		$page = intval($_REQUEST['p']);
		switch($type){
			case 'following':
				$buid = $GLOBALS['ts']['uid'];
				$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}user_follow AS b ON a.uid=b.fid AND b.uid = {$buid} LEFT JOIN {$this->tablePrefix}feed_data AS c ON a.feed_id = c.feed_id";
				$where = !empty($loadId) ? " a.is_del = 0 AND a.is_audit = 1 AND a.feed_id <'{$loadId}'" : "a.is_del = 0 AND a.is_audit = 1";
				$where .= " AND (a.uid = '{$buid}' OR b.uid = '{$buid}' )";
				$where .= " AND c.feed_data LIKE '%".t($key)."%'";
				$feedlist = $this->table($table)->where($where)->field('a.feed_id')->order('a.publish_time DESC')->findPage($limit);
				break;
			case 'all':
				$map['a.is_del'] = 0;
				$map['a.is_audit'] = 1;
				!empty($loadId) && $map['a.feed_id'] = array('LT', intval($loadId));
				$map['b.feed_content'] = array('LIKE', '%'.t($key).'%');
				if($feed_type){
					$map['a.type'] = $feed_type;
					if ( $map['a.type'] == 'post' ){
						unset($map['a.type']);
						$map['a.is_repost'] = 0;
					}
				}
				$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id";
				$feedlist = $this->table($table)->field('a.feed_id')->where($map)->order('a.publish_time DESC')->findPage($limit);
				$feedcount = $this->table($table)->field('a.feed_id')->where($map)->order('a.publish_time DESC')->count();
				break;
			case 'space':
				$map['a.is_del'] = 0;
				$map['a.is_audit'] = 1;
				!empty($loadId) && $map['a.feed_id'] = array('LT', intval($loadId));
				$map['b.feed_content'] = array('LIKE', '%'.t($key).'%');
				if($feed_type){
					$map['a.type'] = $feed_type;
					if ( $map['a.type'] == 'post' ){
						unset($map['a.type']);
						$map['a.is_repost'] = 0;
					}
				}
				$map['a.uid'] = $GLOBALS['ts']['uid'];
				$table = " {$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id";
				$feedlist = $this->table($table)->field('a.feed_id')->where($map)->order('a.publish_time DESC')->findPage($limit);
				break;	
			case 'topic':
				$map['a.is_del'] = 0;
				$map['a.is_audit'] = 1;
				!empty($loadId) && $map['a.feed_id'] = array('LT', intval($loadId));
				$map['b.feed_content'] = array('LIKE', '%#'.t($key).'#%');
				if($feed_type){
					$map['a.type'] = $feed_type;
					if ( $map['a.type'] == 'post' ){
						unset($map['a.type']);
						$map['a.is_repost'] = 0;
					}
				}
				$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id";
				$feedlist = $this->table($table)->field('a.feed_id')->where($map)->order('a.publish_time DESC')->findPage($limit);
				break;
		}
		$feed_ids = getSubByKey($feedlist['data'], 'feed_id');
		if($forApi) {
			if ($feedlist['totalPages'] < $page) {
				return array();
			}
			return $this->formatFeed($feed_ids, true);
		}
		$feedlist['data'] = $this->getFeeds($feed_ids);

		return $feedlist;
	}

	/**
	 * 数据库搜索微博
	 * @param string $key 关键字
	 * @param string $type 微博类型，post、repost、postimage、postfile
	 * @param integer $limit 结果集数目
	 * @param boolean $forApi 是否返回API数据，默认为false
	 * @return array 搜索后的微博数据
	 */
	public function searchFeeds($key, $feed_type, $limit, $Stime, $Etime) {	
		$map['a.is_del'] = 0;
		$map['a.is_audit'] = 1;
		$map['b.feed_content'] = array('LIKE', '%'.t($key).'%');
		if($feed_type){
			$map['a.type'] = $feed_type;
			if ( $map['a.type'] == 'post' ){
				unset($map['a.type']);
				$map['a.is_repost'] = 0;
			}
		}
		if($Stime && $Etime){
			$map['a.publish_time'] = array('between',array($Stime,$Etime));
		}
		$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}feed_data AS b ON a.feed_id = b.feed_id";
		$feedlist = $this->table($table)->field('a.feed_id')->where($map)->order('a.publish_time DESC')->findPage($limit);
		//return D()->getLastSql();exit;
		$feed_ids = getSubByKey($feedlist['data'], 'feed_id');
		$feedlist['data'] = $this->getFeeds($feed_ids);
		foreach($feedlist['data'] as &$v) {
        	switch ( $v['app'] ){
        		case 'weiba':
        			$v['from'] = getFromClient(0 , $v['app'] , '微吧');
        			break;
        		default:
        			$v['from'] = getFromClient( $v['from'] , $v['app']);
        			break;
        	}
        	!isset($uids[$v['uid']]) && $v['uid'] != $GLOBALS['ts']['mid'] && $uids[] = $v['uid'];
        }
		return $feedlist;
	}

	/*** API使用 ***/
	/**
	 * 获取全站最新的微博
	 * @param string $type 微博类型,原创post,转发repost,图片postimage,附件postfile,视频postvideo
	 * @param integer $since_id 微博ID，从此微博ID开始，默认为0
	 * @param integer $max_id 最大微博ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 分页数，默认为1
	 * @return array 全站最新的微博
	 */
	public function public_timeline($type, $since_id = 0, $max_id = 0 ,$limit = 20 ,$page = 1) {
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 
		$where = " is_del = 0 ";
		//动态类型
		if(in_array($type,array('post','repost','postimage','postfile','postvideo'))){
			$where .= " AND type='$type' ";
		}
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND feed_id > {$since_id}";
			!empty($max_id) && $where .= " AND feed_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$feed_ids = $this->where($where)->field('feed_id')->limit("{$start},{$end}")->order('feed_id DESC')->getAsFieldArray('feed_id');
		return $this->formatFeed($feed_ids,true);
	}

	/**
	 * 获取登录用户所关注人的最新微博
	 * @param string $type 微博类型,原创post,转发repost,图片postimage,附件postfile,视频postvideo
	 * @param integer $mid 用户ID
	 * @param integer $since_id 微博ID，从此微博ID开始，默认为0
	 * @param integer $max_id 最大微博ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 分页数，默认为1
	 * @return array 登录用户所关注人的最新微博
	 */
	public function friends_timeline($type, $mid, $since_id = 0, $max_id = 0, $limit = 20, $page = 1) {
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 
		$where = " a.is_del = 0 ";
		//动态类型
		if(in_array($type,array('post','repost','postimage','postfile','postvideo'))){
			$where .= " AND a.type='$type' ";
		}
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND a.feed_id > {$since_id}";
			!empty($max_id) && $where .= " AND a.feed_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$table = "{$this->tablePrefix}feed AS a LEFT JOIN {$this->tablePrefix}user_follow AS b ON a.uid=b.fid AND b.uid = {$mid}";
		// 加上自己的信息，若不需要此数据，请屏蔽下面语句
		$where = "(a.uid = '{$mid}' OR b.uid = '{$mid}') AND ($where)";
		$feed_ids = $this->where($where)->table($table)->field('a.feed_id')->limit("{$start},{$end}")->order('a.feed_id DESC')->getAsFieldArray('feed_id');
		
		return $this->formatFeed($feed_ids, true);
	}

	/**
	 * 获取指定用户发布的微博列表
	 * @param string $type 微博类型,原创post,转发repost,图片postimage,附件postfile,视频postvideo
	 * @param integer $user_id 指定用户ID
	 * @param string $user_name 指定用户名称
	 * @param integer $since_id 微博ID，从此微博ID开始，默认为0
	 * @param integer $max_id 最大微博ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 分页数，默认为1
	 * @return array 指定用户发布的微博列表
	 */
	public function user_timeline($type, $user_id , $user_name , $since_id = 0 , $max_id = 0 , $limit = 20 , $page = 1) {
		if(empty($user_id) && empty($user_name)) {
			return array();
		}
		if(empty($user_id)) {
			$user_info = model('User')->getUserInfoByName($user_name);
			$user_id = $user_info['uid'];
		}
		if(empty($user_id)) {
			return array();
		}
		$where = "uid = '{$user_id}' AND is_del = 0 ";
		//动态类型
		if(in_array($type,array('post','repost','postimage','postfile','postvideo'))){
			$where .= " AND type='$type' ";
		}
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 

		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND feed_id > {$since_id}";
			!empty($max_id) && $where .= " AND feed_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;

		$feed_ids = $this->field('feed_id')->where($where)->field('feed_id')->limit("{$start},{$end}")->order('publish_time DESC')->getAsFieldArray('feed_id'); 
		
		return $this->formatFeed($feed_ids, true);
	}

	/**
	 * 获取某条微博的被转发列表
	 * @param string $row_id 被转发微博ID
	 * @param integer $since_id 微博ID，从此微博ID开始，默认为0
	 * @param integer $max_id 最大微博ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 分页数，默认为1
	 * @return array 全站最新的微博
	 */
	public function repost_timeline($row_id, $since_id = 0, $max_id = 0 ,$limit = 20 ,$page = 1) {
		$row_id = intval($row_id);
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 
		if($row_id<=0){
			return false;
		}

		$where = " is_del = 0 AND type='repost' AND app_row_id=$row_id ";
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND feed_id > {$since_id}";
			!empty($max_id) && $where .= " AND feed_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$feed_ids = $this->where($where)->field('feed_id')->limit("{$start},{$end}")->order('feed_id DESC')->getAsFieldArray('feed_id');
		return $this->formatFeed($feed_ids,true);
	}

	/**
	 * 格式化微博数据
	 * @param array $feed_ids 微博ID数组
	 * @param boolean $forApi 是否为API数据，默认为false
	 * @return array 格式化后的微博数据
	 */
	public function formatFeed($feed_ids, $forApi = false) {
		if(empty($feed_ids)) {
			return array();
		} else {
			if(count($feed_ids) > 0 ) {
			 	$r = array();
			 	$forApi && $diggarr = model('FeedDigg')->checkIsDigg($feed_ids, $GLOBALS['ts']['mid']);
				foreach($feed_ids as $feed_id) {
					$v = $this->getFeedInfo($feed_id, $forApi);
					$v['feed_id'] = intval($v['feed_id']);
					$forApi && $v['is_digg'] = $diggarr[$v['feed_id']] ? 1 : 0;
					$r[] = $v;
				}
				return $r;
			} else {
				return array();
			}	
		}
	}

	/**
	 * 同步到微博
	 * @param string content 内容
	 * @param integer uid 发布者uid
	 * @param mixed attach_ids 附件ID  
	 * @return integer feed_id 微博ID
	 */
	public function syncToFeed($content,$uid,$attach_ids,$from) {	
		$d['content'] = '';
		$d['body'] = $content.'&nbsp;';
		$d['from'] = 0; //TODO
		if($attach_ids){
			$type = 'postimage';
			$d['attach_id'] = $attach_ids;
		}else{
			$type = 'post';
		}
		$feed = model('Feed')->put($uid, 'public', $type, $d, '', 'feed');
		return $feed['feed_id'];
	}

	/**
	 * 分享到微博
	 * @param string content 内容
	 * @param integer uid 分享者uid
	 * @param mixed attach_ids 附件ID  
	 * @return integer feed_id 微博ID
	 */
	public function shareToFeed($content,$uid,$attach_ids,$from) {	
		$d['content'] = '';
		$d['body'] = $content.'&nbsp;';
		$d['from'] = 0; //TODO
		if($attach_ids){
			$type = 'postimage';
			$d['attach_id'] = $attach_ids;
		}else{
			$type = 'post';
		}
		$feed = model('Feed')->put($uid, 'public', $type, $d, '', 'feed');
		return $feed['feed_id'];
	}
}