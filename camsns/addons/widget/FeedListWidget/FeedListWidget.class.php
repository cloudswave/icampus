<?php
/**
 * 微博列表
 * @example {:W('FeedList',array('type'=>'space','feed_type'=>$feed_type,'feed_key'=>$feed_key,'loadnew'=>0,'gid'=>$gid))}
 * @author jason
 * @version TS3.0
 */
class FeedListWidget extends Widget {
	
	private static $rand = 1;
	private $limitnums   = 10;

    /**
     * @param string type 获取哪类微博 following:我关注的 space：
     * @param string feed_type 微博类型
     * @param string feed_key 微博关键字
     * @param integer fgid 关注的分组id
     * @param integer gid 群组id
     * @param integer loadnew 是否加载更多 1:是  0:否
     */
	public function render($data) {
		$var = array();
		$var['loadmore'] = 1;
		$var['loadnew'] = 1;
		$var['tpl'] = 'FeedList.html';
		
 		is_array($data) && $var = array_merge($var, $data);
    
 		$weiboSet = model('Xdata')->get('admin_Config:feed');
        $var['initNums'] = $weiboSet['weibo_nums'];
        $var['weibo_type'] = $weiboSet['weibo_type'];
        $var['weibo_premission'] = $weiboSet['weibo_premission'];
        // 我关注的频道
        $var['channel'] = M('channel_follow')->where('uid='.$this->mid)->count();
 
        // 查询是否有话题ID
        if($var['topic_id']) {
        	$content = $this->getTopicData($var,'_FeedList.html');
        } else {
        	$content = $this->getData($var,'_FeedList.html');
        }
        // 查看是否有更多数据
        if(empty($content['html'])) {
        	// 没有更多的
        	$var['list'] = L('PUBLIC_WEIBOISNOTNEW');
        } else {
        	$var['list'] = $content['html'];
        	$var['lastId'] = $content['lastId'];
        	$var['firstId'] = $content['firstId'] ? $content['firstId'] : 0;
        	$var['pageHtml']	= $content['pageHtml'];
        }
	    $content['html'] = $this->renderFile(dirname(__FILE__)."/".$var['tpl'], $var); 
		self::$rand ++;
		unset($var, $data);
        //输出数据
		return $content['html'];
    }

    /**
     * 显示更多微博
     * @return  array 更多微博信息、状态和提示
     */
    public function loadMore() {
        // 获取GET与POST数据
    	$_REQUEST = $_GET + $_POST;
        // 查询是否有分页
    	if(!empty($_REQUEST['p']) || intval($_REQUEST['load_count']) == 4) {
    		unset($_REQUEST['loadId']);
    		$this->limitnums = 40;
    	} else {
    		$return = array('status'=>-1,'msg'=>L('PUBLIC_LOADING_ID_ISNULL'));
            $_REQUEST['loadId'] = intval($_REQUEST['loadId']);
    		$this->limitnums = 10;
    	}
        // 查询是否有话题ID
        if($_REQUEST['topic_id']) { 
            $content = $this->getTopicData($_REQUEST,'_FeedList.html');
        } else {
    	    $content = $this->getData($_REQUEST,'_FeedList.html');
        }
        // 查看是否有更多数据
    	if(empty($content['html']) || (empty($_REQUEST['loadId']) && intval($_REQUEST['load_count']) != 4) ) {
            // 没有更多的
    		$return = array('status'=>0,'msg'=>L('PUBLIC_WEIBOISNOTNEW'));
    	} else {
    		$return = array('status'=>1,'msg'=>L('PUBLIC_SUCCESS_LOAD'));
    		$return['html'] = $content['html'];
    		$return['loadId'] = $content['lastId'];
            $return['firstId'] = ( empty($_REQUEST['p']) && empty($_REQUEST['loadId']) ) ? $content['firstId'] : 0;
    		$return['pageHtml']	= $content['pageHtml'];
    	}
        exit(json_encode($return));
    }

    /**
     * 显示最新微博
     * @return  array 最新微博信息、状态和提示
     */
    public function loadNew() {
    	$return = array('status'=>-1,'msg'=>'');
        $_REQUEST['maxId'] = intval($_REQUEST['maxId']);
    	if(empty($_REQUEST['maxId'])){
    		echo json_encode($return);exit();
    	}
    	$content = $this->getData($_REQUEST,'_FeedList.html');
    	if(empty($content['html'])){//没有最新的
    		$return = array('status'=>0,'msg'=>L('PUBLIC_WEIBOISNOTNEW'));
    	}else{
    		$return = array('status'=>1,'msg'=>L('PUBLIC_SUCCESS_LOAD'));
    		$return['html'] = $content['html'];
    		$return['maxId'] = intval($content['firstId']);
            $return['count'] = intval($content['count']);
    	}
    	echo json_encode($return);exit();
    }
    
