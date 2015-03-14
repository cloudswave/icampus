<?php
class GroupCommentAction extends BaseAction{
	function _initialize(){
		parent::_initialize();
		include_once APP_PATH.'/Lib/Widget/GroupCommentWidget/GroupCommentWidget.class.php';
	}
	
	public function render(){
		$groupcomment = new GroupCommentWidget();
		$html = $groupcomment->render();
		exit($html);
	}
	public function getCommentList(){
		$groupcomment = new GroupCommentWidget();
		$groupcomment->getCommentList();
	}
	public function addcomment(){
		if ( !$this->ismember ){
			$return = array('status'=>0,'data'=>'抱歉，您不是该群成员');
			exit(json_encode($return));
		}
		$groupcomment = new GroupCommentWidget();
		$groupcomment->addcomment();
	}
	public function delcomment(){
		$groupcomment = new GroupCommentWidget();
		$groupcomment->delcomment();
	}
}