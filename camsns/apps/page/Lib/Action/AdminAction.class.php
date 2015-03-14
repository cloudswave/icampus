<?php
/**
 * DIY页面后台管理
 * @author Stream
 *
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAction extends AdministratorAction {
	
	function _initialize(){
		$this->pageTitle['index'] = '页面管理';
		$this->pageTitle['addPage'] = '添加页面';
		$this->pageTitle['editPage'] = '编辑页面';
		$this->pageTitle['canvas'] = '画布管理';
		$this->pageTitle['addCanvas'] = '添加画布';
		$this->pageTitle['editCanvas'] = '编辑画布';
		parent::_initialize();
	}
	/**
	 * 初始化用户列表管理菜单
	 * @param string $type 列表类型，index、pending、dellist
	 */
	private function _initPageListAdminMenu($type) {
		// tab选项
		$this->pageTab[] = array('title'=>'页面管理','tabHash'=>'index','url'=>U('page/Admin/index'));
		$this->pageTab[] = array('title'=>'添加页面','tabHash'=>'addPage','url'=>U('page/Admin/addPage'));
		$this->pageTab[] = array('title'=>'画布管理','tabHash'=>'canvas','url'=>U('page/Admin/canvas'));
		$this->pageTab[] = array('title'=>'添加画布','tabHash'=>'addCanvas','url'=>U('page/Admin/addCanvas'));
		switch(strtolower($type)) {
			case 'index':
				$this->pageKeyList = array('id', 'page_name','domain','canvas','manager','visit_count','DOACTION');
				break;
			case 'canvas':
				$this->pageKeyList = array('id','title','canvas_name','description','DOACTION');
				break;
		}
	
	}
	/**
	 * 页面列表
	 */
	function index(){
		
		$_REQUEST['tabHash'] = 'index';
		// 初始化diy页面列表管理菜单
		$this->_initPageListAdminMenu('index');
		// 数据的格式化与listKey保持一致
		$listData = $this->_getPageList('20');
		// 列表批量操作按钮
		$this->pageButton[] = array('title'=>'删除页面','onclick'=>"diy.deletePage()");
		$this->displayList($listData);
	}
	/**
	 * 添加diy页面
	 */
	function addPage(){
		// 初始化用户列表管理菜单
		$this->_initPageListAdminMenu();
		// 列表key值 DOACTION表示操作
		$this->pageKeyList = array('page_name','domain','canvas','status','guest','seo_title','seo_keywords','seo_description');
		// 字段选项配置
		$this->opt['status'] = array('1'=>'显示','2'=>'隐藏');
		$this->opt['guest'] = array('1'=>'可见','2'=>'不可见');
		
		$temp = $this->_getPageTempByDB();
		$canvas = array();
		foreach ( $temp as $t ){
			$canvas[$t['canvas_name']] = $t['canvas_name'];
		}
		$this->opt['canvas'] = $canvas;
		// 表单URL设置
		$this->savePostUrl = U('page/Admin/doAddPage');
		$this->notEmpty = array('page_name','domain');
		$this->onsubmit = 'diy.pageCheck(this)';
		
		//默认选中设置
		$defaultdata['status'] = 1;
		$defaultdata['guest'] = 1;
		$this->displayConfig($defaultdata);
	}
	/**
	 * 编辑页面
	 */
	function editPage(){
		$id = $_REQUEST['id'];
		// 初始化用户列表管理菜单
		$this->_initPageListAdminMenu();
		// 列表key值 DOACTION表示操作
		$this->pageKeyList = array('id','page_name','domain','canvas','status','guest','seo_title','seo_keywords','seo_description');
		// 字段选项配置
		$this->opt['status'] = array('1'=>'显示','2'=>'隐藏');
		$this->opt['guest'] = array('1'=>'可见','2'=>'不可见');
		
		$temp = $this->_getPageTempByDB();
		$canvas = array();
		foreach ( $temp as $t ){
			$canvas[$t['canvas_name']] = $t['canvas_name'];
		}
		$this->opt['canvas'] = $canvas;
		// 表单URL设置
		$this->savePostUrl = U('page/Admin/doEditPage');
		$this->notEmpty = array('page_name','domain');
		$this->onsubmit = 'diy.pageCheck(this)';
		
		//默认选中设置
		$map['id'] = $id;
		$defaultdata = model( 'Page' )->getPageInfo( $map );
		$this->displayConfig($defaultdata);
	}
	/**
	 * 保存页面修改到数据库
	 */
	function doEditpage(){
		$page = model ( 'Page' );
		$data = $page->create();
		$result = $page->savePage($data);
		if ( $result ) {
			$this->assign('jumpUrl', U('page/Admin/index'));
			$this->success('操作成功');
		} else {
			$this->error($page->getLastError());
		}
	}
	/**
	 * 添加diy页面至数据库
	 */
	function doAddPage(){
		$page = model ( 'Page' );
		$data = $page->create();
		$result = $page->addPage($data);
		if ( $result ) {
			$this->assign('jumpUrl', U('page/Admin/index'));
			$this->success(L('PUBLIC_ADD_SUCCESS'));
		} else {
			$this->error($page->getLastError());
		}
	}
	/**
	 * 删除页面
	 */
	function doDeletePage(){
		$page = model('Page');
		$id = $_POST['id'] ;
		if ( !is_array( $id ) ){
			$id = array($id);
		}
		$map['id'] = array ( 'in' , $id  );
		$result = $page->deletePage($map);
		echo $result;
	}
	/**
	 * 返回画布列表  -- 读数据库
	 * @param unknown_type $type
	 * @return unknown
	 */
	function _getPageTempByDB(){
		$list = D('diy_canvas')->field('canvas_name')->findAll();
		return $list;
	}
	/**
	 * 画布列表
	 */
	function canvas(){
		$_REQUEST['tabHash'] = 'canvas';
		// 初始化diy页面列表管理菜单
		$this->_initPageListAdminMenu('canvas');
		// 数据的格式化与listKey保持一致
		$listData = $this->_getCanvasList( 20 );
		// 列表批量操作按钮
// 		$this->pageButton[] = array('title'=>'删除画布','onclick'=>"diy.deleteCanvas()");
		$this->allSelected = false;
		$this->displayList($listData);
	}
	/**
	 * 添加画布
	 */
	function addCanvas(){
		// 初始化用户列表管理菜单
		$this->_initPageListAdminMenu();
		// 列表key值 DOACTION表示操作
		$this->pageKeyList = array('title','canvas_name','description','data',);
		
		// 表单URL设置
		$this->savePostUrl = U('page/Admin/doAddCanvas');
		$this->notEmpty = array( 'title','canvas_name','data');
		$this->onsubmit = 'diy.canvasCheck(this)';
		$this->displayConfig();
	}
	/**
	 * 添加画布至数据库
	 */
	function doAddCanvas(){
		$canvas = model ( 'Canvas' );
		$data = $canvas->create();
		$result = $canvas->addCanvas($data);
		if ( $result ) {
			$this->assign('jumpUrl', U('page/Admin/canvas'));
			$this->success(L('PUBLIC_ADD_SUCCESS'));
		} else {
			$this->error($canvas->getLastError());
		}
	}
	/**
	 * 画布修改
	 */
	function editCanvas(){
		// 初始化用户列表管理菜单
		$this->_initPageListAdminMenu();
		// 列表key值 DOACTION表示操作
		$this->pageKeyList = array('id','title','canvas_name','description','data',);
		
		// 表单URL设置
		$this->savePostUrl = U('page/Admin/doEditCanvas');
		$this->notEmpty = array( 'id','title','canvas_name','data');
		$this->onsubmit = 'diy.canvasCheck(this)';
		
		$map['id'] = $_REQUEST['id'];
		//默认值
		$defaultdata = model('Canvas')->getCanvasInfo($map);
		$this->displayConfig($defaultdata);
	}
	/**
	 * 修改画布内容到数据库
	 */
	function doEditCanvas(){
		$canvas = model ( 'Canvas' );
		$data = $canvas->create();
		$result = $canvas->saveCanvas($data);
		if ( $result ) {
			$this->assign('jumpUrl', U('page/Admin/canvas'));
			$this->success(L('保存成功'));
		} else {
			$this->error($canvas->getLastError());
		}
	}
	/**
	 * 删除画布数据
	 */
	function doDeleteCanvas(){
		$canvas = model('Canvas');
		$id = $_POST['id'] ;
		if ( !is_array($id) ){
			$id = array( $id );
		}
		$map['id'] = array ( 'in' , $id );
		$result = $canvas->deleteCanvas($map);
		echo $result;
	}
	/**
	 * 添加页面管理员
	 */
	function addManager(){
		$page = model ( 'Page' );
		$id = $_REQUEST['id'];
		
		$map['id'] = $id;
		$managers = $page->getManagers($map);
		$this->assign( 'managers' ,  $managers);
		$this->assign( 'pageid' , $id );
		$this->display();
	}
	/**
	 * 添加页面管理员至数据库
	 */
	function doAddManager(){
		$manager = t ( $_POST['manager'] );
		$id = intval ( $_POST['pageid'] );
		$result = model('Page')->setField( 'manager' , $manager , 'id='.$id );
		if ( $result ) {
			$this->assign('jumpUrl', U('page/Admin/index'));
			$this->success(L('PUBLIC_ADD_SUCCESS'));
		} else {
			$this->error( '添加失败' );
		}
	}
	/**
	 * 页面管理列
	 * @param int $limit
	 */
	private function _getPageList( $limit ){
		$list = model( 'Page' )->getPageList( $limit );
		$userDao = model( 'User' );
		foreach ( $list['data'] as &$v ){
			$unames = getSubByKey( $userDao->getUserInfoByUids( $v['manager']) , 'uname');
			$v['manager'] = implode( ',' , $unames );
			$v['page_name'] = '<a href="'.U('page/Index/index' , array('page' => $v['domain'])).'" target="_blank">'.$v['page_name'].'</a>';
			$v['DOACTION'] = '<a href="'.U('page/Admin/editPage' , array('id'=>$v['id'] , 'tabHash' => 'addPage') ).'">编辑</a>';
			$v['DOACTION'] .= ' <a href="#" onclick="diy.deletePage('.$v['id'].')">删除</a>';
			$v['DOACTION'] .= ' <a href="#" onclick="diy.addManager('.$v['id'].')">添加管理员</a>';
		}
		return $list;
	}
	/**
	 * 画布管理列
	 * @param int $limit
	 */
	private function _getCanvasList( $limit ){
		$list = model ( 'Canvas' )->getCanvasList(20);
		foreach ( $list['data'] as $k=>&$v ){
			$v['DOACTION'] = '<a href="'.U('page/Admin/editCanvas' , array('id'=>$v['id'] , 'tabHash' => 'addCanvas') ).'">编辑</a>';
// 			$v['DOACTION'] .= ' <a href="#" onclick="diy.deleteCanvas('.$v['id'].')">删除</a>';
		}
		return $list;
	}
}
?>