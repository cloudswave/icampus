<?php
/**
 * 俱乐部控制器
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class IndexAction extends Action {

	/**
	 * 俱乐部首页
	 */
	public function index() {
		// 获取所有分类ID
		$allUserCategoryIds = model('UserCategory')->getAllUserCategoryIds();

		// 获取分类的信息
		$cid = intval($_GET['cid']);
		!in_array($cid, $allUserCategoryIds) && $cid = 0;
		$this->assign('cid', $cid);
		// 获取是否筛选认证用户
/*		$authenticate = intval($_GET['authenticate']);
		$this->assign('authenticate', $authenticate);*/
		// 获取分类下的用户信息
		$userInfo = model('UserCategory')->getUidsByCid($cid, $authenticate);
		$uids = getSubByKey($userInfo['data'], 'uid');
		// 获取用户基本信息
		$data = model('User')->getUserInfoByUids($uids);
		// 获取用户统计信息
		$userData = model('UserData')->getUserDataByUids($uids);
		// 获取用户简介信息，获取认证信息
		$userVerified = model('UserVerified')->getUserVerifiedInfo($uids);
		$verifiedUids = array_keys($userVerified);
		// 获取关注状态
		$userState = model("Follow")->getFollowStateByFids($this->mid, $uids);
		// 组装用户信息
		foreach($userInfo['data'] as $key => $value) {
			$userInfo['data'][$key] = array_merge($userInfo['data'][$key], $data[$value['uid']]);
			$userInfo['data'][$key] = array_merge($userInfo['data'][$key], $userData[$value['uid']]);
			if(in_array($value['uid'], $verifiedUids)) {
				$userInfo['data'][$key]['intro'] = $userVerified[$value['uid']];
			}
			$userInfo['data'][$key]['followState'] = $userState[$value['uid']];
			$userInfo['data'][$key]['GroupData'] = model('UserGroupLink')->getUserGroupData($value['uid']);   //获取用户组信息
		}
		$this->assign($userInfo);

		$this->setTitle( '群英汇' );
		$this->setKeywords( '群英汇' );
		$cate = implode(',',getSubByKey(model('UserCategory')->getNetworkList(),'title'));
		$this->setDescription( $cate );
		
		$this->display();
	}
}