    /**
     * 获取微博数据，渲染微博显示页面
     * @param array $var 微博数据相关参数
     * @param string $tpl 渲染的模板
     * @return array 获取微博相关模板数据
     */
    private function getData($var, $tpl = 'FeedList.html') {
    	$var['feed_key'] = t($var['feed_key']);
        $var['cancomment'] = isset($var['cancomment']) ? $var['cancomment'] : 1;
        //$var['cancomment_old_type'] = array('post','repost','postimage','postfile');
        $var['cancomment_old_type'] = array('post','repost','postimage','postfile','weiba_post','weiba_repost');
        // 获取微博配置
        $weiboSet = model('Xdata')->get('admin_Config:feed');
        $var = array_merge($var, $weiboSet);
    	$var['remarkHash'] = model('Follow')->getRemarkHash($GLOBALS['ts']['mid']);
    	$map = $list = array();
    	$type = $var['new'] ? 'new'.$var['type'] : $var['type'];	// 最新的微博与默认微博类型一一对应

    	switch($type) {
    		case 'following':// 我关注的
    			if(!empty($var['feed_key'])){
    				//关键字匹配 采用搜索引擎兼容函数搜索 后期可能会扩展为搜索引擎
    				$list = model('Feed')->searchFeed($var['feed_key'],'following',$var['loadId'],$this->limitnums);
    			}else{
    				$where ='( a.is_audit=1 OR ( a.is_audit=0 AND a.uid='.$GLOBALS['ts']['mid'].') ) AND a.is_del = 0 ';
    				if($var['loadId'] > 0){ //非第一次
    					$where .=" AND a.feed_id < '".intval($var['loadId'])."'";
    				}
    				if(!empty($var['feed_type'])){
    					if ( $var['feed_type'] == 'post' ){
    						$where .=" AND a.is_repost = 0";
    					} else {
    						$where .=" AND a.type = '".t($var['feed_type'])."'";
    					}
    				}
                    //设定可查看的关注微博总数，可以提高大数据量下的查询效率
                    $max = 1000;
    				$list =  model('Feed')->getFollowingFeed($where,$this->limitnums,'',$var['fgid'],$max);
    			}
    			break;
    		case 'all'://所有的 --正在发生的
    			if(!empty($var['feed_key'])){
    				//关键字匹配 采用搜索引擎兼容函数搜索 后期可能会扩展为搜索引擎
    				$list = model('Feed')->searchFeed($var['feed_key'],'all',$var['loadId'],$this->limitnums);
    			}else{
                    $where =' (is_audit=1 OR is_audit=0 AND uid='.$GLOBALS['ts']['mid'].') AND is_del = 0 ';
    				if($var['loadId'] > 0){ //非第一次
    					$where .=" AND feed_id < '".intval($var['loadId'])."'";
    				}
    				if(!empty($var['feed_type'])){
    					if ( $var['feed_type'] == 'post' ){
    						$where .=" AND is_repost = 0";
    					} else {
    						$where .=" AND type = '".t($var['feed_type'])."'";
    					}
    				}
                    //设定可查看的全站微博总数，可以提高大数据量下的查询效率
                    $max = 10000;
    				$list = model('Feed')->getList($where,$this->limitnums,'',$max);
    			}
    			break;
    		case 'newfollowing'://关注的人的最新微博
                $where ='( a.is_audit=1 OR ( a.is_audit=0 AND a.uid='.$GLOBALS['ts']['mid'].') ) AND a.is_del = 0 ';
    			if($var['maxId'] > 0){
    				$where .=" AND a.feed_id > '".intval($var['maxId'])."'";
    				$list = model('Feed')->getFollowingFeed($where);
                    $content['count'] = $list['count'];
    			}		
    			break;
    		case 'newall':	//所有人最新微博 -- 正在发生的
    			if($var['maxId'] > 0){
    				$map['feed_id'] = array('gt',intval($var['maxId']));
    				$map['is_del'] = 0;
                    $map['is_audit'] = 1;
                    $map['uid']   = array('neq',$GLOBALS['ts']['uid']);
    				$list = model('Feed')->getList($map);   
                    $content['count'] = $list['count'];
    			}
    			break;
    		case 'space':	//用户个人空间
    			if($var['feed_key'] !== ''){
    				//关键字匹配 采用搜索引擎兼容函数搜索 后期可能会扩展为搜索引擎
    				$list = model('Feed')->searchFeed($var['feed_key'],'space',$var['loadId'],$this->limitnums,'',$var['feed_type']);
    			}else{
	    			if($var['loadId']>0){
	    				$map['feed_id'] = array('lt',intval($var['loadId']));
	    			}
	    			$map['is_del'] = 0;
                    if($GLOBALS['ts']['mid'] != $GLOBALS['ts']['uid']) $map['is_audit'] = 1;
    				$list = model('Feed')->getUserList($map,$GLOBALS['ts']['uid'],  $var['feedApp'], $var['feed_type'],$this->limitnums);
    			}
    			break;
            case 'channel':
                $where = ' (c.is_audit=1 OR c.is_audit=0) AND c.is_del = 0 ';
                if($var['loadId'] > 0) { //非第一次
                    $where .= " AND c.feed_id < '".intval($var['loadId'])."'";
                }
                if(!empty($var['feed_type'])) {
                    $where .= " AND c.type = '".t($var['feed_type'])."'";
                }

                $list = D('ChannelFollow', 'channel')->getFollowingFeed($where, $this->limitnums, '' ,$var['fgid']);
                break;
    	}

    	// 分页的设置
        isset($list['html']) && $var['html'] = $list['html'];
    	if(!empty($list['data'])) {
    		$content['firstId'] = $var['firstId'] = $list['data'][0]['feed_id'];
    		$content['lastId'] = $var['lastId'] = $list['data'][(count($list['data'])-1)]['feed_id'];
            $var['data'] = $list['data'];

            //赞功能
            $feed_ids = getSubByKey($var['data'],'feed_id');
            $var['diggArr'] = model('FeedDigg')->checkIsDigg($feed_ids, $GLOBALS['ts']['mid']);
            
            $uids = array();
            foreach($var['data'] as &$v) {
            	switch ( $v['app'] ){
            		case 'weiba':
            			$v['from'] = getFromClient(0 , $v['app'] , '微吧');
            			break;
                    case 'tipoff':
                    $v['from'] = getFromClient(0 , $v['app'] , '爆料');
                    break;
                    case 'w3g':
                    $v['from'] = getFromClient(6 , $v['app'] , '3G版');
                    break;
            		default:
            			$v['from'] = getFromClient( $v['from'] , $v['app']);
            			break;
            	}
            	!isset($uids[$v['uid']]) && $v['uid'] != $GLOBALS['ts']['mid'] && $uids[] = $v['uid'];
            }
            if(!empty($uids)) {
            	$map = array();
            	$map['uid'] = $GLOBALS['ts']['mid'];
            	$map['fid'] = array('in',$uids);
            	$var['followUids'] = model('Follow')->where($map)->getAsFieldArray('fid');
            } else {
            	$var['followUids'] = array();
            }
    	}

       //dump($list);
    	$content['pageHtml'] = $list['html'];
	    // 渲染模版
	    $content['html'] = $this->renderFile(dirname(__FILE__)."/".$tpl, $var);
      
	    return $content;
    }

