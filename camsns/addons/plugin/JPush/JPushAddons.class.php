<?php

/**
 * 极光推送插件
 * @author 朱小波
 * @version  1.0
 * @copyright  
 */
class JPushAddons extends NormalAddons
{
    protected $version = '1.0';
    protected $author  = '启明星网络科技';
    protected $thanks  = 'EthanZhu';
    protected $site    = 'http://q.xlanlab.com';
    protected $info    = 'JPush推送消息';
    protected $pluginName = '极光推送插件';
    protected $tsVersion  = "3.0";                               

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('JPushHooks');
        return $hooks;
    }

    public function adminMenu() {
        $menu = array(
            'config'=> '设置',
            'pushList' => '推送记录',
            'addJPush' => '推送通知',
            //'doAddPush' => '测试',
                      );
        return $menu;
    }

    public function start() {
        return true;
    }

    /**
     * 安裝插件，初始化WebIM數據庫表
     */
    public function install() {     
        $db_prefix = C('DB_PREFIX');
        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}jpush` (
                      `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
                      `sendno` int(64) NOT NULL DEFAULT '1',
                      `n_title` varchar(120) DEFAULT NULL,
                      `n_content` varchar(255) NOT NULL,
                      `n_extras` varchar(255) DEFAULT NULL,
                      `errcode` varchar(64) NOT NULL,
                      `errmsg` varchar(255) NOT NULL,
                      `push_user_alias` varchar(255) NOT NULL,
                      `created` datetime NOT NULL,
                      PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        D()->execute($sql);
        return true;
    }

    public function uninstall() {
        $db_prefix = C('DB_PREFIX');
        $sql = "DROP TABLE IF EXISTS `{$db_prefix}jpush`;";
        D()->execute($sql);
        return true;
    }

}
