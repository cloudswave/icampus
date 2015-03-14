<?php
/**
 * 微博赞模型
 * @version TS3.0
 */
class FeedDiggModel extends Model {
	var $tableName = 'feed_digg';
	protected $fields = array (
			0 => 'id',
			1 => 'uid',
			2 => 'feed_id',
			3 => 'cTime',
			'_pk' => 'id'
	);	
	
	public function addDigg($feed_id, $mid) {
		$data ['feed_id'] = $feed_id;
		$data ['uid'] = $mid;
		$data['uid'] = !$data['uid'] ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !$data['uid'] ){
			$this->error = '未登录不能赞';
			return false;
		}
		$isExit = $this->where ( $data )->getField ( 'id' );
		if ($isExit) {
			$this->error = '你已经赞过';
			return false;
		}
		
		$data ['cTime'] = time ();
		$res = $this->add ( $data );
		//dump($res);dump($this->getLastSql());
		if($res){
			$feed = model ( 'Source' )->getSourceInfo ( 'feed', $feed_id );
			$result = model('Feed')->where('feed_id='.$feed_id)->setInc('digg_count');
			model('Feed')->cleanCache($feed_id);
				// dump($result);dump(model('Feed')->getLastSql());
				
			// 增加通知::  {user} 赞了你的微博：{content}。<a href="{sourceurl}" target='_blank'>去看看>></a>
			$author = model ( 'User' )->getUserInfo ( $mid );
			$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';
			
			$config ['content'] = t($feed ['source_body']);
			$config ['content'] = str_replace('◆','',$config ['content']);
			$config ['content'] = mStr($config ['content'], 34);
			$config ['sourceurl'] = $feed ['source_url'];
			model ( 'Notify' )->sendNotify ( $feed['uid'], 'digg', $config );
						
			//增加积分
			model('Credit')->setUserCredit($mid, 'digg_weibo');
			model('Credit')->setUserCredit($feed['uid'], 'digged_weibo');			
		}
		return $res;
	}

	public function delDigg ($feed_id, $mid) {
		$data['feed_id'] = $feed_id;
		$data['uid'] = $mid;
		$data['uid'] = !$data['uid'] ? $GLOBALS['ts']['mid'] : $data['uid'];
		if ( !$data['uid'] ){
			$this->error = '未登录不能取消赞';
			return false;
		}
		$isExit = $this->where($data)->getField('id');
		if (!$isExit) {
			$this->error = '取消赞失败，您可以已取消过赞信息';
			return false;
		}

		$res = $this->where($data)->delete();

		if ($res) {
			$feed = model('Source')->getSourceInfo('feed', $feed_id);
			$result = model('Feed')->where('feed_id='.$feed_id)->setDec('digg_count');
			model('Feed')->cleanCache($feed_id);
		}

		return $res;
	}

	public function checkIsDigg($feed_ids, $uid) {
		if (! is_array ( $feed_ids ))
			$feed_ids = array (
					$feed_ids 
			);
		
		$feed_ids = array_filter($feed_ids);
		$map ['feed_id'] = array (
				'in',
				$feed_ids 
		);
		$map ['uid'] = $uid;
		$list = $this->where ( $map )->field ( 'feed_id' )->findAll ();
		foreach ( $list as $v ) {
			$res [$v ['feed_id']] = 1;
		}
		
		return $res;
	}

	public function getLastError () {
		return $this->error;
	}
}