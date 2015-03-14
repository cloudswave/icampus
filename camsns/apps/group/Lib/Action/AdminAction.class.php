<?php
/**
 * AdminAction 
 * 群组管理
 * @uses Action
 * @package Admin
 * @version $id$
 * @copyright 2009-2011 SamPeng 
 * @author SamPeng <sampeng87@gmail.com> 
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction {
	var $GroupSetting;
	var $Category;
	public function _initialize() {
		parent::_initialize ();
		$this->GroupSetting = D ( 'GroupSetting' );
		//$this->config = D( 'BlogConfig' );
		$this->Category = D ( 'Category' );
	}
	
	/**
	 * basic 
	 * 基础设置管理
	 * @access public
	 * @return void
	 */
	public function index()
	{
		if (isset ( $_POST ['editSubmit'] ) == 'do') {
			array_map ('h', $_POST);
			$res = model ( 'Xdata' )->lput ( 'group', $_POST );
			if ($res) {
				$this->success ( '保存成功' );
			} else {
				$this->error ( '保存失败' );
			}
		}
		
		//model('Xdata')->lput('group', $this->GroupSetting->getGroupSetting());
		$setting = model ( 'Xdata' )->lget ( 'group' );
		
		$this->assign ( 'credit_types', X ( 'Credit' )->getCreditType () );
		$this->assign ( 'setting', $setting );
		
		$this->display ();
	}
	
	/**
	 * basic 
	 * 组内设置
	 * @access public
	 * @return void
	 */
	public function group() {
		$this->index ();
	}
	
	/**
	 * basic 
	 * 分类管理
	 * @access public
	 * @return void
	 */
	public function category() {
		/*$categoryList	=	$this->Category->getCategoryList(0);
			$this->assign('categoryList',$categoryList);*/
		
		$categoryList = D('Category')->_maskTreeNew(0);
		$this->assign('category_tree', $categoryList);
		// $this->assign('category_tree', D('Category')->_makeTree (0));
		$this->display();
	}
	
	//添加分类
	public function addCategory() {
		if (empty ( $_POST ['title'] )) {
			$this->error ( '名称不能为空！' );
		}
		
		$cate ['title'] = t ( $_POST ['title'] );
		
		$cate['pid'] = $this->Category->_digCateNew($_POST); //多级分类需要打开
		// $cate ['pid'] = intval ( $_POST ['cid0'] ); //1级分类
		S('Cache_Group_Cate_0',null);
		S('Cache_Group_Cate_'.$cate ['pid'],null);
		$categoryId = $this->Category->add ( $cate );
		if ($categoryId) {
			S('Cache_Group_Cate_0',null); 
			$this->success ( '操作成功！' );
		} else {
			$this->error ( '操作失败！' );
		}
	}
	
	// 修改分类   		
	public function editCategory() {
		if (isset ( $_POST ['editSubmit'] )) {
			$id = intval ( $_POST ['id'] );
			$cate ['title'] = trim(t( $_POST ['title']));

			if (! $this->Category->getField ( 'id', 'id=' . $id )) {
				$this->error ( '分类不存在！' );
			} 
			if (empty ( $cate ['title'] )) {
				$this->error ( '名称不能为空！' );
			} 
			if ( get_str_length($cate ['title']) > 25) {
				$this->error ( '名称不能超过25个字！' );
			}
			
			
			// $pid = $cate ['pid'] = intval ( $_POST ['cid0'] ); //1级分类
			$pid = $this->Category->_digCateNew($_POST);
			$cate ['pid'] = intval ( $pid );
			
			if($pid == intval($_POST['id'])) {
				$this->error('不能选择所编辑分类为上级');
			}
			
			S('Cache_Group_Cate_0',null);
			S('Cache_Group_Cate_'.$pid,null);
			
			if ($pid != 0 && ! $this->Category->getField ( 'id', 'id=' . $pid )) {
				$this->error ( '父级分类错误！' );
			} else if ($pid == $id) {
				$res = $this->Category->setField ( 'title', $cate ['title'], 'id=' . $id );
			} else {
				$res = $this->Category->where ( "id={$id}" )->save ( $cate );
			}
			
			if (false !== $res) {
				S('Cache_Group_Cate_0',null); 
				$this->success ( '操作成功！' );
			} else {
				$this->error ( '操作失败！' );
			}
		}
		$id = intval ( $_GET ['id'] );
		$category = $this->Category->where ( "id=$id" )->find ();
		$this->assign ( 'category', $category );
		$this->display ();
	}
	
	// 删除分类
	public function delCategory() {
		$pid = intval ( $_GET ['pid'] );
		$id = intval ( $_GET ['id'] );
		S('Cache_Group_Cate_0',null);
		$pid && S('Cache_Group_Cate_'.$pid,null);
		S('Cache_Group_Cate_'.$id,null);
		if ($this->Category->where ( 'id=' . $id )->delete ()) {
			$this->Category->where ( 'pid=' . $id )->delete ();
			S('Cache_Group_Cate_0',null); 
			$this->success ("删除成功！");
		} else {
			$this->error ( '删除失败！' );
		}
	}

	/**
	 * basic 
	 * 管理
	 * @access public
	 * @return void
	 */
	public function manage()
	{//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
			$_POST = unserialize($_SESSION['admin_search']);
        } else {
        	unset($_SESSION['admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
        $this->assign($_POST);

		$type = !empty($_REQUEST ['type']) ? $_REQUEST ['type'] : 'group';
        $_POST['uid']   && $condition[]    =   ' uid=' . intval($_POST['uid']);
        $_POST['id']    && $condition[]     =   ' id=' . intval($_POST['id']);
        $_POST['name']  && $condition[]   =   ' name LIKE "%' . t($_POST['name']) . '%"';
        $_POST['title'] && $condition[]  =   ' title LIKE "%' . t($_POST['title']) . '%"';
        $_POST['cid0'] 	&& $condition[]   =   ' cid0=' . intval($_POST['cid0']);
        $_POST['cid1'] 	&& $condition[]   =   ' cid1=' . intval($_POST['cid1']);
        $_POST['feed_id']    && $condition[]     =   ' feed_id=' . intval($_POST['feed_id']);
        $_POST['gid']    && $condition[]     =   ' gid=' . intval($_POST['gid']);
        $_POST['content'] && $condition[]  =   ' content LIKE "%' . t($_POST['content']) . '%"';
        $_POST['tid']   && $condition[]    =   ' tid=' . intval($_POST['tid']);
        // 排序
        $_POST['field'] && $_POST['order'] && $order = t($_POST['field']) . ' ' . t($_POST['order']);
        // 分页条数
        $_POST['limit'] && $limit = intval($_POST['limit']);

		if ('group' == $type) {
			// 群组
			$condition[] = 'status=1';
			!$order && $order = 'ctime DESC';
			$list = D('Group')-> getGroupList(1, $condition, null, $order, $limit, 0);
		} else if ('feed' == $type) {
			// 群聊
			!$order && $order = 'publish_time DESC';
			$_POST['gid'] && $map['gid'] = intval( $_POST['gid'] );
			$_POST['feed_id'] && $map['feed_id'] = intval( $_POST['feed_id'] );
			$_POST['uid'] && $map['uid'] = intval( $_POST['uid'] );
			$map['is_del'] = 0;
			$list = D('GroupFeed')->getList($map,$limit,$order);
		} else if ('topic' == $type) {
			// 帖子	
			$list = D('Topic')->getTopicList(1, $condition, null, $order, $limit, 0);
		} else if ('post' == $type) {
			// 回帖
   			$list = D('Post')->getPostList(1, $condition, null, $order, $limit, 0);
	    	/*
	    	 * 缓存帖子标题
	    	 */
	    	$tids = getSubByKey($list['data'], 'tid');
	    	$ttitles = D('Topic')->field('id,title')->where('id IN (' . implode(',', $tids) . ')')->findAll();
	    	foreach ($ttitles as $v) {
	    		$_topics[$v['id']] = $v['title'];
	    	}
	    	$this->assign('_topics', $_topics);
		} else if ('file' == $type) {
			// 文件
			$_POST['id']    && $filemap[]     =   ' id=' . intval($_POST['id']);
			$_POST['name']  && $filemap[]   =   ' a.name LIKE "%' . t($_POST['name']) . '%"';
			$_POST['uid']   && $filemap[]    =   ' ga.uid=' . intval($_POST['uid']);
			$_POST['gid']    && $filemap[]     =   ' gid=' . intval($_POST['gid']);
   			$list = D('Dir')->getFileList(1, $filemap, null, $order, $limit, 0);
		} /* else if ($type == 'album') {
   				// 相册
   				$this->assign('albumData',$data);
   			} else if ($type == 'photo') {
   				// 图片
   				$this->assign('photoData',$data);
   			}*/

		if ('group' != $type) {
	    	/*
	    	 * 缓存群组名称
	    	 */
	    	$gids = getSubByKey($list['data'], 'gid');
	    	$gnames = D('Group')->field('id,name')->where('id IN (' . implode(',', $gids) . ')')->findAll();
	    	foreach ($gnames as $v) {
	    		$_group_names[$v['id']] = $v['name'];
	    	}
	    	$this->assign('_group_names', $_group_names);
		}

		foreach($list['data'] as &$value) {
            $groupinfo['path'] = D('Category', 'group')->getPathWithCateId($value['cid1']);
			$value['path'] = implode(' - ', $groupinfo['path']);
		}
		$this->assign ( 'list', $list );
		$this->assign ( 'type', $type );
		$this->display ( 'manage' . $type );
	}
	
	/**
	 * basic 
	 * 审核
	 * @access public
	 * @return void
	 */
	public function audit() {
		$audit_list = D ( 'Group' )->field ( 'id,uid,name,intro,logo,ctime' )->where ( 'status=0 AND is_del=0' )->order ( 'ctime DESC' )->findPage ();
		
		$this->assign ( 'audit_list', $audit_list );
		$this->display ();
	}
	
	public function doAudit() {
		$gid = is_array ( $_POST ['gid'] ) ? '(' . implode ( ',', $_POST ['gid'] ) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
		$res = D ( 'Group' )->setField ( 'status', 1, 'id IN ' . t($gid) ); // 通过审核
		if ($res) {
			if (strpos ( $_POST ['gid'], ',' )) {
				echo 1;
			} else {
				echo 2;
			}

// 			// 发送通知
// 			$map ['id'] = array ('in', $gid );
// 			$groups = D ( 'Group' )->where ( $map )->findAll ();
// 			$notify_dao = service ( 'Notify' );
// 			foreach ( $groups as $v ) {
// 				$notify_data = array ('title' => $v ['name'], 'group_id' => $v ['id'] );
// 				$notify_dao->sendIn ( $v ['uid'], 'group_audit', $notify_data );
// 			}
		} else {
			echo 0;
		}
	}

	public function dismissed() {
		$this->display ();
	}

	// 群组审核-驳回
	public function doDismissed()
	{
		$_POST ['gid'] = t ( $_POST ['gid'] );
		$res = D('Group')->remove ($_POST ['gid']);
		if ($res) {
			// 发送通知
			if ($_POST ['reason']) {
				$reason = h ( urldecode ( $_POST ['reason'] ) );
				$notify_template = 'group_delaudit';
			} else {
				$notify_template = 'group_del';
			}
			$map ['id'] = array ('in', $_POST ['gid'] );
			$groups = D ( 'Group' )->field ( 'uid,name' )->where ( $map )->findAll ();
// 			$notify_dao = service ( 'Notify' );
// 			foreach ( $groups as $v ) {
// 				$notify_data = array ('title' => $v ['name'], 'reason' => $reason );
// 				$notify_dao->sendIn ( $v ['uid'], $notify_template, $notify_data );
// 			}

			if (strpos ( $_POST ['gid'], ',' )) {
				echo 1;
			} else {
				echo 2;
			}
		} else {
			echo 0;
		}
	}

	public function remove()
	{
		$type = !empty ( $_POST ['type'] ) ? trim ( $_POST ['type'] ) : 'group';
		$ids  = $_POST ['id'] ? t($_POST ['id']) : '';
		// 群组，群聊，话题，文件，相册，话题回复
		if ($type == 'group') {
			// 群组
			$res = D ('Group')->remove ($ids);
		} else if ($type == 'feed') {
			// 群聊
			$res = D('GroupFeed')->doEditFeed($ids, 'delFeed');
		} else if ($type == 'topic') {
			// 话题
			$res = D ( 'Topic' )->remove($ids);
		} else if ($type == 'album') {
			// 相册
			$res = D ( 'Album' )->deleteAlbum($ids);
		} elseif ($type == 'photo') {
			// 图片
			$res = D ( 'Album' )->deletePhoto($ids);
		} else if ($type == 'file') {
			// 文件
			$res = D ( 'Dir' )->remove($ids);
		} else if ($type == 'post') {
			//回帖
			$res = D ( 'Post' )->remove($ids);
		}

		if ($res) {
			if (strpos($_POST ['id'], ',')) {
				echo 1;
			} else {
				echo 2;
			}
		} else {
			echo 0;
		}
	}

	/**
	 * basic 
	 * 回收站
	 * @access public
	 * @return void
	 */
	/*function recycle() {
		$type = ! empty ( $_REQUEST ['type'] ) ? trim ( $_REQUEST ['type'] ) : 'group';
		$limit = ! empty ( $_POST ['limit'] ) ? trim ( $_POST ['limit'] ) : 10;
		$title = ! empty ( $_POST ['title'] ) ? trim ( $_POST ['title'] ) : '';
		$field = ! empty ( $_POST ['field'] ) ? trim ( $_POST ['field'] ) : 'id';
		$asc = ! empty ( $_POST ['asc'] ) ? trim ( $_POST ['asc'] ) : 'desc';
		
		//传递参数
		$this->assign ( 'uid', $uid );
		$this->assign ( 'title', $title );
		$this->assign ( 'content', $content );
		$this->assign ( 'field', $field );
		$this->assign ( 'asc', $asc );
		$this->assign ( 'limit', $limit );
		
		$data = $this->GroupSetting->searchData ( $type, $uid, $username, $title, $content, $field, $asc, $limit, 1 );
		//群组
		if ($type == 'group') {
			
			$this->assign ( 'groupData', $data );
		} //话题
		elseif ($type == 'topic') {
			$this->assign ( 'topicData', $data );
			
			$this->display ( 'recycletopic' );
			exit ();
		} //相册
		elseif ($type == 'album') {
			$this->assign ( 'albumData', $data );
			$this->display ( 'recyclealbum' );
			exit ();
		} elseif ($type == 'photo') {
			
			$this->assign ( 'photoData', $data );
			$this->display ( 'recyclephoto' );
			exit ();
		} //文件
		elseif ($type == 'file') {
			
			$this->assign ( 'fileData', $data );
			$this->display ( 'recyclefile' );
			exit ();
		} //回帖
		elseif ($type == 'post') {
			
			$this->assign ( 'postData', $data );
			$this->display ( 'recyclepost' );
			exit ();
		}
		$this->display ();
	}
	
	//放入回收站
	function remove() {
		$type = ! empty ( $_POST ['act'] ) ? trim ( $_POST ['act'] ) : 'group';
		$ids = isset ( $_POST ['id'] ) ? $_POST ['id'] : 0;
		
		//群组  关联 群组，话题，文件，相册，话题回复
		if ($type == 'group') {
			$res = D ( 'Group' )->remove ( $ids );
		} //话题
		elseif ($type == 'topic') {
			$res = D ( 'Topic' )->remove ( $ids );
		} //相册
		elseif ($type == 'album') {
			$res = D ( 'Album' )->deleteAlbum ( $ids );
		} elseif ($type == 'photo') {
			$res = D ( 'Album' )->deletePhoto ( $ids );
		} //文件
		elseif ($type == 'file') {
			$res = D ( 'Dir' )->remove ( $ids );
		} //回帖
		elseif ($type == 'post') {
			
			$res = D ( 'Post' )->remove ( $ids );
		}
		
		if ($res) {
		
		}
	}
	
	//删除
	function delete() {
		$type = ! empty ( $_POST ['act'] ) ? trim ( $_POST ['act'] ) : 'group';
		$ids = isset ( $_POST ['id'] ) ? $_POST ['id'] : 0;
		
		//群组  关联 群组，话题，文件，相册，话题回复
		if ($type == 'group') {
			D ( 'Group' )->del ( $ids );
		} //话题
		elseif ($type == 'topic') {
			D ( 'Topic' )->del ( $ids );
		} //相册
		elseif ($type == 'album') {
			D ( 'Album' )->removePhoto ( $ids );
		} elseif ($type == 'photo') {
			
			D ( 'Album' )->removePhoto ( $ids );
		} //文件
		elseif ($type == 'file') {
			
			D ( 'Dir' )->del ( $ids );
		} //回帖
		elseif ($type == 'post') {
			
			D ( 'Post' )->del ( $ids );
		}
	}
	
	//恢复内容
	function recover() {
		$type = ! empty ( $_POST ['act'] ) ? trim ( $_POST ['act'] ) : 'group';
		$ids = isset ( $_POST ['id'] ) ? $_POST ['id'] : 0;
		
		//群组  关联 群组，话题，文件，相册，话题回复
		//话题
		if ($type == 'topic') {
			D ( 'Topic' )->recover ( $ids );
		} //相册  
		elseif ($type == 'album') {
			D ( 'Album' )->recoverAlbum ( $ids );
		} elseif ($type == 'photo') {
			D ( 'Album' )->recoverPhoto ( $ids );
		} //文件
		elseif ($type == 'file') {
			D ( 'Dir' )->recover ( $ids );
		} //回帖
		elseif ($type == 'post') {
			
			D ( 'Post' )->recover ( $ids );
		}
	}*/

	//群组设置推荐
	/*function recom() {
		$type = ! empty ( $_POST ['act'] ) ? trim ( $_POST ['act'] ) : 'group';
		$id = ! empty ( $_POST ['id'] ) ? trim ( $_POST ['id'] ) : 0;
		$isrecom = ! empty ( $_POST ['isrecom'] ) ? trim ( $_POST ['isrecom'] ) : 0;
		
		if ($type == 'group') {
			D ( 'Group' )->where ( 'id=' . $id )->setField ( 'isrecom', $isrecom );
		
		} elseif ($type == 'topic') {
			D ( 'Topic' )->where ( 'id=' . $id )->setField ( 'isrecom', $isrecom );
		
		}
		return false;
	}*/

}
