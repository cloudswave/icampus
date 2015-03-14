<?php
/**
 * 插件请求控制器
 * @author zivss guolee226@gmail.com
 * @version TS3.0
 */
class WidgetAction extends Action
{
	public function renderWidget()
	{
		//非登录下widget调用过滤
		if(!$this->mid){
			$access_widget = array();
			if(!in_array($_REQUEST['name'],$access_widget))exit;
		}
		$_REQUEST['name']  = t($_REQUEST['name']);
		$_REQUEST['param'] = unserialize(urldecode($_REQUEST['param']));
		send_http_header('utf8');
		echo empty($_REQUEST['name']) ? 'Invalid Param.' : W(t($_REQUEST['name']), t($_REQUEST['param']));
	}

	// 插件的请求转发
	public function addonsRequest()
	{
		Addons::addonsHook(t($_REQUEST['addon']),t($_REQUEST['hook']));
	}

	// 插件的渲染
	public function displayAddons(){
        $result = array();
        $param['res'] = &$result;
        $param['type'] = $_REQUEST['type'];
        Addons::addonsHook(t($_GET['addon']),t($_GET['hook']),$param);
        isset($result['url']) && $this->assign("jumpUrl",$result['url']);
        isset($result['title']) && $this->setTitle($result['title']);
        isset($result['jumpUrl']) && $this->assign('jumpUrl',$result['jumpUrl']);
        if(isset($result['status']) && !$result['status']){
            $this->error($result['info']);
        }
        if(isset($result['status']) && $result['status']){
            $this->success($result['info']);
        }
	}

	// 发微博
	public function weibo()
	{
		// 解析参数
		$_REQUEST['param'] = unserialize(urldecode($_REQUEST['param']));
		$active_field = $_REQUEST['param']['active_field'] == 'title' ? 'title' : 'body';
		$this->assign('has_status', $_REQUEST['param']['has_status']);
		$this->assign('is_success_status', $_REQUEST['param']['is_success_status']);
		$this->assign('status_title', t($_REQUEST['param']['status_title']));

		// 解析模板(统一使用模板的body字段)
		$_REQUEST['data'] = unserialize(urldecode($_REQUEST['data']));
		$content = model('Template')->parseTemplate(t($_REQUEST['tpl_name']), array($active_field=>$_REQUEST['data']));
		// 设置微博发布框的权限
		$type = array('at', 'image', 'video', 'file', 'contribute');
		$actions = array();
		foreach($type as $value) {
			$actions[$value] = false;
		}
		$this->assign('actions', $actions);
		$this->assign('title', $content['title']);
		$this->assign('initHtml', $content['body']);

		$this->assign('content', h($content[$active_field]));
		$this->assign('source',$_REQUEST['data']['source']);
		$this->assign('sourceUrl',$_REQUEST['data']['url']);
		$this->assign('type',$_REQUEST['data']['type']);
		$this->assign('type_data',$_REQUEST['data']['type_data']);
		$this->assign('button_title', t(urldecode($_REQUEST['button_title'])));
		$this->assign('addon_info',urldecode($_REQUEST['addon_info']));
		$this->display();
	}
}
