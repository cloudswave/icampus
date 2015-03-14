<?php
/**
 * 微博置顶插件模型 - 数据对象模型
 * @author feebas <reedgu@163.com>
 * @version TS3.0
 */
class FeedTopModel extends Model
{
	protected $tableName = 'feed_top';
	protected $_error;
	/**
	 * 添加微博置顶数据
	 * @param array $data 广告位相关数据
	 * @return boolean 是否插入成功
	 */
	public function doAddFeedTop($data)
	{
		$res = $this->add($data);
		return (boolean)$res;
	}
	/**
	 * 获取微博置顶数据
	 * @return array 广告位列表数据
	 */
	public function getFeedTopList($type)
	{
		if($type==1){
			$data = $this->where('status = 0')->order('id DESC')->findAll();
		}else if($type==0){
			$data = $this->limit(6)->order('id DESC')->findAll();
		}else{
			$data = $this->order('id DESC')->findpage(20);
		}
		return $data;
	}
	public function doEditFeedTop($id, $data)
	{
		if(empty($id)) {
			return false;
		}
		$map['id'] = $id;
		$res = $this->where($map)->save($data);
		return (boolean)$res;
	}
	/**
	 * 删除微博置顶操作
	 * @param string|array $ids 广告位ID
	 * @return boolean 是否删除广告位成功
	 */
	public function doDelFeedTop($id)
	{
		if(empty($id)) {
			return false;
		}
		
		$map['id'] = $id;
		$data['status'] = 1;
		$res = $this->where($map)->save($data);
		return (boolean)$res;
	}
	public function doFeedTop($id)
	{
		if(empty($id)) {
			return false;
		}
		
		$map['id'] = $id;
		$data['status'] = 0;
		$res = $this->where($map)->save($data);
		return (boolean)$res;
	}
	public function	doDel($id)
	{
		if(empty($id)) {
			return false;
		}
		$map['id'] = $id;
		$res = $this->where($map)->delete();
		return (boolean)$res;
	}
}