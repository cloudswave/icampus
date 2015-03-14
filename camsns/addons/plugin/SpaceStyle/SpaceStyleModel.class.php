<?php
/**
 * 皮肤风格模型
 * @author 陈伟川 <258396027@qq.com>
 * @version TS3.0
 */
class SpaceStyleModel extends Model
{
	protected $tableName = 'user_change_style';
	protected $fields = array('uid', 'classname', 'background');
	private $_error = null;		// 最后的错误信息

	/**
	 * 获取最后的错误信息
	 * @return string 最后的错误信息
	 */
	public function getLastError()
	{
		return $this->_error;
	}

	/**
	 * 获取指定用户的样式
	 * @param integer $uid 用户ID
	 * @return array 样式相关数据
	 */
	public function getStyle($uid)
	{
		$uid = intval($uid);
		if (!$uid) {
			return false;
		}
        $map = array('uid' => $uid);
        $style_data = $this->where($map)->find();
        $style_data['background'] = unserialize($style_data['background']);
        return $style_data;
	}

	/**
	 * 保存指定用户的样式
	 * @param integer $uid 用户ID
	 * @param array $style_data 样式数据
	 * @return boolean 是否保存成功
	 */
	public function saveStyle($uid, $style_data)
	{
		$style_data = $this->_escapeStyleData($uid, $style_data);
		if(false === $style_data) {
			return false;
		}
        // 判断重名
        $map = array('uid'=>$style_data['uid']);
        $uid = $this->getField('uid', $map);
        if($uid > 0) {
            $res = $this->where('uid='.$uid)->save($style_data);
        } else {
            $res = $this->add($style_data);
        }

        if(false !== $res) {
			$this->_error = '设置成功';
            return true;
        } else {
			$this->_error = '设置失败';
            return false;
        }
	}

	/**
	 * 获取后台设置的默认样式
	 * @return string 默认样式Key值
	 */
	public function getDefaultStyle()
	{
		$default = model('AddonData')->getAddons('default_style');
        empty($default) && $default = 'default';
        return $default;
	}

	/**
	 * 处理数据的合法性
	 * @param integer $uid 用户ID
	 * @param array $style_data 样式相关数据
	 * @return mixed 成功返回处理后的数据，失败返回false
	 */
	private function _escapeStyleData($uid, $style_data)
	{
		$_style_data['uid'] = intval($uid);
		$_style_data['classname'] = t($style_data['classname']);
		$_style_data['background'] = $this->_escapeBackgroundData($style_data['background']);
		if($_style_data['uid'] > 0) {
			return $_style_data; 
		} else {
			$this->_error = '用户UID 不合法';
			return false;
		}
	}

	/**
	 * 序列化样式相关数据
	 * @param array $background_data 背景相关样式
	 * @return string 序列化样式相关数据
	 */
	private function _escapeBackgroundData($background_data)
	{
		$_backgroup_data['color']  = '';//t($background_data['color']);//暂时无效
		$_backgroup_data['image']  = t($background_data['image']);//图片文件
		$_backgroup_data['repeat'] = (in_array($background_data['repeat'],array('repeat','no-repeat')))?t($background_data['repeat']):'';//repeat no-repeat
		$_backgroup_data['attachment'] = (in_array($background_data['attachment'],array('fixed','scroll')))?t($background_data['attachment']):'';//fixed scroll
		$_backgroup_data['position'] = (in_array($background_data['position'],array('top center','top left','top right')))?t($background_data['position']):'';//top center/top left/top right
		return serialize($_backgroup_data);
	}
}