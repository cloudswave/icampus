<?php
/**
     * ***ProtocolModel 
     * 提供给TS核心调用的协议类
     *
     */
class GiftProtocolModel extends Model {
	/**
	 * 假删除用户数据
	 * 开发者把传递过来的UID的用户生成的内容设置为假删除状态（如果有此状态的话）
	 *
	 * @access public
	 * @param array $uidArr
	 *        	以数组传递的用户UID值
	 * @return viod
	 */
	function deleteUserAppData($uidArr) {
	}
	/**
	 * 恢复假删除的用户数据
	 * 开发者把传递过来的UID的用户生成的内容去掉假删除状态（如果有此状态的话）
	 *
	 * @access public
	 * @param array $uidArr
	 *        	以数组传递的用户UID值
	 * @return viod
	 */
	function rebackUserAppData($uidArr) {
	}
	/**
	 * 彻底删除用户数据
	 * 开发者把传递过来的UID的用户生成的内容全部删除掉
	 *
	 * @access public
	 * @param array $uidArr
	 *        	以数组传递的用户UID值
	 * @return viod
	 */
	function trueDeleteUserAppData($uidArr) {
		if (empty ( $uidArr ))
			return false;
		
		$uidStr = implode ( ',', $uidArr );
		
		M ( 'gift_user' )->where ( "fromUserId in ($uidStr) or toUserId in ($uidStr)" )->delete ();
	}
	/**
	 * 在评论列表中获取应用资源的内容
	 * 开发者把传递过来的UID的用户生成的内容全部删除掉
	 *
	 * @access public
	 * @param array $uidArr
	 *        	以数组传递的用户UID值
	 * @return viod
	 */
	function getSourceInfo($row_id, $_forApi) {
/* 		
		$blog = D ( 'blog' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
		$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $blog ['uid'] );
		$info ['source_url'] = U ( 'blog/Index/show', array (
				'id' => $row_id,
				'mid' => $blog ['uid'] 
		) );
		$info ['source_body'] = $blog ['title'] . '<a class="ico-details" href="' . U ( 'blog/Index/show', array (
				'id' => $row_id,
				'mid' => $blog ['uid'] 
		) ) . '"></a>';
		 */
		return $info;
	}
}
