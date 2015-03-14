<?php
/**
 * 资源模型 - 业务逻辑模型
 * @example
 * 根据表名及资源ID，获取对应的资源信息
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class SourceModel {
	
	/**
	 * 获取指定资源，并格式化输出
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $_forApi
	 *        	是否提供API，默认为false
	 * @param string $appname
	 *        	自定应用名称，默认为public
	 * @return [type] [description]
	 */
	public function getSourceInfo($table, $row_id, $_forApi = false, $appname = 'public') {
		static $forApi = '0';
		$forApi == '0' && $forApi = intval ( $_forApi );
		
		$key = $forApi ? $table . $row_id . '_api' : $table . $row_id;
		if ($info = static_cache ( 'source_info_' . $key )) {
			return $info;
		}
		switch ($table) {
			case 'feed' :
				$info = $this->getInfoFromFeed ( $table, $row_id, $_forApi );
				break;
			case 'comment' :
				$info = $this->getInfoFromComment ( $table, $row_id, $_forApi );
				break;
			case 'poster' :
				$poster = D ( 'poster' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $poster ['uid'] );
				$info ['source_url'] = U ( 'poster/Index/posterDetail', array (
						'id' => $row_id 
				) );
				$info ['source_body'] = $poster ['title'] . '<a class="ico-details" href="' . U ( 'poster/Index/posterDetail', array (
						'id' => $row_id 
				) ) . '"></a>';
				break;
			case 'event' :
				$event = D ( 'event' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $event ['uid'] );
				$info ['source_url'] = U ( 'event/Index/eventDetail', array (
						'id' => $row_id,
						'uid' => $event ['uid'] 
				) );
				$info ['source_body'] = $event ['title'] . '<a class="ico-details" href="' . U ( 'event/Index/eventDetail', array (
						'id' => $row_id,
						'uid' => $event ['uid'] 
				) ) . '"></a>';
				break;
			case 'blog' :
				$blog = D ( 'blog' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $blog ['uid'] );
				$info ['source_url'] = U ( 'blog/Index/show', array (
						'id' => $row_id,
						'mid' => $blog ['uid'] 
				) );
				$info ['source_body'] = $blog ['title'] . '<a class="ico-details" href="' . U ( 'blog/Index/show', array (
						'id' => $row_id,
						'mid' => $blog ['uid'] 
				) ) . '"></a>';
				break;
			case 'photo':
				$photo = D('photo')->where('id='.$row_id)->field('name, albumId, userId')->find();
				$info['source_user_info'] = model('User')->getUserInfo($photo['userId']);
				$info['source_url'] = U('photo/Index/photo', array('id'=>$row_id, 'aid'=>$photo['albumId'], 'uid'=>$photo['userId']));
				$info['source_body'] = $photo['name'].'<a class="ico-details" href="'.$info['source_url'].'"></a>';
				break;
			case 'vote' :
				$vote = D ( 'vote' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $vote ['uid'] );
				$info ['source_url'] = U ( 'vote/Index/pollDetail', array (
						'id' => $row_id 
				) );
				$info ['source_body'] = $vote ['title'] . '<a class="ico-details" href="' . U ( 'vote/Index/pollDetail', array (
						'id' => $row_id 
				) ) . '"></a>';
				break;
			case 'develop':
				$develop = D('develop')->where('develop_id='.$row_id )->field('title, uid')->find();
				$info['source_user_info'] = model('User')->getUserInfo($develop['uid']);
				$info['source_url'] = U('develop/Index/detail', array('id'=>$row_id));
				$info['source_body'] = $develop['title'].'<a class="ico-details" href="'.U('develop/Index/detail', array('id'=>$row_id)).'"></a>';
				break;
			default :
				// 单独的内容，通过此路径获取资源信息
				$appname = strtolower ( $appname );
				$name = ucfirst ( $appname );
				$dao = D ( $name . 'Protocol', $appname, false );
				if (method_exists ( $dao, 'getSourceInfo' )) {
					$info = $dao->getSourceInfo ( $row_id, $_forApi );
				}
				unset ( $dao );
				
				// 兼容旧方案
				if (!$info) {
					$modelArr = explode ( '_', $table );
					$model = '';
					foreach ( $modelArr as $v ) {
						$model .= ucfirst ( $v );
					}
					$dao = D ( $model, $appname );
					if (method_exists ( $dao, 'getSourceInfo' )) {
						$info = $dao->getSourceInfo ( $row_id, $_forApi );
					}
				}
				break;
		}
		$info ['source_table'] = $table;
		$info ['source_id'] = $row_id;
		static_cache ( 'source_info_' . $key, $info );
		return $info;
	}
	
	/**
	 * 从Feed中提取资源数据
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $forApi
	 *        	是否提供API，默认为false
	 * @return array 格式化后的资源数据
	 */
	private function getInfoFromFeed($table, $row_id, $forApi) {
		$info = model ( 'Feed' )->getFeedInfo ( $row_id, $forApi );
		$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $info ['uid'] );
		$info ['source_user'] = $info ['uid'] == $GLOBALS ['ts'] ['mid'] ? L ( 'PUBLIC_ME' ) : $info ['source_user_info'] ['space_link']; // 我
		$info ['source_type'] = L ( 'PUBLIC_WEIBO' );
		$info ['source_title'] = $forApi ? parseForApi ( $_info ['user_info'] ['space_link'] ) : $_info ['user_info'] ['space_link']; // 微博title暂时为空
		$info ['source_url'] = U ( 'public/Profile/feed', array (
				'feed_id' => $row_id,
				'uid' => $info ['uid'] 
		) );
		$info ['source_content'] = $info ['content'];
		$info ['ctime'] = $info ['publish_time'];
		$info ['is_del'] = $info ['is_del'];
		unset ( $info ['content'] );
		return $info;
	}
	
	/**
	 * 从评论中提取资源数据
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $forApi
	 *        	是否提供API，默认为false
	 * @return array 格式化后的资源数据
	 */
	private function getInfoFromComment($table, $row_id, $forApi) {
		$_info = model ( 'Comment' )->getCommentInfo ( $row_id, true );
		$info ['uid'] = $_info ['app_uid'];
		$info ['row_id'] = $_info ['row_id'];
		$info ['is_audit'] = $_info ['is_audit'];
		$info ['source_user'] = $info ['uid'] == $GLOBALS ['ts'] ['mid'] ? L ( 'PUBLIC_ME' ) : $_info ['user_info'] ['space_link']; // 我
		$info ['comment_user_info'] = model ( 'User' )->getUserInfo ( $_info ['user_info'] ['uid'] );
		$forApi && $info ['source_user'] = parseForApi ( $info ['source_user'] );
		$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $info ['uid'] );
		$info ['source_type'] = L ( 'PUBLIC_STREAM_COMMENT' ); // 评论
		$info ['source_content'] = $forApi ? parseForApi ( $_info ['content'] ) : $_info ['content'];
		$info ['source_url'] = $_info ['sourceInfo'] ['source_url'];
		$info ['ctime'] = $_info ['ctime'];
		$info ['app'] = $_info ['app'];
		$info ['sourceInfo'] = $_info ['sourceInfo'];
		// 微博title暂时为空
		$info ['source_title'] = $forApi ? parseForApi ( $_info ['user_info'] ['space_link'] ) : $_info ['user_info'] ['space_link'];
		
		return $info;
	}
	
	function getCommentSource($data, $forApi=false){
		if($data['table']=='feed'||$data['table']=='comment'||empty($data['app_detail_summary']) || $forApi){
			return $this->getSourceInfo($data['table'], $data['row_id'], $forApi, $data['app']);
		}
		//新的应用评论机制 20130607
		$info['source_user_info'] = model('User')->getUserInfo($data['app_uid']);
		$info['source_url'] = $data['app_detail_url'];
		$info['source_body'] = $data['app_detail_summary'];
		//dump($info);
		return $info;
	}
}