 <?php
/**
 * 后台，用户管理控制器
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAction extends AdministratorAction {

	public $pageTitle = array();
	
	/**
	 * 初始化，初始化页面表头信息，用于双语
	 */
	public function _initialize() {
		$this->pageTitle['index'] = '商品列表';
		$this->pageTitle['addshop'] = '添加商品';
		$this->pageTitle['convert'] = '兑换记录';
		$this->pageTitle['update'] = '升级软件';
		parent::_initialize();
	}

	/**
	 * 微吧列表
	 * @return void
	 */
	public function index() {
		// 初始化微吧列表管理菜单
		$this->_initWeibaListAdminMenu();
		// 设置列表主键
		$this->_listpk = 'sid';
		//$this->searchKey = array('weiba_id','weiba_name','uid','admin_uid','recommend');
		$this->pageKeyList = array('sid','shop_name','shop_ico','shop_num','credit_type','credit','people','endtime','DOACTION');
		// 数据的格式化与listKey保持一致
		$listData = D('shop')->getWeibaList(20);
		$this->displayList($listData);
	}
	
	public function convert(){
		$this->_initWeibaListAdminMenu();
		$this->pageKeyList = array('cid','uid','sid','dateline','shop_num','get','setget');
		$listData = D('shop_convert')->findPage(20);
		foreach($listData['data'] as $k => $v) {
			$username = D('user')->where('uid='.$listData['data'][$k]['uid'])->find();
			$listData['data'][$k]['uid'] = $username['uname'];
			$listData['data'][$k]['dateline'] = date('Y-m-d h:i:s',$listData['data'][$k]['dateline']);
			switch($v['get']){
				case 0:$listData['data'][$k]['get'] = '未领取';break;
				case 1:$listData['data'][$k]['get'] = '已领取';break;
			}
			$listData['data'][$k]['setget'] = "<a onclick='admin.setget(".$v['cid'].")'>领取</a>";
		}
		$this->displayList($listData);
	}
	
	public function setget(){
		$data['get'] = 1;
		$cid = $_POST['cid'];
		$setstatus = D('shop_convert')->where('cid='.$cid)->save($data);
		if($setstatus){
			$data['status'] = 1;
			$data['data'] = "领取成功";
		}
		exit(json_encode($data));
	}

	public function addshop() {
		// 初始化微吧列表管理菜单
		$this->_initWeibaListAdminMenu();
        // 列表key值 DOACTION表示操作
		$credit_type = D('credit_type')->findAll();
		//var_dump($credit_type);
		foreach($credit_type as $k => $v){
			$this->opt['credit_type'][$v['name']] = $v['alias'];
		}
		$this->pageKeyList = array('shop_name','shop_ico','shop_num','use_cont','credit_type','credit','endtime');
		$this->savePostUrl = U('shop/Admin/doAddshop');
        //$this->onsubmit = 'admin.checkAddWeiba(this)';
		$this->displayConfig();
	}
	
	public function doAddshop() {
		$data['shop_name'] = t($_POST['shop_name']);
		$data['shop_ico'] = t($_POST['shop_ico']);
		$data['shop_num'] = t($_POST['shop_num']);
		$data['use_cont'] = $_POST['use_cont'];
		$data['credit_type'] = $_POST['credit_type'];
		$data['credit'] = $_POST['credit'];
		$data['endtime'] = strtotime($_POST['endtime']);
		$res = D('shop')->add($data);
		$this->assign('jumpUrl', U('shop/Admin/index'));
		$this->success(L('PUBLIC_ADD_SUCCESS'));
	}
	
	public function editshop() {
		$this->assign('pageTitle','编辑商品');
		// 初始化微吧列表管理菜单
		$this->pageTab[] = array('title'=>'商品列表','tabHash'=>'index','url'=>U('shop/Admin/index'));
		$this->pageTab[] = array('title'=>'添加商品','tabHash'=>'addshop','url'=>U('shop/Admin/addshop'));
		$this->pageTab[] = array('title'=>'兑换记录','tabHash'=>'postList','url'=>U('shop/Admin/postList'));
        // 列表key值 DOACTION表示操作
		$credit_type = D('credit_type')->findAll();
		foreach($credit_type as $k => $v){
			$this->opt['credit_type'][$v['name']] = $v['alias'];
		}
		$this->pageKeyList = array('sid','shop_name','shop_ico','shop_num','use_cont','credit_type','credit','endtime');
		$weiba_id = intval($_GET['sid']);
		$data = D('shop')->getshopById($weiba_id);
		// 表单URL设置
		$this->savePostUrl = U('shop/Admin/doEditshop');
        //$this->notEmpty = array('shop_name','logo','use_cont');
        //$this->onsubmit = 'admin.checkAddWeiba(this)';
		$this->displayConfig($data);
	}
	
	public function doEditshop(){
		$weiba_id = intval($_POST['sid']);
		$data['shop_name'] = t($_POST['shop_name']);
		//$data['uid'] = $this->mid;
		$data['shop_ico'] = t($_POST['shop_ico']);
		$data['shop_num'] = t($_POST['shop_num']);
		$data['use_cont'] = $_POST['use_cont'];
		$data['credit_type'] = t($_POST['credit_type']);
		$data['credit'] = t($_POST['credit']);
		$data['endtime'] = strtotime($_POST['endtime']);
		$res = D('shop')->where('sid='.$weiba_id)->save($data);
		$this->assign('jumpUrl', U('shop/Admin/index'));
		$this->success(L('PUBLIC_SYSTEM_MODIFY_SUCCESS'));
	}

	public function delshop(){
		$sid = $_POST['sid'];
		$result = D('shop')->where('sid='.$sid)->delete();
			$return['status'] = 1;
			$return['data']   = L('PUBLIC_ADMIN_OPRETING_SUCCESS');
		echo json_encode($return);exit();
	}
	
	
	public function update() {
		// 初始化微吧列表管理菜单
		$this->_initWeibaListAdminMenu();
		$this->opt['doupdate'] = array('1'=>'是','0'=>'否');
		$this->pageKeyList = array('doupdate');
		$this->savePostUrl = U('shop/Admin/doupdate');
		$this->displayConfig();
	}
	
	public function doupdate(){
		if($_POST['doupdate']==1){
			header('Content-Type: text/html; charset=utf-8');
			$sql_file  = APPS_PATH.'/shop/Appinfo/update.sql';
			//执行sql文件
			$res = D('')->executeSqlFile($sql_file);
			if(!empty($res)){
				$this->error('升级失败');
			}else{
				$this->success('升级成功');
			}
		}else{
			$this->success('取消升级成功');
		}
	}
		
	/**
	 * 微吧后台管理菜单
	 * @return void
	 */
	private function _initWeibaListAdminMenu(){
		$this->pageTab[] = array('title'=>'商品列表','tabHash'=>'index','url'=>U('shop/Admin/index'));
		$this->pageTab[] = array('title'=>'添加商品','tabHash'=>'addshop','url'=>U('shop/Admin/addshop'));
		$this->pageTab[] = array('title'=>'兑换记录','tabHash'=>'postList','url'=>U('shop/Admin/convert'));
		$this->pageTab[] = array('title'=>'升级软件','tabHash'=>'update','url'=>U('shop/Admin/update'));
	}
}