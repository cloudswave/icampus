<?php
/**
 * 相册模型 - 数据对象模型
 */
class AlbumModel extends Model {

	protected $tableName = 'photo_album';

	/**
	 * 为新用户创建默认数据
	 * @param integer $uid 用户UID
	 * @return void
	 */
	public function createNewData ($uid = 0) {
		// 创建默认相册
		if (intval($uid) <= 0) {
			$uid = $GLOBALS['ts']['mid'];
		}
		$count = $this->where("userId='$uid' AND isDel=0")->count();
		if ($count == 0) {
			$name = getShort(getUserName($uid),5).'的相册';			// 默认的相册名
			$album['cTime'] = time();
			$album['mTime'] = time();
			$album['userId'] = $uid;
			$album['name'] = $name;
			$album['privacy'] = 1;
			$this->add($album);
		}
	}

	/**
	 * 更新相册图片数量
	 * @param integer $aid 相册ID
	 * @return boolean 是否更新成功
	 */
	public function updateAlbumPhotoCount ($aid) {
		$count = D('Photo', 'photo')->where("albumId='$aid' AND is_del=0")->count();
		$map['photoCount'] = $count;
		$result = $this->where("id='$aid'")->save($map);
		return (boolean)$result;
	}

	/**
	 * 设置相册封面
	 * @param integer $albumId 相册ID
	 * @param integer $cover 封面图片ID
	 * @return boolean 是否设置成功
	 */
	public function setAlbumCover ($albumId, $cover = 0) {
		// 插入图片封面
		$cover_info = D('Photo')->where("id='$cover'")->find();
		if ($cover > 0 && $cover_info) {
			$map['coverImageId'] = $cover_info['id'];
			$map['coverImagePath'] = $cover_info['savepath'];
		}
		$map['mTime'] = time();
		// 更新相册信息
		$result = $this->where("id='$albumId'")->save($map);
		return (boolean)$result;
	}

	/**
	 * 通过相册ID获取图片集
	 * @param integer $uid 用户UID
	 * @param integer $albumId 相册ID
	 * @param string $type 获取类型，mAll或者其他
	 * @param string $order 排序方式
	 * @param integer $limit 显示结果集个数，默认为5
	 * @return array 相册ID获取图片集
	 */
	public function getPhotos ($uid, $albumId, $type, $order = 'id ASC', $limit = 5) {
		if ($type == 'mAll') {
			// 某个人的全部图片
			$map['userId'] = $uid;
		} else {
			// 某个专辑的全部图片(无type下默认)
			$map['albumId'] = $albumId;
			$map['userId'] = $uid;
		}
		$map['is_del'] = 0;
		$result = D('Photo', 'photo')->order($order)->where($map)->limit($limit)->findAll();
		return $result;
	}

	/**
	 * 删除相册
	 * @param mixed $aids 相册ID数组
	 * @param integer $uid 用户UID
	 * @param integer $isAdmin 是否为管理员操作
	 * @return boolean 是否删除成功
	 */
	public function deleteAlbum ($aids, $uid, $isAdmin = 0) {
		// 解析ID成数组
		$aids = is_array($aids) ? $aids : explode(',', $aids);
		// 非管理员只能删除自己的图片
		!$isAdmin && $map['userId'] = $uid;		
		// 同步删除图片及附件
		$album['albumId'] = array('IN', $aids);
		$photos = D('Photo', 'photo')->field('id')->where($album)->findAll();
		foreach ($photos as $v) {
			$photoIds[] = $v['id'];
		}
		// 处理图片及附件
		$this->deletePhoto($photoIds, $uid, $isAdmin, $delFile);
		// 删除相册
		$map['id'] = array('IN', $aids);
		$result = $this->where($map)->delete();			

		return (boolean)$result;
	}

	/**
	 * 删除图片
	 * @param mixed $pids 图片ID数组
	 * @param integer $uid 用户UID
	 * @param integer $isAdmin 是否为管理员操作
	 * @return boolean 是否删除成功
	 */
	public function deletePhoto ($pids, $uid, $isAdmin = 0) {
		// 解析ID成数组
		$pids = is_array($pids) ? $pids : explode(',', $pids);
		// 非管理员只能删除自己的图片
		!$isAdmin && $map['userId'] = $uid;
		// 获取图片信息
		$photoDao = D('Photo', 'photo');
		$map['id'] = array('IN', $pids);
		$photos = $photoDao->where($map)->findAll();
		// 删除封面
		foreach ($photos as $key => $value) {
			$id = $value['albumId'];
			$data['coverImageId'] = '';
			$data['coverImagePath'] = '';
			D('Album')->where(array('id'=>$id))->save($data);
		}
		$result = $photoDao->where($map)->delete();
		if ($result) {
			foreach ($photos as $v) {
				$attachIds[] = $v['attachId'];
				// 重置相册图片数
				$this->updateAlbumPhotoCount($v['albumId']);
			}
			//处理附件			
			model('Attach')->doEditAttach($attachIds, 'deleteAttach');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 获取热门相册
	 * @param integer $limit 结果集个数，默认为3
	 * @return array 热门相册列表
	 */
	public function getHotList ($limit = 3) {
		$map['isHot'] = 1;
		$map['photoCount'] = array('NEQ', 0);
		$map['isDel'] = 0;
		$list = $this->where($map)->order('cTime DESC')->limit($limit)->findAll();
		return $list;
	}

	/**
	 * 获取最新相册
	 * @param integer $limit 结果集个数，默认为3
	 * @return array 最新相册列表
	 */
	public function getNewList ($limit = 3) {
		$map['photoCount'] = array('NEQ', 0);
		$map['isDel'] = 0;
		$list = $this->where($map)->order('cTime DESC')->limit($limit)->findAll();
		return $list;
	}
}