<?php
/**
 * 
 * @author jason
 *
 */
class UserApi extends Api{

	/**
	 * 按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博
	 * 
	 */
	function show(){
		
		//$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		//用户基本信息
		if(empty($this->user_id) && empty($this->user_name)){
			return false;
		}
		if(empty($this->user_id)){
			$data = model('User')->getUserInfoByName($this->user_name);	
			$this->user_id = $data['uid'];
		}else{
			$data = model('User')->getUserInfo($this->user_id);	
		}
		if(empty($data)){
			return false;
		}
		$data['sex'] = $data['sex'] ==1 ? '男':'女';
		
		$data['profile'] = model('UserProfile')->getUserProfileForApi($this->user_id);

		$profileHash = model('UserProfile')->getUserProfileSetting();
		$data['profile']['email'] = array('name'=>'邮箱','value'=>$data['email']);
		foreach(UserProfileModel::$sysProfile as $k){
			if(!isset($data['profile'][$k])){
				$data['profile'][$k] = array('name'=>$profileHash[$k]['field_name'],'value'=>'');
			}
		}

		//用户统计信息
		$defaultCount =  array('following_count'=>0,'follower_count'=>0,'feed_count'=>0,'favorite_count'=>0,'unread_atme'=>0,'weibo_count'=>0);

		$count   = model('UserData')->getUserData($this->user_id);
		if(empty($count)){
			$count = array();	
		}
		$data['count_info'] = array_merge($defaultCount,$count);
		
		//用户标签
		$data['user_tag'] = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($this->user_id);
		$data['user_tag'] = empty($data['user_tag']) ? '' : implode('、',$data['user_tag']);
		//关注情况
		$followState  = model('Follow')->getFollowState($this->mid,$this->user_id); 
		$data['follow_state'] = $followState;
		
		//最后一条微博
		$lastFeed = model('Feed')->getLastFeed($this->user_id);
		$data['last_feed'] = $lastFeed;

		// 判断用户是否被登录用户收藏通讯录
		$data['isMyContact'] = 0;
		if($this->user_id != $this->mid) {
			$cmap['uid'] = $this->mid;
			$cmap['contact_uid'] = $this->user_id;
			$myCount = D('Contact', 'contact')->where($cmap)->count();
			if($myCount == 1) {
				$data['isMyContact'] = 1;
			}
		}

		return $data;
	}
		
	/**
	 * 上传头像 API
	 * 传入的头像变量 $_FILES['Filedata']
	 */
	function upload_face(){
		$dAvatar = model('Avatar');
		$dAvatar->init($this->mid); // 初始化Model用户id
		$res = $dAvatar->upload(true);
		//Log::write(var_export($res,true));
		if($res['status'] == 1){
			$data['picurl'] = $res['data']['picurl'];
			$data['picwidth'] = $res['data']['picwidth'];
			$scaling = 5;
			$data['w'] = $res['data']['picwidth'] * $scaling;
			$data['h'] = $res['data']['picheight'] * $scaling;
			$data['x1'] = $data['y1'] = 0;
			$data['x2'] = $data['w'];
			$data['y2'] = $data['h'];
			$r = $dAvatar->dosave($data);
		}else{
			return '0';
		}
	}

	/**
	 *	关注一个用户
	 */
	public function follow_create(){
		if(empty($this->mid) || empty($this->user_id)){
			return 0;
		}

		$r = model('Follow')->doFollow($this->mid,$this->user_id);
		if(!$r){
			return model('Follow')->getFollowState($this->mid,$this->user_id);
			//return 0;
		}
		return $r;
	}

	/**
	 * 取消关注
	 */
	public function follow_destroy(){
		if(empty($this->mid) || empty($this->user_id)){
			return 0;
		}
		
		$r = model('Follow')->unFollow($this->mid,$this->user_id);
		if(!$r){
			return model('Follow')->getFollowState($this->mid,$this->user_id);
		}
		return $r;
	}

	/**
	 * 用户粉丝列表
	 */
	public function user_followers(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		// 清空新粉丝提醒数字
		if($this->user_id == $this->mid){
			$udata = model('UserData')->getUserData($this->mid);
			$udata['new_folower_count'] > 0 && model('UserData')->setKeyValue($this->mid,'new_folower_count',0);	
		}
		return model('Follow')->getFollowerListForApi($this->mid,$this->user_id,$this->since_id,$this->max_id,$this->count,$this->page);
	}

