<?php
/**
 * 换肤插件钩子
 * @author 陈伟川 <258396027@qq.com>
 * @version TS3.0
 */
class SpaceStyleHooks extends Hooks
{
    public static $defaultStyle = array();          // 默认样式

    /**
     * 站点头部钩子，加载换肤插件所需样式
     * @param array $param 相关参数
     * @return void
     */
    public function public_head($param)
    {
		// 载入换肤插件基本样式
		echo '<link href="'.$this->htmlPath.'/html/base.css" rel="stylesheet" type="text/css" />';
        // 载入后台设置的默认样式
        $default = $this->model('SpaceStyle')->getDefaultStyle();
        echo '<link href="'.$this->htmlPath.'/themes/'.$default.'/style.css" rel="stylesheet" type="text/css" />';
		// 载入用户个性配置
		$param['uid'] = !$param['uid'] ? $this->mid : $param['uid'];
		$style_data = model( 'Cache' )->get( 'user_space_style_'.$param['uid'] );
		if ( !$style_data ){
			$style_data = $this->model('SpaceStyle')->getStyle($param['uid']);
			model( 'Cache' )->set( 'user_space_style_'.$param['uid'] , $style_data );
		}
        // 验证是否存在用户自定义配置
		if(!$style_data) {
			return false;
		}
        // 样式名
		$classname = $style_data['classname'];
        // 背景图
		$background	= $style_data['background'];
		// 载入基本风格
		if('' !== $classname) {
            $class_url = $this->htmlPath.'/themes/'.$classname.'/style.css';
		}
        echo '<link href="'.$class_url.'" rel="stylesheet" type="text/css" id="change_skin" />';
		// 载入自定义背景
		$background['image'] && $background['image'] = "url('".$background['image']."')";
		$background_CSS = array();
		foreach($background as $key => $value) {
			$value && $background_CSS[$key] = "background-{$key}:{$value};";
		}
		if(!empty($background_CSS)) {
			echo '<style id="change_background">#body_page{'.implode('', $background_CSS).'}</style>';
		}
	}

    /**
     * 主页右上方钩子，加载换肤插件按钮
     * @return void
     */
    public function home_index_right_top()
    {
        $this->display('changeStyleBtn');
    }

    /**
     * 换肤操作浮窗口显示
     * @return void
     */
    public function changeStyleBox()
    {
        // 获取用户皮肤数据
		$style_data = $this->model('SpaceStyle')->getStyle($this->mid);
        $this->assign('styleData', $style_data);
        // 载入默认样式
        $default = $style_data['classname'];
        if(empty($default)) {
            // 载入后台设置的默认样式
            $default = $this->model('SpaceStyle')->getDefaultStyle();
        }
        $this->assign('default', $default);
        // 载入自定义背景图片
        $pic = '';
        if(!empty($style_data['background']['image'])) {
            $pic = $style_data['background']['image'];
        }
        $this->assign('pic', $pic);
        // 获取默认皮肤数据
        $defaultStyle = model('Cache')->get('plugin_space_style');
        if(empty($defaultStyle)) {
            $this->getDefaultStyle();
            $defaultStyle = array();
            foreach(self::$defaultStyle as $value) {
                $styleConf = include(ADDON_PATH.'/plugin/SpaceStyle/themes/'.$value.'/config.php');
                $data[$value]['name'] = $styleConf['name'];
                $data[$value]['thumb_url'] = ADDON_URL.'/plugin/SpaceStyle/themes/'.$value.'/thumb.png';
                $defaultStyle = array_merge($defaultStyle, $data);
            }
            model('Cache')->set('plugin_space_style', $defaultStyle);
        }
        $this->assign('defaultStyle', $defaultStyle);
        $this->display('changeStyleBox');
    }

    /**
     * 获取系统默认皮肤
     * @return void
     */
    public function getDefaultStyle()
    {
        $dirname = ADDON_PATH.'/plugin/SpaceStyle/themes';
        $handle = opendir($dirname);
        while(false !== ($file = readdir($handle))) {
            if($file != '.' && $file != '..' && $file != '.svn') {
                self::$defaultStyle[$file] = $file;
            }
        }
    }

