<?php
/**
 * 前台微吧管理控制器
 * @author 
 * @version TS3.0
 */
class ManageAction extends Action {

	/**
	 * 初始化，验证当前登录用户权限
	 */
	public function _initialize() {
		if(!$_REQUEST['weiba_id']) $this->error('参数错误!');
		if(!CheckPermission('core_admin','admin_login')){
			if(D('weiba_follow')->where('follower_uid='.$this->mid.' and weiba_id='.intval($_REQUEST['weiba_id']))->getField('level')<2){
				$this->error('您没有访问权限');
			}
		}
		$this->assign('weiba_id',intval($_REQUEST['weiba_id']));
		//超级吧主
		$this->assign('weiba_super_admin',D('weiba_follow')->where('level=3 and weiba_id='.intval($_REQUEST['weiba_id']))->getField('follower_uid'));
		$this->assign('weiba_admin',getSubByKey(D('weiba_follow')->where(array('weiba_id'=>intval($_REQUEST['weiba_id']),'level'=>array('in','2,3')))->order('level desc')->field('follower_uid,level')->findAll(),'follower_uid'));
		$weiba_name = D('weiba')->where('weiba_id='.intval($_REQUEST['weiba_id']))->getField('weiba_name');
		$this->assign('weiba_name',$weiba_name);
		//dump($weiba_name);exit;
	}

	/**
	 * 微吧管理首页-修改微吧信息
	 * @return void
	 */
	public function index() {
		$weiba_id = intval($_GET['weiba_id']);
		$weiba_detail = D('weiba')->where('weiba_id='.$weiba_id)->find();
		if($weiba_detail['logo']){
			$weiba_detail['logo_url'] = getImageUrlByAttachId($weiba_detail['logo']);
		}		
		$weiba_cates = D('WeibaCategory')->getAllWeibaCate();
		$this->assign( 'weiba_cates' , $weiba_cates );
		$this->assign('weiba_detail',$weiba_detail);
		$this->setTitle( '基本信息 '.$weiba_detail['weiba_name'] );
		$this->setKeywords( '基本信息 '.$weiba_detail['weiba_name'] );
		$this->display();
	}

	/**
	 * 执行编辑微吧
	 * @return void
	 */
	public function doWeibaEdit(){
		//dump($_POST);exit;
		$weiba_id = intval($_POST['weiba_id']);
		$data['weiba_name'] = t($_POST['weiba_name']);
		$data['intro'] = t($_POST['intro']);
		$data['logo'] = t($_POST['logo']);
		$data['cid'] = intval($_POST['cid']);
		$data['who_can_post'] = t($_POST['who_can_post']);
		if(strlen($data['weiba_name']) == 0){
			//$this->error('微吧名称不能为空');
			echo '微吧名称不能为空';
			exit;
		}
		if(strlen($data['logo']) == 0){
			//$this->error('logo不能为空');
			echo 'logo不能为空';
			exit;
		}
		if(!$data['cid']){
			//$this->error('logo不能为空');
			echo '微吧分类不能为空';
			exit;
		}
		if(strlen($data['intro']) == 0){
			//$this->error('简介不能为空');
			echo '简介不能为空';
			exit;
		}
		$res = D('weiba')->where('weiba_id='.$weiba_id)->save($data);
		if($res !== false){
			D('log')->writeLog($weiba_id,$this->mid,'修改了微吧基本信息','setting');
			//$this->success('保存成功');
			exit('1');
		}else{
			//$this->error('保存失败');
			exit('保存失败');
		}
	}

