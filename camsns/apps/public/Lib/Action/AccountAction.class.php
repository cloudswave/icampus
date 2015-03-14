<?php
/**
 * 账号设置控制器
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class AccountAction extends Action 
{
	private $_profile_model;			// 用户档案模型对象字段
	
	/**
	 * 控制器初始化，实例化用户档案模型对象
	 * @return void
	 */
	protected function _initialize() {

		$this->_profile_model = model('UserProfile');
		// 从数据库读取
		$profile_category_list = $this->_profile_model->getCategoryList();
		
		$tab_list[] = array('field_key'=>'index','field_name'=>L('PUBLIC_PROFILESET_INDEX'));				// 基本资料
		$tab_list[] = array('field_key'=>'tag','field_name'=>L('PUBLIC_PROFILE_TAG'));				// 基本资料
		$tab_lists = $profile_category_list;

		foreach($tab_lists as $v) {
			$tab_list[] = $v;			// 后台添加的资料配置分类
		}
		$tab_list[] = array('field_key'=>'avatar','field_name'=>L('PUBLIC_IMAGE_SETTING'));				// 头像设置
		$tab_list[] = array('field_key'=>'domain','field_name'=>L('PUBLIC_DOMAIN_NAME'));				// 个性域名
		$tab_list[] = array('field_key'=>'authenticate','field_name'=>'申请认证');	// 申请认证
		$tab_list_preference[] = array('field_key'=>'privacy','field_name'=>L('PUBLIC_PRIVACY'));					// 隐私设置
		$tab_list_preference[] = array('field_key'=>'notify','field_name'=>'通知设置');					// 通知设置
		$tab_list_preference[] = array('field_key'=>'blacklist','field_name'=>'黑名单');					// 黑名单
		$tab_list_security[] = array('field_key'=>'security','field_name'=>L('PUBLIC_ACCOUNT_SECURITY'));		// 帐号安全	
		
		//插件增加菜单
		$tab_list_security[] = array('field_key'=>'bind','field_name'=>'帐号绑定');		// 帐号绑定
		
		$this->assign('tab_list',$tab_list);
		$this->assign('tab_list_preference',$tab_list_preference);
		$this->assign('tab_list_security',$tab_list_security);
	}

	/**
	 * 基本设置页面
	 */
	public function index()
	{
		$this->appCssList[] = 'account.css';
		$user_info = model('User')->getUserInfo($this->mid);
		$data = $this->_getUserProfile();
		$data['langType'] = model('Lang')->getLangType();
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user_info['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$this->assign('user_info', $user_info);
		$this->assign($data);
		$this->setTitle( L('PUBLIC_PROFILESET_INDEX') );			// 个人设置
		$this->setKeywords( L('PUBLIC_PROFILESET_INDEX') );
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user_info['category'].$user_info['location'].','.implode(',', $user_tag[$this->mid]).','.$user_info['intro']));
		$this->display();
	}

	/**
	 * 扩展信息设置页面
	 * @param string $extend 扩展类目名称(为插件准备)
	 */
	public function _empty($extend) {
		$cid = D('user_profile_setting')->where("field_key='".ACTION_NAME."'")->getField('field_id');
		$data = $this->_getUserProfile();
		$data['cid'] = $cid;
		$this->assign($data);
		$this->display('extend');
	}

	/**
	 * 获取登录用户的档案信息
	 * @return 登录用户的档案信息
	 */
	private function _getUserProfile() {
		$data['user_profile'] = $this->_profile_model->getUserProfile($this->mid);
		$data['user_profile_setting'] = $this->_profile_model->getUserProfileSettingTree();

		return $data;
	}

	/**
	 * 保存基本信息操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveProfile() {
		$res = true;
		// 保存用户表信息
		if(!empty($_POST['sex'])) {
			$save['sex']  = 1 == intval($_POST['sex']) ? 1 : 2;
		//	$save['lang'] = t($_POST['lang']);
			$save['intro'] = t($_POST['intro']);
			// 添加地区信息
			$save['location'] = t($_POST['city_names']);
			$cityIds = t($_POST['city_ids']);
			$cityIds = explode(',', $cityIds);
			if(!$cityIds[0] || !$cityIds[1] || !$cityIds[2]) $this->error('请选择完整地区');
			isset($cityIds[0]) && $save['province'] = intval($cityIds[0]);
			isset($cityIds[1]) && $save['city'] = intval($cityIds[1]);
			isset($cityIds[2]) && $save['area'] = intval($cityIds[2]);
			// 修改用户昵称
			$uname = t($_POST['uname']);
			$oldName = t($_POST['old_name']);
			$save['uname'] = filter_keyword($uname);
			$res = model('Register')->isValidName($uname, $oldName);
			if(!$res) {
				$error = model('Register')->getLastError();
				return $this->ajaxReturn(null, model('Register')->getLastError(), $res);		
			}
			//如果包含中文将中文翻译成拼音
			if ( preg_match('/[\x7f-\xff]+/', $save['uname'] ) ){
				//昵称和呢称拼音保存到搜索字段
				$save['search_key'] = $save['uname'].' '.model('PinYin')->Pinyin( $save['uname'] );
			} else {
				$save['search_key'] = $save['uname'];
			}
			$res = model('User')->where("`uid`={$this->mid}")->save($save);
			$res && model('User')->cleanCache($this->mid);	
			$user_feeds = model('Feed')->where('uid='.$this->mid)->field('feed_id')->findAll();
			if($user_feeds){
				$feed_ids = getSubByKey($user_feeds, 'feed_id');
				model('Feed')->cleanCache($feed_ids,$this->mid);
			}
		}
		// 保存用户资料配置字段
		(false !== $res) && $res = $this->_profile_model->saveUserProfile($this->mid, $_POST);
		// 保存用户标签信息
		$tagIds = t($_REQUEST['user_tags']);
		!empty($tagIds) && $tagIds = explode(',', $tagIds);
		$rowId = intval($this->mid);
		if(!empty($rowId)) {
			$registerConfig = model('Xdata')->get('admin_Config:register');
			if(count($tagIds) > $registerConfig['tag_num']) {
				return $this->ajaxReturn(null, '最多只能设置'.$registerConfig['tag_num'].'个标签', false);
			}
			model('Tag')->setAppName('public')->setAppTable('user')->updateTagData($rowId, $tagIds);
		}
		$result = $this->ajaxReturn(null, $this->_profile_model->getError(), $res);
		return $this->ajaxReturn(null, $this->_profile_model->getError(), $res);
	}

	/**
	 * 头像设置页面
	 */
	public function avatar() {	
		model('User')->cleanCache($this->mid);
		$user_info = model('User')->getUserInfo($this->mid);
		$this->assign('user_info', $user_info);

		$this->setTitle( L('PUBLIC_IMAGE_SETTING') );			// 个人设置
		$this->setKeywords( L('PUBLIC_IMAGE_SETTING') );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user_info['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user_info['category'].$user_info['location'].','.implode(',', $user_tag[$this->mid]).','.$user_info['intro']));
		$this->display();
	}

	/**
	 * 保存登录用户的头像设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveAvatar() {
        $dAvatar = model('Avatar');
        $dAvatar->init($this->mid); 			// 初始化Model用户id
        // 安全过滤
        $step = t($_GET['step']);
        if('upload' == $step) {
            $result = $dAvatar->upload();
        } else if('save' == $step) {
            $result = $dAvatar->dosave();
        }
        model('User')->cleanCache($this->mid);
        $user_feeds = model('Feed')->where('uid='.$this->mid)->field('feed_id')->findAll();
		if($user_feeds){
			$feed_ids = getSubByKey($user_feeds, 'feed_id');
			model('Feed')->cleanCache($feed_ids,$this->mid);
		}
    	$this->ajaxReturn($result['data'], $result['info'], $result['status']);
	}

	/**
	 * 保存登录用户的头像设置操作，Flash上传
	 * @return string 操作后的反馈信息
	 */
	public function doSaveUploadAvatar() {
		$data['big'] = base64_decode($_POST['png1']);
		$data['middle'] = base64_decode($_POST['png2']);
		$data['small'] = base64_decode($_POST['png3']);
		if(empty($data['big']) || empty($data['middle']) || empty($data['small'])) {
			exit('error='.L('PUBLIC_ATTACHMENT_UPLOAD_FAIL'));						// 图片上传失败，请重试
		}
		if(model('Avatar')->init($this->mid)->saveUploadAvatar($data, $this->user)) {
			exit('success='.L('PUBLIC_ATTACHMENT_UPLOAD_SUCCESS'));					// 附件上传成功
		} else {
			exit('error='.L('PUBLIC_ATTACHMENT_UPLOAD_FAIL'));						// 图片上传失败，请重试
		}	
	}

	/**
	 * 标签设置页面
	 */
	public function tag() {
		$registerConfig = model('Xdata')->get('admin_Config:register');
		$this->assign('tag_num',$registerConfig['tag_num']);
		$this->display();
	}
	
	/**
	 * 隐私设置页面
	 */
	public function privacy() {
    	$user_privacy = D('UserPrivacy')->getUserSet($this->mid);
    	$this->assign('user_privacy', $user_privacy);

    	$user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( L('PUBLIC_PRIVACY') );			
		$this->setKeywords( L('PUBLIC_PRIVACY') );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
    	$this->display();
	}

	/**
	 * 保存登录用户隐私设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSavePrivacy() {
		//dump($_POST);exit;
		$res = model('UserPrivacy')->dosave($this->mid, $_POST);
    	$this->ajaxReturn(null, model('UserPrivacy')->getError(), $res);
	}

	/**
	 * 个性域名设置页面
	 */
	public function domain() {
    	// 是否启用个性化域名
    	$user = model('User')->getUserInfo($this->mid);
    	$data['user_domain'] = $user['domain'];
    	$this->assign($data);

    	$this->setTitle( L('PUBLIC_DOMAIN_NAME') );			// 个人设置
		$this->setKeywords( L('PUBLIC_DOMAIN_NAME') );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
    	$this->display();
	}

	/**
	 * 保存用户个性域名操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveDomain() {
		$domain = t($_POST['domain']);
		// 验证信息
		if(strlen($domain) < 5) {
			$this->ajaxReturn(null, '域名长度不能少于5个字符', 0);			// 仅限5个字符以上20个字符以内的英文/数字/下划线，以英文字母开头，不能含有特殊字符，一经设置，无法更改。
		}
		if(strlen($domain) > 20) {
			$this->ajaxReturn(null, L('PUBLIC_SHORT_DOMAIN_CHARACTERLIMIT'), 0);	// 域名长度不能超过20个字符
		}
		if(!ereg('^[a-zA-Z][_a-zA-Z0-9]+$', $domain)) {
			$this->ajaxReturn(null, '仅限于英文/数字/下划线，以英文字母开头，不能含有特殊字符', 0);			// 仅限5个字符以上20个字符以内的英文/数字/下划线，以英文字母开头，不能含有特殊字符，一经设置，无法更改。
		}
		
		$keywordConfig = model('Xdata')->get('keywordConfig');
		$keywordConfig =explode(",", $keywordConfig);
		if(!empty($keywordConfig) && in_array($domain, $keywordConfig)) {
			$this->ajaxReturn(null, L('PUBLIC_DOMAIN_DISABLED'), 0);				// 该个性域名已被禁用
		}
		
		// 预留域名使用
		$sysDomin = model('Xdata')->getConfig('sys_domain', 'site');
		$sysDomin = explode(",", $sysDomin);
		if(!empty($sysDomin) && in_array($domain, $sysDomin)){
			$this->ajaxReturn(null, L('PUBLIC_DOMAIN_DISABLED'), 0);				// 该个性域名已被禁用
		}
		
		if(model('User')->where("uid!={$this->mid} AND domain='{$domain}'")->count()) {
			$this->ajaxReturn(null, L('PUBLIC_DOMAIN_OCCUPIED'), 0);				// 此域名已经被使用
		} else {
			$user_info = model('User')->getUserInfo($this->mid);
			!$user_info['domian'] && model('User')->setField('domain', "$domain", 'uid='.$this->mid);
			model('User')->cleanCache($this->mid);
			$this->ajaxReturn(null, L('PUBLIC_DOMAIN_SETTING_SUCCESS'), 1);			// 域名设置成功
		}
	}

	/**
	 * 账号安全设置页面
	 */
	public function security() {
		$user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( L('PUBLIC_ACCOUNT_SECURITY') );			
		$this->setKeywords( L('PUBLIC_ACCOUNT_SECURITY') );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
		$this->display();
	}

	/**
	 * 修改登录用户账号密码操作
	 * @return json 返回操作后的JSON信息数据
	 */
    public function doModifyPassword() {
    	$_POST['oldpassword'] = t($_POST['oldpassword']);
    	$_POST['password'] = t($_POST['password']);
    	$_POST['repassword'] = t($_POST['repassword']);
    	// 验证信息
    	if ($_POST['oldpassword'] === '') {
    		$this->error('请填写原始密码');
    	}
    	if ($_POST['password'] === '') {
    		$this->error('请填写新密码');
    	}
    	if ($_POST['repassword'] === '') {
    		$this->error('请填写确认密码');
    	}
    	if($_POST['password'] != $_POST['repassword']) {
    		$this->error(L('PUBLIC_PASSWORD_UNSIMILAR'));			// 新密码与确认密码不一致
    	}
    	if(strlen($_POST['password']) < 6) {
			$this->error('密码太短了，最少6位');				
		}
		if(strlen($_POST['password']) > 15) {
			$this->error('密码太长了，最多15位');				
		}
		if($_POST['password'] == $_POST['oldpassword']) {
			$this->error(L('PUBLIC_PASSWORD_SAME'));				// 新密码与旧密码相同
		}

    	$user_model = model('User');
    	$map['uid'] = $this->mid;
    	$user_info = $user_model->where($map)->find();

    	if($user_info['password'] == $user_model->encryptPassword($_POST['oldpassword'], $user_info['login_salt'])) {
			$data['login_salt'] = rand(11111, 99999);
			$data['password'] = $user_model->encryptPassword($_POST['password'], $data['login_salt']);
			$res = $user_model->where("`uid`={$this->mid}")->save($data);
    		$info = $res ? L('PUBLIC_PASSWORD_MODIFY_SUCCESS') : L('PUBLIC_PASSWORD_MODIFY_FAIL');			// 密码修改成功，密码修改失败
    	} else {
    		$info = L('PUBLIC_ORIGINAL_PASSWORD_ERROR');			// 原始密码错误
    	}
    	return $this->ajaxReturn(null, $info, $res);
    }

    /**
     * 申请认证
     * @return void
     */
    public function authenticate(){
    	$auType = model('UserGroup')->where('is_authenticate=1')->findall();
    	$this->assign('auType', $auType);
    	$verifyInfo = D('user_verified')->where('uid='.$this->mid)->find();
    	if($verifyInfo['attach_id']){
			  $a = explode('|', $verifyInfo['attach_id']);
			  foreach($a as $key=>$val){
			  	if($val !== "") {
			  		$attachInfo = D('attach')->where("attach_id=$a[$key]")->find();
			  		$verifyInfo['attachment'] .= $attachInfo['name'].'&nbsp;<a href="'.getImageUrl($attachInfo['save_path'].$attachInfo['save_name']).'" target="_blank">下载</a><br />';
			  	}
			  }
		}
		// 获取认证分类信息
		if(!empty($verifyInfo['user_verified_category_id'])) {
			$verifyInfo['category']['title'] = D('user_verified_category')->where('user_verified_category_id='.$verifyInfo['user_verified_category_id'])->getField('title');
		}

		switch ($verifyInfo['verified']) {
			case '1':
				$status = '<i class="ico-ok"></i>已认证 <a href="javascript:void(0);" onclick="delverify()">注销认证</a>';
				break;
			case '0':
				$status = '<i class="ico-wait"></i>已提交认证，等待审核';
				break;
			case '-1':
				// 安全过滤
				$type = t($_GET['type']);
				if($type == 'edit'){
					$status = '<i class="ico-no"></i>未通过认证，请修改资料后重新提交';
					$this->assign('edit',1);
					$verifyInfo['attachIds'] = str_replace('|', ',', substr($verifyInfo['attach_id'],1,strlen($verifyInfo['attach_id'])-2));
				}else{
					$status = '<i class="ico-no"></i>未通过认证，请修改资料后重新提交 <a href="'.U('public/Account/authenticate',array('type'=>'edit')).'">修改认证资料</a>';
				}
				break;
			default:
				//$verifyInfo['usergroup_id'] = 5;
				$status = '未认证';
				break;
		}
		//附件限制
		$attach = model('Xdata')->get("admin_Config:attachimage");
		$imageArr = array('gif','jpg','jpeg','png','bmp');
		foreach($imageArr as $v){
			if(strstr($attach['attach_allow_extension'],$v)){
				$imageAllow[] = $v;
			}
		}
		$attachOption['attach_allow_extension'] = implode(', ', $imageAllow);
		$attachOption['attach_max_size'] = $attach['attach_max_size'];
		$this->assign('attachOption',$attachOption);

		// 获取认证分类
		$category = D('user_verified_category')->findAll();
		foreach($category as $k=>$v){
			$option[$v['pid']] .= '<option ';
			if($verifyInfo['user_verified_category_id']==$v['user_verified_category_id']){
				$option[$v['pid']] .= 'selected';
			}
			$option[$v['pid']] .= ' value="'.$v['user_verified_category_id'].'">'.$v['title'].'</option>';
		}
		//dump($option);exit;
		$this->assign('option', json_encode($option));
		$this->assign('options', $option);
		$this->assign('category', $category);
    	$this->assign('status' , $status);
    	$this->assign('verifyInfo' , $verifyInfo);
    	//dump($verifyInfo);exit;

    	$user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( '申请认证' );			
		$this->setKeywords( '申请认证' );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
    	$this->display();
    }

    /**
     * 提交申请认证
     * @return void
     */
    public function doAuthenticate(){
    	$verifyInfo = D('user_verified')->where('uid='.$this->mid)->find();      
        $data['usergroup_id'] = intval($_POST['usergroup_id']);
        if(!$data['usergroup_id']) $data['usergroup_id'] = 5;
       	$data['company'] = t($_POST['company']);	
        $data['realname'] = t($_POST['realname']);
        $data['idcard'] = t($_POST['idcard']);
        $data['phone'] = t($_POST['phone']);
        $data['reason'] = t($_POST['reason']);
        //$data['info'] = t($_POST['info']);
        $data['attach_id'] = t($_POST['attach_ids']);
        if(D('user_verified_category')->where('pid='.$data['usergroup_id'])->find()){
        	$data['user_verified_category_id'] = intval($_POST['verifiedCategory']);
    	}else{
    		$data['user_verified_category_id'] = 0;
    	}
        $Regx1 = '/^[0-9]*$/';
        $Regx2 = '/^[A-Za-z0-9]*$/';
        $Regx3 = '/^[A-Za-z|\x{4e00}-\x{9fa5}]+$/u';

        if($data['usergroup_id'] == 6){
        	if(strlen($data['company'])==0){
        		//$this->error('企业名称不能为空');
        		echo '企业名称不能为空';exit;
        	}
        	if(strlen($data['realname'])==0){
        		//$this->error('法人姓名不能为空');	
        		echo '法人姓名不能为空';exit;
        	}
        	if(strlen($data['idcard'])==0){
        		//$this->error('营业执照号不能为空');	
        		echo '营业执照号不能为空';exit;
        	}
        	if(strlen($data['phone'])==0){
        		//$this->error('联系方式不能为空');
        		echo '联系方式不能为空';exit;	
        	}
        	if(strlen($data['reason'])==0){
        		//$this->error('认证理由不能为空');
        		echo '认证理由不能为空';exit;	
        	}
        	if(preg_match($Regx3, $data['realname'])==0 || strlen($data['realname'])>30){
                echo '请输入正确的法人姓名';exit;
            }  
        	// if(strlen($data['info'])==0){
        	// 	$this->error('认证资料不能为空');	
        	// }
        	if(preg_match($Regx2, $data['idcard'])==0){
        		//$this->error('请输入正确的营业执照号');	
        		echo '请输入正确的营业执照号';exit;	
        	}
        	
        }else{
        	if(strlen($data['realname'])==0){
        		//$this->error('真实姓名不能为空');	
        		echo '真实姓名不能为空';exit;
        	}
        	if(strlen($data['idcard'])==0){
        		//$this->error('身份证号码不能为空');	
        		echo '身份证号码不能为空';exit;

        	}
        	if(strlen($data['phone'])==0){
        		//$this->error('手机号码不能为空');	
        		echo '手机号码不能为空';exit;
        	}
        	if(strlen($data['reason'])==0){
        		//$this->error('认证理由不能为空');	
        		echo '认证理由不能为空';exit;
        	}
        	// if(strlen($data['info'])==0){
        	// 	$this->error('认证资料不能为空');	
        	// }
        	if(preg_match($Regx3, $data['realname'])==0 || strlen($data['realname'])>30){
                //$this->error('请输入正确的姓名格式');
                echo '请输入正确的姓名格式';exit;
            }  
        	if(preg_match($Regx2, $data['idcard'])==0 || preg_match($Regx1, substr($data['idcard'],0,17))==0 || strlen($data['idcard'])!==18){
        		//$this->error('请输入正确的身份证号码');	
        		echo '请输入正确的身份证号码';exit;
        	}
        	if(strlen($data['phone']) !== 11 || preg_match($Regx1, $data['phone'])==0){
                //$this->error('请输入正确的手机号码格式');
                echo '请输入正确的手机号码格式';exit;
            }
        }
        preg_match_all('/./us', $data['reason'], $matchs);   //一个汉字也为一个字符
        if(count($matchs[0])>140){
        	//$this->error('认证理由不能超过140个字符');	
        	echo '认证理由不能超过140个字符';exit;
        }
        // preg_match_all('/./us', $data['info'], $match);   //一个汉字也为一个字符
        // if(count($match[0])>140){
        // 	$this->error('认证资料不能超过140个字符');	
        // }       
    	if($verifyInfo){
    		$data['verified'] = 0;
    		$res = D('user_verified')->where('uid='.$verifyInfo['uid'])->save($data);
    	}else{
    		$data['uid'] = $this->mid; 
    		$res = D('user_verified')->add($data);
    	}
        if($res){
        	//echo '1';
        	model('Notify')->sendNotify($this->mid,'public_account_doAuthenticate');
        	$touid = D('user_group_link')->where('user_group_id=1')->field('uid')->findAll();
			foreach($touid as $k=>$v){
				model('Notify')->sendNotify($v['uid'], 'verify_audit');
			}
        	//return $this->ajaxReturn(null, '申请成功，请等待审核', 1);
        	echo '1';
        }else{
        	//$this->error("申请失败");
        	echo '申请失败';exit;
        }
    }

    /**
     * 注销认证
     * @return bool 操作是否成功  1:成功   0:失败
     */	
    public function delverify(){
    	$verified_group_id = D('user_verified')->where('uid='.$this->mid)->getField('usergroup_id');
    	$res = D('user_verified')->where('uid='.$this->mid)->delete();
    	$res2 = D('user_group_link')->where('uid='.$this->mid.' and user_group_id='.$verified_group_id)->delete();
    	if($res && $res2){
    		//清除权限组 用户组缓存
    		model('Cache')->rm('perm_user_'.$this->mid);
    		model('Cache')->rm('user_group_'.$this->mid);
    		model('Notify')->sendNotify($this->mid,'public_account_delverify');
    		echo 1;
    	}else{
    		echo 0;
    	}
    }

    /**
     * 黑名单设置
     * @return void
     */
    public function blacklist(){

    	$user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( '黑名单' );			
		$this->setKeywords( '黑名单' );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
    	$this->display();
    }

    /**
     * 通知设置
     * @return void
     */
    public function notify(){
    	$user_privacy = D('UserPrivacy')->getUserSet($this->mid);
    	$this->assign('user_privacy', $user_privacy);

    	$user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( '通知设置' );			
		$this->setKeywords( '通知设置' );
		// 获取用户职业信息
		$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
		$userCateArray = array();
		if(!empty($userCategory)) {
			foreach($userCategory as $value) {
				$user['category'] .= '<a href="#" class="link btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
			}
		}
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags(array($this->mid));
		$this->setDescription(t($user['category'].$user['location'].','.implode(',', $user_tag[$this->mid]).','.$user['intro']));
    	$this->display();
    }

    /**
     * 修改用户身份
     */
    public function editUserCategory(){
    	$this->assign('mid', $this->mid);
    	$this->display();
    }

    /**
     * 执行修改用户身份操作
     */
    public function doEditUserCategory(){
    	$userCategoryIds = t($_POST['user_category_ids']);
		empty($userCategoryIds) && exit($this->error('请至少选择一个职业信息'));
		$userCategoryIds = explode(',', $userCategoryIds);
		$userCategoryIds = array_filter($userCategoryIds);
		$userCategoryIds = array_unique($userCategoryIds);
		$result = model('UserCategory')->updateRelateUser($this->mid, $userCategoryIds);
		if($result) {
			// 获取用户身份信息
			$userCategory = model('UserCategory')->getRelatedUserInfo($this->mid);
			$userCateArray = array();
			if(!empty($userCategory)) {
				foreach($userCategory as $value) {
					$category .= '<a href="#" class="btn-cancel"><span>'.$value['title'].'</span></a>&nbsp;&nbsp;';
				}
			}
			$this->ajaxReturn($category, L('PUBLIC_SAVE_SUCCESS'), $result);
		} else {
			$this->ajaxReturn(null, '职业信息保存失败', $result);
		}
    }

    /**
     * 帐号绑定
     */
    public function bind(){
 		// 邮箱绑定
 		// 	$user = M('user')->where('uid='.$this->mid)->field('email')->find();
		// $replace = substr($user['email'],2,-3);
		// for ($i=1;$i<=strlen($replace);$i++){
		// 	$replacestring.='*';
		// }
		// $data['email'] = str_replace(  $replace, $replacestring ,$user['email'] );
        
        // 站外帐号绑定
        $bindData = array();
        Addons::hook('account_bind_after',array('bindInfo'=>&$bindData));
        $data['bind']  = $bindData;
   	    $this->assign($data);
   	    $user = model('User')->getUserInfo($this->mid);
    	$this->setTitle( '帐号绑定' );			
		$this->setKeywords( '帐号绑定' );
		$this->setDescription(t(implode(',', getSubByKey($data['bind'],'name'))));
   	    $this->display();
    }
}