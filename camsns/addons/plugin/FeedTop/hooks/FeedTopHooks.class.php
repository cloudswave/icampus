<?php
class FeedTopHooks extends Hooks
{
	/**
     * 主页右钩子
     */
    public function home_index_left_feedtop()
    {
       $list = $this->model('FeedTop')->getFeedTopList(1);
		$close_feeds = $_SESSION['feed_top_'.$this->mid];
		foreach($list as $k =>$v){
			if(!in_array($v['feed_id'],$close_feeds)){
				$list[$k]['feed_info'] = model('Feed')->get($v['feed_id']);
			}else{
				unset($list[$k]);
			}
		}
 
		foreach($list as &$v) {
            	switch ( $v['feed_info']['app'] ){
            		case 'weiba':
            			$v['feed_info']['from'] = getFromClient(0 , $v['feed_info']['app'] , '微吧');
            			break;
                    case 'tipoff':
                    $v['feed_info']['from'] = getFromClient(0 , $v['feed_info']['app'] , '爆料');
                    break;
            		default:
            			$v['feed_info']['from'] = getFromClient( $v['feed_info']['from'] , $v['feed_info']['app']);
            			break;
            	}
            	!isset($uids[$v['feed_info']['uid']]) && $v['feed_info']['uid'] != $GLOBALS['ts']['mid'] && $uids[] = $v['feed_info']['uid'];
         }
		$this->assign('data',$list);
		// 赞微博
		$feed_ids = getSubByKey($list, 'feed_id');
		$diggArr = model('FeedDigg')->checkIsDigg($feed_ids, $GLOBALS['ts']['mid']);
		$this->assign('diggArr', $diggArr);
        $this->display('feedtop');
    }
	//public function home_index_right_top(){	
	// 	$list = $this->model('FeedTop')->getFeedTopList(0);
	// 	foreach($list as $k =>$v){
	// 		$list[$k]['feed_info'] = model('Feed')->get($v['feed_id']);
	// 	}
	// 	$this->assign('recomment_lists',$list);
	// 	$this->display('recomment');
	// }
	 //用户删除指定微博置顶
	public function close_feed_top(){
		$feed_top_id = t($_POST['feed_id']);
		$has_del_feed = $_SESSION['feed_top_'.$this->mid];
		if(!is_array($has_del_feed)){
			$has_del_feed = array();
		}
		$has_del_feed[] = $feed_top_id;
		$has_del_feed = array_unique($has_del_feed);
        $_SESSION['feed_top_'.$this->mid] = $has_del_feed;
		echo 1;
	}
    //后台列表
	public function config()
	{
		// 列表数据
		$list = $this->model('FeedTop')->getFeedTopList(2);
		foreach($list['data'] as $k =>$v){
			$list['data'][$k]['feed_info'] = model('Feed')->get($v['feed_id']);
		}
		//dump($list);exit;
		$this->assign('list', $list);
		$this->display('config');
	}
     
	 /**
	 * 添加置顶页面
	 * @return void
	 */
	public function addFeedTop()
	{
		$this->display('addFeedTop');
	}
	/**
	 * 添加置顶操作
	 * @return void
	 */
	public function doAddFeedTop()
	{
		$data['title'] = t($_POST['title']);
		$data['feed_id'] = intval($_POST['feed_id']);
		$data['status'] = intval($_POST['status']);
		$data['ctime'] = time();
		$res = $this->model('FeedTop')->doAddFeedTop($data);
		return false;
	}
	/**
	 * 编辑广告位页面
	 * @return void
	 */
	public function editFeedTop()
	{
		// 获取广告位信息
		$id = intval($_GET['id']);
		$data = $this->model('FeedTop')->find($id);
		$this->assign('data', $data);
		$this->assign('editPage', true);
		$this->display('addFeedTop');
	}
	/**
	 * 编辑广告位操作
	 * @return void
	 */
	public function doEditFeedTop()
	{
		$id = intval($_POST['id']);
		$data['title'] = t($_POST['title']);
		$data['feed_id'] = intval($_POST['feed_id']);
		$data['status'] = intval($_POST['status']);
		$data['ctime'] = time();
		$res = $this->model('FeedTop')->doEditFeedTop($id, $data);
		return false;
	}
	/**
	 * 取消置顶操作
	 * @return json 是否删除成功
	 */
	public function doDelFeedTop()
	{
		$result = array();
		$id= t($_POST['id']);
		if(empty($id)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('FeedTop')->doDelFeedTop($id);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}
    /**
	 * 重新置顶操作
	 * @return json 是否成功
	 */
	public function doFeedTop()
	{
		$result = array();
		$id= t($_POST['id']);
		if(empty($id)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('FeedTop')->doFeedTop($id);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '置顶成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '置顶失败';
		}
		exit(json_encode($result));
	}
	 public function doDel()
	{
		$result = array();
		$id= t($_POST['id']);
		if(empty($id)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('FeedTop')->doDel($id);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}
}