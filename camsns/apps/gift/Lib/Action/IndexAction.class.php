<?php
    /**
     * GiftAction
     * 礼物控制层
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2011 SamPeng 水上铁
     * @author SamPeng <sampeng87@gmail.com> 水上铁<wxm201411@163.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
class IndexAction extends Action{
	private $gift;             //礼品表模型
	private $gift_category;    //礼品类型表模型
	private $user_gift;        //用户送礼记录表模型
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
        //整个应用的赋值
        $this->gift = D('Gift');
		$this->gift_category = D('GiftCategory');
		$this->user_gift = D('UserGift');

		$this->user_gift->setGift($this->gift);
		$this->user_gift->setCategory($this->gift_category);		
		$this->gift_category->setGift($this->gift);

		global $ts;
		$this->app_alias = $ts['app']['app_alias'];

		// 获取礼品单位
		$config['creditName'] = '经验值';
		$this->assign('config', $config);
	}
	
	/**
	 * 礼物中心
	 *
	 */
	function index() {
		//获取分组好的礼物列表
		$giftList = $this->gift_category->GiftToCategory();
		$this->assign('categorys',$giftList);

		//获取当前用户的积分		
		$money = model('Credit')->getUserCredit($this->mid);
		$moneyType = gift_getConfig('credit');
		$this->assign('money',$money['credit'][$moneyType]);
		$this->setTitle("{$this->app_alias}中心");
		// 已选定用户
		$this->assign('uid',intval($_GET['uid']));
		$this->display();
	}

	/**
	 * 收到的礼物
	 *
	 */
	function receivebox(){
		//获取收到的礼物列表
		$gift = $this->user_gift->receiveList($this->mid);

		$type = gift_getConfig('credit');
		// 积分  经验
		$map['name'] = array('LIKE',$type);
		$name = M('credit_type')->where($map)->getField('alias');
		$this->assign('type',$name);
		$fromUids = getSubByKey($gift['data'],'fromUserId');
		$toUids = getSubByKey($gift['data'],'toUserId');
		$uids = array_unique(array_merge($fromUids,$toUids));
		$this->assign('user_info',model('User')->getUserInfoByUids($uids));
		$this->assign('gifts',$gift);
		$this->setTitle("收到的{$this->app_alias}");
		$this->display();
	}
	/**
	 * 送出的礼物
	 *
	 */
	function sendbox(){
		//获取送出的礼物列表
		$gift = $this->user_gift->sendList($this->mid);
		//判断是否有公开赠送信息，用于发微薄
		$tpldata = unserialize($_SESSION['gift_send_weibo']);
		if(isset($_SESSION['gift_send_weibo'])&&!empty($_SESSION['gift_send_weibo'])){
			$tpldata = unserialize($_SESSION['gift_send_weibo']);
			
			$str = '我在礼物中心赠送了礼物‘'.$tpldata['title'].'’给'.$tpldata['user'].' ,赶快来参与送礼';
			$this->assign('tpl_data',$str);
			unset($_SESSION['gift_send_weibo']);	
		}
		$type = gift_getConfig('credit');
		// 积分  经验
		$map['name'] = array('LIKE',$type);
		$name = M('credit_type')->where($map)->getField('alias');
		$this->assign('type',$name);
		$fromUids = getSubByKey($gift['data'],'fromUserId');
		$toUids = getSubByKey($gift['data'],'toUserId');
		$uids = array_unique(array_merge($fromUids,$toUids));
		$this->assign('user_info',model('User')->getUserInfoByUids($uids));
		$this->assign('gifts',$gift);
		$this->setTitle("送出的{$this->app_alias}");
		$this->display();
	}

	/**
	 * 某人的礼物
	 *
	 */	
	function personal(){
		//获取用户ID
		$uid = intval($_GET['uid']);
		if(empty($uid)){
			$this->error('非法操作！');
		}
		//获取收到的礼物列表	
		if ('send' == $_GET['box'])	{
			$gift = $this->user_gift->sendList($uid);
			$this->assign('on2','current');
		} else {
			$gift = $this->user_gift->receiveList($uid);
			$this->assign('on1','current');
		}		

		$this->assign('gifts',$gift);
		$this->display();		
	}
	/**
	 * 送出礼物
	 *
	 */
	function send(){
		//获取当前用户的ID和姓名
		$fromUid = $this->mid;
		//获取要发送的好友ID，如有不明可参考'好友选择widget'的说明
		$toUserId = $_POST['fri_ids'];
		if(empty($toUserId)){
			$this->error('你还没有选择好友');
			exit;
		}
		//获取附加信息
		$sendInfo['sendInfo'] = t($_POST['sendInfo']);
		//获取发送方式
		$sendInfo['sendWay']  = t($_POST['sendWay']);
		
		//获取礼品ID并用t函数过滤
		$giftId =  t($_POST['giftId']);
		//查询数据库获取礼品的全部信息
		$giftInfo = $this->gift->where('id='.$giftId)->find();
		if($giftInfo['status']!=1){
			$this->error('该礼物已被禁用');
		}
		//发送礼品
		$result = $this->user_gift->sendGift($toUserId,$fromUid,$sendInfo,$giftInfo);     

		if($result===true){
		    //如果发送成功就跳到‘送出的礼品’页面
			// $this->assign('jumpUrl',U('/Index/sendbox'));
			// global $ts;
			// $this->success($ts['app']['app_alias'].'赠送成功！');
			$res['jumpUrl'] = U('/Index/sendbox');
            $res['gift_name'] = $ts['app']['app_alias'];
            exit($this->ajaxReturn($res, $ts['app']['app_alias'].'赠送成功', 1));
		}else{
			//如果发送失败就跳转到错误提示页并显示失败原因
			$this->error($result);
		}
		
	}
}
