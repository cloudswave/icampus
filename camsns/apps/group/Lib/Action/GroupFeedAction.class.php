<?php
/**
 * 群聊 控制类
 * @author Stream
 *
 */
class GroupFeedAction extends BaseAction{
	/**
	 * 3.0发布微博操作，用于AJAX
	 * @return json 发布微博后的结果信息JSON数据
	 */
	public function PostFeed()
	{
		if ( !$this->ismember ){
			$return = array('status'=>0,'data'=>'抱歉，您不是该群成员');
			exit(json_encode($return));
		}
		// 返回数据格式
		$return = array('status'=>1, 'data'=>'');
		//群组ID
		$gid = intval($_POST['gid']);
		// 用户发送内容
		$d['content'] = isset($_POST['content']) ? filter_keyword(h($_POST['content'])) : '';
		$d['gid'] = $gid;
		// 原始数据内容
		$d['body'] = filter_keyword(h($_POST['body']));
		$d['source_url'] = urldecode($_POST['source_url']);  //应用分享到微博，原资源链接
		// 滤掉话题两端的空白
		$d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);
		// 附件信息
		$d['attach_id'] = trim(t($_POST['attach_id']), "|");
		!empty($d['attach_id']) && $d['attach_id'] = explode('|', $d['attach_id']);
		// 发送微博的类型
		$type = t($_POST['type']);
		// 所属应用名称
		//$app = isset($_POST['app_name']) ? t($_POST['app_name']) : APP_NAME;			// 当前动态产生所属的应用
		$app = 'group';

		if($data = D('GroupFeed')->put($this->uid, $app, $type, $d)) {
			// 发布邮件之后添加积分
			//model('Credit')->setUserCredit($this->uid,'add_weibo');
			// 微博来源设置
			$data['from'] = getFromClient($data['from'], 'public');
			$this->assign($data);
			//微博配置
			$weiboSet = model('Xdata')->get('admin_Config:feed');
			$this->assign('weibo_premission', $weiboSet['weibo_premission']);
			$return['data'] = $this->fetch();
	
			// // 微博ID
			// $return['feedId'] = $data['feed_id'];
			// $return['is_audit'] = $data['is_audit'];
			// //添加话题
			//          model('FeedTopic')->addTopic(html_entity_decode($d['body'], ENT_QUOTES), $data['feed_id'], $type);
			// //更新用户最后发表的微博
			// $last['last_feed_id'] = $data['feed_id'];
			// $last['last_post_time'] = $_SERVER['REQUEST_TIME'];
			// model( 'User' )->where('uid='.$this->uid)->save($last);
	
			//         // 添加微博到投稿数据中
			//         $isOpenChannel = model('App')->isAppNameOpen('channel');
			//         if($isOpenChannel) {
			//          $channelId = t($_POST['channel_id']);
			//          // 绑定用户
			//          $bindUserChannel = D('Channel', 'channel')->getCategoryByUserBind($this->mid);
			//          if(!empty($bindUserChannel)) {
			//          	$channelId = array_merge($bindUserChannel, explode(',', $channelId));
			//          	$channelId = array_filter($channelId);
			//          	$channelId = array_unique($channelId);
			//          	$channelId = implode(',', $channelId);
			//          }
			//          // 绑定话题
			//          $content = html_entity_decode($d['body'], ENT_QUOTES);
			//     		$content = str_replace("＃", "#", $content);
			// preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $content, $topics);
			// $topics = array_unique($topics[1]);
			// foreach($topics as &$topic) {
			// 	$topic = trim(preg_replace("/#/",'',t($topic)));
			// }
			// $bindTopicChannel = D('Channel', 'channel')->getCategoryByTopicBind($topics);
			//          if(!empty($bindTopicChannel)) {
			//          	$channelId = array_merge($bindTopicChannel, explode(',', $channelId));
			//          	$channelId = array_filter($channelId);
			//          	$channelId = array_unique($channelId);
			//          	$channelId = implode(',', $channelId);
			//          }
			//          if(!empty($channelId)) {
			//          	D('Channel', 'channel')->setChannel($data['feed_id'], $channelId, false);
			//          }
			//         }
		} else {
			$return = array('status'=>0,'data'=>model('Feed')->getError());
		}
	
		exit(json_encode($return));
	}
}