<?php
/**
 * 相册应用控制器
 */
class IndexAction extends BaseAction {

	/**
	 * 相册首页
	 * @return void
	 */
	public function index () {
		$order = t($_GET['order']);
		if (empty($order) || !in_array($order, array('hot', 'new', 'following'))) {
			$order = 'hot';
		}
		$this->assign('order', $order);
		$this->display();
    }

	/**
	 * 某人的全部图片
	 * @return void
	 */
	public function photos () {
		// 隐私控制
		if ($this->mid != $this->uid) {
			$relationship = getFollowState($this->uid, $this->mid);
			if ($relationship == 'eachfollow' || $relationship == 'havefollow') {
				$map['privacy']	= array('IN', array(1, 2));
			} else {
				$map['privacy']	= 1;
			}
		}
		// 获取图片数据
		$order = '`albumId` DESC, `order` DESC';
		$map['userId'] = $this->uid;

		$photos = D('Photo', 'photo')->order($order)->where($map)->findPage(20);
		$this->assign('type', 'mAll');
		$this->assign('photos', $photos);
		$this->display();
	}

	/**
	 * 某人的全部专辑
	 * @return void
	 */
	public function albums () {
		// 获取相册数据
		$map['userId'] = $this->uid;
		$map['isDel'] = 0;
		// 默认创建相册
		D('Album', 'photo')->createNewData($this->uid);
		// 相册信息
		$data = D('Album', 'photo')->order("mTime DESC")->where($map)->findPage(20);
		// 获取微博相册
		$weibo = D('WeiboAttach', 'photo')->getWeiboAlbum($this->uid);
		// 用户信息
		$userInfo = model('User')->getUserInfo($this->uid);
		// 获取微博配图信息
		$feedImages = D('WeiboAttach', 'photo')->getUserAttachData($this->uid, 5);
		// 所有的图片数目
		$count = $weibo['photoCount'];
		// 最后更新时间
		$lastUpdateTime = D('Album', 'photo')->where("userId='{$this->uid}'")->order('mTime DESC')->limit(1)->getField('mTime');

		$this->assign('weibo', $weibo);
		$this->assign('data', $data);
		$this->assign('userInfo', $userInfo);
		$this->assign('feedImages', $feedImages);
		$this->assign('count', $count);
		$this->assign('lastUpdateTime', $lastUpdateTime);

		$this->display();
	}

	/**
	 * 显示一个图片专辑
	 * @return void
	 */
	public function album () {
		$id = intval($_REQUEST['id']);
		// 获取相册信息
		$albumDao = D('Album');
		$album = $albumDao->where("id={$id}")->find();

		if (!$album) {
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}

		// 隐私控制
		if ($this->mid != $album['userId']) {
			$relationship = getFollowState($this->mid, $this->uid);
			if ($album['privacy'] == 3) {
				$this->error('这个'.$this->appName.'，只有主人自己可见。');
			} else if ($album['privacy'] == 2 && $relationship == 'unfollow') {
				$this->error('这个'.$this->appName.'，只有主人的粉丝可见。');
			} else if ($album['privacy'] == 4) {;
				$cookie_password = cookie('album_password_'.$album['id']);
				// 如果密码不正确，则需要输入密码
				if ($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)) {
					$this->need_password($album);
					exit;
				}
			}
		}

		// 获取图片数据
		$order = '`order` DESC, `id` DESC';
		$map['albumId'] = $id;
		$map['userId'] = $this->uid;
		$map['is_del'] = 0;

		$config = photo_getConfig();
		// $photos	= D('Photo', 'photo')->order($order)->where($map)->findPage($config['photo_raws']);
		$photos	= D('Photo', 'photo')->order($order)->where($map)->findPage(20);
		$this->assign('photos', $photos);

		// 点击率加1
		$res = $albumDao->where("id={$id} AND userId={$this->uid}")->setInc('readCount');
		//dump($res);dump($albumDao->getLastSql());exit;

		$this->setTitle(getUserName($this->uid).'的'.$this->appName.'：'.$album['name']);

