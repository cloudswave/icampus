<?php
/**
 * ProtocolModel
 * 提供给Ts核心调用的协议类
 */
class BlogProtocolModel extends Model {

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
		$this->_deal($uidArr, 'trueDeleteUserAppData');
	}

	// 获取评论内容
	public function getSourceInfo ($row_id, $_forApi) {
/* 		$blog = D ( 'blog' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
		$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $blog ['uid'] );
		$info ['source_url'] = U ( 'blog/Index/show', array ( 'id' => $row_id, 'mid' => $blog ['uid'] ) );
		$info ['source_body'] = $blog ['title'] . '<a class="ico-details" href="' . U ( 'blog/Index/show', array ( 'id' => $row_id, 'mid' => $blog ['uid'] ) ) . '"></a>';
 */		
		return $info;
	}

	/**
	 * 在个人空间里查看该应用的内容列表
	 * @param integer $uid 用户UID
	 * @return array 个人空间数据列表
	 */
	public function profileContent ($uid) {
		$map['uid'] = $uid;
		$map['status'] = 1;
		$list = M('blog')->where($map)->order('cTime DESC')->findPage(20);
		foreach ($list['data'] as $k => $v) {
			if (empty($v['category_title']) && !empty($v['category'])) {
				$list['data'][$k]['category_title'] = M('blog_category')->where('id='.$v['category'])->getField('name');
			}
		}
		$list['titleshort'] = 200;
		$list['suffix'] = '......';			

		$tpl = APPS_PATH.'/blog/Tpl/default/Index/profileContent.html';
		return fetch($tpl,$list);
	}
	
	// 共同处理方法
	private function _deal ($uidArr, $type) {
		if (empty($uidArr)) {
			return false;
		}
		$map['uid'] = array('IN', $uidArr);
		switch ($type) {
			case 'deleteUserAppData':
				$res = M('Blog')->where($map)->setField('status', 2);
				break;
			case 'rebackUserAppData':
				$res = M('Blog')->where($map)->setField('status', 1);
				break;
			case 'trueDeleteUserAppData':
				$res = M('Blog')->where($map)->delete();
				break;
		}
		
		return $res;
	}
}
