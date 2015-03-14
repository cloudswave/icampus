<?php
/**
 * 微博发布框
 * @example W('Share',array('sid'=>14983,'stable'=>'contact','appname'=>'contact','nums'=>10,'initHTML'=>'这里是默认的话')) 
 * @author jason
 * @version TS3.0
 */
class ShareWidget extends Widget{
	
	/**
     * @param integer sid 资源ID,如分享小名片就是对应用户的用户ID，分享微博就是微博的ID
     * @param string stable 资源所在的表，如小名片就是contact表，微博就是feed表
     * @param string appname 资源所在的应用
     * @param integer nums 该资源被分享的次数
     * @param string initHTML 默认的内容 
	 */
	public function render($data){
		$var = array();
		$var['appname'] = 'public';
		$var['cancomment'] = intval(CheckPermission('core_normal','feed_comment'));
		$var['feed_type'] = 'repost';
		
		is_array($data) && $var = array_merge($var,$data);

		// 获取资源是否被删除
		switch ($data['appname']) {
			case 'weiba':
				$wInfo = D('WeibaPost', 'weiba')->where('post_id='.$var['sid'])->find();
				$sInfo = model('Feed')->getFeedInfo($sInfo['feed_id']);		
				break;
			default:
				$sInfo = model('Feed')->getFeedInfo($var['sid']);		
		}
		
		$var['s_is_del'] = $sInfo['is_del'];

		extract($var, EXTR_OVERWRITE);

		if($nums>0){
			$showNums = "&nbsp;({$nums})";
		}else{
			$showNums = "";
		}

		if($s_is_del == 1){
			return "<span>".L('PUBLIC_SHARE_STREAM').$showNums."</span>";
		}else{
			return "<a event-node=\"share\" href=\"javascript:void(0);\" event-args='sid={$sid}&stable={$stable}&curtable={$current_table}&curid={$current_id}&initHTML={$initHTML}&appname={$appname}&cancomment={$cancomment}&feedtype={$feed_type}&is_repost={$is_repost}'>".L('PUBLIC_SHARE_STREAM').$showNums."</a>";
		}

	 	//    //渲染模版
	 	//    $content = $this->renderFile(dirname(__FILE__)."/Share.html",$var);
	
		// unset($var,$data);
  		//       //输出数据
		// return $content;
    }
}