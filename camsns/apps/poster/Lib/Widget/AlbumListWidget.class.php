<?php
class AlbumListWidget extends Widget{
	//type:select
	public function render( $data ){
		//初始化参数
		if(empty($data['type']))		$data['type']		= 'select';
		if(empty($data['form_name']))	$data['form_name']	= 'albumlist';
		if(empty($data['form_id']))		$data['form_id']	= 'albumlist';
		if(empty($data['uid']))			$data['uid']		= $_SESSION['mid'];

		//创建默认相册
		$pre	=	C('DB_PREFIX');
		if(D()->table("{$pre}photo_album")->where("isDel=0 AND userId='".$data['uid']."'")->count()==0){
			$album['cTime']		=	time();
			$album['mTime']		=	time();
			$album['userId']	=	$data['uid'];
			$album['name']		=	'我的相册';
			$album['privacy']	=	1;
			D()->table("{$pre}photo_album")->add($album);
		}

		//获取相册列表数据
		$data['data']	=	D()->table("{$pre}photo_album")->where("isDel=0 AND userId='".$data['uid']."'")->findAll();
		return $this->renderFile( $data );
	}
	/**
	 * renderFile
	 * 重写renderFile.可以自由组合参数进行模板输出. 如果删除了模版缓存文件,第一次的输出结果就为空了.
	 * @param string $templateFile
	 * @param string $var
	 * @param string $charset
	 * @access protected
	 * @return maxed
	 * /
	protected function renderFile( $data,$charset = 'utf-8' ){

		//输出模版
		$templateFile		=	'AlbumListWidget/'.$data['type'];

		//模版赋值
		$var['data']		=	$data['data'];
		$var['form_name']	=	$data['form_name'];
		$var['form_id']		=	$data['form_id'];
		$var['selected']	=	intval($data['selected']);

		return parent::renderFile( $templateFile,$var,$charset );
	}
	/*
		Widget模版在第一次初始化的时候，写不进数据，故先改成如下形式。
	*/
	protected function renderFile( $data,$charset = 'utf-8' ){

		$out	=	'<select name="'.$data['form_name'].'" id="'.$data['form_id'].'">';
		foreach($data['data'] as $vo){
			if( $vo['id'] == intval($data['selected']) ){
				$out	.=	'<option value="'.$vo['id'].'" selected="selected">'.$vo['name'].'</option>';
			}else{
				$out	.=	'<option value="'.$vo['id'].'">'.$vo['name'].'</option>';
			}
		}
		$out	.=	'</select>';
		return	$out;
	}
	public function getData($data){
       return $data;
    }
}