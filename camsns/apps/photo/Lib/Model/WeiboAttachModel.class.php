<?php
/**
 * 微博图片模型 - 业务逻辑对象
 */
class WeiboAttachModel {

	/**
	 * 获取指定用户的微博附件统计信息
	 * @param integer $uid 用户UID
	 * @return array 指定用户的微博附件统计信息
	 */
	public function getUserAttachCount ($uid) {
		if (empty($uid)) {
			return false;
		}
		// 获取统计信息
		$map['uid'] = $uid;
		$map['type'] = 'postimage';
		$map['is_del'] = 0;
		$count = model('Feed')->where($map)->count();

		return $count;
	}

	/**
	 * 获取指定用户的微博图片附件
	 * @param integer $uid 用户UID
	 * @param integer $limit 结果集数目，默认为20
	 * @return array 指定用户的微博图片附件
	 */
	public function getUserAttachDataNew ($uid, $limit = 20) {
		$map['a.uid'] = $uid;
		$map['a.type'] = 'postimage';
		$map['is_del'] = 0;
		$list = D()->table('`'.C('DB_PREFIX').'feed` AS a LEFT JOIN `'.C('DB_PREFIX').'feed_data` AS b ON a.`feed_id` = b.`feed_id`')
		   		   ->field('a.`feed_id`, a.`publish_time`, b.`feed_data`')
		   		   ->where($map)
		   		   ->order('feed_id DESC')
		   		   ->findPage($limit);

		// 获取附件信息
		foreach ($list['data'] as &$value) {
			$tmp = unserialize($value['feed_data']);
			$attachId = is_array($tmp['attach_id']) ? intval($tmp['attach_id'][0]) : intval($tmp['attach_id']);
			$attachInfo = model('Attach')->getAttachById($attachId);
			$value['savepath'] = $attachInfo['save_path'].$attachInfo['save_name'];
			$value['name'] = $attachInfo['name'];
			$value['body'] = $tmp['body'];
		}

		return $list;
	}

	/**
	 * 获取指定用户的微博图片附件 - 不分页
	 * @param integer $uid 用户UID
	 * @param integer $limit 结果集数目，默认为20
	 * @return array 指定用户的微博图片附件
	 */
	public function getUserAttachData ($uid, $limit = 20) {
		$map['a.uid'] = $uid;
		$map['a.type'] = 'postimage';
		$map['is_del'] = 0;
		$list = D()->table('`'.C('DB_PREFIX').'feed` AS a LEFT JOIN `'.C('DB_PREFIX').'feed_data` AS b ON a.`feed_id` = b.`feed_id`')
		   		   ->field('a.`feed_id`, a.`publish_time`, b.`feed_data`')
		   		   ->where($map)
		   		   ->order('feed_id DESC')
		   		   ->limit($limit)
		   		   ->findAll();

		// 获取附件信息
		foreach ($list as &$value) {
			$tmp = unserialize($value['feed_data']);
			$attachId = is_array($tmp['attach_id']) ? intval($tmp['attach_id'][0]) : intval($tmp['attach_id']);
			$attachInfo = model('Attach')->getAttachById($attachId);
			$value['savepath'] = $attachInfo['save_path'].$attachInfo['save_name'];
			$value['name'] = $attachInfo['name'];
			$value['body'] = $tmp['body'];
		}

		return $list;
	}

	/**
	 * 获取微博相册
	 * @param integer $uid 用户UID
	 * @return array 微博相册相关数据
	 */
	public function getWeiboAlbum ($uid) {
		// 获取统计数目
		$count = $this->getUserAttachCount($uid);
		// 获取最后一个图片微博
		if ($count > 0) {
			$map['uid'] = $uid;
			$map['attach_type'] = 'feed_image';
			$map['is_del'] = 0;
			$lastFeedAttach = model('Attach')->where($map)->order('`ctime` DESC')->limit(1)->find();
		}
		// 微博相册信息
		$weibo['id'] = 0;
		$weibo['userId'] = $uid;
		$weibo['name'] = '微博相册';
		$weibo['info'] = null;
		$weibo['cTime'] = !empty($lastFeedAttach['ctime']) ? $lastFeedAttach['ctime'] : time();
		$weibo['mTime'] = !empty($lastFeedAttach['ctime']) ? $lastFeedAttach['ctime'] : time();
		$weibo['coverImageId'] = $lastFeedAttach['attach_id'];
		$weibo['coverImagePath'] = $lastFeedAttach['save_path'].$lastFeedAttach['save_name'];
		$weibo['photoCount'] = $count;
		$weibo['readCount'] = 0;
		$weibo['status'] = 1;
		$weibo['isHot'] = 0;
		$weibo['rTime'] = 0;
		$weibo['share'] = 0;
		$weibo['privacy'] = 0;
		$weibo['privacy_data'] = null;
		$weibo['isDel'] = 0;

		return $weibo;
	}
}