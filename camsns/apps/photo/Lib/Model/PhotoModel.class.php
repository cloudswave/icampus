<?php
/**
 * 照片模型 - 数据对象模型
 */
class PhotoModel extends Model {
	
	protected $tableName = 'photo';

	/**
	 * 获取指定类型的照片数据 - 分页数据
	 * @param string $type 数据类型分类
	 * @param integer $loadId 载入ID
	 * @param integer $limit 一次载入的结果集数目，默认为20
	 * @return array 指定类型的照片数据 - 分页数据
	 */
	public function getDataWithType ($type, $loadId, $limit = 20) {
		if ($type === 'hot') {
			$map['a.is_del'] = 0;
			$map['a.status'] = 1;
			$map['b.privacy'] = 1;
			$order = 'a.readCount DESC, a.commentCount DESC, a.cTime DESC';
			// 获取分页数据
			$result = D()->table('`'.$this->tablePrefix.'photo` AS a LEFT JOIN `'.$this->tablePrefix.'photo_album` AS b ON a.albumId = b.id')->field('a.*')->where($map)->order($order)->findPage($limit * 4);
			$ids = getSubByKey($result['data'], 'id');
			// 获取数据
			if ($_REQUEST['newload']) {
				$loadId = $result['data'][0]['id'] - 1;
			}
			$page = intval($_REQUEST['p']);
			$p = 1;
			if ($page != 1 && empty($loadId)) {
				$data = array();
			} else {
				if (!empty($loadId)) {
					$key = array_search($loadId, $ids);
					$key += 1;
					$p = $key / $limit;
					$p += 1;
				} else {
					$loadId = 0;
				}
				$limit = (($limit * 4) * ($page - 1) + ($p - 1) * $limit).', '.$limit;
				$data = D()->table('`'.$this->tablePrefix.'photo` AS a LEFT JOIN `'.$this->tablePrefix.'photo_album` AS b ON a.albumId = b.id')->field('a.*')->where($map)->order($order)->limit($limit)->findAll();
			}
		} else if ($type === 'new') {
			$map['a.is_del'] = 0;
			$map['a.status'] = 1;
			$map['b.privacy'] = 1;
			$order = 'a.id DESC';
			// 获取分页数据
			$result = D()->table('`'.$this->tablePrefix.'photo` AS a LEFT JOIN `'.$this->tablePrefix.'photo_album` AS b ON a.albumId = b.id')->field('a.*')->where($map)->order($order)->findPage($limit * 4);
			// 获取数据
			if ($_REQUEST['newload']) {
				$loadId = $result['data'][0]['id'] - 1;
			}
			!empty($loadId) && $map['a.id'] = array('LT', $loadId);
			$data = D()->table('`'.$this->tablePrefix.'photo` AS a LEFT JOIN `'.$this->tablePrefix.'photo_album` AS b ON a.albumId = b.id')->field('a.*')->where($map)->order($order)->limit($limit)->findAll();
		} else if ($type === 'following') {
			$map['b.is_del'] = 0;
			$map['b.status'] = 1;
			$map['a.uid'] = $GLOBALS['ts']['mid'];
			$map['b.attachId'] = array('exp', 'IS NOT NULL');
			$map['c.privacy'] = 1;
			$order = 'b.id DESC';
			// 获取分页数据
			$result = D()->table('`'.$this->tablePrefix.'user_follow` AS a LEFT JOIN `'.$this->tablePrefix.'photo` AS b ON b.`userId` = a.`fid` LEFT JOIN `'.$this->tablePrefix.'photo_album` AS c ON b.albumId = c.id')->field('b.*')->where($map)->order($order)->findPage($limit * 4);
			// 获取数据
			if ($_REQUEST['newload']) {
				$loadId = $result['data'][0]['id'] - 1;
			}
			!empty($loadId) && $map['b.id'] = array('LT', $loadId);
			$data = D()->table('`'.$this->tablePrefix.'user_follow` AS a LEFT JOIN `'.$this->tablePrefix.'photo` AS b ON b.`userId` = a.`fid` LEFT JOIN `'.$this->tablePrefix.'photo_album` AS c ON b.albumId = c.id')->field('b.*')->where($map)->order($order)->limit($limit)->findAll();
		}
		// 设置指定的宽高
		$data = $this->_formatImageSize($data, 195);
		$result['data'] = $data;

		return $result;
	}

	/**
	 * 格式化图片的大小，使瀑布流图片显示正常
	 * @param array $data 频道数据数组，包含宽高数据
	 * @param integer $width 格式化后的宽度，默认225px
	 * @return array 格式化宽高后的数据
	 */
	private function _formatImageSize ($data, $width = 225) {
		if(empty($data)) {
			return array();
		}
		foreach($data as &$value) {
			$attach = model('Attach')->getAttachById($value['attachId']);
			$value['height'] = ceil($width * $attach['height'] / $attach['width']);
			$value['width'] = $width;
		}

		return $data;
	}
}