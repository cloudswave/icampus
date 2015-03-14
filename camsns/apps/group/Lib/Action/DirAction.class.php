<?php
class DirAction extends BaseAction
{
	var $dir;

	public function _initialize(){
		parent::_initialize();

		// 判断功能是否开启
		if (!$this->groupinfo['openUploadFile']) {
			$this->error('群文档已关闭');
		}

		$this->dir = D('Dir');
		$this->assign('current','dir');
	}

	//相册列表
	function index() {
		parent::base();

		$search_key = $this->_getSearchKey('k','group_file_search');
		if ($search_key) {
			$map[] = "a.name LIKE '%{$search_key}%'";
		}

		$map[] = "gid={$this->gid}";
		$fileList = $this->dir->getFileList($html=1, $map, null, 'ctime DESC');
// 		$usedSpace = $this->dir->where('gid=' . $this->gid . ' AND is_del=0')->field('s');
		$usedSpace = $this->dir->join('as a inner join '.C('DB_PREFIX').'attach as b on a.attachId=b.attach_id ')->where('a.gid='.$this->gid.' AND a.is_del=0')->sum('b.size');
		$this->assign('usedRate', ($usedSpace/($this->config['spaceSize']*1024*1024)));  //空间使用率
		$this->assign('usedSpace', $usedSpace);  //使用空间大小
		$this->assign('fileList', $fileList);
		$this->setTitle($this->siteTitle['file_index']);
		$this->display();
	}

	function file() {
		$fid = intval($_GET['fid']) > 0 ?  intval($_GET['fid']) : 0;
		if($fid == 0) exit();

		$fileInfo = $this->dir->where('id='.$fid.' AND is_del=0')->find();
		if(!$fileInfo) $this->error('文件不存在');
		$this->assign('fileInfo',$fileInfo);
		$this->setTitle($fileInfo['name']);
		$this->display();
	}

	//上传文件
	function upload() {
		//权限判读 用户没有加入该群组
		if(!$this->ismember){
			$this->error('对不起，您不是群内成员');
		}

		//系统后台配置仅管理员可以上传
		if($this->groupinfo['whoUploadFile'] == 2 && !$this->isadmin) {
			$this->error('对不起，仅管理员可以上传文件');
		}

		parent::base();
		$usedSpace = $this->dir->join('as a inner join '.C('DB_PREFIX').'attach as b on a.attachId=b.attach_id ')->where('a.gid='.$this->gid.' AND a.is_del=0')->sum('b.size'); //判读空间大小
		
		$uploadSize = intval($_FILES['uploadfile']['size']);
		$usedSpace  = intval($usedSpace) + $uploadSize;
		if($usedSpace >= $this->config['spaceSize']*1024*1024) {
			if ($_GET['ajax'] == 1) {
				exit(json_encode(array('status'=>0, 'info'=>'空间已经使用完！')));
			}
			$this->error('空间已经使用完');//如果使用完，提示错误信息
		}
		if(isset($_POST['uploadsubmit']) || $_GET['ajax'] == 1) {
			//上传参数
			$upload['max_size']   = $this->config['simpleFileSize'] * 1024 * 1024;
			$upload['allow_exts'] = str_replace('|', ',', $this->config['uploadFileType']);
			$upload['attach_type'] = 'groupfile';
			$info = model('Attach')->upload(null,$upload);
        	//执行上传操作
        	if($info['status']){  //上传成功
        		$uploadFileInfo = $info['info'][0];

        		$attchement['gid'] = $this->gid;
        		$attchement['uid'] = $this->mid;
        		$attchement['attachId'] = $uploadFileInfo['attach_id'];
        		$attchement['name'] = $uploadFileInfo['name'];
        		$attchement['note'] = !empty($_POST['note']) ? t($_POST['note']) : '';
            	$attchement['filesize'] = $uploadFileInfo['size'];
            	$attchement['filetype'] = $uploadFileInfo['extension'];
            	$attchement['fileurl'] = $uploadFileInfo['save_path'] . $uploadFileInfo['save_name'];
            	$attchement['ctime'] = time();
			    if ($_GET['ajax'] == 1) {
			        $attchement['is_del'] = 1; // 异步上传的文件默认为删除状态，等异步信息保存时候再设定为非删除
			    }
            	$result = $this->dir->add($attchement);

            	if($result) {
            		// 积分
// 					X('Credit')->setUserCredit($this->mid, 'group_upload_file');

			        if ($_GET['ajax'] == 1) {
			        	$info['info']['0']['id'] = $result;
			            exit(json_encode($info));
			        }
            		//添加动态
					/*$title_data["actor"] = getUserName($this->mid);
					$title_data['gid'] = $this->gid;
					$title_data['group_name'] = $this->groupinfo['name'];

   					//$body_data['url'] = __APP__."/Dir/file/gid/{$this->gid}/fid/".$result;
   					$body_data['name'] = $uploadFileInfo['name'];
   					$body_data['gid'] = $this->gid;
   					$body_data['fid'] = $result;

   					$appid= 'group_'.$this->gid;

            		$this->api->feed_publish('group_file',$title_data,$body_data,$this->appId,0,$this->gid);

            		setScore($this->mid,'group_file_upload');*/
            		//$this->assign('fid',$result);
        			//$this->assign('uploadSuccess',true);
        			$_SESSION['uploadSuccess'] = 1;
        			$this->redirect('group/Dir/upload', array('gid'=>$this->gid,'fid'=>$result));
            	}else{
			        if ($_GET['ajax'] == 1) {
			            exit(json_encode(array('status'=>0, 'info'=>'保存文件失败')));
			        }
            		$this->error('保存文件失败');
            	}
        	}else{
			    if ($_GET['ajax'] == 1) {
			        exit(json_encode($info));
			    }
        		$this->error($info['info']);
        	}
		}

		$this->setTitle($this->siteTitle['file_upload']);
		$this->assign('uploadType',str_replace('|', ',', $this->config['uploadFileType']));
		$this->assign('upload',$upload);
		$this->display();

		unset($_SESSION['uploadSuccess']);
	}

