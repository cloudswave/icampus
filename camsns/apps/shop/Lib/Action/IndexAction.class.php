<?php
/**
 * 微吧控制器
 * @author 
 * @version TS3.0
 */
class IndexAction extends Action {

	public function _initialize()
	{
		$this->appCssList[] = 'shop.css';
	}
	
	public function index() {
		//微吧推荐

		$this->setTitle( '积分商城首页' );
		$this->setKeywords( '积分商城首页' );
		$shop_list = D('shop')->where('endtime>'.time())->order('sid DESC')->findPage(100);
		foreach($shop_list['data'] as $k=>$v){
			$logo = D('attach')->where('attach_id='.$v['shop_ico'])->find();
			$shop_list['data'][$k]['shop_ico'] = UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'];
			$shop_list['data'][$k]['credit_type'] = D('shop')->get_credit_type($v['credit_type']);
		}
		$this->assign('shop_list',$shop_list);
		$this->display();
	}

	public function shop_item(){
		$sid = $_GET['sid'];
		$shop_data = D('shop')->where('sid='.$sid)->find();
		if($shop_data['shop_ico']){
			$logo = D('attach')->where('attach_id='.$shop_data['shop_ico'])->find();
			$shop_data['shop_ico'] = UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'];
			$shop_data['credit_type'] = D('shop')->get_credit_type($shop_data['credit_type']);
		}
		$shop_userlist = D('shop_convert')->where('sid='.$sid)->order('dateline DESC')->findAll();
		$shop_user = array();
		$convert_num = 0;
		foreach($shop_userlist as $k=>$v){
			$flag = 0;
			foreach($shop_user as $b=>$a){
				if($a['uid']==$v['uid']){
					$flag = 1;
					break;
				}
			}
			if($flag!=1){
				$convert_num++;
				if(count($shop_user)<=30){
					$shop_user[$k]['uid'] = $v['uid'];
					$shop_user[$k]['avatar'] = model('Avatar')->init($v['uid'])->getUserAvatar();
					$userinfo = D('user')->where('uid='.$v['uid'])->find();
					$shop_user[$k]['uname'] = $userinfo['uname'];
					$friend = D('user_follow')->where('uid='.$this->mid.' AND fid='.$v['uid'])->find();
					if(is_array($friend)){
						$shop_user[$k]['fstatus'] = 1;
					}else{
						$shop_user[$k]['fstatus'] = 0;
					}
				}
			}
		}
		$other_shop = D('shop')->order('people DESC')->findPage(8);
		foreach($other_shop['data'] as $k=>$v){
			$logo = D('attach')->where('attach_id='.$v['shop_ico'])->find();
			$other_shop['data'][$k]['shop_ico'] = UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'];
		}
		$hot_convert = D('shop_user')->order('connum DESC')->findPage(8);
		foreach($hot_convert['data'] as $k=>$v){
			$userinfo = D('user')->where('uid='.$v['uid'])->find();
			$hot_convert['data'][$k]['uinfo']['uname'] = $userinfo['uname'];
			$hot_convert['data'][$k]['uinfo']['avatar'] = model('Avatar')->init($v['uid'])->getUserAvatar();
		}
		$this->assign('hot_convert',$hot_convert);
		$this->assign('other_shop',$other_shop);
		$this->assign('convert_num',$convert_num);
		$this->assign('shop_userlist',$shop_userlist);
		$this->assign('shop_user',$shop_user);
		$this->assign('shop_data',$shop_data);
		$this->assign('nav','shop_item');
		$this->display();
	}
	
	public function checkuserinfo(){
		$uid = $_POST['uid'];
		$sid = $_POST['sid'];
		$shop_num = $_POST['num'];
		$credit = D('shop')->where('sid='.$sid)->find();
		if($credit['shop_num']>0){
			$sumcredit = $credit['credit']*$shop_num;
			$usercredit = D('credit_user')->where('uid='.$uid)->find();
			if($usercredit[$credit['credit_type']]>=$sumcredit){
				$data['status'] = 1;
			}else{
				$data['status'] = 0;
			}
		}else{
			$data['status'] = 2;
		}
		exit(json_encode($data));
	}
	
	public function convert(){
		$uid = $_POST['uid'];
		$sid = $_POST['sid'];
		$shop_num = $_POST['num'];
		$usercredit = D('shop')->where('sid='.$sid)->find();
		$sumcredit = $usercredit['credit']*$shop_num;
		$credit = D('credit_user')->setDec($usercredit['credit_type'],'uid='.$uid,$sumcredit);
		$map_shop['people'] = $usercredit['people']+1;
		$shop = D('shop')->where('sid='.$sid)->save($map_shop);
		$map_convert['uid'] = $uid;
		$map_convert['sid'] = $sid;
		$map_convert['dateline'] = time();
		$map_convert['shop_num'] = $shop_num;
		$shop_num = D('shop')->setDec('shop_num');
		$convert = D('shop_convert')->add($map_convert);
		$shop_user = D('shop_user')->where('uid='.$uid)->find();
		if(empty($shop_user)){
			$map_user['uid'] = $uid;
			$map_user['connum'] = 1;
			$map_user['usercre'] = $sumcredit;
			$user = D('shop_user')->where('uid='.$uid)->add($map_user);
		}else{
			$map_user['connum'] = $shop_user['connum']+1;
			$map_user['usercre'] = $shop_user['usercre']+$sumcredit;
			$user = D('shop_user')->where('uid='.$uid)->save($map_user);
		}
		if($credit && $shop && $convert && $user && $shop_num){
			$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		exit(json_encode($data));
	}
	
	public function myshop(){
		$uid = $this->mid;
		$myshop = D('shop_convert')->where('uid='.$uid)->order('dateline DESC')->findPage(10);
		foreach($myshop['data'] as $k=>$v){
			$shop_name = D('shop')->where('sid='.$v['sid'])->find();
			$myshop['data'][$k]['shop_name'] = $shop_name['shop_name'];
			$myshop['data'][$k]['credit'] = $shop_name['credit'] * $v['shop_num'];
			switch($v['get']){
				case 0:$myshop['data'][$k]['get'] = "<span style='color:#F00'>未领取</span>";break;
				case 1:$myshop['data'][$k]['get'] = "已领取";break;
			}
		}
		$this->assign('nav','myshop');
		$this->assign('myshop',$myshop);
		$this->display();
	}
}