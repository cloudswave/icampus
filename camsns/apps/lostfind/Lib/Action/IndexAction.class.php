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
    	//$this->assign("title",'QQ小助手');
    	//$this->assign("imageurl","apps/qqhelper/Tpl/default/");

    }


    /**
     * 列表页
     *
     */
	public function index()
	{

	    	redirect(SITE_URL."/index.php?app=poster&mod=Index&act=addPoster&typeId=7");
	}


}
?>