<?php
/**
 * 极光推送插件模型 - 数据对象模型
 * @author 朱小波 <ethanzhu@qq.com>
 * @version TS3.0
 */
class JPushModel extends Model
{
	protected $tableName = 'jpush';
	/**
	 * 添加自定义通知
	 * @param array $data 广告位相关数据
	 * @return boolean 是否插入成功
	 */
	public function doAddPush($data)
	{
		$res = $this->add($data);
		return (boolean)$res;
	}
	/**
	 * 获取通知列表数据
	 * @return array 列表数据
	 */
	public function getPushList()
	{
	    $data = $this->order('id DESC')->findpage(20);
		//echo "1111";
		return $data;
	}


	public function	doDel($id)
	{
		if(empty($id)) {
			return false;
		}
		$map['id'] = $id;
		$res = $this->where($map)->delete();
		return (boolean)$res;
	}

	public function getMaxNum(){
        $sendno=$this->max('sendno');
        //echo $sendno;
		return $sendno; // 获取最大number;
	}
}