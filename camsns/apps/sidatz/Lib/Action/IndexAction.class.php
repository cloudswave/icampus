<?php
/**
 * CMS前台
 * @author  Tomcat<707514663@qq.com>  2013.4.10
 * @version TS3.0
 */
class IndexAction extends Action
{
    
    protected function _initialize()
    {
    	//$this->assign("title",'四达投资工具');
    	

    }
    
    public function checkNewTarget(){
        $url="https://www.sidatz.com/";
        require 'addons/library/phpQuery/QueryList.class.php';
        $reg = array("content"=>array("ul.news:eq(0)","text"));
        $hj = new QueryList($url,$reg);
        $arr_content = $hj->jsonArr;
        //dump($arr_content);
        

        
        if(stripos($arr_content[0]["content"],"暂无新标")>0){
            $this->ajaxReturn(null,"无新标",0); 
        }
        $arr['content']=$arr_content[0]['content'];
        
        //echo $hj->getJSON();
        //$arr['content']=iconv("gb2312","utf-8",$arr_content[0]['content']);
    
         //dump($arr_content);
       //exit(urldecode(json_encode(array('status'=>1,'info'=>'教务通知详情','data'=>$arr))));
        $push_user_alias="*";
     	$_POST['n_title']="有新标了，来看看吧0.0";
        $_POST['n_content']="四达投资有新标了，快去用抢标助手抢标吧！";
        $_POST['n_extras']="";
        $_POST['push_user_alias']=$push_user_alias;//推送所有人
        $_POST['getPushResult']=0;
        Addons::addonsHook("JPush","doAddPush",array(),true);
    
        $this->ajaxReturn($arr,"新标情况",1); 
    }

