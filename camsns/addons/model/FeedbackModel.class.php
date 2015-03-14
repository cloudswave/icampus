<?php
/**
 * 意见反馈模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class FeedbackModel extends Model {

	protected $tableName = 'feedback';
	protected $fields = array(0=>'id',1=>'feedbacktype',2=>'feedback',3=>'uid',4=>'ctime',5=>'mtime',6=>'type','_autoinc'=>true,'_pk'=>'id');

	/**
	 * 修改指定的意见反馈
	 * @param integer $id 意见反馈资源ID
	 * @return void
	 */
	public function savefeedback($id) {
		$map['id'] = intval($id);
		$save['type'] = '10';
		$this->where($map)->save($save);		
	}
	
	/**
	 * 获取意见反馈类型的Hash数组
	 * @return array 意见反馈类型的Hash数组
	 */
	public function getFeedBackType() {
		$data = D('')->table($this->tablePrefix.'feedback_type')->getHashList('type_id','type_name');
		return $data;
	}
}