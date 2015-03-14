<?php
/**
 * DIY操作类
 * @author Stream
 *
 */
class DiyAction extends Action {
	private static $html = array ();
	private $layout = array ();
	public function _initialize() {
		$html = <<<EOT
<div class="diy_layout_box">
    <div class="div_tit_sort_box">
        <div class="diy_tit_sort">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="diy_tit_sort">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="diy_tit_sort">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="diy_tit_sort">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="diy_tit_sort">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="diy_tit_sort no">
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <br/>
            <a href="#"><strong>链接</strong></a>
            <a href="#">链接</a>
            <a href="#">链接</a>
            <a href="#">链接</a>
        </div>
        <div class="C">
        </div>
    </div>
</div>
EOT;
		$navigation ['html'] = $html;
		$navigation ['title'] = '导航模板';
		self::$html ['navigation'] = $navigation;
	}
	
	public function index() {
		$page = $_GET ['page'];
		$parseTag = model ( 'ParseTag' );
		$map['domain'] = $page;
		$databaseData = model( 'Page' )->getPageInfo ( $map , 'id,page_name,domain,canvas,manager,status,layout_data,widget_data,guest,seo_title,seo_keywords,seo_description');
		$this->checkRole ( $databaseData ['manager'], $databaseData );
		
		$pageData = $parseTag->parseId ( $databaseData ['layout_data'], false );
		$databaseData['canvas'] = CANVAS_PATH . $databaseData['canvas'];
		$this->setTitle ( $databaseData ['page_name'] );
		$this->assign ( 'tempData', $databaseData ['layout_data'] );
		$this->assign ( 'layoutData', unserialize ( $databaseData ['widget_data'] ) );
		$this->assign ( 'template', false );
		$this->assign ( 'data', $pageData );
		$this->assign ( 'page', $page );
		if ( !file_exists( $databaseData['canvas'] ) ){
			$databaseData['canvas'] = APP_TPL_PATH.'/Index/index.html';
		}
		$this->display ( $databaseData ['canvas'] );
	}
	/**
	 * 显示模块设置界面
	 */
	public function getPopUp() {
		switch ($_GET ['tagName']) {
			case "w:DiyCustom" :
				foreach ( self::$html as $key => $value ) {
					$var ['style'] [$key] = $value ['title'];
					$var ['htmlTpl'] [$key] = $value ['html'];
				}
				$this->assign ( $var );
				break;
		}

		$parseTag = model ( 'ParseTag' );
		if (isset ( $_GET ['sign'] )) {
			$data = $parseTag->getTagInfo ( $_GET ['sign'] );
			$attr = $data ['attr'];
			if (! empty ( $data ['content'] )) {
				$this->assign ( 'html', $data ['content'] );
			}
			if ( $_GET ['tagName'] == 'w:DiyWeibo' || $_GET ['tagName'] == 'w:DiyUser'){
				$attr['users'] = explode( ',' , $attr['user'] );
			}
			list ( $o, $t ) = explode ( " ", $attr ['order'] );
			$attr ['order'] = $o;
			$attr ['order_t'] = $t;
			$attr ['content'] = str_replace ( '[@]', '&', $attr ['content'] );
			$attr ['head_link'] = json_encode ( $attr ['head_link'] );
			$attr ['title'] = isset ( $attr ['title'] ) ? $attr ['title'] : "";
			$this->assign ( 'attr', $attr );
		}
		//获取Tag名
		$pop = explode(":", t($_GET['tagName']) );
		if ( !preg_match( '/^[a-zA-z0-9]+$/i' , $pop[0] ) || !preg_match( '/^[a-zA-z0-9]+$/i' , $pop[1])){
			$this->error( '对不起，您访问的页面不存在！' );
		}
		$popup = basename($pop[0]).'/'.basename($pop[1]);
		// $popup = str_replace ( ':', "/", $_GET ['tagName'] );
		$path = SITE_PATH . '/addons/diywidget/Tags/' . $popup . '/popUp.html';
		$this->assign ( 'id', $_GET ['id'] );
		$this->assign ( 'index', $_GET ['index'] );
		$this->assign ( 'parentId', $_GET ['parentId'] );
		$this->assign ( 'layout', $_GET ['needClass'] );
		$this->assign ( 'tagName', $_GET ['tagName'] );
		$this->display ( $path );
	}
	/**
	 * 预览
	 */
	public function preview() {
		$parseTag = model ( 'ParseTag' );
		$map['domain'] = t ( $_REQUEST ['page'] );
		$databaseData = D ( 'Page' )->getPageInfo ( $map , 'canvas' );
		$layout = base64_decode( $_SESSION [ 'layout_' . $_REQUEST ['page'] ] );
		$content = $this->getLayout ( $layout );
		$content = $parseTag->parseId ( $content, false );
		$this->assign( 'page' , $map['domain'] );
		$this->assign ( 'data', $content );
		if ( !file_exists( $databaseData['canvas'] ) ){
			$databaseData['canvas'] = APP_TPL_PATH.'/Index/index.html';
		}
		
		$this->display ( $databaseData ['canvas'] );
	}
	public function copyTemplate() {
		$page = $_GET ['page'];
		$channel = $_GET ['channel'];
		$databaseData = D ( 'Page' )->getPageInfo ( $page, $channel );
		$result = $this->checkRole ( $databaseData ['manager'], $databaseData );
		if ($result ['admin']) {
			$this->assign ( "jumpUrl", U ( 'page/Diy/index', array ('page' => $page, 'channel' => $channel, 'diy' => true ) ) );
			$this->assign ( 'page', $page );
			$this->assign ( 'channel', $channel );
			$info = D ( 'pageTemplate' )->getPageList ( $_GET ['searchKey'], 9 );
			$this->assign ( $info );
			$this->display ();
		} else {
			$this->error ( "您没有权限" );
		}
	}

