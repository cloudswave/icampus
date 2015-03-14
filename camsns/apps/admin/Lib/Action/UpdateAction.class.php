<?php
class UpdateAction extends AdministratorAction {
	var $updateURL = '';
	// var $updateURL = 'http://192.168.1.100/ts3';
	function _initialize() {
		parent::_initialize ();
		set_time_limit ( 0 );
		
		$this->updateURL = C ( 'TS_UPDATE_URL' );
	}

	function index() {
		//目录可写权限判断
		$dirList = array (
				'addons',
				'apps',
				'config',
				'core',
				'URLRewrite',
				'data',
		);
		$noWritable = array();		
		foreach ($dirList as $dir){
			$dirPath = SITE_PATH.'/'.$dir;
			if(is_dir($dirPath) && !is_writable($dirPath)){
				$noWritable[] = $dir; 
			}
		}
		$this->assign('noWritable', $noWritable);
		$this->display ();
	}
	//增加一键升级的功能
	function upateAll(){
		$versionArr = F ( 'versions', '', DATA_PATH . '/update' );
		$key = 0;
		$packageName = '';
		foreach ($versionArr as $k=>$vo){
			if($vo['status']==2) continue;
			
			if($key==0 || $key>$k){
				$key = $k;
				$packageName = $vo['package'];
			}
		}
		
		if($key!=0){
			$_SESSION['admin_update_upateAll'] = true;
			U('admin/Update/index', array('step'=>'isDownBefore','packageName'=>$packageName,'key'=>$key), true);
		}else{
			unset($_SESSION['admin_update_upateAll']);
			U('admin/Update/index', '', true);
		}
		
		
	}
	//查询是否有更新版本
	function step01_checkVersionByAjax() {
		// 取当前版本号
		$path = DATA_PATH . '/update';
		$versionArr = F ( 'versions', '', $path );
		if (! $versionArr) {
			$versionArr [0] = array ();
		}
		$keyArr = array_keys ( $versionArr );
		
		// 取官方最新版本信息
		$url = C('TS_UPDATE_SITE') . '/index.php?app=public&mod=Tool&act=getVersionInfo';
		$remote = file_get_contents ( $url );
		$remote = json_decode ( $remote, true );
		$newArr = getSubByKey($remote, 'id');
		
		$diff = array_diff ( $newArr, $keyArr );
		
		foreach ( $diff as $d ) {
			$list [$d] = $remote [$d];
			$this->_writeVersion ( $d, $remote [$d] );
		}
		foreach ( $versionArr as $k=>$d ) {
			if($k!=0 && $d['status']!=2)
			    $list [$k] = $versionArr [$k];
		}
		
		if(isset($_POST['isCheck'])){
			echo empty($list) ? 0 : 1;
			exit;
		}
		
		$nowkey = 0;
		$title = '';
		foreach ($list as $k=>$vo){
			if($nowkey==0 || $nowkey>$k){
				$nowkey = $k;
				$nowtitle = $vo['title'];
			}
		}

		$this->assign ( 'list', $list );
		$this->assign ( 'nowkey', $nowkey );
		$this->assign ( 'nowtitle', $nowtitle );
		$this->display ();
	}
	//判断是否需要自动升级，如果已经手工下载覆盖代码，则不需要
	function step02_isDownBefore(){
		// 更新当前版本为升级中的版本状态
		$this->_updateVersionStatus ( t ( $_GET ['key'] ) );
				
		$packageName = t ( $_GET ['packageName'] );
		$lockName = DATA_PATH.'/update/'.str_replace('.zip', '.lock', $packageName);
		if(file_exists($lockName)){
			echo 1;
		}else{
			echo 0;
		}
	}
	function step03_download() {
		header ( "content-Type: text/html; charset=utf-8" );
		
		$packageName = t ( $_GET ['packageName'] );
		// $packageName = jiemi ( $packageName );
		
		tsload(ADDON_PATH.'/library/Update.class.php');
		$updateClass = new Update();
		
		$packageURL = $this->updateURL . '/' . $packageName;
		
		echo $updateClass->downloadFile($packageURL);
	}
	function step04_unzipPackage() {
		tsload(ADDON_PATH.'/library/Update.class.php');
		$updateClass = new Update();
		
		$packageName = t ( $_GET ['packageName'] );
		echo $updateClass->unzipPackage($packageName);
	}
	// 关闭站点，并设置关闭原因
	function closeSite() {
		$data = model ( 'Xdata' )->get ( 'admin_Config:site' );
		
		$config ['site_closed'] = $data ['site_closed'];
		$config ['site_closed_reason'] = $data ['site_closed_reason'];
		
		// 保存当前站点的配置关闭原因
		F ( 'site_config', $config, DATA_PATH . '/update' );
		
		$data ['site_closed'] = 0;
		$data ['site_closed_reason'] = '站点升级中...请稍后再访问。';
		
		model ( 'Xdata' )->put ( 'admin_Config:site', $data );
	}
	// 恢复升级前的站点配置
	function openSite() {
		$config = F ( 'site_config', '', DATA_PATH . '/update' );
		if (empty ( $config )) {
			return false;
		}
		
		$data = model ( 'Xdata' )->get ( 'admin_Config:site' );
		$data ['site_closed'] = $config ['site_closed'];
		$data ['site_closed_reason'] = $config ['site_closed_reason'];
		
		model ( 'Xdata' )->put ( 'admin_Config:site', $data );
	}
	// 清除文件缓存
	function cleanCache() {
		$this->_rmdirr ( CORE_RUN_PATH . '/' );
	}
	//自动更新数据库
	function step07_dealSQL() {
		//$this->closeSite();
		
		$filePath = $targetDir = DATA_PATH . '/update/download/unzip/updateDB.php';
		if (! file_exists ( $filePath )) { // 如果本次升级没有数据库的更新，直接返回
			echo 1;
			exit ();
		}
		
		require_once ($filePath);
		updateDB ();
		unlink ( $filePath );
		
		// 数据库验证
		$filePath = $targetDir = DATA_PATH . '/update/download/unzip/checkDB.php';
		if (! file_exists ( $filePath )) { // 如果本次升级没有数据库的更新后的验证代码，直接返回
			echo 1;
			exit ();
		}
		
		require_once ($filePath);
		// checkDB方法正常返回1 否则返回异常的说明信息，如：ts_xxx数据表创建不成功
		checkDB ();	
		
		unlink ( $filePath );
		echo 1;
	}
	//递归检查文件的可写权限
	private function _checkFileIsWritable($source = '',  $res=array()) {
		if (empty ( $source ))
			$source = DATA_PATH . '/update/download/unzip';
	
		$handle = dir ( $source );
		while ( $entry = $handle->read () ) {
			if (($entry != ".") && ($entry != "..")) {
				$file = $source . "/" . $entry;
				if (is_dir ( $file )) {
					$res = $this->_checkFileIsWritable ( $file, $res );
				} else {
					if(!is_writable($file)){
						$res[] = $file;
					}
				}
			}
		}
	
		return $res;
	}	
	
