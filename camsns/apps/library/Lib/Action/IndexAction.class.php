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
    	$this->assign("title",'我的图书馆');
    	$this->assign("imageurl","apps/library/Tpl/default/");

    }

	public function tuijian()
	{
		$this->display();
	}

	public function index()
	{

	    $data = D('user_password')->where(array('uid'=>$this->uid,'system_id'=>1))->findAll();
	    if(!$data)
	    	$this->display();
	    else
	    	redirect(U("library/Index/login",array('username' =>$data[0]['username'] ,'password'=>$data[0]['password'] )));
	}
	
}
?>