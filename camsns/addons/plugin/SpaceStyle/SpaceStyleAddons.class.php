<?php
/**
 * Ts插件 - 换肤插件
 * @author 陈伟川 <258396027@qq.com>
 * @version TS3.0
 */
class SpaceStyleAddons extends NormalAddons
{
    protected $version = '2.0';
    protected $author = '智士软件';
    protected $site = 'http://www.thinksns.com';
    protected $info = '用户自定义风格官方优化版';
    protected $pluginName = '空间换肤 - 官方优化版';
    protected $tsVersion = "3.0";

    /**
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @return void
     */
    public function getHooksInfo()
    {
        $hooks['list'] = array('SpaceStyleHooks');
        return $hooks;
    }

    /**
     * 后台管理入口
     * @return array 管理相关数据
     */
    public function adminMenu()
    {
        $menu = array('config' => '皮肤管理');
        return $menu;
    }

    public function start()
    {

    }

    /**
     * 安装插件
     * @return boolean 是否安装成功
     */
    public function install()
    {
        // 插入数据表
        $db_prefix = C('DB_PREFIX');
        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_change_style` (
                `uid` int(11) unsigned NOT NULL,
                `classname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                `background` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                UNIQUE KEY `uid` (`uid`)
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
        $sql = "DROP TABLE `{$db_prefix}user_change_style`;";
	    D()->execute($sql);
        // 卸载Xdata数据
        $sql = "DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'addons' AND `key` = 'default_style';";
        D()->execute($sql);
        return true;
    }
}