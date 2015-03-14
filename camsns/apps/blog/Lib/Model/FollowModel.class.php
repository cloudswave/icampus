<?php
class FollowModel extends Model{
	var $tableName = 'weibo_follow';
	//添加关注
	function dofollow( $mid,$fid,$type=0 ){
		if( $mid!=$fid ){
			$map['uid'] = $mid;
			$map['fid'] = $fid;
			$map['type'] = $type;
			if( 0==$this->where($map)->count() ){
				$this->add( $map );
				unset($map);
				//关注记录 - 漫游使用
				if($type==0){
					$map['uid']			= $mid;
					$map['fuid']		= $fid;
					$map['action']		= 'add';
					$map['dateline']	= time();
					M('myop_friendlog')->add($map);
					x('Notify')->send($fid,'weibo_follow'); //通知发送
					x('Feed')->put('weibo_follow',array('fid'=>$fid));
				}
				if(0==$this->where("uid=$fid AND fid=$mid AND type=$type")->count() ){
					return '12'; //我已关注
				}else{
					return '13'; //双方已关注
				}
			}else{
				return '11'; //已关注过
			}
		}else{
			return '10'; //不能关注自己
		}
	}
	//取消关注
	function unfollow( $mid,$fid,$type=0 ){
		$map['uid']  = $mid;
		$map['fid']  = $fid;
		$map['type'] = $type;
		if( $this->where($map)->delete() ){
			//关注记录 - 漫游使用
			unset($map);
			$map['uid']			= $mid;
			$map['fuid']		= $fid;
			$map['action']		= 'delete';
			$map['dateline']	= time();
			M('myop_friendlog')->add($map);
			return '01'; //取消成功
		}else{
			return '00'; //取消失败
		}
	}
	//获取话题关注状态
	function getTopicState($mid,$name){
		$topicId = D('Topic','miniblog')->getTopicId($name);
		if($topicId){
			return $this->where("uid=$mid AND fid=$topicId AND type=1")->count();
		}else{
			return false;
		}
	}
	//获取关注话题的列表
	function getTopicList($mid){
		$list = $this->query("SElECT a.* FROM {$this->tablePrefix}weibo_topic a LEFT JOIN {$this->tablePrefix}weibo_follow b ON b.fid=a.topic_id WHERE b.uid=$mid AND b.type=1");
		return $list;
	}
	//获取关注状态
	function getState( $uid , $fid , $type=0 ){
		return getFollowState( $uid,$fid);
	}
	//获取关注列表
	function getList( $uid , $operate ,$type=0 ){
		global $ts;
		if( $operate == 'following' ){ //关注
			$list = $this->where("uid=$uid AND type=$type")->findpage(10);
		}else{ //粉丝
			$list = $this->where("fid=$uid AND type=$type")->findpage(10);
			foreach ($list['data'] as $key=>$value){
				$uid = $value['uid'];
				$fid = $value['fid'];
				$list['data'][$key]['uid'] = $fid;
				$list['data'][$key]['fid'] = $uid;
			}
		}
		foreach ($list['data'] as $k=>$v){
			$list['data'][$k]['mini'] = M('weibo')->where('uid='.$v['fid'].' AND type='.$type)->order('weibo_id DESC')->find();
			$list['data'][$k]['user'] = M('user')->where('uid='.$v['fid'])->field('location')->find();
			$list['data'][$k]['following'] = $this->where('uid='.$v['fid'].' AND type='.$type)->count();
			$list['data'][$k]['follower']  = $this->where('fid='.$v['fid'].' AND type='.$type)->count();
			$list['data'][$k]['followState']  = $this->getState( $ts['user']['uid'] , $v['fid'] );
		}
		return $list;
	}
	function getfollowList($uid){
		$list= $this->field('fid')->where("uid=$uid AND type=0")->findall();
		return $list;
	}
    //搜索用户
    function doSearchUser($key){
    	global $ts;
    	if($key){
    		$list = $this->table(C('DB_PREFIX').'user')->where("uname LIKE '%{$key}%'")->findpage();
    	   	foreach ($list['data'] as $k=>$v){
				$list['data'][$k]['mini'] = M('weibo')->where('uid='.$v['uid'].' AND type=0')->order('weibo_id DESC')->find();
				$list['data'][$k]['following'] = $this->where('uid='.$v['uid'])->count();
				$list['data'][$k]['follower']  = $this->where('fid='.$v['uid'])->count();
				$list['data'][$k]['followState']  = $this->getState( $ts['user']['uid'] , $v['uid'] );
				$list['data'][$k]['area']         = $v['location'];
			}
    	}else{
    		$list['count'] = 0;
    	}
    	return $list;
    }
}
?>