	public function doCopyTemplate() {
		$id = intval ( $_POST ['id'] );
		$page = $_POST ['page'];
		$channel = $_POST ['channel'];
		$databaseData = D ( 'Page' )->getPageInfo ( $page, $channel );
		$result = $this->checkRole ( $databaseData ['manager'], $databaseData );
		if ($result ['admin']) {
			echo D ( 'pageTemplate' )->saveCopyAction ( $id, $this->mid, $page, $channel );
		} else {
			echo - 1;
		}
	}

	public function setSession() {
		if( trim(strtolower($_POST ['name']))=='mid' || trim(strtolower($_POST ['name']))=='adminlogin' ) {
			exit;
		}
		echo $_SESSION [ $_POST ['name'] ]  = base64_encode( $_POST ['layout']) ;
	}
	
	/**
	 * 保存模块
	 */
	public function saveModel() {
		$parseTag = model ( 'ParseTag' );
		$_POST['tagName'] = t( $_POST['tagName'] );
		$widgetTags = $this->_getTagWidget ( $_POST );
		if (is_array ( $widgetTags )) {
			$result ['html'] = $parseTag->parse ( $widgetTags [0], true );
			$result ['widget'] = $widgetTags [1];

		} else {
			$result ['html'] = $parseTag->parse ( $widgetTags, true );
			$result ['widget'] = $widgetTags;
		}
		$result ['sign'] = $parseTag->getSign ( $_POST ['tagName'] );
		echo json_encode ( $result );
	}
	/**
	 * 模块预览
	 */
	public function previewModel() {
		$parseTag = model ( 'ParseTag' );
		$widgetTags = $this->_getTagWidget ( $_POST );
		
		if( !$_POST && $_GET['p']){
			$this->error('预览状态不支持分页');
		}
		if (is_array ( $widgetTags )) {
			$result ['html'] = $parseTag->parse ( $widgetTags [0], true );
			$result ['widget'] = $widgetTags [1];

		} else {
			$result ['html'] = $parseTag->parse ( $widgetTags, true );
			$result ['widget'] = $widgetTags;
		}
		echo json_encode ( $result );
	}
	/**
	 * 保存DIY页面
	 */
	function saveLayout() {
		$page = t ( $_POST ['page'] );
		$content = $this->getLayout ( $_POST ['layout'] );
		
		//权限判断
		$manager = model('Page')->where("domain='".$page."'")->getField('manager');
		$this->checkRole( $manager );
		
		//对数据进行处理
		if (empty ( $content )) {
			$content = null;
		}
		$result = model ( 'Page' )->saveData ( $page, $content, $this->layout );
		echo U ( 'page/Index/index/', 'page=' . $page );
	}

	/**
	 模板专用
	 **/

	function saveTemplateLayout() {
		$page = $_POST ['page'];
		$channel = 0;
		$userModel = model ( 'UserGroup' );
		if (! $userModel->isAdmin ( $this->mid )) {
			echo U ( 'index/index/index' );
			exit ();
		}

		$content = $this->getLayout ( $_POST ['layout'] );
		//对数据进行处理
		if (empty ( $content )) {
			$content = null;
		}
		$databaseData = D ( 'Page' )->getPageInfo ( $page, $channel );

		$result = D ( 'pageTemplate' )->changeTemplate ( $page, $content, $this->layout );

		echo U ( 'page/Diy/Template', 'page=' . $page );
	}

