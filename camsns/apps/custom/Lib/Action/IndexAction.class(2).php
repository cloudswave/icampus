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
    private $apps = null ;

    /**
     * 分类
     * @var int
     */
    private $_t = 0 ;



    private $_site = null ;
    /**
     * 初始化
     *
     */
    protected function _initialize()
    {

    }

    /**
     * 列表页
     *
     */
	public function index()
	{

		$this->display();
	}


	public function staticApp()
	{
		$this->display();
	}
	public function haslist()
	{
		$data = model('UserApps')->where(array('uid'=>$this->uid))->findAll();
		$apps = array();

		if($data!=null)
		{
		$i=0;
			foreach($data as $app)
			{
				$temp = model('App')->getAppById($app['app_id']);
				$apps[$i++] = $temp;
			}


		echo json_encode($apps);
		}
		else
		{
				echo json_encode(null);	
		}
	}

	public function addList()
	{
		$data = model('UserApps')->where(array('uid'=>$this->uid))->findAll();
		if(empty($data))
		{
			echo json_encode(model('App')->query("select * from cs_app"));
		}
		else
		{
			$i=0;
			$str='(';
			foreach($data as $app)
			{
				$str=$str.$app['app_id'].",";
			}
			$str=substr($str,0,strlen($str)-1).")";
			echo json_encode(model('App')->query("select * from cs_app where app_id not in ".$str));
		}
	}
	public function add()
	{

		$apps = D('UserApps');
		$data = model('UserApps')->where(array('uid'=>$this->uid,'app_id'=>$_GET['appID']))->findAll();

		if (count($data)>0) {
		}
		else
		{
			if($apps->create())
			{
				$apps->app_id=$_GET['appID'];
				$apps->uid=$this->uid;
				$apps->display_order=5;
				$apps->add();
			}
		}
	}

	public function deleteByID()
	{

		$apps = model('UserApps');
		$apps->deleteByUserAppID($this->uid,$_GET['appID']);

	}
	
	
	public function appsList(){
	    $page=$_GET['page'];
	   // echo "dsfsfsdf";
	    //local 为本地应用
	    if($page=="exchange"){
    	    $data["0"]=array('id'=>'0','name'=>'萌果果','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"44","page":"trade","hasCls":"1","title":"萌果果"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/mengguoguo88.png','imgname'=>'mengguoguo88','type'=>'local');
    	   // $data["0"]=array('id'=>'0','name'=>'店铺1','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"43","page":"trade","hasCls":"1","title":"店铺1"}','icon'=>'accIcon1','type'=>'local');
    	   // $data["1"]=array('id'=>'1','name'=>'店铺2','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"43","page":"trade","hasCls":"1","title":"店铺2"}','icon'=>'accIcon2','type'=>'local');
    	   // $data["2"]=array('id'=>'2','name'=>'店铺3','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"43","page":"trade","title":"店铺3"}','icon'=>'accIcon3','type'=>'local');
    	   // $data["3"]=array('id'=>'3','name'=>'其他','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"44","page":"trade","title":"其他"}','icon'=>'accIcon4','type'=>'local');
    	    $data["1"]=array('id'=>'1','name'=>'二手','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"2","page":"trade","title":"二手","hasCls":"1","user_post":true}','icon'=>'accIconErshou','type'=>'local');
    	    $data["2"]=array('id'=>'2','name'=>'招募中...','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"43","page":"trade","title":"商城"}','icon'=>'accIcon8','style'=>'','type'=>'local');
    	    $data["3"]=array('id'=>'3','name'=>'校园小超市','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"56","page":"trade","hasCls":"1","title":"校园小超市"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/chaoshi.png','imgname'=>'chaoshi','type'=>'local');
    	   
    	   // $data["5"]=array('id'=>'5','name'=>'求职','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"36","page":"common","title":"求职"}','icon'=>'accIcon6','type'=>'local');
    	   // $data["6"]=array('id'=>'6','name'=>'招聘','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"37","page":"common","title":"招聘"}','icon'=>'accIcon7','type'=>'local');

    	   // $data["9"]=array('id'=>'9','name'=>'片刻','wnm'=>'ting','url'=>'http://ting.pianke.me','params'=>'','icon'=>'accIcon2','type'=>'webapp');
    	   // $data["10"]=array('id'=>'10','name'=>'二维码扫描','wnm'=>'scanner','url'=>'grid/grid.html','params'=>'{"action":"scanner"}','icon'=>'accIcon2','type'=>'local');
    	   // $data["11"]=array('id'=>'11','name'=>'秘密','wnm'=>'webapp','url'=>'http://m.ai9475.com/mimi/index','params'=>'','icon'=>'accIcon3','type'=>'webapp');
    	   // $data["12"]=array('id'=>'12','name'=>'校园导航','wnm'=>'webapp','url'=>'http://map.baidu.com/mobile/webapp/index/index','params'=>'','icon'=>'accIcon4','type'=>'webapp');
	    }else{
    	   // $data["0"]=array('id'=>'0','name'=>'我的课表','wnm'=>'jwc_course','url'=>'jwc_course.html','params'=>'','icon'=>'accIcon1','type'=>'local');
    	    $data["0"]=array('id'=>'0','name'=>'成绩查询','wnm'=>'jwc_score_list','url'=>'jwc_score_list.html','params'=>'{"view":"score"}','icon'=>'accIconCjcx','style'=>'background-image:url(css/myImg/11.png);','_bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/11.png','type'=>'local');
    	    $data["1"]=array('id'=>'1','name'=>'教务通知','wnm'=>'jwc_notice_list','url'=>'jwc_notice_list.html','params'=>'','icon'=>'accIconJwtz','type'=>'local');
    	    $data["2"]=array('id'=>'2','name'=>'一键评教','wnm'=>'jwc_evaluation','url'=>'jwc_login.html','params'=>'{"view":"evaluation"}','icon'=>'accIconYjpj','type'=>'local');
    	    $data["3"]=array('id'=>'3','name'=>'失物招领','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"46","page":"common","title":"失物招领","hasCls":"1","user_post":true}','icon'=>'accIconSwzl','type'=>'local');
            $data["4"]=array('id'=>'4','name'=>'吐槽留言','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"39","page":"","title":"吐槽留言","user_post":true,"show_author":true}','icon'=>'accIconTcly','type'=>'local');
            $data["5"]=array('id'=>'5','name'=>'学校动态','url'=>'forum_listct.html','jscode'=>'uescript("main","navSelected(2);");','icon'=>'accIconXxdt','type'=>'js');
            $data["6"]=array('id'=>'6','name'=>'片刻','wnm'=>'ting','url'=>'http://ting.pianke.me','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/ting.png','imgname'=>'ting','style'=>'width:140px;height:140px;','type'=>'webapp');
            $data["7"]=array('id'=>'7','name'=>'校园导航','wnm'=>'webapp','url'=>'http://map.baidu.com/mobile/webapp/index/index','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/navi2.png','imgname'=>'navi2','style'=>'width:140px;height:140px;','type'=>'webapp');

	    }
	    
	    
	    
	    
	    $this->ajaxReturn($data,"应用列表");
	}
	
	
		public function say(){
	   // echo "dsfsfsdf";
	    //local 为本地应用
	    header("Content-type: text/html; charset=utf-8"); 
	    echo "请用浏览器访问：<a href='http://xlanlab.com/index.php?app=custom&mod=Index&act=say&word=我是一头猪'>http://xlanlab.com/index.php?app=custom&mod=Index&act=say&word=我是一头猪</a><br>你可以把\"我是一头猪\"换个词语说给我听额！0.0";
	    echo $_GET['word'];
	    echo "<script>alert('我想对你说：".$_GET['word']."');</script>";
	}
	
	

}
?>