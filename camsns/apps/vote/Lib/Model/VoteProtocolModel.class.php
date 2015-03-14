<?php
/**
 * ProtocolModel
 * 提供给Ts核心调用的协议类
 */
class VoteProtocolModel extends Model {

	// 假删除用户数据
	public function deleteUserAppData ($uidArr) {
	}

	// 恢复假删除的用户数据
	public function rebackUserAppData ($uidArr) {
	}

	// 彻底删除用户数据
	public function trueDeleteUserAppData ($uidArr) {
		if (empty($uidArr)) {
			return false;
		}
		$map['uid'] = array('IN', $uidArr);
		M('vote')->where($map)->delete();
		M('vote_user')->where($map)->delete();
	}

	public function profileContent ($uid) {
		$voteDao = D('Vote', 'vote');
		$map['uid'] = $uid;
		$list = $voteDao->where($map)->order('id DESC')->findPage(20);
		// 选项
        $optDao = D('VoteOpt', 'vote');
        foreach ($list['data'] as $k => $v) {
            $opts = $optDao->where("vote_id = {$v['id']}")->order("id asc")->field("*")->limit('0,2')->findAll();
            $list['data'][$k]['opts'] = $opts;
            $list['data'][$k]['user_info'] = model('User')->getUserInfo($v['uid']);
        }
		
		$tpl = APPS_PATH . '/vote/Tpl/default/Index/profileContent.html';
		return fetch ( $tpl, $list );
	} 
}
