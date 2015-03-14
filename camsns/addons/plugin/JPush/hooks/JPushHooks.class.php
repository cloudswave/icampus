<?php
class JPushHooks extends Hooks
{

	public function config(){
		$imc = require_once SITE_PATH . '/addons/plugin/JPush/conf/config.php';
		$this->assign('IMC', $imc);
		//echo "ok";
		$this->display('config');
	}


	public function saveConfig() {
        $cfg = require SITE_PATH . '/addons/plugin/JPush/conf/config.php';
        if(!$_POST['appkeys']) {
			$this->error('appkeys不能为空');
            return;
        }
        $cfg['appkeys'] = $_POST['appkeys'];

        if(!$_POST['masterSecret']) {
			$this->error('masterSecret不能为空');
            return;
        }
        $cfg['masterSecret'] = $_POST['masterSecret'];


        if(!$_POST['platform']) {
			$this->error('platform服务器和端口不能为空');
            return;
        }
        $cfg['platform'] = $_POST['platform'];

        $this->writeConfig($cfg);
        $this->success('设置成功');
	}

	public function writeConfig($cfg) {
		$data = '<?php return ' . var_export($cfg, true) . ';';
		$file = fopen(SITE_PATH. '/addons/plugin/JPush/conf/config.php', "wb");
		fwrite($file, $data);  
		@fclose($file);
	}


   /**
    * [push_list description] 通知列表
    * @return [type] [description] list
    */
	public function pushList() {

       //echo "ok";
		// 列表数据
		  $list = $this->model('JPush')->getPushList();
		  //echo "ok";
		   //dump($list);
		   $this->assign('list', $list);
           $this->display('pushList');
	}

	public function addJPush(){
		//echo "ok";
		$this->display('addJPush');
	}

	// 	/**
	//  * 添加操作
	//  * @return void
	//  */
	
	public function doAddPush()
	{

		    //echo "推送。。。";

           include_once $this->path . "/lib/jpush.class.php";//导入class
		  
		   $imc = require_once SITE_PATH . '/addons/plugin/JPush/conf/config.php';

		   $getPushResult=$_POST['getPushResult'];//是否获取推送结果 退出则不继续执行 返回json结果 不退出则继续执行 不防护json结果 

			$data['n_title']   =  t($_POST['n_title']);
			$data['n_content']  =  t($_POST['n_content']);
			$data['n_extras']  =   $_POST['n_extras'];
			$data['push_user_alias']  =  $_POST['push_user_alias'];
			$data['sendno']  = ($this->model('JPush')->getMaxNum())+1;
			$created = time();
            $data['created']  =  date("Y-m-d H:i:s",$created);

            
			//调用api推送通知		
			$platform = $imc['platform'] ;
			$receiver_type=$data['push_user_alias']=="*"?4:3;
			//$msg_content = json_encode(array('n_builder_id'=>0, 'n_title'=>$data['n_title'], 'n_content'=>$data['n_content'] ,'n_extras'=>$data['n_extras']));        
			$obj = new jpush($imc['masterSecret'],$imc['appkeys']);	
				
			//dump(json_decode($data['n_extras'],true));
            
            $n_extras='';
            if(!empty($data['n_extras'])){
            	$n_extras=json_decode($data['n_extras'],true);
            }

           $msg_content = json_encode(array(
           	'n_builder_id'=>'1', 
           	'n_title'=>$data['n_title'], 
           	'n_content'=>$data['n_content'] ,
           	'n_extras'=>$n_extras,
           	)
           ); 
           //$msg_content = json_encode(array('n_builder_id'=>'1', 'n_title'=>'测试', 'n_content'=>'推送了百度网址','n_extras'=>array('go_url'=>'http://www.baidu.com')));
			//$res = $obj->send(1, 4,'', 1, $msg_content,'android'); 
			
			
			
		    $res = $obj->send($data['sendno'], $receiver_type, $data['push_user_alias'], 1, $msg_content, $platform);		


			$data['errcode'] = $res['errcode'];
			$data['errmsg'] = $res['errmsg'];

            $result = array('status'=>true,'info'=>"推送成功");
			if($res['errcode']==0){
			   $result = array('status'=>true,'info'=>"推送成功");
		    }else{
		    	$result = array('status'=>false,'info'=>$res['errmsg']);
		    }

		    $this->model('JPush')->doAddPush($data);//存储到数据库
            
			if($getPushResult){
				//dump($data);
				exit(json_encode($result));
			}

             return true;
            //sleep(120);
			//exit(json_encode($result));
			//
	}

	public function doDel()
	{
		$result = array();
		$id= t($_POST['id']);
		if(empty($id)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('JPush')->doDel($id);
		if($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}







}