    /**
     * 获取话题微博数据，渲染微博显示页面
     * @param array $var 微博数据相关参数
     * @param string $tpl 渲染的模板
     * @return array 获取微博相关模板数据
     */
    private function getTopicData($var,$tpl='FeedList.html') {
        $var['cancomment'] = isset($var['cancomment']) ? $var['cancomment'] : 1;
        //$var['cancomment_old_type'] = array('post','repost','postimage','postfile');
        $var['cancomment_old_type'] = array('post','repost','postimage','postfile','weiba_post','weiba_repost');
        $weiboSet = model('Xdata')->get('admin_Config:feed');
        $var = array_merge($var,$weiboSet);
        $var['remarkHash'] = model('Follow')->getRemarkHash($GLOBALS['ts']['mid']);
        $map = $list = array();
        $type = $var['new'] ? 'new'.$var['type'] : $var['type'];    //最新的微博与默认微博类型一一对应

        if($var['loadId'] > 0){ //非第一次
            $topics['topic_id'] = $var['topic_id'];
            $topics['feed_id'] = array('lt',intval($var['loadId']));
            $map['feed_id'] = array('in',getSubByKey(D('feed_topic_link')->where($topics)->field('feed_id')->select(),'feed_id'));
        }else{
            $map['feed_id'] = array('in',getSubByKey(D('feed_topic_link')->where('topic_id='.intval($var['topic_id']))->field('feed_id')->select(),'feed_id'));
        }
        if(!empty($var['feed_type'])){
            $map['type'] = t($var['feed_type']);
        }
        //$map['is_del'] = 0;
        $map['_string'] = ' (is_audit=1 OR is_audit=0 AND uid='.$GLOBALS['ts']['mid'].') AND is_del = 0 ';
        $list = model('Feed')->getList($map,$this->limitnums);
        //分页的设置
        isset($list['html']) && $var['html'] = $list['html'];
        
        if(!empty($list['data'])){
            $content['firstId'] = $var['firstId'] = $list['data'][0]['feed_id'];
            $content['lastId']  = $var['lastId'] = $list['data'][(count($list['data'])-1)]['feed_id'];
            $var['data'] = $list['data'];
            
            //赞功能
            $feed_ids = getSubByKey($var['data'],'feed_id');
            $var['diggArr'] = model('FeedDigg')->checkIsDigg($feed_ids, $GLOBALS['ts']['mid']);
            
            $uids = array();
            foreach($var['data'] as &$v){
            	switch ( $v['app'] ){
            		case 'weiba':
            			$v['from'] = getFromClient(0 , $v['app'] , '微吧');
            			break;
            		default:
            			$v['from'] = getFromClient( $v['from'] , $v['app']);
            			break;
            	}
                !isset($uids[$v['uid']]) && $v['uid'] != $GLOBALS['ts']['mid'] && $uids[] = $v['uid'];
            }
            if(!empty($uids)){
                $map = array();
                $map['uid'] = $GLOBALS['ts']['mid'];
                $map['fid'] = array('in',$uids);
                $var['followUids'] = model('Follow')->where($map)->getAsFieldArray('fid');
            }else{
                $var['followUids'] = array();
            }
        }

        $content['pageHtml'] = $list['html'];
       
        //渲染模版
        $content['html'] = $this->renderFile(dirname(__FILE__)."/".$tpl,$var);
      
        return $content;
    }