	public function previewTemplate() {
		$parseTag = model ( 'ParseTag' );
		$layout = Session::get ( 'layout_' . $_GET ['page'] );
		$content = $this->getLayout ( $layout );
		$content = $parseTag->parseId ( $content, false );
		$this->assign ( 'data', $content );
		$this->display ( DATA_PATH . '/page/tpl/default/index.html' );
	}

	public function template() {
		$page = $_GET ['page'];
		$channel = 0;
		$userModel = model ( 'UserGroup' );
		if (! $userModel->isAdmin ( $this->mid )) {
			$this->error ( "您没有权限" );
		}
		$databaseData = D ( 'pageTemplate' )->getTemplate ( $page );
		$this->assign ( 'openDiy', true );
		$this->assign ( 'admin', true );
		$parseTag = model ( 'ParseTag' );
		$this->setTitle ( $databaseData ['page_name'] );
		$this->assign ( 'tempData', $databaseData ['layoutData'] );
		$this->assign ( 'layoutData', unserialize ( $databaseData ['widgetData'] ) );
		$pageData = $parseTag->parseId ( $databaseData ['layoutData'], false );
		$this->assign ( 'template', true );
		$this->assign ( 'data', $pageData );
		$this->assign ( 'page', $page );
		$this->assign ( 'channel', $channel );
		$this->display ( DATA_PATH . '/page/tpl/default/index.html' );
	}

	/**
	 * 模板结束
	 *
	 */

	public function getTpl() {
		$parseTag = model ( 'ParseTag' );
		$tpl = $_REQUEST ['tpl'];
		$sign = $_REQUEST ['sign'];
		$tagName = $_REQUEST ['tagName'];
		echo $parseTag->getTplContent ( $tpl, $tagName, $sign );
	}

	public function checkUserRole() {
		$page = t ( $_POST ['page'] );
		$page = empty ( $page ) ? "index" : $page;
		$channel = t ( $_POST ['channel'] );

		$manager = model ( 'Page' )->where ( "domain='".$page."'")->getField('manager');
		$reuslt = $this->checkRole ( $manager );

		//dump(intval($reuslt['admin']) );
		echo intval ( $reuslt ['admin'] );
		exit ();
	}

	private function checkRole($user, $pageInfo) {
		$admin = false;
		$openDiy = false;
		$userModel = model ( 'UserGroup' );
		$user = explode( ',' , $user);
		if (in_array ( $this->mid, $user ) || $userModel->isAdmin ( $this->mid )) {
			$admin = true;
		} else {
			$this->error( '您没有管理权限！' );
		}
		if (isset ( $_GET ['diy'] ) && $pageInfo ['pageType'] != 'list') {
			$openDiy = true;
		}

		$this->assign ( 'openDiy', $openDiy );
		$this->assign ( 'admin', $admin );
		$result ['admin'] = $admin;
		$result ['openDiy'] = $openDiy;
		return $result;
	}
	private function getLayout( $layout ) {
		$newLayout = json_decode ( $layout );
		$content = '';
		$count = 0;
		$preg = "/([\n\r\t\s]*)<DIV class=[\"']?bg_diy_tit(.*)[\"']?(.*)>(.*)<\/DIV>([\n\r\t\s]*)/siU";
		//dump($newLayout);
		foreach ( $newLayout as $key => $value ) {
			$count ++;
			$key = explode ( 'F', $key );
			$newId = $key [0] . 'F' . (time () + $count);
			$layoutL = "diy_" . $key [0] . "_L";
			$layoutR = "diy_" . $key [0] . "_R";
			$layoutC = "diy_" . $key [0] . "_C";
			$layoutP = "diy_" . $key [0] . "_P";

			$value->html = str_replace ( ' line_E', '', $value->html );
			$value->html = preg_replace ( $preg, '', $value->html );

			$html = $this->replaceHtml ( $layoutL, $value->html, $value->$layoutL, $newId );
			$html = $this->replaceHtml ( $layoutR, $html, $value->$layoutR, $newId );
			$html = $this->replaceHtml ( $layoutC, $html, $value->$layoutC, $newId );
			$html = $this->replaceHtml ( $layoutP, $html, $value->$layoutP, $newId );

			$content .= "<div id='" . $newId . "' class='diy_" . $key [0] . "'>";
			$content .= $html;
			$content .= "</div>";
		}
		return $content;
	}

