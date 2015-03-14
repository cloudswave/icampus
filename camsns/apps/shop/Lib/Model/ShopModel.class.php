<?php
/**
 * 微吧模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class shopModel	extends	Model {

	protected $tableName = 'shop';
	protected $error = '';

	/**
	 * 获取微吧列表，后台可以根据条件查询
	 * @param integer $limit 结果集数目，默认为20
	 * @param array $map 查询条件
	 * @return array 微吧列表信息
	 */
	public function getWeibaList($limit = 20, $map = array()) {	
		if(isset($_POST)) {
			//搜索时用到
			$_POST['weiba_id'] && $map['weiba_id']=intval($_POST['weiba_id']);
			$_POST['weiba_name'] && $map['weiba_name']=array('like','%'.$_POST['weiba_name'].'%');
			$_POST['uid'] && $map['uid']=intval($_POST['uid']);
			$_POST['admin_uid'] && $map['admin_uid']=intval($_POST['admin_uid']);
			$_POST['recommend'] && $map['recommend']=$_POST['recommend']==1?1:0;
		}		
		$map['is_del'] = 0;
		// 查询数据
		$list = $this->findPage($limit);
		
		// 数据组装
		foreach($list['data'] as $k => $v) {
			$list['data'][$k]['shop_name'] = $v['shop_name'];
			$logo = D('attach')->where('attach_id='.$v['shop_ico'])->find();
			$list['data'][$k]['shop_ico'] = '<img src="'.UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'].'" width="50" height="50">';
			$list['data'][$k]['DOACTION'] = '<a href="'.U('shop/Admin/editshop',array('sid'=>$v['sid'],'tabHash'=>'editshop')).'">编辑</a>|<a onclick="admin.delshop('.$v['sid'].');" href="javascript:void(0)">删除</a>';
			//'shop_id','shop_name','logo','credit','people','end_time','DOACTION'
			$list['data'][$k]['shop_name'] = $v['shop_name'];
			//$list['data'][$k]['logo'] &&  $list['data'][$k]['logo'] = '<img src="'.UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'].'" width="50" height="50">';
			$list['data'][$k]['credit_type'] = $this->get_credit_type($v['credit_type']);
			$list['data'][$k]['credit'] = $v['credit'];
			$list['data'][$k]['people'] = $v['people'];
			$list['data'][$k]['endtime'] = date('Y-m-d h:i:s',$v['endtime']);
		}
		return $list;
	}
	
	public function getconvertList($limit = 20, $map = array()) {	
		if(isset($_POST)) {
			//搜索时用到
			$_POST['weiba_id'] && $map['weiba_id']=intval($_POST['weiba_id']);
			$_POST['weiba_name'] && $map['weiba_name']=array('like','%'.$_POST['weiba_name'].'%');
			$_POST['uid'] && $map['uid']=intval($_POST['uid']);
			$_POST['admin_uid'] && $map['admin_uid']=intval($_POST['admin_uid']);
			$_POST['recommend'] && $map['recommend']=$_POST['recommend']==1?1:0;
		}		
		$map['is_del'] = 0;
		// 查询数据
		$list = $this->findPage($limit);
		
		return $list;
	}




	/**
	 * 根据微吧ID获取微吧信息
	 * @param integer $weiba_id 微吧ID
	 * @return array 微吧信息
	 */
	public function getshopById($weiba_id){
		$weiba = $this->where('sid='.$weiba_id)->find();
		if($weiba['logo']){
			$logo = D('attach')->where('attach_id='.$weiba['logo'])->find();
			$weiba['pic_url'] = UPLOAD_URL.'/'.$logo['save_path'].$logo['save_name'];
		}
		if($weiba['endtime']){
			$weiba['endtime']=date('Y-m-d h:i:s',$weiba['endtime']);
		}

		return $weiba;
	}
	
	public function get_credit_type($credit_type){
		$credit_name = D('credit_type')->where('name = "'.$credit_type.'"')->find();
		return $credit_name['alias'];
	}


}