    /**
     * 获取微吧帖子数据
     * @param  [varname] [description]
     */
    public function getPostDetail() {
        $post_id = intval($_POST['post_id']);
        $post_detail = D('weiba_post')->where('is_del=0 and post_id='.$post_id)->find();
        if($post_detail && D('weiba')->where('is_del=0 and weiba_id='.$post_detail['weiba_id'])->find()){
            $post_detail['post_url'] = U('weiba/Index/postDetail',array('post_id'=>$post_id));
            $author = model('User')->getUserInfo($post_detail['post_uid']);
            $post_detail['author'] = $author['space_link'];
            $post_detail['post_time'] = friendlyDate($post_detail['post_time']);
            $post_detail['from_weiba'] = D('weiba')->where('weiba_id='.$post_detail['weiba_id'])->getField('weiba_name');
            $post_detail['weiba_url'] = U('weiba/Index/detail',array('weiba_id'=>$post_detail['weiba_id']));
            return json_encode($post_detail);
        }else{
            echo 0;
        }
    }

    public function getTipoffDetail(){
        $tipoff_id = intval($_POST['tipoff_id']);
        $tipoff_detail = D('tipoff')->where('deleted=0 and archived=0 and tipoff_id='.$tipoff_id)->find();
        if($tipoff_detail){
            $tipoff_detail['tipoff_url'] = U('tipoff/Index/detail',array('id'=>$tipoff_id));
            $author = model('User')->getUserInfo($tipoff_detail['uid']);
            $tipoff_detail['author'] = $author['space_link'];
            $tipoff_detail['publish_time'] = friendlyDate($tipoff_detail['publish_time']);
            $tipoff_detail['from_category'] = D('tipoff_category')->where('tipoff_category_id='.$tipoff_detail['category_id'])->getField('title');
            $tipoff_detail['category_url'] = U('tipoff/Index/index',array('cid'=>$tipoff_detail['category_id']));
            return json_encode($tipoff_detail);
        }else{
            echo 0;
        }
    }
    
}