	/**
	 * 微吧成员管理
	 * @return void
	 */
	public function member(){
		$weiba_id = intval($_GET['weiba_id']);
		if($_GET['type'] == 'apply'){
			if(!CheckPermission('core_admin','admin_login')){
				if(D('weiba_follow')->where('follower_uid='.$this->mid.' and weiba_id='.intval($_REQUEST['weiba_id']))->getField('level')<3){
					$this->error('您没有访问权限');
				}
			}
			$weiba_member = D('weiba_apply')->where('status=0 AND weiba_id='.$weiba_id)->findPage(20);
			$this->assign('on','apply');
		}else{
			$weiba_member = D('weiba_follow')->where('weiba_id='.$weiba_id)->order('level desc')->findPage(20);
			$this->assign('on','all');
		}
		foreach($weiba_member['data'] as $k=>$v){
			// 获取用户用户组信息
			$userGids = model('UserGroupLink')->getUserGroup($v['follower_uid']);
			$userGroupData = model('UserGroup')->getUserGroupByGids($userGids[$v['follower_uid']]);
			foreach($userGroupData as $key => $value) {
				if($value['user_group_icon'] == -1) {
					unset($userGroupData[$key]);
					continue;
				}
				$userGroupData[$key]['user_group_icon_url'] = THEME_PUBLIC_URL.'/image/usergroup/'.$value['user_group_icon'];
			}
			$weiba_member['data'][$k]['userGroupData'] = $userGroupData;
		}
		$uids = getSubByKey($weiba_member['data'], 'follower_uid');	
		$user_info = model('User')->getUserInfoByUids($uids);
		$this->assign('user_info', $user_info);
		$this->assign('weiba_member', $weiba_member);

		$weiba_detail = D('weiba')->where('weiba_id='.$weiba_id)->find();
		$this->setTitle( '成员管理 '.$weiba_detail['weiba_name'] );
		$this->setKeywords( '成员管理 '.$weiba_detail['weiba_name'] );
		$this->display();
	}

	/**
	 * 设置微吧成员等级
	 * @return void
	 */
	public function editLevel(){
		$map['weiba_id'] = intval($_POST['weiba_id']);
		$map['follower_uid'] = intval($_POST['follower_uid']);
		$targetLevel = intval($_POST['targetLevel']);
		if($targetLevel == 3){
			if(D('weiba_follow')->where('level=3 AND weiba_id='.$map['weiba_id'])->find()){
				$return['status'] = 0;
				$return['data'] = '只能设置一个吧主';
				echo json_encode($return);exit();
			}
		}
		$result = D('weiba_follow')->where($map)->setField('level',$targetLevel);
		if(!$result){
			$return['status'] = 0;
			$return['data'] = '设置失败';
		}else{
			D('weiba_apply')->where($map)->delete();
			$user_info = model('User')->getUserInfoByUids(array($map['follower_uid']));
			switch ($targetLevel) {
				case '1':
					$content = '将用户'.$user_info[$map['follower_uid']]['space_link'].'设为普通成员';
					D('log')->writeLog($map['weiba_id'],$this->mid,$content,'member');

					//添加积分
					model('Credit')->setUserCredit(intval($_POST['follower_uid']),'unappointed_weiba');
					
					break;
				case '2':
					$content = '将用户'.$user_info[$map['follower_uid']]['space_link'].'设为小吧';
					D('log')->writeLog($map['weiba_id'],$this->mid,$content,'member');
					break;
				case '3':
					$content = '将用户'.$user_info[$map['follower_uid']]['space_link'].'设为吧主';
					D('log')->writeLog($map['weiba_id'],$this->mid,$content,'member');

					//添加积分
					model('Credit')->setUserCredit(intval($_POST['follower_uid']),'appointed_weiba');

					break;
			}
			$return['status'] = 1;
			$return['data']   = '设置成功';
		}
		echo json_encode($return);exit();
	}

	/**
	 * 移出成员
	 * @return void
	 */
	public function moveOut(){
		!is_array($_POST['follower_uid']) && $_POST['follower_uid'] = array($_POST['follower_uid']);
		$map['weiba_id'] = intval($_POST['weiba_id']);
		$followerUid = array_map('intval', $_POST['follower_uid']);
		$map['follower_uid'] = array('in',$followerUid);
		$result = D('weiba_follow')->where($map)->delete();
		if(!$result){
			$return['status'] = 0;
			$return['data'] = '移出失败';
		}else{
			D('weiba_apply')->where($map)->delete();
			$user_info = model('User')->getUserInfoByUids(array($map['follower_uid']));
			$content = '将用户'.$user_info[$map['follower_uid']]['space_link'].'移出微吧';
			D('log')->writeLog($map['weiba_id'],$this->mid,$content,'member');
			D('weiba')->where('weiba_id='.$map['weiba_id'])->setDec('follower_count','',count($_POST['follower_uid']));
			$return['status'] = 1;
			$return['data']   = '移出成功';
		}
		echo json_encode($return);exit();
	}

