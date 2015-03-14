<?php
class UserStyleAddons extends NormalAddons{
	protected $version = "3.0";
	protected $author  = "Geek微动力";
	protected $site    = "http://www.lirongtong.com";
	protected $info    = "个人空间页封面设计，让自己的个人首页不再单调(需要自定义图片功能的，可联系Geek微动力[754319866@qq.com])";
    protected $pluginName = "个人空间封面设计";
    protected $tsVersion = '3.0';

    public function getHooksInfo()
    {
        $hooks['list']=array('UserStyleHooks');
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
	    return false;
    }

    public function start()
    {
        return true;
    }

    /**
     * 安装插件
     * @return boolean 是否安装成功
     */
    public function install()
    {
        // 插入数据表
        $db_prefix = C('DB_PREFIX');
        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_home_style` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `uid` int(11) unsigned NOT NULL,
                `background` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        D()->execute($sql);
        return true;
    }

    /**
     * 卸载插件
     * @return boolean 是否卸载成功
     */
    public function uninstall()
    {
        // 卸载数据表
        $db_prefix = C('DB_PREFIX');
        $sql = "DROP TABLE `{$db_prefix}user_home_style`;";
        D()->execute($sql);
        // 卸载addons数据
        $sql1 = "DELETE FROM `{$db_prefix}addons` WHERE `name` = 'UserStyle';";
        D()->execute($sql1);
        return true;
    }
}
