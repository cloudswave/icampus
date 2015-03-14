<?php
/**
 * 相册列表Widget
 */
class AlbumListWidget extends Widget {

	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 相册内容渲染入口
	 */
	public function render ($data) {
		// 设置频道模板
		$template = empty($data['tpl']) ? 'hot' : t($data['tpl']);
		// 配置参数
		switch ($template) {
			case 'hot':
				$var['title'] = '推荐';
				$var['list'] = $this->_getHostAlbum();
				break;
			case 'new':
				$var['title'] = '最新上传的';
				$var['list'] = $this->_getNewAlbum();
				break;
			case 'photo':
				$var['albumId'] = intval($data['albumId']);
				$var['uid'] = intval($data['uid']);
				$var['list'] = $this->_getPhotoAlbum($var['albumId'], $var['uid']);
				// 选中图片数目
				$var['photoId'] = intval($data['photoId']);
				$sort = getSubByKey($var['list'], 'id');
				$var['checkNum'] = array_search($var['photoId'], $sort) + 1;
				// 选择页数
				$var['pageKey'] = ceil((array_search(intval($data['photoId']), $sort) + 1) / 9);
				break;
		}
		// 如果数据为空
		if (empty($var['list'])) {
			return false;
		}

		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
    }

    /**
     * 获取热门相册
     * @return array 热门相册数据
     */
    private function _getHostAlbum () {
    	$list = D('Album', 'photo')->getHotList();
    	return $list;
    }

    /**
     * 获取最新相册
     * @return array 最新相册数据
     */
    private function _getNewAlbum () {
    	$list = D('Album', 'photo')->getNewList();
    	return $list;
    }

    /**
     * 获取相册详细照片列表
     * @param integer $albumId 相册ID
     * @param integer $uid 用户UID
     * @return array 相册详细照片列表
     */
    private function _getPhotoAlbum ($albumId, $uid) {
    	$list = D('Album', 'photo')->getPhotos($uid, $albumId, '', '`order` DESC, `id` DESC', 0);
    	return $list;
    }
}