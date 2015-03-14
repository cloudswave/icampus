<?php
class LoginPageHooks extends Hooks {
    
    private function checkPage(){
        if( APP_NAME == 'public' && MODULE_NAME =='Passport' && ACTION_NAME == "login" ){
            return true;
        }else{
            return false;
        }
    }
    public function core_display_tpl($param){
		if(!$this->checkPage()) return;
		
        $templateFile = dirname(dirname(__FILE__)).'/html/login.html';
        // 获取配置
        $registerConf = model('Xdata')->get('admin_Config:register');
        $siteConf = model( 'Xdata' )->get('admin_Config:site');
        $param['vars'] = $this->login_vars( $param['vars'] );
        $param['vars']['register_type'] = $registerConf['register_type'];
        $param['vars']['site_slogan'] = $siteConf['site_slogan'];
        $param['vars']['site_name'] = $siteConf['site_name'];
        $param['vars']['site_logo'] = getSiteLogo($siteConf['site_logo']);
        //seo
        $seo= model('Xdata')->get("admin_Config:seo_login");
        $param['vars']['_title'] = !empty($seo['title'])?$seo['title']:$siteConf['site_slogan'];
        $param['vars']['_keywords'] = !empty($seo['keywords'])?$seo['keywords']:$siteConf['site_header_keywords'];
        $param['vars']['_description'] = !empty($seo['des'])?$seo['des']:$siteConf['site_header_description'];
        echo fetch($templateFile, $param['vars'], $param['charset'], $param['contentType']);
        exit;
    }
    private function login_vars($data){
    	//登陆配置
    	$data = model( 'Xdata' )->lget( 'hook_login_page' );
    	$logo = explode( '|',  $data['logo']['logo']);
    	$logo[1] && $data['logo']['logo'] = getImageUrl( $logo[1] );
    	
    	$map['is_del'] = 0;
    	$feedlimit = $data['feed']['feed_num'] ? $data['feed']['feed_num'] : 10;
    	switch ( $data['feed']['feed_source'] ){
    		case 2:
    			$map['uid'] = array( 'in' , explode( ',' , $data['feed']['feed_user'] ) );
    			break;
    		case 3:
    			$users = model( 'UserVerified' )->field('uid')->group('uid')->order('id desc')->limit($feedlimit)->findAll();
    			$map['uid'] = array( 'in' , getSubByKey( $users , 'uid' ) );
    			break;
    	
    	}
    	$feeds = model( 'Feed' )->where($map)->order("feed_id desc")->limit($feedlimit)->field('feed_id')->findAll();
    	$feedids = getSubByKey( $feeds , 'feed_id' );
    	$loginlastfeed = model('Feed')->getFeeds( $feedids );
    	foreach($loginlastfeed as &$v) {
    		switch ( $v['app'] ){
    			case 'weiba':
    				$v['from'] = getFromClient(0 , $v['app'] , '微吧');
    				break;
    			default:
    				$v['from'] = getFromClient( $v['from'] , $v['app']);
    				break;
    		}
    	}
    	
    	$data['loginlastfeed'] = $loginlastfeed;
    	$limit = $data['user']['user_num'] ? $data['user']['user_num'] : 12;
    	switch ( $data['user']['user_source'] ){
    		case 1:
    			$users = model( 'UserData' )->where("`key`='follower_count'")->order('`value`+0 desc')->field('uid')->limit($limit)->findAll();
    			$uids = getSubByKey( $users , 'uid' );
    			break;
    		case 2:
    			$uids = explode( ',' , $data['user']['users'] );
    			$uids = array_slice( $uids , 0 , $limit);
    			break;
    		case 3:
    			$users = model( 'UserVerified' )->field('uid')->group('uid')->order('id desc')->limit($limit)->findAll();
    			$uids = getSubByKey( $users , 'uid' );
    			break;
    		case 4:
    			$feedmap['is_del'] = 0;
    			$feedmap['is_audit'] = 1;
    			$feedmap['is_active'] = 1;
    			$feedmap['is_init'] = 1;
    			$users = model( 'User' )->where($feedmap)->field('uid')->order('ctime desc')->limit($limit)->findAll();
    			$uids = getSubByKey( $users , 'uid' );
    			break;
    	}
    	$uids = array_unique(array_filter($uids));
        if($uids){
            $ulist = model( 'User' )->getUserInfoByUids( $uids );
    	   $data['userlist'] = $ulist;
    	}
        
    	$data['navilist'] = model('Navi')->getBottomNav(); 
        $announcement = model('Xarticle')->where('type=1')->order('id desc')->limit(1)->findAll();
        $data['announcement'] = $announcement;
        if ( $data['logo']['weiba_recommend'] != 2 ){
        	$weiba_recommend = D('weiba')->order('follower_count desc')->where('recommend=1 and is_del=0')->limit(5)->findAll();
        	foreach($weiba_recommend as $k=>$v){
        		$weiba_recommend[$k]['logo'] = getImageUrlByAttachId($v['logo']);
        	}
        	$data['logo']['weiba_recommend_list'] = $weiba_recommend;
        }
    	return $data;
    }
    /**
     * logo配置
     */
    public function login_page_logo(){
    	if ( $_POST ){
    		$attachid = intval( $_POST['login_logo'] );
    		$attach = model( 'Attach' )->getAttachById( $attachid );
    		if( $attach ){
    			$data['logo'] = $attachid.'|'.$attach['save_path'].$attach['save_name'];
    		} else {
    			$data['logo'] = '';
    		}
    		$data['login_top_title'] = t( $_POST['login_top_title'] );
    		$data['logo_site_title'] = t( $_POST['login_site_title'] );
    		$data['weiba_recommend'] = intval( $_POST['weiba_recommend'] );
    		$data['login_foot_content'] = $_POST['login_foot_content'];
    		model( 'Xdata' )->saveKey( 'hook_login_page:logo' , $data );
    		return ;
    	} else {
    		$data = model( 'Xdata' )->get( 'hook_login_page:logo' );
    		if ( $data ){
	    		$logo = explode( '|' , $data['logo'] );
	    		$this->assign( 'logo' , $logo[0] );
	    		$this->assign( 'logosrc' , getImageUrl( $logo[1] ) );
	    		$this->assign( 'weibarecommend' , $data['weiba_recommend'] );
	    		$this->assign( 'logintoptitle' , $data['login_top_title'] );
	    		$this->assign( 'loginfootcontent' , $data['login_foot_content'] );
	    		$this->assign( 'loginsitetitle' , $data['logo_site_title'] );
    		}
    	}
    	$this->display('login_page_logo');
    }
    /**
     * banner配置
     */
    public function login_page_banner(){
    	$data = model( 'Xdata' )->get( 'hook_login_page:banner' );
    	foreach ( $data as &$v ){
    		$v['bannerurl'] = getImageUrl( $v['bannerurl'] ); 
    	}
    	$this->assign( 'data' , $data );
    	$this->display('login_page_banner');
    }
    /**
     * 添加banner
     */
    public function login_page_banner_addimage(){
    	if ( $_POST ){
    		$attachid = intval( $_POST['login_banner'] );
    		$attach = model( 'Attach' )->getAttachById( $attachid );
    		if ( !$attachid ){
	    		$this->assign('jumpUrl',Addons::adminPage('login_page_banner_addimage'));
	    		$this->error('banner图片不能为空');
    		}
    		
    		$data = model( 'Xdata' )->get( 'hook_login_page:banner' );
    		if ( $_POST['banner_key'] ){
    			$bannerkey = intval ( $_POST['banner_key'] );
    			$attachid && $data[$bannerkey]['banner'] = $attachid;
    			$attachid && $data[$bannerkey]['bannerurl'] = $attach['save_path'].$attach['save_name'];
    			
    			$data[$bannerkey]['bannerlink'] = t ( $_POST['banner_link'] );
    		} else {
    			$adddata = array( 'banner'=>$attachid , 
    					'bannerurl'=>$attach['save_path'].$attach['save_name'] , 
    					'bannerlink'=> t ( $_POST['banner_link'] ) ) ;
    			$data[ $attachid ] = $adddata ;
    		}
    		krsort($data);
    		model( 'Xdata' )->saveKey( 'hook_login_page:banner' , $data );
    		return;
    		
    	} else {
    		$bannerkey = intval( $_GET['banner_key'] );
    		if( $bannerkey ){
    			$data = model( 'Xdata' )->get( 'hook_login_page:banner' );
    			$this->assign( 'banner_key' , $bannerkey );
    			$this->assign( 'banner' , $data[$bannerkey]['banner'] );
    			$this->assign( 'bannerurl' , getImageUrl( $data[$bannerkey]['bannerurl'] ) );
    			$this->assign( 'bannerlink' , $data[$bannerkey]['bannerlink'] );
    		}
    	}
    	$this->display('login_page_banner_addimage');
    }
    /**
     * 删除banner数据
     */
    public function login_page_banner_delete(){
		$bannerkey = intval ( $_POST['bannerkey'] );
		if ( $bannerkey ){
			$data = model( 'Xdata' )->get( 'hook_login_page:banner' );
			
			unset( $data[$bannerkey] );
			model( 'Xdata' )->saveKey( 'hook_login_page:banner' , $data );
		}
    }
    /**
     * 动态配置
     */
    public function login_page_feed(){
    	if ( $_POST ){
    		$feedtitle = t ( $_POST['feed_title'] );
    		$feedsource = intval ( $_POST['feed_source'] );
    		$feeduser = t( $_POST['feed_user'] );
    		$feednum = intval( $_POST['feed_num'] );
    		if ( $feedsource == 2 && !$feeduser ){
    			$this->error( '请填写指定用户' );
    		}
    		if ( $feednum > 1000 ){
    			$this->error( '数字必须小于1000' );
    			return;
    		}
    		
    		$data['feed_title'] = $feedtitle;
    		$data['feed_source'] = $feedsource;
    		$data['feed_user'] = $feeduser;
    		$data['feed_num'] = $feednum;
    		
    		model( 'Xdata' )->saveKey( 'hook_login_page:feed' , $data );
    		return;
    		
    	} else {
    		$data = model( 'Xdata' )->get( 'hook_login_page:feed' );
    		$this->assign( 'feedtitle' , $data['feed_title'] );
    		$this->assign( 'feeduser' , $data['feed_user'] );
    		!$data['feed_source'] && $data['feed_source'] = 1;
    		$this->assign( 'feedsource' , $data['feed_source'] );
    		$this->assign( 'feednum' , $data['feed_num'] );
    	}
    	$this->display( 'login_page_feed' );
    }
    /**
     * 用户模块配置
     */
    public function login_page_user(){
       	if ( $_POST ){
    		$usertitle = t ( $_POST['user_title'] );
    		$usersource = intval ( $_POST['user_source'] );
    		$users = t( $_POST['users'] );
    		$usernum = intval( $_POST['user_num'] );
    		if ( $usersource == 2 && !$users ){
    			$this->error( '请填写指定用户' );
    		}
    		if ( $usernum > 1000 ){
    			$this->error( '数字必须小于1000' );
    			return;
    		}
    		
    		$data['user_title'] = $usertitle;
    		$data['user_source'] = $usersource;
    		$data['users'] = $users;
    		$data['user_num'] = $usernum;
    		
    		model( 'Xdata' )->saveKey( 'hook_login_page:user' , $data );
    		return;
    		
    	} else {
    		$data = model( 'Xdata' )->get( 'hook_login_page:user' );
    		$this->assign( 'usertitle' , $data['user_title'] );
    		$this->assign( 'users' , $data['users'] );
    		!$data['user_source'] && $data['user_source'] = 1;
    		$this->assign( 'usersource' , $data['user_source'] );
    		$this->assign( 'usernum' , $data['user_num'] );
    	}
    	$this->display( 'login_page_user' );
    }
}