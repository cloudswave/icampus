<?php
class LoginPageAddons extends NormalAddons{
	protected $version = "3.0";
	protected $author  = "智士软件";
	protected $site    = "http://www.thinksns.com";
	protected $info    = "一个开放式的登录前首页插件，可以在插件中配置首页信息，参考此插件可以实现各种类型的首页。";
    protected $pluginName = "经典登录页插件";
    protected $tsVersion = '3.0';
    public function getHooksInfo()
    {
        $hooks['list']=array('LoginPageHooks');
        return $hooks;
    }
	/**
	 * 该插件的管理界面的处理逻辑。
	 * 如果return false,则该插件没有管理界面。
	 * 这个接口的主要作用是，该插件在管理界面时的初始化处理
	 * @param string $page
	 */
    public function adminMenu()
    {
	    return array(
                'login_page_logo'=>"登录页logo配置",
	    		'login_page_banner'=>'Banner图片配置',
	    		'login_page_feed'=>'动态模块配置',
	    		'login_page_user'=>'用户模块配置',
            );
    }
    public function start()
    {
        return true;
    }
    public function install()
    {
        return true;
    }
    public function uninstall()
    {
        return true;
    }
}