	//下载
	function download()
	{
		$gid = $this->groupinfo['id'];
		$this->assign('jumpUrl',U('group/Dir/index', array('gid'=>$gid)));
		//权限判读 用户没有加入过
		if (3 == intval($this->groupinfo['whoDownloadFile']) && !$this->ismember) {
			$this->error('对不起，该群只允许群内成员下载文件');
		} else if (2 == intval($this->groupinfo['whoDownloadFile']) && !$this->isadmin) {
			$this->error('对不起，只允许管理员下载文件');
		}

		$fid = intval($_POST['fid']) > 0 ?  intval($_POST['fid']) : 0;
		if($fid == 0) exit();
		//下载函数
		//import("ORG.Net.Http");             //调用下载类

		$file_info = $this->dir->where('id='.$fid.' AND is_del=0')->find();
		$file_path = UPLOAD_PATH . '/' . $file_info['fileurl'];
		if (file_exists($file_path)) {
			// 增加下载次数
	   		$this->dir->setInc('totaldowns', 'id=' . $fid);
	   		// 积分
			X('Credit')->setUserCredit($this->mid, 'group_download_file');

			include_once(SITE_PATH . '/addons/libs/Http.class.php');
			$file_info['name'] = iconv("utf-8", 'gb2312', $file_info['name']);
			Http::download($file_path, $file_info['name']);
		}
		$this->error('文件不存在');
	}

	//删除文件
	public function delfile()
	{
		$id = $_POST['fid'] ?  t($_POST['fid']) : 0;
		if($id == 0) exit(json_encode(array('flag'=>'0', 'msg'=>'文件参数错误')));

		if (strpos($id, ',') && $this->isadmin) {
			$map['id']  = array('IN',$id);
			$map['gid'] = $this->gid;
			$file = $this->dir->field('uid')->where($map)->findAll();
			if(empty($file)) exit(json_encode(array('flag'=>'0', 'msg'=>'文件不存在')));
		} else if (is_numeric($id)) {
			$map['id']  = $id;
			$map['gid'] = $this->gid;
			$file = $this->dir->field('uid')->where($map)->find();
			if(empty($file)) exit(json_encode(array('flag'=>'0', 'msg'=>'文件不存在')));
			if (!$this->isadmin && $file['uid'] != $this->mid) {
				exit(json_encode(array('flag'=>'0', 'msg'=>'你没有权限')));
			}
		} else {
			exit(json_encode(array('flag'=>'0', 'msg'=>'你没有权限')));
		}

		if($this->dir->remove($id)) {
			exit(json_encode(array('flag'=>'1', 'msg'=>'删除成功')));
		}else{
			exit(json_encode(array('flag'=>'0', 'msg'=>'删除失败')));
		}
	}

	function deldialog() {
		$this->display();
	}

	//修改文件
	function editfile() {

		//判段权限
		$fid = intval($_POST['fid']) > 0 ?  intval($_POST['fid']) : 0;
		$file = $this->dir->find($fid);
		if($fid == 0 || empty($file)) exit();
		//权限判读 管理者，或者用户自己删除
		if(!$this->isadmin || $this->mid != $file['uid'] ){
			exit('你没有权限');
		}

		$note = isset($_POST['note']) ?  $_POST['note'] : '';
		$this->dir->where('id='.$fid)->setField('note',$note);
	}

	function editdialog() {
		$this->display();
	}
}