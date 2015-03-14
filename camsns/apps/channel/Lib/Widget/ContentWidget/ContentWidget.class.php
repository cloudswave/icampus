<?php
/**
 * 频道内容渲染Widget
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class ContentWidget extends Widget
{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data)
	{
		// 设置频道模板
		$template = empty($data['tpl']) ? 'list' : t($data['tpl']);
		// 配置参数
		// $var['loadCount'] = intval($data['loadCount']);
		// $var['loadMax'] = intval($data['loadMax']);
		// $var['loadId'] = intval($data['loadId']);
		// $var['loadLimit'] = intval($data['loadLimit']);
		$var['cid'] = intval($data['cid']);
		// 获取微博数据
		if($template == 'list') {
			$var['list'] = $this->getListData($var['cid']);
			// 微博配置
			$weiboSet = model('Xdata')->get('admin_Config:feed');
			$var['weibo_premission'] = $weiboSet['weibo_premission'];
		}
		if($template === 'load') {
			$var['categoryJson'] = json_encode($data['channelCategory']);
		}

		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
    }

    /**
     * 载入频道内容
     * @return json 频道渲染内容
     */
    public function loadMore()
    {
    	// 频道分类ID
    	$cid = intval($_REQUEST['cid']);
    	$loadLimit = intval($_REQUEST['loadlimit']);
    	$loadId = intval($_REQUEST['loadId']);
    	$loadCount = intval($_REQUEST['loadcount']);
    	// 获取HTML数据
    	$content = $this->getData($cid, $loadLimit, $loadId);
		// 查看是否有更多数据
		if(empty($content['html']) && empty($content['pageHtml'])) {
			$return['status'] = 0;
			$return['msg'] = L('PUBLIC_WEIBOISNOTNEW');
		} else {
			$return['status'] = 1;
			$return['msg'] = L('PUBLIC_SUCCESS_LOAD');
    		$return['html'] = $content['html'];
    		$return['loadId'] = $content['lastId'];
            $return['firstId'] = (empty($_REQUEST['p']) && empty($_REQUEST['loadId']) ) ? $content['firstId'] : 0;
            $return['pageHtml'] = $content['pageHtml'];
		}

    	exit(json_encode($return));
    }

    public function getData($cid, $loadLimit, $loadId)
    {
		// 获取微博数据
		$list = D('Channel', 'channel')->getDataWithCid($cid, $loadId, $loadLimit);
    	// 分页的设置
    	if(!empty($list['data'])) {
    		$content['firstId'] = $var['firstId'] = $list['data'][0]['feed_channel_link_id'];
    		$content['lastId'] = $list['data'][(count($list['data'])-1)]['feed_channel_link_id'];
            $var['data'] = $this->_formatContent($list['data']);
            // 微博配置
			$weiboSet = model('Xdata')->get('admin_Config:feed');
			$var['weibo_premission'] = $weiboSet['weibo_premission'];
    	}

    	$content['pageHtml'] = $list['html'];
	    // 渲染模版
		$content['html'] = fetch(dirname(__FILE__).'/_load.html', $var);

	    return $content;    	
    }

	/**
	 * 处理微博附件数据
	 * @param array $data 频道关联数组信息
	 * @return array 处理后的微博数据
	 */
	private function _formatContent($data)
	{
		// 组装微博信息
		foreach($data as &$value) {
			// 获取微博信息
			$feedInfo = model('Feed')->get($value['feed_id']);
			$value = array_merge($value, $feedInfo);
			switch($value['type']) {
				case 'postimage':
					$feedData = unserialize($value['feed_data']);
					$imgAttachId = is_array($feedData['attach_id']) ? $feedData['attach_id'][0] : $feedData['attach_id'];
					$attach = model('Attach')->getAttachById($imgAttachId);
					$value['attachInfo'] = getImageUrl($attach['save_path'].$attach['save_name'],'225');
					$value['attach_id'] = $feedData['attach_id'];
					$feedData['body'] = replaceUrl($feedData['body']);
					$value['body'] = parse_html($feedData['body']);
					break;
				case 'postvideo':
					$feedData = unserialize($value['feed_data']);
					$value['body'] = replaceUrl($feedData['body']);
					$value['flashimg'] = $feedData['flashimg'];
					break;
				case 'postfile':
					$feedData = unserialize($value['feed_data']);
					$attach = model('Attach')->getAttachByIds($feedData['attach_id']);
					foreach($attach as $key => $val) {
						$_attach = array(
								'attach_id' => $val['attach_id'],
								'name' => $val['name'],
								'attach_url' => getImageUrl($val['save_path'].$val['save_name'],'225'),
								'extension' => $val['extension'],
								'size' => $val['size']
							);
						$value['attachInfo'][] = $_attach;
					}
					$feedData['body'] = replaceUrl($feedData['body']);
					$value['body'] = parse_html($feedData['body']);
					break;
				case 'repost':
					$feedData = unserialize($value['feed_data']);
					$value['body'] = parse_html($feedData['body']);
					break;
				case 'weiba_post':
					$feedData = unserialize($value['feed_data']);
					$post_url = '<a class="ico-details" target="_blank" href="'.U('weiba/Index/postDetail',array('post_id'=>$value['app_row_id'])).'"></a>';
					$value['body'] = preg_replace('/\<a href="javascript:void\(0\)" class="ico-details"(.*)\>(.*)\<\/a\>/',$post_url,$value['body']);
					break;
				case 'weiba_repost':
					$feedData = unserialize($value['feed_data']);
					$post_id = D('feed')->where('feed_id='.$value['app_row_id'])->getField('app_row_id');
					$post_url = '<a class="ico-details" target="_blank" href="'.U('weiba/Index/postDetail',array('post_id'=>$post_id)).'"></a>';
					$value['body'] = preg_replace('/\<a href="javascript:void\(0\)" class="ico-details"(.*)\>(.*)\<\/a\>/',$post_url,$value['body']);
					break;
			}
		}

		return $data;
	}

	/**
	 * 获取频道分类列表数据
	 * @param integer $cid 频道分类ID
	 * @return array 频道分类列表数据
	 */
	public function getListData($cid)
	{
		$list = D('Channel', 'channel')->getChannelFindPage($cid);
		$feedIds = getSubByKey($list['data'], 'feed_id');
		$list['data'] = model('Feed')->getFeeds($feedIds);

		return $list;
	}
}