    /**
     * 保存样式
     * @return json 相应的Json数据
     */
    public function saveStyle()
    {
    	$change_style_model = $this->model('SpaceStyle');
    	$res = $change_style_model->saveStyle($this->mid, $_POST);

    	$ajax_return = array(
    		'data' => '',
    		'info' => $change_style_model->getLastError(),
    		'status' => false !== $res
    	);
    	
    	model( 'Cache' )->set( 'user_space_style_'.$this->mid , null);
        exit(json_encode($ajax_return));
    }

    /**
     * 删除临时图片
     * @return void
     */
	public function delImage() 
    {
        $dir_path = SITE_PATH.'/data/upload/background'.$this->convertUidToPath($this->mid);
        $imagePath = $dir_path.'/'.basename($_POST['imagePath']);
        if(unlink($imagePath)) {
            echo 1;
        }
	}

    /**
     * 保存临时图片
     * @return void
     */
	public function saveImageTemp()
    {
        $imageInfo = getimagesize($_FILES['pic']['tmp_name']);
        $filesize = abs(filesize($_FILES['pic']['tmp_name']));
        $result = array();
        if($filesize > 1024*1024*2 || $_FILES['pic']['error'] > 0) {
            $result['status'] = 0;
            $result['info'] = '上传文件不能大于2MB';
        } else {
            $imageType = strtolower(substr($_FILES['pic']['name'],strrpos($_FILES['pic']['name'],'.')+1));
            if($imageType == "jpeg") {
                $imageType ='jpg';
            }
            if(!in_array($imageType,array('jpg','png','gif','bmp'))){
                $result['status'] = 0;
                $result['info'] = '不是有效的图片类型';
                exit(json_encode($result));
            }
            // 配置相关上传选项
            $option['attach_type'] = 'space_image';
            // 配置data信息
            $data['upload_type'] = 'image';
            $info = model('Attach')->upload($data, $option);
            // 返回JSON数据
            $result['status'] = 1;
            $result['info'] = getImageUrl($info['info'][0]['save_path'].$info['info'][0]['save_name']);
        }

        exit(json_encode($result));
	}

    /**
     * 将用户的UID转换为三级路径
     * @param integer $uid 用户UID
     * @return string 用户路径
     */
    public function convertUidToPath($uid)
    {
        // 静态缓存
        $sc = static_cache('avatar_uidpath_'.$uid);
        if(!empty($sc)) {
            return $sc;
        }
        $md5 = md5($uid);
        $sc = '/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4, 2);
        static_cache('avatar_uidpath_'.$uid, $sc);

        return $sc;
    }

    /**
     * 换肤插件，后台管理
     * @return void
     */
    public function config()
    {
        $default = $this->model('SpaceStyle')->getDefaultStyle();
        $this->assign('default', $default);
        // 获取默认皮肤数据
        $defaultStyle = model('Cache')->get('plugin_space_style');
        if(empty($defaultStyle)) {
            $this->getDefaultStyle();
            $defaultStyle = array();
            foreach(self::$defaultStyle as $value) {
                $styleConf = include(ADDON_PATH.'/plugin/SpaceStyle/themes/'.$value.'/config.php');
                $data[$value]['name'] = $styleConf['name'];
                $data[$value]['thumb_url'] = ADDON_URL.'/plugin/SpaceStyle/themes/'.$value.'/thumb.png';
                $defaultStyle = array_merge($defaultStyle, $data);
            }
            model('Cache')->set('plugin_space_style', $defaultStyle);
        }
        $this->assign('defaultStyle', $defaultStyle);
        $this->display('config');
    }

    /**
     * 保存后台配置数据
     * @return void
     */
    public function saveConfig()
    {
        $default = t($_REQUEST['default']);
        model('AddonData')->putAddons('default_style', $default, true);
    }
}