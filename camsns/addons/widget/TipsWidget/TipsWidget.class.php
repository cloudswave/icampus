<?php
/**
 * 渲染顶操作页面Widget
 * @example W('Tips',array('source_id'=>$source_id,'source_table'=>$source_table,'type'=>0,'display_text'=>'顶','count'=>10,'uid'=>11860))
 * @author zivss
 * @version TS3.0
 **/
class TipsWidget extends Widget {
    
    /**
     * @param integer source_id 资源ID
     * @param integer source_table 资源表
     * @param integer type 类型 0支持 1反对
     * @param string  display_text 显示的字 如“顶”或“踩”
     * @param integer count 统计数目
     * @param integer uid 操作用户UID 不填写为登录用户
     */
	public function render($data) {
		// 获取所需的操作数据
		$var['sid'] = intval($data['source_id']);
		$var['stable'] = t($data['source_table']);
		$var['uid'] = empty($data['uid']) ? $GLOBALS['ts']['mid'] : intval($data['uid']);
		$var['type'] = intval($data['type']);
		$var['displayText'] = t($data['display_text']);
		$var['callback'] = t($data['callback']);

		// 获取顶或踩的数目
		$var['count'] = model('Tips')->getSourceExec($var['sid'], $var['stable'], $var['type']);
		$var['whetherExec'] = model('Tips')->whetherExec($var['sid'], $var['stable'], $var['uid'], $var['type']);

		// 渲染页面路径
		$content = $this->renderFile(dirname(__FILE__)."/tips.html", $var);
		
		return $content;
	}

	/**
	 * 执行顶或踩的操作
	 * @return ajax传送信息 0（添加失败）、1（添加成功）、2（已经添加）
	 */
	public function doExec() {
		$sid = intval($_POST['sid']);
		$stable = t($_POST['stable']);
		// $uid = $GLOBALS['ts']['mid'];
		$uid = intval($_POST['uid']);
		$type = intval($_POST['type']);

		$res = model('Tips')->doSourceExec($sid, $stable, $uid, $type);
		echo $res;
	}
}