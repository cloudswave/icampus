<?php
class GroupFeedListAction extends Action{
	
	public function __construct(){
		include_once APP_PATH.'/Lib/Widget/GroupFeedListWidget/GroupFeedListWidget.class.php';
	}
	
	public function loadMore(){
		$feedlist = new GroupFeedListWidget();
		$feedlist->loadMore();
	}
	public function loadNew(){
		$feedlist = new GroupFeedListWidget();
		$feedlist->loadNew();
	}
}