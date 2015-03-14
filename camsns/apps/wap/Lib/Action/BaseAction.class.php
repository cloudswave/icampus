<?php
class BaseAction extends Action {
	// 站点名称
	protected $_title;
	// 分页使用
	protected $_page;
	protected $_item_count;
	// 来源类型
	protected $_from_type;
	protected $_type_wap;
	// 关注状态
	protected $_follow_status;
	// 当前URL
	protected $_self_url;

	public function _initialize() {
		// 登录验证
		$passport = model('Passport');
		if (!$passport->isLogged()) {
			redirect(U('wap/Public/login'));
		}

		global $ts;

		// 站点名称
		$this->_title  = $ts['site']['site_name'] . ' WAP版';
		$this->assign('site_name', $this->_title);

		// 分页
		$_GET['page']  = $_POST['page'] ? intval($_POST['page']) : intval($_GET['page']);
		$this->_page   = $_GET['page'] > 0 ? $_GET['page'] : 1;
		$this->assign('page', $this->_page);
		$this->_item_count = 20;
		$this->assign('item_count', $this->_item_count);

		// 来源类型
		$this->_type_wap  = 1;
		$this->_from_type = array('0'=>'网站','1'=>'手机网页版','2'=>'Android客户端','3'=>'iPhone客户端');
		$this->assign('from_type', $this->_from_type);

		// 关注状态
		$this->_follow_status = array('eachfollow'=>'相互关注','havefollow'=>'已关注','unfollow'=>'未关注');
		$this->assign('follow_status', $this->_follow_status);

		// 当前URL
		$this->_self_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (isset($_POST['key'])) {
			$this->_self_url .= "&key={$_POST['key']}";
			$this->_self_url .= isset($_POST['user']) ? '&user=1' : '&weibo=1';
		}
		$this->assign('self_url', $this->_self_url);

		// 是否为owner
		$_GET['uid'] = intval($_GET['uid']);
		$this->assign('is_owner', ($_GET['uid']==0 || $_GET['uid']==$ts['user']['uid']) ? '1' : '0');

		// 获取新通知
		//$counts = X ( 'Notify' )->getCount ( $this->mid );
		//$this->assign('news',$counts);
		return ;
	}
}