<?php
/**
 * CMS前台
 * @author  Tomcat<707514663@qq.com>  2013.4.10
 * @version TS3.0
 */
class IndexAction extends Action 
{
    /**
     * 资讯模型
     *
     * @var NewsModel
     */
    private $_news = null ;
    
    /**
     * 分类
     * @var int
     */
    private $_t = 0 ;
    
    /**
     * 分类
     * @var CategoryTreeModel
     */
    private $_category = null ;
    
    
    private $_site = null ;
    /**
     * 初始化
     *
     */
    protected function _initialize()
    {
    	$this->_site = $this->tVar['site'];
    	$this->_t = isset($_GET['t'])?intval($_GET['t']):0;
        $this->_news = D('News');
        $this->_category = model('CategoryTree')->setTable('news_category') ;
        //获取资讯分类
        $this->assign('news_type', $this->_category ->getNetworkList() );
        $this->assign('app' , $this->app);
        $this->assign('t', $this->_t );
        $seo_str = $this->app['app_alias'].'_'.$this->_site['site_name'].'_'.$this->_site['site_slogan'] ;
        $this->setTitle($this->app['app_alias'].'_'.$this->_site['site_name'].'_'.$this->_site['site_slogan'] );
        $this->setKeywords($this->app['app_alias'].','.$this->_site['site_name'].','.$this->_site['site_slogan'] );
        $this->setDescription($this->app['app_alias'].','.$this->_site['site_name'].','.$this->_site['site_slogan'] );
    }
    
    /**
     * 列表页
     *
     */
	public function index()
	{
	    //获取配置
	    $limit = model('Cache')->get('news_list_num');//每页显示数量
	    $show_type = model('Cache')->get('news_show_type');//浏览模式
	    $limit = $limit ? $limit : 15 ;
	    $show_type = $show_type? $show_type: 0 ;
	    $st = isset($_GET['st'])?intval($_GET['st']):0;
	    if ($show_type == 0)
	    {
	        if ($st != 1 && $st != 2)
	        {
	            $st = 1 ;
	        }
	    }else
	    {
	        $st = $show_type ;
	    }
	    
	    //资讯分类
	    $real_tid  =$this->_t ;
	    
	    //处理小分类
	    if ($this->_t)
	    {
	    	//查询当前分类
	    	$current_type = $this->_category->getCategoryById($this->_t);
	    	$seo_title = isset($current_type['title'])?$current_type['title']:'' ;
	    	$this->tVar['_title'] = $seo_title . '_'.$this->tVar['_title'] ;
	    	if ($current_type && $current_type['pid'] != 0)
	    	{
	    		//设置当前大分类
	    		$this->assign('t',$current_type['pid']);
	    		//查找小分类
	    		$childs = $this->_category->getCategoryHash($current_type['pid']);
	    	}else
	    	{
	    		$childs = $this->_category->getCategoryHash($this->_t);
	    	}
	    	foreach ($childs as $ch)
	    	{
	    		$this->tVar['_keywords'] = $ch.','.$this->tVar['_keywords'] ;
	    	}
	    	$this->setDescription($this->tVar['_keywords'] );
	    	$this->assign('small_types',$childs);
	    }
	    $k = trim(rawurldecode($_GET['k']));
	    $this->assign('k' , $k);
	    $this->assign('newsList',$this->_news->getList($limit,$real_tid,$k));
	    $this->assign('show_type',$show_type);
	    $this->assign('current_show_type',$st); 
	    $this->assign('real_tid', $real_tid);
	    $this->FormatSildeNews($real_tid);
	    $this->display();
	}
	
	/**
	 * 详细页
	 *
	 * @param int $id
	 */
	public function detail()
	{
	    $id = intval($_GET['id']);
	    if (empty($id))
	    {
	        $this->redirect(U('news/Index/index'));
	    }
	    $news = $this->_news->getOneyById($id,false,true);
	    if (!$news)
	    {
	    	$this->assign('jumpUrl',(U('news/Index/index')));
	        $this->error('该信息不存在或已被删除');
	    }
	    //获取用户信息
	    if ($news['uid'])
	    {
	    	$user = model('User')->getUserInfo($news['uid']);
	    	$this->assign('userinfo',$user);
	    }
	    $this->FormatSildeNews();
	    //获取分类
	    $type = model('CategoryTree')->setTable('news_category')->getCategoryById($news['type_id']);
	    
	    //SEO信息
	    $this->setTitle($news['news_title'].'_'.$this->tVar['_title']);
	    if (isset($type['title']))
	    {
	    	$keywords = $news['news_title'].','.$type['title'].$this->tVar['_keywords'];
	    }else
	    {
	    	$keywords = $news['news_title'].','.$this->tVar['_keywords'];
	    }
	    $this->setKeywords($keywords);
	    $this->setDescription($news['news_title'].','.msubstr(strip_tags($news['news_content']), 0, 200  ));
	    
	    $t = isset($type['id'])?$type['id']:0 ;
	    $this->assign('t',$t);
	    $this->assign('type', $type);
	    $this->assign('news' , $news);
	    $this->assign('current_url',U('news/Index/detail',array('id' => $id)));
	    $this->display();
	}
	
	/**
	 * 初始化测栏数据
	 *
	 */
	private function FormatSildeNews($type = 0)
	{
	    //最新
	    $this->assign('silde_new_news',$this->_news->getList(10,$type,'','news_id desc',false));
	    //热门
	    $this->assign('silde_hot_news',$this->_news->getList(10,$type,'','hits desc',false));
	    
	}
}
?>