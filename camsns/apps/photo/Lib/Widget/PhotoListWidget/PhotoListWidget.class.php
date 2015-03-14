<?php
/**
 * 照片瀑布流Widget
 */
class PhotoListWidget extends Widget {

	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 照片内容渲染入口
	 */
	public function render ($data) {
		// 设置照片模板
		$template = empty($data['tpl']) ? 'load' : t($data['tpl']);
		// 配置参数
		$var['type'] = t($data['type']); 

		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
    }

    /**
     * 载入照片内容
     * @return json 照片渲染内容
     */
    public function loadMore () {
    	$loadLimit = intval($_REQUEST['loadlimit']);
    	$loadId = intval($_REQUEST['loadId']);
    	$type = t($_REQUEST['type']);
    	// 获取HTML数据
    	$content = $this->getData($type, $loadLimit, $loadId);
		// 查看是否有更多数据
		if(empty($content['data']) && empty($content['pageHtml'])) {
			$return['status'] = 0;
			$return['msg'] = '没有更多照片';
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

    /**
     * 获取照片数据
     * @param string $type 分类数据类型
     * @param integer $loadLimit 一次加载的结果集数目
     * @param integer $loadId 载入ID
     * @return array 照片流数据
     */
    public function getData ($type, $loadLimit, $loadId) {
		// 获取照片数据
		$list = D('Photo', 'photo')->getDataWithType($type, $loadId, $loadLimit);
    	// 分页的设置
    	if(!empty($list['data'])) {
    		$content['firstId'] = $var['firstId'] = $list['data'][0]['id'];
    		$content['lastId'] = $list['data'][(count($list['data'])-1)]['id'];
            $var['data'] = $this->_formatContent($list['data']);
    	}
    	$content['data'] = $list['data'];
		$content['pageHtml'] = $list['html'];
	    // 渲染模版
		$content['html'] = fetch(dirname(__FILE__).'/_load.html', $var);

	    return $content;    	
    }

	/**
	 * 处理图片附件数据
	 * @param array $data 照片关联数组信息
	 * @return array 处理后的微博数据
	 */
	private function _formatContent ($data) {
		// 获取图片的相册信息
		$albumIds = getSubByKey($data, 'albumId');
		$albumIds = array_unique($albumIds);
		$albumIds = array_filter($albumIds);
		$map['id'] = array('IN', $albumIds);
		$albumHash = D('Album', 'photo')->where($map)->getHashList('id', 'name');
		foreach ($data as &$value) {
			$value['albumName'] = $albumHash[$value['albumId']];
		}

		return $data;
	}
}