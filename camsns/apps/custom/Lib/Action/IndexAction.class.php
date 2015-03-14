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
    	    $data["1"]=array('id'=>'1','name'=>'二手','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"2","page":"trade","hasCls":"1","title":"二手","user_post":true}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/ershou.png','imgname'=>'ershou','type'=>'local');
    	    /*
    	    $data["2"]=array('id'=>'2','name'=>'招募中...','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"43","page":"trade","title":"商城"}','icon'=>'accIcon8','style'=>'','type'=>'local');
    	    */
    	    $data["2"]=array('id'=>'2','name'=>'新生专栏','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"56","page":"trade","hasCls":"1","title":"新生专栏"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/chaoshi.png','imgname'=>'chaoshi','type'=>'local');
    	    $data["3"]=array('id'=>'3','name'=>'企鹅屋','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"58","page":"trade","hasCls":"1","title":"企鹅屋"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/qiew.png','imgname'=>'qiew','type'=>'local');
    	    $data["4"]=array('id'=>'4','name'=>'lock7','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"59","page":"trade","hasCls":"1","title":"lock7"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/lock7.png','imgname'=>'lock7','type'=>'local');
    	    $data["5"]=array('id'=>'5','name'=>'天晟数码','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"60","page":"trade","hasCls":"1","title":"天晟数码"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/tsxm.png','imgname'=>'tsxm','type'=>'local');
    	    $data["6"]=array('id'=>'6','name'=>'小二很忙','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"62","page":"trade","hasCls":"1","title":"小二很忙"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/xehm.png','imgname'=>'xehm','type'=>'local');
    	    $data["7"]=array('id'=>'7','name'=>'金雨轩','wnm'=>'forum_listct','url'=>'forum_listct.html','params'=>'{"fid":"63","page":"trade","hasCls":"1","title":"金雨轩"}','icon'=>'','style'=>'','bgImg'=>'http://xlantek.com/weicam/data/attachment/common/03/common_63_icon.png','imgname'=>'jyx3','type'=>'local');
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
            $data["6"]=array('id'=>'6','name'=>'实验预订(开发中)','wnm'=>'apps','url'=>'http://physics.ctgu.edu.cn/lxy-order/student/student-login.jsp','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/20.png','imgname'=>'labs','type'=>'webapp');
            $data["7"]=array('id'=>'7','name'=>'应用中心','wnm'=>'webapp','url'=>'http://qmxsys.sinaapp.com','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/38.png','imgname'=>'qmxapp','type'=>'webapp');
            
            /*
            $data["6"]=array('id'=>'6','name'=>'片刻','wnm'=>'ting','url'=>'http://ting.pianke.me','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/ting.png','imgname'=>'ting','style'=>'width:140px;height:140px;','type'=>'webapp');
            $data["7"]=array('id'=>'7','name'=>'校园导航','wnm'=>'webapp','url'=>'http://map.baidu.com/mobile/webapp/index/index','params'=>'','icon'=>'','bgImg'=>'http://xlantek.com/weicam/source/plugin/zywx/icons/navi2.png','imgname'=>'navi2','style'=>'width:140px;height:140px;','type'=>'webapp');
            */

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
	
	
	
	public function torrent_clean(){
	    include_once "addons/library/Snoopy.class.php";
	    $_magnet=$_REQUEST["magnet"];
	    //$_magnet="magnet:?xt=urn:btih:F2DBEB059003BDB95B1AE13C9006D04CB732B061&dn=在不讓女友發現的情況下，跟她的好友偷偷幹砲影片主演大槻ひびき%20早乙女ルイ早乙女露依.avi";
	    file_put_contents("1.torrent",file_get_contents("http://de.76lt.com/magnet-bt/api/torrent.php?magnet=".$_magnet."&btname=torrent1"));
	    
	    $action="http://360xixi.com/";
	    $snoopy = new Snoopy();
	   
	    $snoopy->accept="text/html, application/xhtml+xml, */*";
	    $snoopy->_submit_type = 'multipart/form-data';
	    $snoopy->referer="http://360xixi.com/";
	    $snoopy->agent="Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)";
	    //$snoopy->rawheaders["Cookie"]="session=MWQzZjZiYjJkNDhhNDExYTcwM2FlZjM5MzllZGZlNDkxZWUyYjI5MmViYjEzY2FmNjU2NTI2M2FhYzFkZDZjMK1fwdBl4kpGA3EtMrKRucff5htXZV3gIKFRHbJPdobxCooKtPIoLx63gep7HcK6gVII1Sx6TnhsBnXInGVjV1U%3D; Hm_lvt_41066128218bb854bfa3e7056224b477=1410705501,1410705658,1410705927; Hm_lpvt_41066128218bb854bfa3e7056224b477=1410705985";
	    //$snoopy->_mime_boundary="=---------------------------7de1f5276f0324";
	    //$Parameters["torrent[]"]=file_get_contents("http://de.76lt.com/magnet-bt/api/torrent.php?magnet=88450FC38FE1CAC98E10FF69F006F4AEF259AFB4&btname=生活大爆炸第六季第6集@圣城流氓兔兔");
	    $postfiles["torrent[]"]="1.torrent"; 
	    $Parameters["type"]="torrent";
	    $Parameters["strategy"]="md5";
	    $Parameters["mode"]="";
	    $Parameters["time"]="1410706007";
	    $Parameters["token"]="9abd342e8e0bda4dc591e824d6d57304";
	    
	    // dump($snoopy);
	    
	    $snoopy->submit($action, $Parameters, $postfiles); 
	    
	    //dump($snoopy);
	    $_file=pathinfo($snoopy->lastredirectaddr);
	    $Parameter2["file"]=$_file["basename"];
	    $snoopy->referer=$snoopy->lastredirectaddr;
	    $snoopy->submit("http://360xixi.com/download",$Parameter2);
	    //dump($Parameter2);
	    //dump($snoopy);
	    
	    file_put_contents("2.torrent",$snoopy->results);
	    //echo "清洗后的种子地址<a href='2.torrent' target='_blank'>http://xlanlab.com/2.torrent</a>";
	    
	    $filename = '2.torrent'; 
        //文件的类型 
        header('Content-type: application/octet-stream'); 
        //下载显示的名字 
        header('Content-Disposition: attachment; filename="clean.torrent"'); 
        readfile($filename); 
	    
	   // $postfiles2["upfile"]="2.torrent"; 
	   // $snoopy->submit("http://code.76lt.com/magnet-bt/","",$postfiles2);
	   // dump($snoopy);
	    
	    //echo file_get_contents("http://360xixi.com/download?file=4505415c7be56e89.torrent");
	    
	    //echo file_get_contents("http://de.76lt.com/magnet-bt/api/torrent.php?magnet=88450FC38FE1CAC98E10FF69F006F4AEF259AFB4&btname=生活大爆炸第六季第6集@圣城流氓兔兔");
	}
	

}
?>