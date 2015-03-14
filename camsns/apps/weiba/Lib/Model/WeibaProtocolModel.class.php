<?php
/**
 * ProtocolModel
 * 提供给Ts核心调用的协议类
 */
class WeibaProtocolModel extends Model {

	// 假删除用户数据
	public function deleteUserAppData ($uidArr) {
		$this->_deal($uidArr, 'deleteUserAppData');
	}

	// 恢复假删除的用户数据
	public function rebackUserAppData ($uidArr) {
		$this->_deal($uidArr, 'rebackUserAppData');
	}

	// 彻底删除用户数据
	public function trueDeleteUserAppData ($uidArr) {
		if (empty($uidArr)) {
			return false;
		}
		$uidStr = implode(',', $uidArr);
		M('weiba')->where("uid in ($uidStr) or admin_uid in ($uidStr)")->delete();
		M('weiba_post')->where("post_uid in ($uidStr) or last_reply_uid in ($uidStr)")->delete();
		M('weiba_reply')->where("uid in ($uidStr) or post_uid in ($uidStr)")->delete();
		M('weiba_follow')->where("follower_uid in ($uidStr)" )->delete();
	}
	
	// 共同处理方法
	public function _deal ($uidArr, $type) {
		if (empty($uidArr)) {
			return false;
		}
		$uidStr = implode(',', $uidArr);
		$value = 0;
		if ($type == 'deleteUserAppData') {
			$value = 1;
		}
		M('weiba')->where("uid in ($uidStr) or admin_uid in ($uidStr)")->setField('is_del', $value);
		M('weiba_post')->where("post_uid in ($uidStr) or last_reply_uid in ($uidStr)")->setField('is_del', $value);
		M('weiba_reply')->where("uid in ($uidStr) or post_uid in ($uidStr)")->setField('is_del', $value);
		M('weiba_follow')->where("follower_uid in ($uidStr)")->setField('is_del', $value);
	}

	// 在个人空间里查看该应用的内容列表
 	public function profileContent($uid) {
 		$map['post_uid'] = $uid;
 		$map['is_del'] = 0;
 		$list = D('weiba_post')->where($map)->order('post_time DESC')->findPage(20);
 		$weibaIds = getSubByKey($list['data'], 'weiba_id');
 		$weibaMap['weiba_id'] = array('IN', $weibaIds);
 		$weibaHash = D('weiba')->where($weibaMap)->getHashList('weiba_id', 'weiba_name');
 		foreach ($list['data'] as $key => $value) {
 			$list['data'][$key]['weiba'] = $weibaHash[$value['weiba_id']];
 		}

		$tpl = APPS_PATH.'/weiba/Tpl/default/Index/profileContent.html';
		return fetch($tpl, $list);
	} 
}
