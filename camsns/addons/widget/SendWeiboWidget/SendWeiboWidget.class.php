<?php
/**
 * 微博发布框
 * @example {:W('SendWeibo',array('send_type'=>'repost_weibo','oldUid'=>$oldInfo['source_user_info']['uid'],'space_link'=>$oldInfo['source_user_info']['space_link'],'sid'=>$shareInfo['sid'],'app_name'=>$shareInfo['appname'],'stype'=>$shareInfo['stable'],'initHtml'=>$shareInfo['initHTML'],'curid'=>$shareInfo['curid'],'curtable'=>$shareInfo['curtable'],'cancomment'=>$shareInfo['cancomment']))}
 * @author jason
 * @version TS3.0
 */
class SendWeiboWidget extends Widget {
	
	private static $rand = 1;
    
    /**
     * 渲染微博发布框模板
     * @example
     * $data['send_type'] string 微博发送类型
     * $data['app_name'] string 发布微博所在的应用名称
     * $data['initHtml'] string 发布微博框中的默认内容
     * $data['cancomment'] integer 是否可以评论 
     *$data['channelID']  发布到某个频道的id
     * @param array $data 发布微博框的配置参数
     * @return string 渲染后的模板内容
     */
	public function render($data) {
		$var = array();
		//频道id
		$var['channelID'] = $data['channelID'];
		
		$var['initHtml'] = '';
		$var['post_event'] ='post_feed';
		$var['cancomment'] = 0;
		is_array($data) && $var = array_merge($var,$data);
		!$var['send_type'] && $var['send_type'] = 'send_weibo';
		$weiboSet = model('Xdata')->get('admin_Config:feed');
		$var['initNums'] = $weiboSet['weibo_nums'];
		$var['weibo_type'] = $weiboSet['weibo_type'];
		$var['weibo_premission'] = $weiboSet['weibo_premission'];
		!$var['type'] && $var['type'] = 'post';
		!$var['app_name'] && $var['app_name'] = 'public';
		!$var['prompt'] && $var['prompt'] = '转发成功';
		$var['time'] = $_SERVER['REQUEST_TIME'];
		$var['topicHtml'] = t($data['topicHtml']).' ';//空格 控制话题提示
		// 获取安装的应用列表
		$var['hasChannel'] = model('App')->isAppNameOpen('channel');
		// 权限控制
		$type = array('face', 'at', 'image', 'video', 'file', 'topic', 'contribute');
		foreach($type as $value) {
			!isset($var['actions'][$value]) && $var['actions'][$value] = true; 
		}
	    // 渲染模版
	    $content = $this->renderFile(dirname(__FILE__)."/SendWeibo.html", $var);
	
		self::$rand++;
		unset($var, $data);
        // 输出数据
		return $content;
    }
}