<?php
/**
 * 赞Widget
 */
class DiggWidget extends Widget {

	/**
	 * 渲染赞页面
	 * @return string 赞HTML相关信息
	 */
	public function render ($data) {
		$var['tpl'] = 'digg';
		$var['feed_id'] = intval($data['feed_id']);
		$var['digg_count'] = intval($data['digg_count']);
		$var['diggArr'] = (array) $data['diggArr'];
		$var['diggId'] = empty($data['diggId']) ? 'digg' : t($data['diggId']);
		// 判断是否可以自己赞自己
		// $var['self_feed'] = ($GLOBALS['ts']['mid'] == intval($data['feed_uid'])) ? true : false;
		
		//直接输出，减少模版解析，提升效率
		if($var['tpl']=='digg')
			return $this->renderData($var);
		else
			return $this->renderFile(dirname(__FILE__).'/'.t($var['tpl']).'.html', $var);
	}

	private function renderData($var){
		extract($var, EXTR_OVERWRITE);
		$html =  "<span id='{$diggId}{$feed_id}' rel='{$digg_count}'>";
		if(!isset($diggArr[$feed_id])):
	   	$html .= "<a href=\"javascript:;\" onclick=\"core.digg.addDigg({$feed_id})\">赞<if condition='!empty($digg_count)'>({$digg_count})</if></a>";
		else:
	   	$html .= "<a href=\"javascript:;\" onclick=\"core.digg.delDigg({$feed_id})\">已赞<if condition='!empty($digg_count)'>({$digg_count})</if></a>";
		endif;
		$html .= "</span>";
		return $html;
	}
}