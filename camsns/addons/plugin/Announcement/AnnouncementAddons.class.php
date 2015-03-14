<?php
/**
 * 后台发布公告在右侧栏展示
 */
class AnnouncementAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '程序_小时代';
	protected $site    = 'http://demoapply.com';
	protected $info    = '后台发布公告在右侧栏展示';
	protected $pluginName = '公告展示';
    protected $tsVersion  = "3.0";                               // ts核心版本号

	public function getHooksInfo()
	{
		$this->apply("home_index_right_top","Announcement");
	}

    //在公告获取显示
    public function Announcement()
	{
		$announcement = model('Xarticle')->where('type=1')->order('id desc')->findAll();
		$this->assign('announcement',$announcement);
        $this->display('announcement');
    }

}