<?php
/**
 * 相册上传控制器
 */
class UploadAction extends BaseAction {

	public function _initialize () {
		parent::_initialize();
	}

	/**
	 * 普通上传
	 * @return void
	 */
	public function index () {
		// 获取相册配置信息
		$config = photo_getConfig();
		$this->assign($config);	
		$this->setTitle('普通上传');
		$this->display();
	}

	/**
	 * flash上传
	 * @return void
	 */
	public function flash () {
		// 获取相册配置信息
		$config = photo_getConfig();
		$config['photo_max_limit'] = $config['photo_max_limit'] * 1024;
		$this->assign($config);	
		$this->setTitle('批量上传');	
		$this->display();
	}

	/**
	 * 执行单张图片上传
	 * @return JSON 上传附件后的相关信息
	 */
    public function upload_single_pic () {
		$albumId = intval($_REQUEST['albumId']);
		$albumDao = D('Album');
		$albumInfo = $albumDao->field('id')->find($albumId);
		if (!$albumInfo) {
			echo "0";
		}
		$config = photo_getConfig();
		$options['userId'] = $this->mid;
		$options['allow_exts'] = $config['photo_file_ext'];
		$options['max_size'] = $config['photo_max_size'];

		$info = model('attach')->upload(array('upload_type'=>'image'),$options);
		if ($info['status']) {			
			// 保存图片信息
			$info['info'] = $this->save_photo($albumId, $info['info']);
			// 重置相册图片数
			$albumDao->updateAlbumPhotoCount($albumId);
		}

		exit(json_encode($info));
    }

	/**
	 * 执行多张图片上传
	 * @return void
	 */
	public function upload_muti_pic() {
		$albumId	=	intval($_REQUEST['albumId']);
		$albumDao   =   D('Album', 'photo');
		$albumInfo	=	$albumDao->field('id')->find($albumId);
		if(!$albumInfo)$this->error('不存在的相册ID');
		$config     =   photo_getConfig();
 		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	$config['photo_file_ext'];
		$options['max_size']    =   $config['photo_max_size'];

		$info	=	model('attach')->upload(array('upload_type'=>'image'),$options); 
		if($info['status']){
			$info['info'] = $this->save_photo($albumId,$info['info']);
			//记录上传的图片数量
			$upnum	=	count($info['info']);
			//重置相册图片数
			$albumDao->updateAlbumPhotoCount($albumId);

			$res['status'] = 1;
			$res['info'] = '上传成功';
			$res['data'] = array('albumId'=>$albumId, 'upnum'=>$upnum);
		}else{
			$res['status'] = 0;
			$res['info'] = $info['info'];
			$res['data'] = '';
		}
		exit(json_encode($res));
	}

	/**
	 * 保存照片信息
	 * @param integer $albumId 相册ID
	 * @param array $attachInfos 附件信息
	 * @return array 附件信息
	 */
	public function save_photo ($albumId, $attachInfos) {		
		// 获取相册隐私
		$albumInfo = D('Album', 'photo')->field('privacy')->find($albumId);
		// 保存图片附件进入相册 并进行积分操作
		foreach($attachInfos as $k=>$v){
			$photo['attachId']	=	$v['attach_id'];
			$photo['albumId']	=	$albumId;
			$photo['userId']	=	$v['uid'];
			$photo['cTime']		=	time();
			$photo['mTime']		=	time();
			$photo['name']		=	substr($v['name'],'0',strpos($v['name'],'.'));	//去掉后缀名
			$photo['size']		=	$v['size'];
			$photo['savepath']	=	$v['save_path'].$v['save_name'];
			$photo['privacy']	=	$albumInfo['privacy'];
			$photo['order']		=	10000;

			$photoid            =   D('Photo')->add($photo);
			$attachInfos[$k]['photoId']		=	$photoid;
			$attachInfos[$k]['albumId']		=	$albumId;
		}
		// 添加修改相册更新时间
		D('Album', 'photo')->where("id='{$albumId}'")->setField('mTime', time());

	 	//计算积分
		model('Credit')->setUserCredit($v['userId'],'add_photo');

		return $attachInfos;
	}

