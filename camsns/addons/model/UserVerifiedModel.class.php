<?php
/**
 * 用户认证模型 - 数据对象模型
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class UserVerifiedModel extends Model {

	protected $tableName = 'user_verified';
	protected $fields = array('id', 'uid', 'usergroup_id', 'user_verified_category_id', 'company', 'realname', 'idcard', 'phone', 'info', 'verified', 'attach_id');

	/**
	 * 获取指定用户的认证信息
	 * @param array $uids 用户ID
	 * @return array 指定用户的认证信息
	 */
	public function getUserVerifiedInfo($uids) {
		if(empty($uids)) {
			return array();
		}
		$map['uid'] = array('IN', $uids);
		$map['verified'] = 1;
		$data = $this->where($map)->getHashList('uid', 'info');
		return $data;
	}
}