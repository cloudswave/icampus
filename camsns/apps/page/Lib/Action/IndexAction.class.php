<?php
/**
 * 前台显示
 * @author Stream
 *
 */
class IndexAction extends Action {
	private  $layout = array();
	public function index(){
		$page		=	t($_GET['page']);
		$map['domain'] = $page;
		if ( !model('UserGroup')->isAdmin ( $this->mid ) ){
			$map['status'] = 1;  //显示
			$map['guest'] = 1; //游客是否可见
		}
		$databaseData	=	model('Page')->getPageInfo($map , 'id,page_name,canvas,layout_data,widget_data');
		if(!$databaseData){
			$this->error("Error! 找不到该页面或者该页面已隐藏！");
		}
		$parseTag = model ( 'ParseTag' );
		$this->setTitle($databaseData['page_name']);
		$this->assign('tempData',$databaseData['layout_data']);
		$this->assign('layoutData',unserialize($databaseData['widget_data']));
		$pageData = $parseTag->parseId ( $databaseData['layout_data'],180);
		$this->assign('data',$pageData);
		$this->assign('page',$page);
		$databaseData['canvas'] = CANVAS_PATH . $databaseData['canvas'];
		model( 'Page' )->addReader ( $databaseData ['id'] );
		if ( !file_exists( $databaseData['canvas'] ) ){
			$this->display();
		} else {
			$this->display($databaseData['canvas']);
		}
	}
}
?>