    /**
     * 列表页
     *
     */
	public function index()
	{
		///$this->checkhaslogin();
	    $data = D('user_password')->where(array('uid'=>$this->uid,'system_id'=>2))->findAll();
	    // dump($data);
	    $cookies = null;
	    if($data)
	    {
	    	$this->assign("username",$data[0]['username']);
	    	$this->assign("password",$data[0]['password']);
	    	$cookies = $data[0]['cookies'];
	    }
	    
	    include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/simple_html_dom.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";

		if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/")) {
			//var_dump($snoopy->headers);
			// if(is_null($cookies)||$cookies=="null"||$cookies=="")
			// 	$snoopy->setcookies();
			// else
			// 	$snoopy->cookies = json_decode($cookies);
			$snoopy->setcookies();
			$html = str_get_html($snoopy->results);

			//var_dump($html->find('div[id]'));

			$view = $html->find('#__VIEWSTATE',0)->value;
			$event = $html->find('#__EVENTVALIDATION',0)->value;
			//var_dump($html->getElementById("#__VIEWSTATE"));
			$_SESSION['jwc_event'] = $event;
			$_SESSION['jwc_view']  = $view;
			$_SESSION['jwc_cookies']  = $snoopy->cookies;
		}
	    $this->display();
	   
	}

	PUBLIC function getcheckimage()
	{
		//$this->index();
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/simple_html_dom.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";


		if($_COOKIE['ASP_NET_SessionId']!=null){
			  $_SESSION['jwc_cookies']=array('ASP.NET_SessionId'=>$_COOKIE['ASP_NET_SessionId']);
	     } 
		
	    if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/")) {

			if ($_SESSION['jwc_cookies']!=null) {
			    $snoopy->cookies=$_SESSION['jwc_cookies'];
			}else{
			    $snoopy->setcookies();

			}
			
			
			$html = str_get_html($snoopy->results);

			$view = $html->find('#__VIEWSTATE',0)->value;
			$event = $html->find('#__EVENTVALIDATION',0)->value;
			//var_dump($html->getElementById("#__VIEWSTATE"));
			$_SESSION['jwc_event'] = $event;
			$_SESSION['jwc_view']  = $view;
			$_SESSION['jwc_cookies']  = $snoopy->cookies;
			
			//dump($_SESSION);
		 }
		 
		//$snoopy->cookies = $_SESSION['jwc_cookies'];
		if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/ValidateCode.aspx"))
		{
			echo ($snoopy->results);
			//exit($snoopy->results);

		}
	}
	

	public function checkhaslogin()
	{
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/simple_html_dom.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		
		//dump($_COOKIE);将客户端cookie传给 本服务器$_SESSION['jwc_cookies'] 然后传给snnopy
		if($_COOKIE['ASP_NET_SessionId']!=null){
			  $_SESSION['jwc_cookies']=array('ASP.NET_SessionId'=>$_COOKIE['ASP_NET_SessionId']);
	     } 
		
	    if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/")) {

			if ($_SESSION['jwc_cookies']!=null) {
			    $snoopy->cookies=$_SESSION['jwc_cookies'];
			}else{
			    $snoopy->setcookies();

			}
			
			
			$html = str_get_html($snoopy->results);

			$view = $html->find('#__VIEWSTATE',0)->value;
			$event = $html->find('#__EVENTVALIDATION',0)->value;
			//var_dump($html->getElementById("#__VIEWSTATE"));
			$_SESSION['jwc_event'] = $event;
			$_SESSION['jwc_view']  = $view;
			$_SESSION['jwc_cookies']  = $snoopy->cookies;
			
			//dump($_SESSION);
		 }

		$snoopy->cookies = $_SESSION['jwc_cookies'];
		//dump($_SESSION);

		if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/Stu_Notice/Notice_Query.aspx"))
		{
			$doc = phpQuery::newDocumentHTML($snoopy->results);
			
			$student= pq("#ctl00_lblSignIn")->html();
			if(!is_null($student)&&$student!='')
			{
			     $return['result'] = "yes";
			     $return['data'] = "已经登录";
			     $_SESSION['login'] = true;
				 exit(json_encode($return));
				//redirect(U("jwc/Index/login"));
			}else{
			     $return['result'] = "no";
			     $return['data'] = "没有登录";
			      $_SESSION['login'] = false;
			     echo json_encode($return);
			}

		}
		
	}
	
	public function login()
	{
	    include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/simple_html_dom.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		//dump($_SESSION['login']);
		
		
		
		
		if(true)
		{
			//var_dump($html->getElementById("#__VIEWSTATE"));

			$formvars["__VIEWSTATE"] = $_SESSION['jwc_view'];

			$formvars["__EVENTVALIDATION"] = $_SESSION['jwc_event'];

			$formvars["txtUserName"] = $_REQUEST['username'];

			$formvars["txtPassword"] = $_REQUEST['password'];

			$formvars["CheckCode"] = $_REQUEST['checkcode'];

			$formvars["btnLogin.x"] = "0";
			$formvars["btnLogin.y"] = "0";

			$action = "http://210.42.38.26:81/jwc_glxt/login.aspx";//表单提交地址
			$snoopy->cookies = $_SESSION['jwc_cookies'];

			setcookie('ASP.NET_SessionId',$snoopy->cookies["ASP.NET_SessionId"]);
			//$snoopy->cookies["ASP.NET_SessionId"]="3ndnapm1fi3bt52u04i4h045";
			if($snoopy->submit($action,$formvars))//$formvars为提交的数组
			{
				
				if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/Stu_Notice/Notice_Query.aspx"))
				{
					$doc = phpQuery::newDocumentHTML($snoopy->results);

					$return['result'] = "success";
					$return['data'] = pq("#ctl00_lblSignIn")->html();
					$_SESSION['login'] = true;
					$this->assign("data",$return['data']);
					$apps = D('user_password');
					$data = $apps->where(array('uid'=>$this->uid,'system_id'=>2))->findAll();
					if (!$data||empty($data))
					{
						if($apps->create())
						{

							$apps->password=$_REQUEST['password'];
							$apps->uid=$this->uid;
							$apps->username=$_REQUEST['username'];
							$apps->cookies=json_encode($snoopy->cookies);
							$apps->system_id=2;
							$apps->add();


						}
					}
				}
			}

		//echo "<PRE>".htmlspecialchars($snoopy->results)."</PRE>\n";
		}
		else {
			//$info = $html->find("#userInfoContent",0);
		}
		// $return['result'] = "error";
		// $return['data'] = null;
		 exit(json_encode($return));
		//$this->display();
	}
	
	
	public function login_json()
	{
	    include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/simple_html_dom.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		//dump($_SESSION['login']);
		
		if($_SESSION['login']){
		    exit(json_encode(array('result'=>'hasLogin','data'=>'已经登陆!')));
		}
		

		//dump($_COOKIE);
		if(true)
		{
			//var_dump($html->getElementById("#__VIEWSTATE"));

			$formvars["__VIEWSTATE"] = $_SESSION['jwc_view'];

			$formvars["__EVENTVALIDATION"] = $_SESSION['jwc_event'];

			$formvars["txtUserName"] = $_REQUEST['username'];

			$formvars["txtPassword"] = $_REQUEST['password'];

			$formvars["CheckCode"] = $_REQUEST['checkcode'];

			$formvars["btnLogin.x"] = "0";
			$formvars["btnLogin.y"] = "0";

			$action = "http://210.42.38.26:81/jwc_glxt/login.aspx";//表单提交地址
			$snoopy->cookies = $_SESSION['jwc_cookies'];
			
			//$snoopy->cookies["ASP.NET_SessionId"]="3ndnapm1fi3bt52u04i4h045";
			//dump($formvars);
			if($snoopy->submit($action,$formvars))//$formvars为提交的数组
			{
			    
				//dump($snoopy->results);
				if($snoopy->fetch("http://210.42.38.26:81/jwc_glxt/Stu_Notice/Notice_Query.aspx"))
				{
					$doc = phpQuery::newDocumentHTML($snoopy->results);
					//echo $doc;
                        if(pq("#ctl00_lblSignIn")->html()!=""){
    					$return['result'] = "success";
    					$return['data'] = pq("#ctl00_lblSignIn")->html();
    					$_SESSION['login'] = true;
    					
    					
			             $lifeTime = 3600; 
			             setcookie('ASP_NET_SessionId',$snoopy->cookies["ASP.NET_SessionId"],time() + $lifeTime);
			             
    					exit(json_encode($return));
                    }
				}
			}

		//echo "<PRE>".htmlspecialchars($snoopy->results)."</PRE>\n";
		}
		else {
			//$info = $html->find("#userInfoContent",0);
		}
	     $return['result'] = "error";
		 $return['data'] = null;
		exit(json_encode($return));
		//$this->display();
	}

	public function logout()
	{
		include_once "addons/library/Snoopy.class.php";
		$snoopy = new Snoopy();
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		$action = "http://210.42.38.26:81/jwc_glxt/Login.aspx?xttc=1";
		$snoopy->cookies = $_SESSION['jwc_cookies'];
			//$snoopy->cookies["ASP.NET_SessionId"]="3ndnapm1fi3bt52u04i4h045";
		if($snoopy->fetch($action))//$formvars为提交的数组
		{
		    setcookie("ASP.NET_SessionId",'',time()-1); 
		    setcookie("ASP_NET_SessionId",'',time()-1); 
		    //$_COOKIE['ASP_NET_SessionId']=null;
		    //dump($_COOKIE);
		    $_SESSION['jwc_cookies']=null;
			$_SESSION['login'] = false;
			
			$this->ajaxReturn(null,"退出成功",1);

			//exit(json_encode($return));
		}

	}
	public function course()
	{
	    
	    if(!$_SESSION['login']){
	        $this->ajaxReturn($_SESSION['login'],'No Login!',0);
	    }
	    
	    
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		include_once "addons/library/simple_html_dom.php";
		$snoopy = new Snoopy();
		$snoopy->cookies = $_SESSION['jwc_cookies'];
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		

		
		$url = "http://210.42.38.26:81/jwc_glxt/Course_Choice/Course_Schedule.aspx";
		
		if($snoopy->fetch($url)) {
			
			$html = str_get_html($snoopy->results);
			$view = $html->find('#__VIEWSTATE',0)->value;
			$event = $html->find('#__EVENTVALIDATION',0)->value;
			
			//echo $view;
			//echo $event;
		
			$doc = phpQuery::newDocumentHTML($snoopy->results);
			$i=0;
			
			foreach(pq('#ctl00_MainContentPlaceHolder_GridScore tr:has("td")') as $li) {
				for($j=1;$j<8;$j++)
				{
					$books[$i][$j] = pq($li)->find('td:eq('.$j.')')->html();
				}
				$i++;
			}
		}
		 $formvars["__VIEWSTATE"] = 	$view;
        $formvars["__EVENTVALIDATION"] = $event;
        $formvars['ctl00$MainContentPlaceHolder$School_Year'] = $_REQUEST['year'];//'2014';
        $formvars['ctl00$MainContentPlaceHolder$School_Term'] = $_REQUEST['term'];//'0 1 3';
        $formvars['ctl00$MainContentPlaceHolder$BtnSearch.x'] = '28';
        $formvars['ctl00$MainContentPlaceHolder$BtnSearch.y'] = '7';
		
		//dump($formvars);
		
	    if($snoopy->submit($url,$formvars))//$formvars为提交的数组
		{
			//var_dump($snoopy);
			$doc = phpQuery::newDocumentHTML($snoopy->results);
			$i=0;
			//pq('#ctl00_MainContentPlaceHolder_GridScore')->html();
			foreach(pq('#ctl00_MainContentPlaceHolder_GridScore tr:gt(0)') as $li) {
				//echo $i.":";
				for($j=0;$j<=7;$j++)
				{
					$books[$i][$j] = pq($li)->find('td:eq('.$j.')')->html();
				}
				$i++;
			}
		}
		
		
		$return['result'] = "success";
		$return['data'] = $books;
		
		if($_GET["callback"]=="json"){
		    exit(json_encode($return));
		}
		
		$this->assign("books",$books);
		

		//exit(json_encode($return));
		$this->display();
	}


	public function score()
	{
	    if(!$_SESSION['login']){
	        $this->ajaxReturn(null,'No Login!',0);
	    }
	    
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->cookies = $_SESSION['jwc_cookies'];
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		$url = "http://210.42.38.26:81/jwc_glxt/Student_Score/Score_Query.aspx";
		if($snoopy->fetch($url)) {
			
			$doc = phpQuery::newDocumentHTML($snoopy->results);
			$i=0;
			
			foreach(pq('#ctl00_MainContentPlaceHolder_GridScore tr:has("td")') as $li) {
				for($j=1;$j<=7;$j++)
				{
					$score[$i][$j] = pq($li)->find('td:eq('.($j-1).')')->text();
				}
				$i++;
			}
		}

		
		if($_REQUEST['callback']=='json'){
		    $this->ajaxReturn($score,'成绩获取成功');
		}
		
		//exit(json_encode($return));
		$this->assign("score",$score);
		//exit(json_encode($return));
		$this->display();
	}



	public function evaluation()
	{
	    if(!$_SESSION['login']){
	        $this->ajaxReturn(null,'未登陆!',0);
	    }
	    
	    if(!$_REQUEST['score']){
	         $_REQUEST['score']=5;
	    }
	    
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		include_once "addons/library/simple_html_dom.php";
		$snoopy = new Snoopy();
		$snoopy->cookies = $_SESSION['jwc_cookies'];
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		
		$url="http://210.42.38.26:81/jwc_glxt/Stu_Assess/Stu_Assess.aspx";
       if($snoopy->fetch($url)) {
				$html = str_get_html($snoopy->results);
                preg_match_all("/window.open\(\'(.*?)\'/",$html,$urls);


    			$formvars['GridCourse2$ctl02$userscore'] = $_REQUEST['score'];
    			$formvars['GridCourse2$ctl03$userscore'] = $_REQUEST['score'];
    			$formvars['GridCourse2$ctl04$userscore'] = $_REQUEST['score'];
    		    $formvars['GridCourse2$ctl05$userscore'] = $_REQUEST['score'];
                
                if(empty($urls[1])){
                    $this->ajaxReturn(null,'已经都评教了，不需要评教了!');
                }
                
                foreach($urls[1] as $u){
                    $url = "http://210.42.38.26:81/jwc_glxt/Stu_Assess/".$u;
		
            		if($snoopy->fetch($url)) {
            			
            			$html1 = str_get_html($snoopy->results);
            			$formvars["__VIEWSTATE"]  = $html1->find('#__VIEWSTATE',0)->value;
            			$formvars["__EVENTVALIDATION"] = $html1->find('#__EVENTVALIDATION',0)->value;

            		
            		}
            		$formvars["btnSave"] = 	"·确定·";
                    //dump($formvars);
                    echo $u;
                    if($snoopy->submit($url,$formvars)){
                        //dump($snoopy->results);
                        
                    }
                }
			}	

		

		   $this->ajaxReturn(null,'一键评教成功!');
		

	}

	public function stuinfo()
	{
		
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->cookies = $_SESSION['jwc_cookies'];
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		$url = "http://210.42.38.26:81/jwc_glxt/Stu_Info/Stu_info.aspx";
		if($snoopy->fetch($url)) {
			
			$doc = phpQuery::newDocumentHTML($snoopy->results);
			$i=0;
			$table = pq('table:last');
			foreach(pq($table)->find('tr td') as $li) {
				$books[$i++] = $li->nodeValue;
			}
		}
		$return['result'] = "success";
		$return['data'] = $books;
		
		exit(json_encode($return));

	}

	
	public function password()
	{
		include_once "addons/library/Snoopy.class.php";
		include_once "addons/library/phpQuery/phpQuery.php";
		$snoopy = new Snoopy();
		$snoopy->cookies = $_SESSION['jwc_cookies'];
		$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/6.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; EIE10;ENUSWOL)";
		$url = "http://210.42.38.26:81/jwc_glxt/Stu_Info/Stu_Password.aspx";
		$issubmit = $_REQUEST['oldpass'];
		if(isset($issubmit))//提交
		{

			$formvars['__EVENTTARGET'] = "";
			$formvars['__EVENTARGUMENT'] = "";
			$formvars['__VIEWSTATE'] = "";
			$formvars['ctl00$MainContentPlaceHolder$TextBox1'] = $_REQUEST['oldpass'];
			$formvars['ctl00$MainContentPlaceHolder$TextBox2'] = $_REQUEST['newpass'];
			$formvars['ctl00$MainContentPlaceHolder$TextBox3'] = $_REQUEST['newpass'];
			$formvars['ctl00$MainContentPlaceHolder$Button1'] = "确 定";
			$formvars['__EVENTVALIDATION'] = $_SESSION['event'];
			dump($formvars);
			if($snoopy->submit($url,$formvars)) {
				$return['result'] = "success";
				$return['data'] = "修改成功";
				dump($return);
				echo json_encode($return);
			}
 		}
		else//进入修改页面
		{
			include_once "addons/library/simple_html_dom.php";
			if($snoopy->fetch($url)) {
				$html = str_get_html($snoopy->results);

				$event = $html->find('#__EVENTVALIDATION',0)->value;
				//var_dump($html->getElementById("#__VIEWSTATE"));
				$_SESSION['event'] = $event;
				$doc = phpQuery::newDocumentHTML($snoopy->results);
				$i=0;
				$table = pq('table:last');
				foreach(pq($table)->find('tr td:has("span")') as $li) {
					$books[$i++] = $li->nodeValue;
				}
			}

			$return['result'] = "success";
			$return['data'] = $books;
			dump($return);
			exit(json_encode($return));
		}
		
		
		

	}
	
	
	public function test(){
	    
	    
	    
	    //*******匹配字符串之间的字符串
	    $html = file_get_contents('http://xlantek.com/apps/newfile.html');
	    echo $html;
	    $str="javascript:var Stu_Assess_Proc= window.open('Stu_Assess_Proc.aspx?id=6358360','学生评教','top=150,left=50,toolbar=no, menubar=no,scrollbars=yes, resizable=yes, location=no, status=no, width=850,height=600');payAccountList.focus(0)";
	    preg_match_all("/window.open\(\'(.*?)\'/",$html,$urls);
	    dump($urls);

	    
	    //*****
	    
	    
// 	    dump(S('jwtz11'));
//           require 'addons/library/phpQuery/QueryList.class.php';

//         //采集OSC的代码分享列表，标题 链接 作者
//         $url = "http://www.oschina.net/code/list";
//         $reg = array("title"=>array(".code_title a:eq(0)","text"),"url"=>array(".code_title a:eq(0)","href"),"author"=>array("img","title"));
//         $rang = ".code_list li";
//         $hj = new QueryList($url,$reg,$rang);
//         $arr = $hj->jsonArr;
//         print_r($arr);
//         //如果还想采当前页面右边的 TOP40活跃贡献者 图像，得到JSON数据,可以这样写
//         $reg = array("portrait"=>array(".hot_top img","src"));
//         $hj->setQuery($reg);
//         $json = $hj->getJSON();
//         echo $json . "<hr/>";
        
//         //采OSC内容页内容
//         $url = "http://www.oschina.net/code/snippet_186288_23816";
//         $reg = array("title"=>array(".QTitle h1","text"),"con"=>array(".Content","html"));
//         $hj = new QueryList($url,$reg);
//         $arr = $hj->jsonArr;
//         print_r($arr);
        
//         //就举这么多例子吧，是不是用来做采集很方便
// 		$return['result'] = "success";
// 		$return['data'] = urlencode("喔喔");
// 		//dump($return);
		
// 			echo S('jwtz11',$arr);
// 		echo urldecode(json_encode($return));//跨域json
 	}
	
	public function jwtz(){
	    $page=$_REQUEST['page']==null?1:$_REQUEST['page'];
	    
	     $cache_return=S('jwtz'.$page);//缓存数据
	     $data=json_decode($cache_return,true);
	     if(sizeof($data["data"])!=0){
	         exit($cache_return);
	        //$this->ajaxReturn($return,'教务处新闻');
	     }
	     
	     
	    

	    //++---------------------
	    require 'addons/library/phpQuery/QueryList.class.php';
    
        $url = "http://jwc.ctgu.edu.cn/news_more.asp?lm=&lm2=67&page=".$page;//http://jwc.ctgu.edu.cn/news_more.asp?lm=&lm2=67&page=2
        $reg = array("title"=>array("td:eq(1)","text"),"url"=>array("td:eq(1) a:eq(1)","href"),"time"=>array("td:eq(2) font","text"));
        $rang = "#table4 table tr";
        $hj = new QueryList($url,$reg,$rang);
        $arr = $hj->jsonArr;
        foreach($arr as $k=>$v){
            $arr[$k]['url']="http://jwc.ctgu.edu.cn/".$v['url'];
            
            
            // $reg = array("content"=>array("#table4 tr:eq(2) td","text"));
            // $hj = new QueryList("http://jwc.ctgu.edu.cn/".$v['url'],$reg);
            // $arr_content = $hj->jsonArr;
            // $arr[$k]['content']=$arr_content[0]['content'];

        }
        $result  =  array();
        $result['status']  =  1;
        $result['info'] =  '教务处通知';
        $result['data'] = $arr;
        $json=json_encode($result);
      //$this->ajaxReturn($arr,'教务处新闻');
		S('jwtz'.$page,$json,3600);////缓存数据
		exit($json);
	    
	}
	
		public function jwtzDetail(){
	    $newsUrl=$_REQUEST['newsUrl']==null?$this->ajaxReturn(null,"未传入新闻链接",0):$_REQUEST['newsUrl'];
	    
        //echo $newsUrl;
	    require 'addons/library/phpQuery/QueryList.class.php';
        $reg = array("content"=>array("#table4 tr:eq(2) td","html"));
        $hj = new QueryList($newsUrl,$reg);
        $arr_content = $hj->jsonArr;
        
        //echo $hj->getJSON();
        $arr['content']=iconv("gb2312","utf-8",$arr_content[0]['content']);
    
         //dump($arr_content);
       //exit(urldecode(json_encode(array('status'=>1,'info'=>'教务通知详情','data'=>$arr))));
    
    
        $this->ajaxReturn($arr,"教务通知详情",1);
	    
	}
	

}
?>