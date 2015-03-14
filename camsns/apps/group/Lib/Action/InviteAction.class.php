<?php
class InviteAction extends BaseAction {
	public function _initialize()
	{
		parent::_initialize();
		if (!$this->ismember || ($this->groupinfo['need_invite'] == 2 && !$this->isadmin)) {
			$this->error('你没有权限进行邀请');
		}
        $this->setTitle("邀请 - " . $this->groupinfo['name']);
	}

	// 群组创建的邀请
	public function create()
	{
		$from  = isset($_GET['from']) ? t($_GET['from']) : '';
		if($_POST['sendsubmit']) {

			$toUserIds = explode(",",$_POST["fri_ids"]);
			if( !$_POST["fri_ids"] ){
				$this->error('请选择您的好友！');exit();
			}
			foreach ($toUserIds as $k=>$v) {
				if (!$v) continue;
				if (D('Member')->where("gid={$this->gid} AND uid={$v}")->count() > 0) {
					unset($toUserIds[$k]);
					continue;
				}
				if ($this->isadmin) {
					$invite_verify_data['gid'] = $this->gid;
					$invite_verify_data['uid'] = $v;
					M('group_invite_verify')->add($invite_verify_data);
				}
			}

			$message_data['title']   = "邀您加入群组 {$this->groupinfo['name']}";
			$url = '<a href="' . U('group/Group/index', array('gid'=>$this->gid)).'" target="_blank">去看看</a>';
			$message_data['content'] = "你好，诚邀您加入“{$this->groupinfo['name']}” 群组，点击   " . $url . '进入。';
			foreach ($toUserIds as $t_u_k => $t_u_v) {
				$message_data['to'] 	 = $t_u_v;
	            $res = model('Message')->postMessage($message_data,  $this->mid);
	            if (!$res) {
					unset($toUserIds[$t_u_k]);
	            }
			}

            if (count($toUserIds) > 0) {
            	$this->assign('成功发送' + count($toUserIds) + '份邀请');
            	X('Credit')->setUserCredit($this->mid,'invite_friend');
            	$this->display('success');
            	//echo count($toUserIds);
            } else {
            	$this->assign('jumpUrl' , U('group/Invite/create',array('gid'=>$this->gid)));
            	$this->error('邀请失败,您的好友可能已经加入了该群组！');
            	//echo -1;
            }
			exit;
		}

		$this->assign('from',$from);
		$this->display();
	}
}