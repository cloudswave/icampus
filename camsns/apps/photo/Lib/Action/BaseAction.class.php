<?php
/**
 * 相册应用 - 公共基础控制器
 */
class BaseAction extends Action {

	var $appName;			// 应用名称

	/**
	 * 执行应用初始化
	 * @return void
	 */
	public function _initialize() {
		global $ts;
		$this->appName = $ts['app']['app_alias'];
		if ($this->mid == $this->uid) {
			$userName = '我';
		} else {
			$userName = getUserName($this->uid);
		}
		$this->assign('userName', $userName);
		$this->setTitle($userName.'的'.$this->appName);
	}
}