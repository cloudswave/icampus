<?php
/**
 * CMS后台管理
 * @author  Tomcat<707514663@qq.com>  2013.4.10
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminAction extends AdministratorAction
{
    function _initialize ()
    {
        $this->pageTitle['index'] = '应用配置';
        $this->pageTitle['newslist'] = '资讯管理';
        $this->pageTitle['newsCategory'] = '分类配置';
        $this->pageTitle['setinfo'] = '创建/修改信息';
        
        // tab选项
        $this->pageTab[] = array('title' => '应用配置' , 'tabHash' => 'index' , 'url' => U('news/Admin/index'));
        $this->pageTab[] = array('title' => '资讯管理' , 'tabHash' => 'newslist' , 'url' => U('news/Admin/newslist'));
        $this->pageTab[] = array('title' => '分类配置' , 'tabHash' => 'newsCategory' , 'url' => U('news/Admin/newsCategory'));
        $this->pageTab[] = array('title' => '创建/修改信息' , 'tabHash' => 'setinfo' , 'url' => U('news/Admin/setinfo'));
        parent::_initialize();
    }
    
    /**
     * 页面列表
     */
    function index ()
    {
        $cache = model('Cache');
        $info = array
        (
            'news_publish_uid' => $cache->get('news_publish_uid'),//资讯发布者
            'news_list_num' => $cache->get('news_list_num'),// '前台每页展示数量',
            'news_show_type' => $cache->get('news_show_type'), //浏览模式
        );
        if ($_POST)
        {
            foreach ($info as $key => $v)
            {
                $cache->set($key,$_POST[$key]);
            }
            return $this->success('成功保存配置');
        }
        
        $this->opt['news_show_type'] = array('图片/文字','图片', '文字'); 
        $this->pageKeyList = array('news_publish_uid','news_list_num','news_show_type');
        // 表单URL设置
		$this->savePostUrl = U('news/Admin/index');
		$this->displayConfig($info);
    }
    
    /**
     * 页面列表
     */
    public function newslist ()
    {
        $_REQUEST['tabHash'] = 'newslist';
        
        //按钮
        //$this->pageButton[] = array('uid','title'=>'搜索', 'onclick'=>"admin.fold('search_form')");
        $this->pageButton[] = array('uid','title'=>'删除', 'onclick'=>"admin.deleteInfo();");
        $this->pageButton[] = array('uid','title'=>'添加资讯', 'onclick'=>"location.href='".U('news/admin/setinfo',array('tabHash'=>'newslist'))."';");
       
        //处理分类HASH
        $cs = model('CategoryTree')->setTable('news_category')->getCategoryList();
        $categorys = array();
        foreach ($cs as $ct)
        {
            $categorys[$ct['news_category_id']] = $ct ;
        }
        
        //构造搜索条件
        //列表key值 DOACTION表示操作
		$this->pageKeyList = array('image','news_title','news_content','state','is_top','hits','date','DOACTION');
        $listData = M('News')->order('news_id desc')->findPage(15);
        foreach ($listData['data'] as $key => $val)
        {
            $listData['data'][$key]['id'] = $val['news_id'];
            $thumb = APPS_URL.'/'.APP_NAME.'/_static/nopic.jpg';
            if ($val['image'])
            {
                $attach = model('Attach')->getAttachById($val['image']);
                if ($attach)
                {
                    $thumb = getImageUrl($attach['save_path']. $attach['save_name'],100,100,true);   
                }
            }
            //获取分类
            $type_str = '' ;
            if ($val['type_id'])
            {
                if (isset($categorys[$val['type_id']]))
                {
                    $type_str .= $categorys[$val['type_id']]['title'] ;
                    if (isset($categorys[$categorys[$val['type_id']]['pid']]))
                    {
                        $type_str = $categorys[$categorys[$val['type_id']]['pid']]['title'].'--'.$type_str ;
                    }
                }
            }
            $listData['data'][$key]['image'] = '<img src="'.$thumb.'">';
            $listData['data'][$key]['news_title'] = msubstr($val['news_title'],0,20)."<BR><BR><font style='color:#7d7d7d;'>分类: ".$type_str."</font>";
            $listData['data'][$key]['news_content'] = msubstr(strip_tags($val['news_content']),0,20);
            $listData['data'][$key]['state'] = D('News')->getState($val['state']);
            $listData['data'][$key]['date'] = '创建:'.date('m/d G:i',$val['created']);
            if ($val['updated'])
            {
               $listData['data'][$key]['date'].= '<br>更新:'.date('m/d G:i',$val['updated']);
            }
            $listData['data'][$key]['is_top'] = ($val['is_top'])?'<font color="red">置顶</font>':'否';
            $listData['data'][$key]['DOACTION'] = '<a href="'.U('news/admin/setinfo',array('news_id' => $val['news_id'],'tabHash'=>'setinfo')).'">编辑</a>';
        }
        $this->displayList($listData);
    }
    
    /**
     * 更新 / 创建信息
     */
    public function setinfo ()
    {
        $id  = $_REQUEST['news_id'];
        $info = array('state' => 1);
        if ($id)
        {
            $info = D('News')->find($id);
        } 
        if ($_POST)
	    {
	        if ($_POST['uid'])
	        {
	            $uid = $_POST['uid'];
	        }else 
	        {
	            $uid = model('Cache')->get('news_publish_uid');
	        }
	        $save = D('News')->setNews($uid);
            if ($save['ret'] == true)
            {
                $this->assign('jumpUrl',U('news/Admin/newslist'));
                return $this->success( '成功保存信息');
            }else 
            {
                return $this->error( $save['msg'] );
            }
	    }
	    
        // 列表key值 DOACTION表示操作
		$this->pageKeyList = array('type_id','news_title','image','news_content','state','is_top','hits','news_id','uid');
    	
    	//字段属性
    	$this->opt['state'] = D('News')->getState();
    	$this->opt['is_top'] = array(0 => '否', 1 => '置顶');
    	
		$type_ids = array('0' => '--无--') ;
    	$cate = model('CategoryTree');
    	$pc = $cate->settable('news_category')->getCategoryList(0) ;
    	foreach ($pc as $pv)
    	{
    		//查找子分类
    		$pid = $pv['news_category_id'];
    		$type_ids[$pid] = $pv['title']; 
    		$child = $cate->getCategoryList($pid) ;
    		foreach ($child as $cv)
    		{
    			$type_ids[$cv['news_category_id']] ='&nbsp;&nbsp;&nbsp;--- '. $cv['title'];
    		}
    		
    	}
    	$this->opt['type_id'] = $type_ids ;
		// 表单URL设置
		$this->savePostUrl = U('news/Admin/setinfo',array('news_id' => $id));
        $this->notEmpty = array
        (
        	'news_title','news_content'
        );
 
		$this->displayConfig($info);
    }
    
    /**
     * 删除记录
     *
     */
    public function delNews()
    {
        $ret = array('status' => 0, 'data' => '请选择要删除的条目');
        $id =  (array) $_POST['id'];
        if ($id) 
        {
            $ids = implode(',',$id);
        	if (D('News')->delete($ids))
        	{
        	    $ret = array('status' => 1, 'data' => '成功删除信息');
        	}else 
        	{
        	    $ret = array('status' => 1, 'data' => '删除失败');
        	}
        }
        echo json_encode($ret);
    }
    
     
    /**
     * 分类 
     *
     */
    public function newsCategory()
    {
    	$treeData = model('CategoryTree')->settable('news_category')->getNetworkList();
    	$extra = array(  'show_type'=>array('图片/文字','图片', '文字') );
    	$channelConf = model('Xdata')->get('news_Admin:index');
    	$defaultExtra = array('show_type'=>$channelConf['show_type']);
    	$extra = encodeCategoryExtra($extra, $defaultExtra);
    	// 配置删除关联信息
    	$delParam['app'] = 'news';
    	$delParam['module'] = 'News';
    	$delParam['method'] = 'deleteAssociatedData';
    	$this->displayTree($treeData, 'news_category', 2, $delParam);
    }
   
}
?>