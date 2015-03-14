<?php
/**
 * 频道API模型
 * @author zivss <guolee226@gmail.com>
 * @version ts3.0
 */
class ChannelApiModel
{
	/**
	 * 获取所有频道分类数据
	 * @return array 所有频道分类数据
	 */
	public function getAllChannel()
	{
		$data = model('CategoryTree')->setTable('channel_category')->getCategoryAllHash();
		
		// 组装附件信息
		$attachIds = getSubByKey($data, 'attach');
		$attachIds = array_filter($attachIds);
		$attachIds = array_unique($attachIds);
		$attachInfos = model('Attach')->getAttachByIds($attachIds);
		$attachData = array();
		
		foreach($attachInfos as $attach) {
			$attachData[$attach['attach_id']] = $attach;
		}

		foreach($data as &$value) {
			if(!empty($value['attach']) && !empty($attachData[$value['attach']])) {
				$value['icon_url'] = getImageUrl($attachData[$value['attach']]['save_path'].$attachData[$value['attach']]['save_name']);
			} else {
				$value['icon_url'] = null;
			}
			unset($value['ext'],$value['attach'],$value['user_bind'],$value['topic_bind']);
		}
		
		return $data;
	}

	/**
	 * 获取指定分类下的微博数据
	 * @param integer $cid 分类ID
	 * @param integer $sinceId 起始资源ID
	 * @param integer $maxId 最大资源ID
	 * @param integer $count 每页数目
	 * @param integer $page 分页数目
	 * @return array 指定分类下的微博数据
	 */
	public function getChannelFeed($cid, $sinceId, $maxId, $count, $page)
	{
		$cid = intval($cid);
        $sinceId = intval($sinceId);
        $maxId = intval($maxId);
        $count = intval($count);
        $page = intval($page);
        // 组装查询条件
        $where = "`status` = 1 AND `channel_category_id` = {$cid} ";
        if(!empty($sinceId) || !empty($maxId)) {
            !empty($sinceId) && $where .= " AND `feed_id` > {$sinceId}";
            !empty($maxId) && $where .= " AND `feed_id` < {$maxId}";
        }
        $start = ($page - 1) * $count;
        $end = $count;
        $feedIds = D('channel')->where($where)->limit("$start, $end")->order('feed_id DESC')->field('feed_id')->getAsFieldArray('feed_id');
        $data = model('Feed')->formatFeed($feedIds, true);
        
        return $data;
	}
}