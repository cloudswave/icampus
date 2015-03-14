<?php
    /**
     * GiftAction
     * 礼物控制层
     *
     * @uses
     * @package
     * @version
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {

    public function _initialize(){
        //管理权限判定
        parent::_initialize();
    }

    public function index(){
    	switch($_GET['action']){
    		case 'smallType':     //二级分类
                $posterSmallType = D('PosterSmallType');
                $smallType = $posterSmallType->getSmallType();
                $this->assign('smallType',$smallType);
    			break;
    		default:              //大分类
                $posterTypeDao = D('PosterType');
                $this->assign('poster_type',$posterTypeDao->getType());
    	}
    	//$posterWidget = D('PosterWidget');
        //$this->assign('widget',$posterWidget->getWidgetList());
    	//$ico = $posterTypeDao->getIcoList();
    	//$this->assign('ico',$ico);
    	$this->display();
    }

    public function editSmallType(){
    	$label = $_GET['id'];
    	$posterSmallType = D('PosterSmallType');
        $smallType = $posterSmallType->getAllSmallType($label);
        $this->assign('label',$label);
    	$this->assign('smallType',$smallType);
    	$this->display();
    }
	
	//更新小分类
    public function doEditSmallType(){
    	$posterSmallType = D('PosterSmallType');
        $type = array_unique($_POST['type']);
        $more = $_POST['more'];
        if(count($_POST['type']) != count($type)){
            $this->error('小分类项不能重复'); 
        }
        if(count(array_intersect($type , $more)) > 0){
            $this->error('小分类项不能重复'); 
        }   
    	foreach($_POST['type'] as $key=>$value){
    		$map= array();
    		if(empty($_POST['type'][$key])){
    			$map['id'] = $key;
    			$posterSmallType->where($map)->delete();
    		}else{
    			$condition['id'] = $key;
    			$map['label'] = $_POST['name'];
    			$map['name']  = $value;
    			$posterSmallType->where($condition)->save($map);
    		}
    	}
    	$map = array();
    	foreach($_POST['more'] as $value){
    		$map['label'] = $_POST['name'];
    		$map['name'] = $value;
    		$posterSmallType->add($map);
    	}
    	$this->assign('jumpUrl',U('/Admin/editSmallType',array('id'=>$_POST['name'])));
    	$this->success('编辑成功');
    }
    public function doEditSmallTypeItem(){
    	$posterSmallType = D('PosterSmallType');
    	$id = intval($_POST['id']);
    	$map['id'] = $id;
    	if(0 == $map['id']){
    		echo -1;
    	}else{
    		echo $posterSmallType->where($map)->delete()?1:-1;
    	}
    }
    public function adminPoster(){

        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if ( !empty($_POST) ) {
            $_SESSION['admin_search'] = serialize($_POST);
        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
            $_POST = unserialize($_SESSION['admin_search']);
        }else {
            unset($_SESSION['admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');


        $_POST['pid']   && $pid    =   intval($_POST['pid']);
        $_POST['type']  && $typeId =   intval($_POST['type']);
        $_POST['title'] && $title  =   t( $_POST['title'] );

        if ( !strpos($_POST['uid'],",") ){
            //单个
            $_POST['uid']   && $uid = t(h($_POST['uid']));
        }else{
            //多个
            $_POST['uid']   && $uid =  explode(",",$_POST['uid']);
        }

        $posterTypeDao = D('PosterType');
        $posterSmallType = D('PosterSmallType');
        $posterDao = D('Poster');
        $type = $posterTypeDao->getPosterTypeByIdArray();
        $smallType = $posterSmallType->getPosterSmallTypeByIdArray();
        $this->assign('posterType',$type);
        $this->assign('smallType',$smallType);
        $this->assign($_POST);

        $poster = $posterDao->getPosterList($pid,$typeId,$uid,$title);
        $this->assign($poster);
        $this->display();
    }

    public function doDeletePoster(){
    	$posterDao = D('Poster');
    	$id = t($_REQUEST['id']);
    	if(strpos($id,',')){
    		$map['id'] = array('in',$id);
    		$code = 1;
    	}else{
    		if(intval($id) === 0 ){
    			exit;
    		}
    		$map['id'] = $id;
    		$code = 2;
    	}
    	if($posterDao->where($map)->delete()){
            echo $code;            //删除多个
    	}
    }
    public function editType(){
        $posterTypeDao = D('PosterType');
        $posterWidget = D('PosterWidget');
        $posterSmallType = D('PosterSmallType');
        $widget = $posterWidget->getWidget();
        $field = $posterWidget->getFieldWidget($widget);
        $smallType = $posterSmallType->getSmallType();
        $poster = $posterTypeDao->getType(intval($_GET['id']));
        if(!empty($poster['extraField'])){
            $leave_field = $posterWidget->getLeaveField($poster['extraField'],$widget);
        }else{
            $leave_field = $widget;
        }
        $ico = $posterTypeDao->getIcoList();

        $this->assign('liveField',$leave_field);
        $this->assign('smallType',$smallType);
        $this->assign('widget',$widget);
        $this->assign('fields',$field);
        $this->assign('poster',$poster);
        $this->assign('ico',$ico);
        $this->display();
    }


    public function add(){
        switch($_GET['action']){
            case 'extra':       //额外字段
                $posterWidget = D('PosterWidget');
                $this->assign('widget',$posterWidget->getWidgetList());
                break;
            case 'smallType':   //二级分类
                break;
            default:            //大分类
                $posterTypeDao = D('PosterType');
                $ico = $posterTypeDao->getIcoList();
                $this->assign('icopath',APP_PUBLIC_URL.'/images/ico/');
                $this->assign('ico',$ico);
        }
        $this->display();
    }
    public function doEditType(){
    	$data['name'] = trim($_POST['name']);
    	$id = intval($_POST['id']);
    	$data['explain'] = trim($_POST['explain']);
    	$data['ico'] = $_POST['ico'];
    	$data['templet'] = implode(',',$_POST['widget']);
    	if(0 !== $_POST['type']){
    		$data['type'] = $_POST['type'];
    	}
    	$posterTypeDao = D('PosterType');
    	if($posterTypeDao->where('id='.$id)->save($data)){
    		$this->success("修改成功");
    	}else{
    		$this->error("修改失败");
    	}
    }
	//删除招贴大分类
    public function doTypeDel(){
    	$posterTypeDao = D('PosterType');
        $posterDao = D('Poster');
    	if(isset($_POST['id'])){
    		$id = array('in',$_POST['id']);
    	}else{
    		$id = intval($_GET['id']);
    	}
    	$map['id'] = $id;
    	if(0 == $id){
    		echo 0;
    	}else{
    		$posterTypeDao->where($map)->delete();
    		unset($map);
    		$map['pid'] = $id;
            $posterDao->where($map)->delete();
    		if ( !strpos($_POST['id'],",") ){
            	echo 2;            //说明只是删除一个
            }else{
            	echo 1;            //删除多个
            }
    	}
    }
    //删除二级分类
	public function doDeleteSmallType(){
		$posterSmallDao = D('PosterSmallType');
		if(!strpos($_REQUEST['id'],",") ){
			$id = $_REQUEST['id'];
			$map['label'] = $id;
        }else{
            $id = explode(',',$_REQUEST['id']);
            $map['label'] = array('in',$id);
        }
        $res = $posterSmallDao->where($map)->delete();
        // dump($posterSmallDao->getlastsql());exit;
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    //添加二级分类
    public function doAddSmallType(){
    	$type = explode('<br />',nl2br($_POST['data']));
        if(count(array_unique($type)) != count($type)){
            $this->error("小分类项不能重复");
        }        
    	$dao = D('PosterSmallType');
        $map['name'] = $_POST['name'];
        $res = $dao->where($map)->find();
        if($res){
            $this->error('该小分类集合名已存在');
        }
        foreach($type as $value){
        	$map['label'] = trim($_POST['name']);
        	$map['name']  = str_replace('\r\n','',$value);
        	$dao->add($map);
        }
        $this->assign('jumpUrl',U('/Admin/editSmallType',array('id'=>$map['label'])));
        $this->success('添加成功,将跳转到小分类编辑页面');
    }

    public function doAddType(){
    	$data['name'] = trim($_POST['name']);

    	$data['ico'] = trim($_POST['ico']);
    	$data['explain'] = trim($_POST['explain']);
    	$posterTypeDao = D('PosterType');
    	$old_name = $posterTypeDao->where('name='.$data['name'])->find();
        if($old_name){
        	$this->error('招贴种类名称重复');
        }
    	$rs = $posterTypeDao->addType($data);
    	switch($rs){
    		case -1:
    			$this->error('招贴种类名称没有填写');
    			break;
    		case -2:
    			$this->error('招贴种类描述没有填写');
    			break;
    		case -3:
    			$this->error('招贴种类的图标不存在或者为空');
    			break;
    		case false:
    			$this->error('未知错误，添加失败');
    			break;
    		default:
    			$this->assign('jumpUrl',U('/Admin/editType',array('id'=>$rs)));
    			$this->success('添加成功,即将跳转到大分类编辑页面');
    	}
    }

    public function doAddWidget(){
    	$data['label'] = t(h($_POST['name']));
    	$data['widget'] = str_replace('Widget','',$_POST['widget']);
    	require_once APP_PATH."/Lib/Widget/".$_POST['widget'].'.class.php';
        $class = new $_POST['widget']();
        $data['data'] = serialize($class->getData($_POST['data']));
        $data['field'] = t(h($_POST['field']));
        $posterWidgetDao = D('PosterWidget');
        $rs = $posterWidgetDao->addWidget($data);
            switch($rs){
            case -1:
                $this->error('额外字段名称没有填写');
                break;
            case -2:
                $this->error('渲染器所需数据为空');
                break;
            case false:
                $this->error('未知错误，添加失败');
                break;
            default:
                $this->success('添加额外属性成功');
        }
    }

    // 推荐招贴
    public function recommendPoster(){
        $map['id'] = intval($_POST['id']);
        if(intval($_POST['recommend']) == 1){
            $save['recommend'] = 0;
        }else{
            $save['recommend'] = 1;
        }
        $res = D('Poster')->where($map)->save($save);
        echo $res;
    }
}