	/**
	 * 上传后执行编辑操作
	 * @return void
	 */
	public function muti_edit_photos () {
		$upnum = intval($_REQUEST['upnum']);
		if ($upnum == 0) {
			$this->error('请至少上传一张图片！');
		}
		$albumId = intval($_REQUEST['albumId']);
		$albumDao = D('Album', 'photo');
		$albumInfo = $albumDao->find($albumId);

		if (!$albumInfo) {
			$this->error('请上传到指定的相册！');
		}
		// 公开的相册发布微薄
		if ($albumInfo['privacy'] <= 2) {
			$this->assign('publish_weibo', 1);
		}
		if ($upnum > 0) {
			$photos = D('Photo', 'photo')->limit($upnum)->order("id DESC")->where("userId='$this->mid'")->findAll();
			$this->assign('photos', $photos);
			$this->assign('album', $albumInfo);
			$this->assign('upnum', $upnum);
			$albumlist = $albumDao->where("userId='{$this->uid}'")->findAll();
			$this->assign('albumlist', $albumlist);
			$this->display();
		} else {
			$this->error('上传出错：没有上传任何图片！');
		}
	}

	/**
	 * 保存上传的图片
	 * @return void
	 */
	public function save_upload_photos () {
		// 相册信息
		$albumId = intval($_POST['albumId']);
		$album_cover = intval($_POST['album_cover']);
		$upnum = intval($_POST['upnum']);
		$albumDao = D('Album', 'photo');
		$albumInfo = $albumDao->find($albumId);

		if (!$albumInfo) {
			$this->error('请先正确选择相册，再上传图片！');
		}
		// 处理图片信息
		$photoDao = D('Photo', 'photo');
		// 解析图片数据
		foreach ($_POST['name'] as $k => $v) {
			$new_photos[$k]['name'] = t($v);
			$new_photoids[] = t($k);
		}
		foreach ($_POST['move_to'] as $k => $v) {
			$new_photos[$k]['albumId'] = t($v);
		}
		// 对比原始数据，筛选出需要更新的图片
		$photo_ids['id'] = array('IN', $new_photoids);
		$old_photos = $photoDao ->where($photo_ids)->findAll();
		foreach ($old_photos as $k => $v) {
			//如果相册ID和名称都没变化，不需要保存
			$photoid = $v['id'];
			if ($v['albumId'] == $new_photos[$photoid]['albumId'] && $v['name'] == $new_photos[$photoid]['name']) {
				unset($new_photos[$photoid]);
			}
		}
		// 保存图片信息并统计新图片数
		foreach ($new_photos as $k => $v) {
			unset($map);
			$map['userId'] = $this->mid;
			$map['albumId'] = $v['albumId'];
			$map['name'] = $v['name'];
			$map['privacy'] = $album_privacy;
			// 相册信息更新
			$photoDao->limit(1)->where("id='$k'")->save($map);
			// 重置相册图片数
			$albumDao->updateAlbumPhotoCount($map['albumId']);
		}
		// 重置相册图片数
		$albumDao->updateAlbumPhotoCount($albumId);
		// 如果相册封
		if ($album_cover) {
			$album['coverImageId'] = $album_cover;
			if ($coverInfo = $photoDao->field('id,savepath')->find($album_cover)) {
				$album['coverImagePath'] = $coverInfo['savepath'];
				$albumDao->where("id='$albumId'")->save($album);
			}
		}

		if (intval($_POST['publish_weibo']) == 1) {
			$newphotoCount = count($new_photoids);
			$photo_ids['albumId'] = $albumId;
			$photoInfo = $photoDao->where($photo_ids)->order('id ASC')->find();
			if (!$photoInfo) {
				$photoInfo = $photoDao->where(array('id'=>array('IN',$new_photoids)))->order('id ASC')->find();
			}
			$_SESSION['publish_weibo']['initHTML'] = '我上传了'.$newphotoCount.'张新图片:【'.$photoInfo['name'].'】... ';
			$_SESSION['publish_weibo']['attachId'] = $photoInfo['attachId'];
			$_SESSION['publish_weibo']['weibo_type'] = 'postimage';
			$_SESSION['publish_weibo']['app_name'] = 'photo';
			$_SESSION['publish_weibo']['source_url'] = U('photo/Index/photo',array('id'=>$photoInfo['id'],'uid'=>$photoInfo['userId'],'aid'=>$photoInfo['albumId']));
		}
		$this->assign('jumpUrl',U('/Index/album',array(id=>$albumId,uid=>$this->mid)));
		$this->success('图片上传保存成功！');
	}
}