	function step05_checkFileIsWritable(){
		$list = $this->_checkFileIsWritable();
		if(empty($list)){
			echo 1;
			exit;
		}
		
		//删除更新锁
		$packageName = t ( $_GET ['packageName'] );
		$lockName = DATA_PATH.'/update/'.str_replace('.zip', '.lock', $packageName);
		unlink($lockName);
			
		$this->assign('list', $list);
		$this->display();
	}

	//自动覆盖文件
	function step06_overWritten() {
		// 提示需要删除的文件
		$filePath = $targetDir = DATA_PATH . '/update/download/unzip/fileForDeleteList.php';
		if (file_exists ( $filePath )) {
			$deleteList = require_once ($filePath);
			foreach ($deleteList as $d){
				unlink ( SITE_PATH.'/'. $d);
			}
			unlink ( $filePath );
		}
		
		// 执行文件替换
		tsload(ADDON_PATH.'/library/Update.class.php');
		$updateClass = new Update();
		$res = $updateClass->overWrittenFile ();
		if(!empty($res['error'])){
			$this->assign ( 'error', $res ['error'] );
			$this->display ();
		}else{
			echo 1;
		}
	}
	function step08_finishUpate(){
		// 清除缓存
		$this->cleanCache ();
		
		// 开启站点
		$this->openSite ();
		
		// 更新本地版本号信息
		$this->_updateFinishVersionStatus ();
		
		//如果是一键升级的话
		if($_SESSION['admin_update_upateAll']==true){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	// 写入当前版本信息
	private function _writeVersion($key, $arr) {
		$path = DATA_PATH . '/update';
		$arr ['status'] = 0; // 未升级状态
		
		$versionArr = $this->_getVersionInfo ( $path );
		$versionArr [$key] = $arr;
		
		F ( 'versions', $versionArr, $path );
		
		return $versionArr;
	}
	private function _updateVersionStatus($key) {
		$path = DATA_PATH . '/update';
		$versionArr = $this->_getVersionInfo ( $path );
		
		foreach ( $versionArr as $k => &$vo ) {
			if ($k != $key)
				continue;
			
			$vo ['status'] = 1; // 升级中的状态
		}
		
		F ( 'versions', $versionArr, $path );
	}
	private function _updateFinishVersionStatus() {
		$path = DATA_PATH . '/update';
		$versionArr = $this->_getVersionInfo ( $path );
		
		foreach ( $versionArr as $k => &$vo ) {
			if ($vo ['status'] != 1)
				continue;
			
			$vo ['status'] = 2; // 升级完成的状态
		}
		
		F ( 'versions', $versionArr, $path );
	}
	private function _getVersionInfo($path) {
		$file = $path . '/versions.php';
		
		$versionArr = array ();
		if (file_exists ( $file )) {
			$versionArr = F ( 'versions', '', $path );
		}
		
		return $versionArr;
	}
	private function _rmdirr($dirname) {
		if (! file_exists ( $dirname )) {
			return false;
		}
		if (is_file ( $dirname ) || is_link ( $dirname )) {
			return unlink ( $dirname );
		}
		$dir = dir ( $dirname );
		if ($dir) {
			while ( false !== $entry = $dir->read () ) {
				if ($entry == '.' || $entry == '..') {
					continue;
				}
				$this->_rmdirr ( $dirname . DIRECTORY_SEPARATOR . $entry );
			}
		}
		$dir->close ();
		return rmdir ( $dirname );
	}
	function initBetaToRc() {
		$installfile = SITE_PATH.'/ts3BetaToRc.sql';
		if(!file_exists($installfile)){
			echo 'ts3BetaToRc.sql is not exist!';
			exit;
		}
		
		$fp = fopen ( $installfile, 'rb' );
		$sql = fread ( $fp, filesize ( $installfile ) );
		fclose ( $fp );
		
		$db_charset = C ( 'DB_CHARSET' );
		$db_prefix = C ( 'DB_PREFIX' );
		$sql = str_replace ( "\r", "\n", str_replace ( '`' . 'ts_', '`' . $db_prefix, $sql ) );
		foreach ( explode ( ";\n", trim ( $sql ) ) as $query ) {
			$query = trim ( $query );
			if ($query) {
				if (substr ( $query, 0, 12 ) == 'CREATE TABLE') {
					$query = $this->_createtable ( $query, $db_charset );
				} 
				
				$res = M()->execute( $query );
			}
		}
		
		$this->_updataStorey ();
		unlink($installfile);
		echo('Update Finish!');
	}
	private function _createtable($sql, $db_charset){
		$db_charset = (strpos($db_charset, '-') === FALSE) ? $db_charset : str_replace('-', '', $db_charset);
		$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
		$type = in_array($type, array("MYISAM", "HEAP")) ? $type : "MYISAM";
		return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > "4.1" ? " ENGINE=$type DEFAULT CHARSET=$db_charset" : " TYPE=$type");
	}
	private function _updataStorey() {
		$map ['data'] = array (
				'neq',
				'N;' 
		);
		$commentlist = D ( 'comment' )->where ( $map )->findAll ();
		foreach ( $commentlist as $v ) {
			$data = unserialize ( $v ['data'] );
			if ($data ['storey']) {
				D ( 'comment' )->where ( 'comment_id=' . $v ['comment_id'] )->setField ( 'storey', $data ['storey'] );
			}
		}
	}

	function md5File() {
		ini_set();
		$res = $this->_md5File ();
	}
	private function _md5File($source = '.', $res = array()) {
		$handle = dir ( $source );
		
		while ( $entry = $handle->read () ) {
			if (($entry != ".") && ($entry != "..")) {
				$file = $source . "/" . $entry;
				if (is_dir ( $file )) {
					$this->_md5File ( $file, $res );
				} else {
					$data['version'] = 221301;
					$data['file'] = str_replace('./', '', $file);
					$data['md5'] = md5_file($file);
					M('file_version')->add($data);
				}
			}
		}
		
		return $res;
	}
}
