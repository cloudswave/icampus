<?php
/**
     * ***ProtocolModel 
     * 提供给TS核心调用的协议类
     *
     */
class EventProtocolModel extends Model {
	// 假删除用户数据
	function deleteUserAppData($uidArr) {
	}
	// 恢复假删除的用户数据
	function rebackUserAppData($uidArr) {
	}
	function test(){
		dump('event');
	}	
	// 彻底删除用户数据
	function trueDeleteUserAppData($uidArr) {
		if (empty ( $uidArr ))
			return false;
		
		$map ['uid'] = array (
				'in',
				$uidArr
		);

		M('event')->where($map)->delete();
		M('event_photo')->where($map)->delete();
		M('event_user')->where($map)->delete();
	}
	//在个人空间里查看该应用的内容列表
/* 	function profileContent($uid){
		$map ['uid'] = $uid;
		$map ['status'] = 1;
		$list = M ( 'blog' )->where ( $map )->order ( 'cTime DESC' )->findPage ( 10 );
		foreach ( $list ['data'] as $k => $v ) {
			if (empty ( $v ['category_title'] ) && ! empty ( $v ['category'] ))
				$list ['data'] [$k] ['category_title'] = M ( 'blog_category' )->where ( 'id=' . $v ['category'] )->getField ( 'name' );
		}
		
		$tpl = APPS_PATH . '/blog/Tpl/default/Index/profileContent.html';
		return fetch ( $tpl, $list );
	}	 */
}