	private function replaceHtml($layout, $htmlData, $data, $id) {
		if (isset ( $data )) {
			$parseTag = model ( 'ParseTag' );
			$widget = '';
			foreach ( $data as $key => $value ) {
				$tagInfo = $parseTag->getTagInfo ( $value );
				if ($tagInfo ['tagName'] == 'w:DiyCustom') {
					$temp = sprintf ( '<div id="%s" rel = "%s" class="mb10 alL" sign= "%s">', $id . "-" . $layout . "-" . ($key + 1), $tagInfo ['tagName'], $value );
				} else {
					$temp = sprintf ( '<div id="%s" rel = "%s" class="mb10" sign= "%s">', $id . "-" . $layout . "-" . ($key + 1), $tagInfo ['tagName'], $value );
				}
				$temp .= "[widget:" . $value . "]";
				$temp .= "</div>";
				$this->layout [$id] [$layout] [$key] = $value;
				$widget .= $temp;
			}
			$r = sprintf ( '<div class="%s">%s</div>', $layout, $widget );
			$preg = "/([\n\r\t\s]*)<div class=[\"']" . $layout . "(.*)[\"'](.*)>(.*)<\/div>([\n\r\t\s]*)/siU";
			//$htmlData = preg_replace($preg2,'',$htmlData);


			$html = preg_replace ( $preg, $r, $htmlData );
			return $html;
		}
		return $htmlData;
	}
	/**
	 * 组装模块参数<w:test 参数列表  />
	 * @param unknown_type $post
	 * @return string  
	 */
	private function _getTagWidget($post) {
		$tagName = $post ['tagName'];
		unset ( $post ['tagName'] );
		switch ($tagName) {
			case "w:DiyImage" :
				if (! empty ( $post ['image'] )) {
					$attr [] = "image_list=" . "\"" . htmlspecialchars ( json_encode ( $post ['image'] ) ) . "\"";
				}
				foreach ( $post as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false && ! empty ( $value )) {
						$key = str_replace ( 'PARAM_', '', $key );
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}
				$attr = implode ( ' ', $attr );
				break;
			case "w:DiyCustom" :
				$html = $post ['PARAM_html'];
				unset ( $post ['PARAM_html'] );
				foreach ( $post as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false) {
						$key = str_replace ( 'PARAM_', '', $key );
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}
				$replace = array ("echo", "phpinfo", "eval", "mysql", "admin", "<php>", "<?php" );
				$html = str_replace ( $replace, "", $html );
				if (empty ( $html )) {
					$html = "&nbsp;";
				}
				$attr = implode ( ' ', $attr );
				$result [0] = sprintf ( '<%s %s>%s</%s>', $tagName, $attr, $html, $tagName );
				$result [1] = sprintf ( '<%s %s/>', $tagName, $attr );
				return $result;
				break;
			case "w:DiyTab" :

				if (! empty ( $post ['content'] )) {
					$attr [] = "content=" . "\"" . htmlspecialchars ( json_encode ( $post ['content'] ) ) . "\"";
				}

				foreach ( $post as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false && ! empty ( $value )) {
						$key = str_replace ( 'PARAM_', '', $key );
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}

				$attr = implode ( ' ', $attr );
				break;
			default :
				if (isset ( $post ['PARAM_order'] )) {
					$post ['PARAM_order'] = $post ['PARAM_order'] . " " . $post ['PARAM_order_t'];
				}
				unset ( $post ['PARAM_order_t'] );
				if (isset ( $post ['attach'] )) {
					$image = array_shift ( $post ['attach'] );
					list ( $id, $imageName ) = explode ( '|', $image );
					$map ['id'] = $id;
					$model = model ( 'Xattach' )->where ( $map )->field ( 'savepath,savename' )->find ();
					$post ['PARAM_image'] = DATA_URL . "/uploads/" . $model ['savepath'] . $model ['savename'];
				}

				$param = $post;
				$safetyArray = array ("title", "tplHeight", "limit", "words" );
				foreach ( $param as $key => $value ) {
					if (strpos ( $key, 'PARAM_' ) !== false && ! empty ( $value )) {
						$key = str_replace ( 'PARAM_', '', $key );
						if (in_array ( $key, $safetyArray ))
							$value = t ( $value );
						$attr [] = $key . "=\"" . $value . "\"";
					}
				}
				foreach($post['head_link'] as &$value){
					foreach($value as $key=>$v){
						$value[$key] = str_ireplace("script",'s cript',$v);

					}
					$value = array_map("t",$value);
				}
				if (isset ( $post ['head_link'] ) && ! empty ( $post ['head_link'] )) {
					$post ['head_link'] = str_replace ( '[@]', '&', $post ['head_link'] );
					$attr [] = "head_link=" . "\"" . htmlspecialchars ( json_encode ( $post ['head_link'] ) ) . "\"";
				}

				$attr = implode ( ' ', $attr );
		}
		return sprintf ( '<%s %s/>', $tagName, $attr );
	}

	public function popup() {
		$this->assign ( $_GET );
		$this->display ();
	}

}
?>
