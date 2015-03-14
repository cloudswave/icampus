<?php
/**
     * ChannelProtocolModel 
     * 提供给TS核心调用的协议类
     *
     */
class ChannelProtocolModel extends Model {
	// 假删除用户数据
	function deleteUserAppData($uidArr) {
	}
	// 恢复假删除的用户数据
	function rebackUserAppData($uidArr) {
	}
	// 彻底删除用户数据
	function trueDeleteUserAppData($uidArr) {
		if (empty ( $uidArr ))
			return false;
		
		$map ['uid'] = array (
				'in',
				$uidArr
		);

		M('channel')->where($map)->delete();
		M('channel_follow')->where($map)->delete();
	}
}
