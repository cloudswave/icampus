<?php
/**
* IndexAction
* 礼品应用的后台操作
*
* @package default
* @version $id$
* @copyright 2009-2011 水上铁
* @author 水上铁 <wxm201411@163.com>
* @license PHP Version 5.2 {@link www.sampeng.cn}
*/
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {
	var $category;   //礼品类型模型
	var $gift;       //礼品模型
	
	/**
	 * 构造函数
	 *
	 * 获取礼品模型，礼品类型模型
	 * 获取礼品配置信息并赋值给模板
	 */	
	function _initialize(){
		//管理权限判定
        parent::_initialize();

		$this->category = D('GiftCategory');
		$this->gift = D('Gift');
	}


	/**
	 * 全局设置
	 *
	 */
	function index() {
		$config   = model('Xdata')->lget('gift');
		$this->assign($config);

		//$types=array('score'=>'积分','exp'=>'经验');
		$types=X('Credit')->getCreditType();
        $this->assign('types',$types); 
		$this->display();
	}
	/*
	*更新礼品配置信息
	*/	   
	function doEditConfig(){
		//变量过滤 todo:更细致的过滤
		foreach($_POST as $k=>$v){
			$config[$k]	=	t($v);
		}
        if(model('Xdata')->lput('gift',$config)){
            $this->assign('jumpUrl', U('/Admin/index'));
			$this->success('设置成功！');
		}else{
			$this->error('设置失败！');
		}
	}  
	/**
	 * 礼物管理
	 *
	 */
	 function giftlist(){
		//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if ( !empty($_POST) ) {
			$_SESSION['gift_admin_search'] = serialize($_POST);
		}else if(!empty($_GET['categoryId'])){
			$_SESSION['gift_admin_search'] = serialize($_GET);
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['gift_admin_search']);
		}else {
			unset($_SESSION['gift_admin_search']);
		}	
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');	

        $_POST['id']             && $map['id']          =   intval($_POST['id']);
        $_POST['name']     	     && $map['name']        =   array( 'like','%'.t( $_POST['name'] ).'%' );
        $_POST['categoryId']     && $map['categoryId']  =   intval($_POST['categoryId']);
        $_GET['categoryId']      && $map['categoryId']  =   intval($_GET['categoryId']);
        $_POST['status']!=NULL   && $map['status']      =   intval($_POST['status']);
        ($_POST['order']         && $order     	        =   'id '.t($_POST['order'])) || $order='id DESC';

		//分类信息
		$categorysInfo = $this->category->__getCategory();
		//礼物列表
		$list = $this->gift->where($map)->order($order)->findPage(10);

		$this->assign($_POST);
		$this->assign("categorysInfo",$categorysInfo);
		$this->assign("list",$list);	
		$this->display();
	 }

	/**
	 *
	 * 默认删除操作
	 */
	public function delete()
	{
		$id = t($_REQUEST['id']);
		if($this->gift->delete($id)){			
			//删除成功
			if ( strpos($_REQUEST['id'],",") ){
                echo 1;exit;            //删除多个
            }else{
                echo 2;exit;         //说明只是删除一个
            }
		}else {
			echo 0;exit;
		}
	}

	/**
	 *
	 * 默认锁定操作
	 *
	 */
	function forbid()
	{
		$id = t($_REQUEST['id']);
		if($this->gift->forbid($id)){		
			//锁定成功
			if ( strpos($id,",") ){
                echo 1;exit;         //锁定多个
            }else{
                echo 2;exit;      //锁定一个
            }
		}else {
			echo 0;exit;
		}
	}

	/** 
	 *
	 * 默认恢复操作
	 */
	function resume()
	{
		$id = t($_REQUEST['id']);
		if($this->gift->resume($id)){		
			//恢复成功
			if ( strpos($id,",") ){
                echo 1;exit;          //恢复多个
            }else{
                echo 2;exit;      //恢复一个
            }
		}else {
			echo 0;exit;
		}
	}
	/*
	*编辑礼物
	*/
	function edit_gift_tab()
	{
		if($id = (int)$_REQUEST['id']){
			//礼物信息
			$gift = $this->gift->find($id);
			if(!$gift)
			{
				echo("无法找到对象!");
				return;
			}
			$this->assign("gift",$gift);
		}
		//分类信息
		$categorys = $this->category->__getCategory();
		$this->assign('categorys',$categorys);

		$this->display();
	}
	function edit_gift()
	{
		$id = intval($_REQUEST['id']);
		if(empty($id)&&!empty($_REQUEST['name'])){
			$this->insert_gift();
		}
		if(!empty($id)&&!empty($_REQUEST['name'])){			
			$this->update_gift($id);
		}
	}
	/*
	*增加礼品到数据库
	*/
	function insert_gift()
	{
		$info	= $this->_upload();		
		if(!$info['status'])$this->error("上传出错! ".$info['info']);
			//保存当前数据对象
			$this->gift->categoryId = intval($_REQUEST['categoryId']);
			$this->gift->name = t($_REQUEST['name']);
			$this->gift->num = intval($_REQUEST['num']);
			$this->gift->price = intval($_REQUEST['price']);
			$this->gift->img = $info['info'][0]['savename'];
			$this->gift->status = intval($_REQUEST['status']);
			$this->gift->cTime = time();
			if($this->gift->add()) {
				//成功提示
				$this->assign('jumpUrl',U('/Admin/giftlist'));
				$this->success('添加成功！');
			}else {
				//失败提示
				$this->error('添加失败！');
			}
	}
	/*
	* 更新礼品到数据库
	*/
	function update_gift($id)
	{
		$gift=$this->gift->find($id);
		if(!$gift){
			$this->error('非法参数，礼物不存在！');	
		}
		$info	=	$this->_upload();
		if($info['status']){
			$this->gift->img = $info['info'][0]['savename'];
		}
		//保存当前数据对象
		$this->gift->categoryId = intval($_REQUEST['categoryId']);
		$this->gift->name = t($_REQUEST['name']);
		$this->gift->num = intval($_REQUEST['num']);
		$this->gift->price = intval($_REQUEST['price']);
		$this->gift->status = intval($_REQUEST['status']);
		$this->gift->cTime = time();
		if($this->gift->where("id={$id}")->save()) {
			//保存成功则刷新页面
			$this->success('修改成功');
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}else {
			//失败提示
			$this->error('修改失败！');
		}
	}
	/*
	*执行单图上传操作
	*/	
	protected function _upload($path) {
		//上传参数
		$options['max_size']   = '2000000';
		$options['allow_exts'] = 'jpg,gif,png,jpeg';
		$options['save_path']  = UPLOAD_PATH.'/gift/';
		$options['save_to_db'] = false;
		//$saveName && $options['save_name'] = $saveName;
        //执行上传操作
		$info = X('Xattach')->upload('gift',$options);
		return $info;
    }

	/**
	* 获取分组列表
	*/
	public function category(){
		$categorys = $this->category->__getCategory();
		$this->assign('categorys',$categorys);
		$this->display();
	}
	public function edit_category_tab(){
		if($id = intval($_REQUEST['gid'])){
			$category=$this->category->find($id);
			$this->assign('id',$id);
			$this->assign('category',$category);
		}
		$this->display();
	}
	public function addCategory(){
        $data['name'] = t(h($_POST['name']));
        $data['status'] = 1;
        if (empty($data['name'])) {
         	echo -1;
        }else{
			if($this->category->add($data)){
				echo 1;
			}else{
				echo 0;
			}
        }
	}
	public function editCategory(){
        $data['name'] = t(h($_POST['name']));
        $data['status'] = 1;
        $map['id'] = intval($_POST['id']);
        if (empty($data['name']) ) {
         	echo -1;
        }else{
        	$res = $this->category->where($map)->save($data);
			if($res == 1 ){
				echo 1;
			}else if ($res == 0){
				echo 2;
			}
        }
	}
	public function delCategory(){
		$id = intval($_POST['gid']);
		$giftNum    = $this->gift->where("categoryId={$id}")->count();
		if($giftNum>0){
			echo 0;
		}else{
			if($this->category->where("id={$id}")->delete()){
				echo 1;
			}else{
				echo -1;
			}
		}
	}
}
?>