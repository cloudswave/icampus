<?php
/**
 * 招贴子分类关注模型 - 数据对象模型
 * @author 朱小波
 * @version 1.0
 */
class PosterSmallTypeFollowModel extends Model
{
	protected $tableName = 'poster_smalltype_follow';

	/**
	 * 获取指定分类的关注数目
	 * @param integer $cid 分类ID
	 * @return integer 指定分类的关注数目
	 */
	public function getFollowingCount($cid)
	{
		!empty($cid) && $map['poster_smalltype_id'] = intval($cid);
		$count = $this->where($map)->count();

		return $count;
	}

	/**
	 * 更新关注状态
	 * @param integer $uid 关注用户ID
	 * @param integer $cid 分类ID
	 * @param string $type 更新操作，add or del
	 * @return boolean 更新频道关注状态是否成功
	 */
	public function upFollow($uid, $cid, $type)
	{
		// 验证数据的正确性
		if(empty($uid) || empty($cid)) {
			return false;
		}
		$result = false;
		// 更新状态修改
		switch($type) {
			case 'add':
				// 验证是否已经添加关注
				$map['uid'] = $uid;
				$map['poster_smalltype_id'] = $cid;
				$isExist = $this->where($map)->count();
				if($isExist == 0) {
					$data['uid'] = $uid;
					$data['poster_smalltype_id'] = $cid;
					$result = $this->add($data);
					$result = (boolean)$result;
				}
				break;
			case 'del':
				$map['uid'] = $uid;
				$map['poster_smalltype_id'] = $cid;
				$result = $this->where($map)->delete();
				$result = (boolean)$result;
				break;
		}

		return $result;
	}

	/**
	 * 获取指定用户与指定分类的关注状态
	 * @param integer $uid 用户ID
	 * @param integer $cid 分类ID
	 * @return boolean 返回是否关注
	 */
	public function getFollowStatus($uid, $cid)
	{
		$map['uid'] = $uid;
		$map['poster_smalltype_id'] = $cid;
		$count = $this->where($map)->count();
		$result = ($count == 0) ? false : true;

		return $result;
	}

	/**
	 * 获取指定用户的关注列表
	 * @param integer $uid 指定用户ID
	 * @return array 指定用户的关注列表
	 */
	public function getFollowList($uid)
	{
		if(empty($uid)) {
			return array();
		}
		$map['f.uid'] = $uid;
		$list = D()->table("`".C('DB_PREFIX')."poster_smalltype_follow` AS f LEFT JOIN `".C('DB_PREFIX')."poster_small_type` AS c ON f.poster_smalltype_id=c.id")
				   ->field('c.`id`, c.`name`')
				   ->where($map)
				   ->findAll();

		return $list;
	}


	/**
	 * 获取指定分类的用户列表
	 * @param integer $cid 分类
	 * @return array 用户列表
	 */
	public function getFollowUserList($cid)
	{

		$map['poster_smalltype_id'] = $cid;
		$list = $this->where($map)->findAll();

		return $list;
	}


}