		$this->assign('photo_preview', $config['photo_preview']);
		$this->assign('album', $album);
		$this->display();
	}

	/**
	 * 显示一张图片
	 * @return void
	 */
	public function photo() {
		$uid = intval($_REQUEST['uid']);
		$aid = intval($_REQUEST['aid']);
		$id = intval($_REQUEST['id']);
		$type = t($_REQUEST['type']);	// 图片来源类型，来自某相册，还是其它的

		// 判断来源类型
		if (!empty($type) && $type != 'mAll') {
			$this->error('错误的链接！');
		}
		$this->assign('type', $type);

		// 获取所在相册信息
		$albumDao = D('Album');
		$album = $albumDao->find($aid);
		if (!$album) {
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}

		// 获取图片信息
		$photoDao = D('Photo');
		$photo = $photoDao->where(" albumId={$aid} AND `id`={$id} AND userId={$uid} ")->find();
		$this->assign('photo', $photo);

		// 验证图片信息是否正确
		if (!$photo) {
			$this->assign('jumpUrl', U('photo/Index/album', array('uid'=>$this->uid,'id'=>$aid)));
			$this->error('图片不存在或已被删除！');
		}

		// 隐私控制
		if ($this->mid != $album['userId']) {
			$relationship = getFollowState($this->mid, $this->uid);
			if ($album['privacy'] == 3) {
				$this->error('这个'.$this->appName.'的图片，只有主人自己可见。');
			} else if ($album['privacy'] == 2 && $relationship == 'unfollow') {
				$this->error('这个'.$this->appName.'的图片，只有主人的粉丝可见。');
			} else if ($album['privacy'] == 4) {;
				$cookie_password = cookie('album_password_'.$album['id']);
				// 如果密码不正确，则需要输入密码
				if ($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)) {
					$this->need_password($album, $id);
					exit;
				}
			}
		}
		
		$this->assign('album', $album);
		$this->assign('albumId', $album['id']);
		$this->assign('photoId', $id);

		// 获取所有图片数据
		$photos = $albumDao->getPhotos($this->uid, $aid, '', '`order` DESC, `id` DESC', 0);

		// 获取上一页 下一页 和 预览图
		if ($photos) {
			foreach ($photos as $v) {
				$photoIds[] = intval($v['id']);
			}
			$photoCount = count($photoIds);
			// 颠倒数组，取索引
			$pindex = array_flip($photoIds);
			// 当前位置索引
			$now_index = $pindex[$id];
			// 上一张
			$pre_index = $now_index - 1;
			if ($now_index <= 0) {
				$pre_index = $photoCount - 1;
			}
			$pre_photo = $photos[$pre_index];
			// 下一张
			$next_index = $now_index + 1;
			if ($now_index >= $photoCount - 1) {
				$next_index = 0;
			}
			$next_photo = $photos[$next_index];
			// 预览图的位置索引
			$start_index = $now_index - 2;
			if ($photoCount - $start_index < 5) {
				$start_index = ($photoCount - 5);
			}
			if ($start_index < 0) {
				$start_index = 0;
			}
			// 取出预览图列表 最多5个
			$preview_photos = array_slice($photos, $start_index, 5);
		} else {
			$this->error('图片列表数据错误！');
		}
		// 点击率加1
		$res = $photoDao->where("id={$id} AND albumId={$aid} AND userId={$this->uid}")->setInc('readCount');
		//dump($res);dump($albumDao->getLastSql());exit;

		$this->assign('photoCount', $photoCount);
		$this->assign('now', $now_index + 1);
		$this->assign('pre', $pre_photo);
		$this->assign('next', $next_photo);
		$this->assign('previews', $preview_photos);

		unset($pindex);
		unset($photos);
		unset($album);
		unset($preview_photos);

		$this->setTitle(getUserName($this->uid).'的图片：'.$photo['name']);

		$this->display();
	}

	/**
	 * 输入相册密码
	 * @param  [type] $album [description]
	 * @param  string $pid   [description]
	 * @return [type]        [description]
	 */
	public function need_password($album,$pid='') {

		//$aid	=	intval($_REQUEST['aid']);
		//$pid	=	intval($_REQUEST['pid']);
		//$uid	=	intval($_REQUEST['uid']);

		//获取相册信息
		/*$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();

		if(!$album){
			$this->error('专辑不存在或已被删除！');
		}*/

		$this->assign('username',getUserName($album['userId']));
		$this->assign('pid',$pid);
		$this->assign('album',$album);
		$this->display('need_password');
	}

	//验证相册密码
	public function check_password() {

		$aid	=	intval($_REQUEST['aid']);
		$uid	=	intval($_REQUEST['uid']);
		$password	=	t($_REQUEST['password']);
		$_REQUEST['pid'] && $pid = intval($_REQUEST['pid']);
		//获取相册信息
		$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();
		$id = $album['id'];
		if($album['isDel'] != 0){
			$this->error('专辑不存在或已被删除！');
		}
		if($password == $album['privacy_data']){
		// 	//跳转到图片页面
		// 	$url	=	U('/Index/photo',array('uid'=>$album['userId'],'aid'=>$album['id']));
		// }else{
			//跳转到相册页面
			$url	=	U('/Index/album',array('uid'=>$album['userId'],'id'=>$album['id']));
		}
		//验证密码
		if( $password == $album['privacy_data'] ){

			//加密保存密码
			$cookie_password	=	md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid);
			//密码保存7天
			cookie( 'album_password_'.$album['id'] , $cookie_password , 3600*24*7 );
			$this->assign('jumpUrl',$url);
			$this->success('密码验证成功，将自动保存7天。马上跳转到'.$this->appName.'页面！');

		}else{
			$this->assign('jumpUrl',$url);
			$this->error('密码验证失败！');
		}
	}

	/**
	 * 微博相册
	 * @return void
	 */
	public function weiboalbum () {
		// 微博相册ID为0
		if ($id == 0 && $this->uid > 0) {
			$weibo = D('WeiboAttach', 'weibo')->getWeiboAlbum($this->uid);
			$this->assign('album', $weibo);
		}
		$photos = D('WeiboAttach','weibo')->getUserAttachDataNew($this->uid, 20);
		$this->assign('photos', $photos);
		$this->setTitle(getUserName($this->uid).'的微博相册');

		$this->display();
	}


	/**
	 * 显示一张微博图片
	 * @return void
	 */
	public function weibophoto () {
		// 获取GET数据
		$feedId = intval($_REQUEST['id']);
		$uid = intval($_REQUEST['uid']);
		//获取所有图片数据
		$feedInfo = model('Feed')->get($feedId);
		// 验证图片信息是否正确
		if (empty($feedInfo)) {
			$this->error('图片不存在或已被删除！');
		}	
		$this->assign('feedInfo', $feedInfo);
		// 获取图片信息
		$feedData = unserialize($feedInfo['feed_data']);
		$attachIds = $feedData['attach_id'];
		$map['attach_id'] = array('IN', $attachIds);
		$attachInfos = model('Attach')->where($map)->findAll();
		$this->assign('attachInfos', $attachInfos);
		// 获取所有微博图片
		$feedInfos = D('WeiboAttach','weibo')->getUserAttachData($this->uid, 0);
		// 获取上一页 下一页 和 预览图
		if ($feedInfos) {
			$feedIds = getSubByKey($feedInfos, 'feed_id');
			// dump($feedIds);
			$feedCount = count($feedIds);
			// 颠倒数组，取索引
			$findex = array_flip($feedIds);
			// 当前位置索引
			$nowIndex = $findex[$feedId];
			// 上一张
			$preIndex = $nowIndex - 1;
			if ($nowIndex <= 0) {
				$preIndex = $feedCount - 1;
			}
			$preFeed['id'] = $feedIds[$preIndex];
			// 下一张
			$nextIndex = $nowIndex + 1;
			if ($nowIndex >= $feedCount - 1) {
				$nextIndex = 0;
			}
			$nextFeed['id'] = $feedIds[$nextIndex];
		} else {
			$this->error('图片列表数据错误！');
		}

		$this->assign('now', $nowIndex + 1);
		$this->assign('pre', $preFeed);
		$this->assign('next', $nextFeed);
		// 微博权限
		$weiboSet = model('Xdata')->get('admin_Config:feed');
        $weibo_premission = $weiboSet['weibo_premission'];
        $this->assign('weibo_premission', $weibo_premission);

		$this->setTitle(getUserName($this->uid).'的微博图片：'.$photo['name']);

		$this->display();
	}
}