	/**
	 * 获取用户关注的人列表
 	 */
	public function user_following(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		return model('Follow')->getFollowingListForApi($this->mid,$this->user_id,$this->since_id,$this->max_id,$this->count,$this->page);
	}

	/**
	 * 获取用户的朋友列表
	 * 
	 */
	public function user_friends(){
		$this->user_id = empty($this->user_id) ? $this->mid : $this->user_id;
		return model('Follow')->getFriendsForApi($this->mid, $this->user_id, $this->since_id, $this->max_id, $this->count, $this->page);
	}

	// 按名字搜索用户
	public function wap_search_user(){
		$key = t($this->data['key']);
		$map['uname'] = array('LIKE',$key);
		$userlist = M('user')->where($map)->findAll();
		return $userlist;
	}

	/**
	 * 获取用户相关信息
	 * @param array $uids 用户ID数组
	 * @return array 用户相关数组
	 */
	public function getUserInfos($uids, $data, $type = 'basic')
	{
		// 获取用户基本信息
		$userInfos = model('User')->getUserInfoByUids($uids);
		$userDataInfo = model('UserData')->getUserKeyDataByUids('follower_count',$uids);

		if($type=='all'){
		// 获取其他用户统计数据
			// 获取关注信息
			$followStatusInfo = model('Follow')->getFollowStateByFids($GLOBALS['ts']['mid'], $uids);
			// 获取用户组信息
			$userGroupInfo = model('UserGroupLink')->getUserGroupData($uids);
		}

		// 组装数据
		foreach($data as &$value) {
			$value = array_merge($value, $userInfos[$value['uid']]);
			$value['user_data'] = $userDataInfo[$value['uid']];
			if($type=='all'){	
				$value['follow_state'] = $followStatusInfo[$value['uid']];
				$value['user_group'] = $userGroupInfo[$value['uid']];
			}
		}
	
		return $data;
	}
	// 按标签搜索用户
	public function search_by_tag()
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$tagid = intval ( $this->data['tagid'] );
		if ( !$tagid ){
			return 0;
		}
		$data = model('UserCategory')->getUidsByCid($tagid, null ,$limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 按地区搜索用户
	public function search_by_area($value='')
	{
		$_REQUEST['p'] = $_REQUEST['page'] = $this->page;
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$areaid = intval ( $this->data['areaid'] );
		if ( !$areaid && $this->data['areaname']){
			$amap['title'] = t( $this->data['areaname'] );
			$areaid = D('area')->where($amap)->getField('area_id');
		}
		if ( !$areaid ){
			return 0;
		}
		
		$pid1 = model('Area')->where('area_id='.$areaid)->getField('pid');
		$level = 1;
		if($pid1 != 0){
			$level = $level +1;
			$pid2 = model('Area')->where('area_id='.$pid1)->getField('pid');
			if($pid2 != 0){
				$level = $level +1;
			}
		}
		switch ($level) {
			case 1:
				$map['province'] = $areaid;
				break;
			case 2:
				$map['city'] = $areaid;
				break;
			case 3:
				$map['area'] = $areaid;
				break;
		}
		
		$map['is_del'] = 0;
		$map['is_active'] = 1;
		$map['is_audit'] = 1;
		$map['is_init'] = 1;
		
		$data = D('user')->where($map)->field('uid')->order("uid desc")->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		
		return $data['data'] ? $data : 0;
	}

	// 按认证分类搜索用户
	public function search_by_verify_category($value='')
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$verifyid = intval ( $this->data['verifyid'] );
		if ( !$verifyid && $this->data['verifyname']){
			$amap['title'] = t( $this->data['verifyname'] );
			$verifyid = D('user_verify_category')->where($amap)->getField('user_verified_category_id');
		}
		if ( !$verifyid ){
			return 0;
		}
		$maps['user_verified_category_id'] = $verifyid;
		$maps['verified'] = 1;
		$data = D('user_verified')->where($maps)->field('uid, info AS verify_info')->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 按官方推荐分类搜索用户
	public function search_by_uesr_category($value='')
	{
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$cateid = intval ( $this->data['cateid'] );
		if ( !$cateid && $this->data['catename']){
			$amap['title'] = t( $this->data['catename'] );
			$cateid = D('user_official_category')->where($amap)->getField('user_official_category_id');
		}
		if ( !$cateid ){
			return 0;
		}
	 	$maps['user_official_category_id'] = $cateid;
		$data = D('user_official')->where($maps)->field('uid, info AS verify_info')->findPage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	} 

	public function get_user_category()
	{
		$type = t ( $this->data['type'] );
		switch ($type) {
			//地区分类 最多只列出二级
			case 'area':
				$category = model('CategoryTree')->setTable('area')->getNetworkList();
				break;

			//认证分类 最多只列出二级
			case 'verify_category':
				$category = model('UserGroup')->where('is_authenticate=1')->findAll();
				foreach($category as $k=>$v){
					$category[$k]['child'] = D('user_verified_category')->where('pid='.$v['user_group_id'])->findAll();
				}
				break;

			//推荐分类 最多只列出二级
			case 'user_category':
				$category = model('CategoryTree')->setTable('user_official_category')->getNetworkList();
				break;

			//标签 tag 最多只列出二级
			default:
				$category = model('UserCategory')->getNetworkList();
				break;
		}
		return $category;
	}
	/**
	 * 粉丝最多
	 * @return Ambigous <number, 返回新的一维数组, boolean, multitype:Ambigous <array, string> >
	 */
	public function get_user_follower(){
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$page = $this->data['page'] ? intval($this->data['page']) : 1;
		$limit = ($page - 1) * $limit.', '.$limit;
		
		$followermap['key'] = 'follower_count';
		$followeruids = model('UserData')->where($followermap)->field('uid')->order('`value`+0 desc,uid')->limit($limit)->findAll();
		$followeruids = $this->getUserInfos ( getSubByKey( $followeruids , 'uid' ) , $followeruids,'basic');
		return $followeruids ? $followeruids : 0;
	}

	// 按地理位置搜索邻居
	public function neighbors(){
		//经度latitude 
		//纬度longitude
		//距离distance
		$latitude = floatval ( $this->data['latitude'] );
		$longitude = floatval( $this->data['longitude'] );
		//根据经度、纬度查询周边用户 1度是 111 公里
		//根据ts_mobile_user 表查找，经度和纬度在一个范围内。  
		//latitude < ($latitude + 1) AND latitude > ($latitude - 1)
		//longitude < ($longitude + 1) AND longitude > ($longitude - 1)
		$limit = 20;
		$this->data['limit'] && $limit = intval( $this->data['limit'] );
		$map['last_latitude'] = array( 'between' , ($latitude - 1).','.($latitude + 1) );
		$map['last_longitude'] = array( 'between' , ($longitude - 1).','.($longitude + 1) );
		
		$data = D('mobile_user')->where($map)->field('uid')->findpage($limit);
		$data['data'] = $this->getUserInfos ( getSubByKey( $data['data'] , 'uid' ) , $data['data'],'basic');
		return $data['data'] ? $data : 0;
	}

	// 记录用户的最后活动位置
	public function checkin(){
		$latitude = floatval ( $this->data['latitude'] );
		$longitude = floatval ( $this->data['longitude'] );
		//记录用户的UID、经度、纬度、checkin_time、checkin_count
		//如果没有记录则写入，如果有记录则更新传过来的字段包括：sex\nickname\infomation（用于对周边人进行搜索）
		$checkin_count = D('mobile_user')->where('uid='.$this->mid)->getField('checkin_count');
		$data['last_latitude'] = $latitude;
		$data['last_longitude'] = $longitude;
		$data['last_checkin'] = time();
		
		if ( $checkin_count ){
			$data['checkin_count'] = $checkin_count + 1;
			$res = D('mobile_user')->where('uid='.$this->mid)->save($data);
		} else {
			
			$user = model('User')->where('uid='.$this->mid)->field('uname,intro,sex')->find();
			$data['nickname'] = $user['uname'];
			$data['infomation'] = $user['intro'];
			$data['sex'] = $user['sex'];
			
			$data['checkin_count'] = 1;
			$data['uid'] = $this->mid;
			$res = D('mobile_user')->add($data);
		}
		return $res ? 1 : 0;
	}
}