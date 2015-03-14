<?php
/**
 * ProtocolModel
 * 提供给Ts核心调用的协议类
 */
class PhotoProtocolModel extends Model {

	// 假删除用户数据
	public function deleteUserAppData ($uidArr) {
	}

	// 恢复假删除的用户数据
	public function rebackUserAppData ($uidArr) {
	}

	// 彻底删除用户数据
	public function trueDeleteUserAppData ($uidArr) {
	}

	// 获取评论内容
	public function getSourceInfo ($row_id, $_forApi) {
	}

	/**
	 * 在个人空间里查看该应用的内容列表
	 * @param integer $uid 用户UID
	 * @return array 个人空间数据列表
	 */
	public function profileContent ($uid) {
		$map['userId'] = $uid;
		$map['is_del'] = 0;
		$list = D('photo')->where($map)->order('cTime DESC')->findPage(20);
		$listIds = getSubByKey($list['data'], 'albumId');
		$albumMap['id'] = array('IN', $listIds);
		$albumHash = D('photo_album')->where($albumMap)->getHashList('id', 'name');
		foreach ($list['data'] as &$value) {
			$value['album'] = $albumHash[$value['albumId']];
		}
		$list['isMe'] = ($uid == $GLOBALS['ts']['mid']) ? true : false;

		$tpl = APPS_PATH.'/photo/Tpl/default/Index/profileContent.html';
		return fetch($tpl, $list);
	}
}