	/**
	 * 处理用户申请吧主或小吧
	 */
	public function verify(){
		$map['weiba_id'] = intval($_POST['weiba_id']);
		$map['follower_uid'] = $_POST['uid'];
		$value = intval($_POST['value']);
		$weiba = D('weiba')->where('weiba_id='.$map['weiba_id'])->find();
		$actor = model('User')->getUserInfo($this->mid);
		$config['name'] = $actor['space_link'];
		$config['weiba_name'] = $weiba['weiba_name'];
		$config['source_url'] = U('weiba/Index/detail',array('weiba_id'=>$map['weiba_id']));
		if($value != -1){
			if($value==3){
				if(D('weiba_follow')->where('level=3 AND weiba_id='.$map['weiba_id'])->find()){
					$return['status'] = 0;
					$return['data'] = '只能设置一个吧主';
					echo json_encode($return);exit();
				}
			}
			$res = D('weiba_follow')->where($map)->setField('level',$value);
			if($res){
				if($value==3){
					D('weiba')->where('weiba_id='.$map['weiba_id'])->setField('admin_uid',$_POST['uid']);
				}
				D('weiba_apply')->where($map)->delete();
				model('Notify')->sendNotify($_POST['uid'], 'weiba_apply_ok', $config); 
				$return['status'] = 1;
				$return['data']   = '操作成功';
			}else{
				$return['status'] = 0;
				$return['data']   = '操作失败';
			}
		}else{
			D('weiba_apply')->where($map)->delete();
			model('Notify')->sendNotify($_POST['uid'], 'weiba_apply_reject', $config); 
			$return['status'] = 1;
			$return['data'] = '驳回成功';
		}
		echo json_encode($return);exit();
	}

	/**
	 * 公告发布
	 * @return void
	 */
	public function notify(){
		$weiba_id = intval($_GET['weiba_id']);
		$this->assign('weiba_id',$weiba_id);
		$notify = D('weiba')->where('weiba_id='.$weiba_id)->getField('notify');
		$this->assign('notify',$notify);

		$weiba_detail = D('weiba')->where('weiba_id='.$weiba_id)->find();
		$this->setTitle( '公告发布 '.$weiba_detail['weiba_name'] );
		$this->setKeywords( '公告发布 '.$weiba_detail['weiba_name'] );
		$this->display();
	}

	/**
	 * 修改公告
	 * @return void
	 */
	public function doNotify(){
		$weiba_id = intval($_POST['weiba_id']);
		$notify = t($_POST['notify']);
/*		if(strlen($notify) == 0){
			$this->error('公告内容不能为空');
		}*/
		preg_match_all('/./us', $notify, $match);  
        if(count($match[0])>200){     //汉字和字母都为一个字
        	$this->error('公告内容不能超过200个字');
        } 
        $data['notify'] = $notify;
		$res = D('weiba')->where('weiba_id='.$weiba_id)->save($data);
		if($res !== false){
			D('log')->writeLog($weiba_id,$this->mid,'发布了公告','notify');
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}

	/**
	 * 微吧管理日志
	 * @return void
	 */
	public function log(){
		$map['weiba_id']  = intval($_GET['weiba_id']);
	 	if ($_GET['type']) $map['type'] = $_GET['type'];
	 	$log_list = D('log')->where($map)->order('ctime DESC')->findPage(20);
	 	$uids = array_unique(getSubByKey($log_list['data'],'uid'));
	 	$user_info = model('User')->getUserInfoByUids($uids);
		$this->assign('user_info', $user_info);
	 	$this->assign('on', $_GET['type'] ? $_GET['type'] : 'all');
	 	$this->assign('logList', $log_list);

	 	$weiba_detail = D('weiba')->where('weiba_id='.$map['weiba_id'])->find();
		$this->setTitle( '管理日志 '.$weiba_detail['weiba_name'] );
		$this->setKeywords( '管理日志 '.$weiba_detail['weiba_name'] );
		$this->display();
	}

	/**
	 * 解散微吧
	 * @return array 操作成功状态和提示信息
	 */
	public function delWeiba(){
		if(empty($_POST['weiba_id'])){
			echo -1;
			exit;
		}
		!is_array($_POST['weiba_id']) && $_POST['weiba_id'] = array($_POST['weiba_id']);
		$weiboId = array_map('intval', $_POST['weiba_id']);
		$data['weiba_id'] = array('in', $weiboId);
		$result = D('weiba')->where($data)->setField('is_del',1);
		if($result){
			// D('weiba_post')->where('weiba_id='.$weiba_id)->delete();
			// D('weiba_reply')->where('weiba_id='.$weiba_id)->delete();
			// D('weiba_follow')->where('weiba_id='.$weiba_id)->delete();
			// D('weiba_log')->where('weiba_id='.$weiba_id)->delete();
			echo 1;exit;
		}else{
			echo 0;exit;
		}
	}
}