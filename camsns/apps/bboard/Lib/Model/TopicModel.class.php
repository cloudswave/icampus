<?php
/**
 * 频道分类模型 - 数据对象模型
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class TopicModel extends Model
{
	protected $tableName = 'bboard_topic';
	// protected $fields =	array('channel_category_id', 'title', 'pid');

	/**
	 * 当指定pid时，查询该父分类的所有子分类；否则查询所有分类
	 * @param integer $pid 父分类ID
	 * @return array 相应的分类列表
	 */
	public function getTopicList($limit = 20, $map = array())
	{	
		if(isset($_POST)) {
		/*	//搜索时用到
			$_POST['weiba_id'] && $map['weiba_id']=intval($_POST['weiba_id']);
			$_POST['weiba_name'] && $map['weiba_name']=array('like','%'.$_POST['weiba_name'].'%');
			$_POST['uid'] && $map['uid']=intval($_POST['uid']);
			$_POST['admin_uid'] && $map['admin_uid']=intval($_POST['admin_uid']);
			$_POST['recommend'] && $map['recommend']=$_POST['recommend']==1?1:0;*/
		}		

		// 查询数据
		$list = $this->where($map)->order('topic_id desc,topic_time desc')->findPage($limit);
		
		// 数据组装
		foreach($list['data'] as $k => $v) {
			$list['data'][$k]['weiba_name'] = '<a target="_blank" href="'.U('weiba/Index/detail',array('weiba_id'=>$v['weiba_id'])).'">'.$v['weiba_name'].'</a>';
			$create_uid = model('User')->getUserInfoByUids($v['topic_uid']);
			$list['data'][$k]['topic_uid'] = $create_uid[$v['topic_uid']]['space_link'];
			$list['data'][$k]['topic_time'] = friendlyDate($v['topic_time']);
			$list['data'][$k]['DOACTION'] = '<a href="'.U('bboard/Admin/editTopic',array('topic_id'=>$v['topic_id'],'tabHash'=>'editTopic')).'">编辑</a>|<a onclick="admin.delTopic('.$v['topic_id'].');" href="javascript:void(0)">删除</a>';
		}
		return $list;
	}
	public function getData($limit = 20, $map = array())
	{	
		if(isset($_POST)) {
		/*	//搜索时用到
			$_POST['weiba_id'] && $map['weiba_id']=intval($_POST['weiba_id']);
			$_POST['weiba_name'] && $map['weiba_name']=array('like','%'.$_POST['weiba_name'].'%');
			$_POST['uid'] && $map['uid']=intval($_POST['uid']);
			$_POST['admin_uid'] && $map['admin_uid']=intval($_POST['admin_uid']);
			$_POST['recommend'] && $map['recommend']=$_POST['recommend']==1?1:0;*/
		}		

		// 查询数据
		$list = $this->where($map)->order('topic_time desc')->select();
		
		// 数据组装
		foreach($list as $k => $v) {
			$create_uid = model('User')->getUserInfoByUids($v['topic_uid']);
			$list[$k]['topic_uid'] = $create_uid[$v['topic_uid']]['space_link'];
			$list[$k]['topic_time'] = date('Y-m-d',$v['topic_time']);
		}
		return $list;
	}
}