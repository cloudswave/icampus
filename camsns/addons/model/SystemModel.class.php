<?php
/**
 * 系统模型 - 业务逻辑模型
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class SystemModel
{
	/**
	 * 插入统计数据
	 * @return array 返回的相关信息
	 */
	public function upgrade()
	{
		// 请求地址
		$url = 'http://t.thinksns.com/upgrade.php';
		$siteData = $this->_getSiteData();
		// 是否开启CURL，配置CURL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($siteData));
		$result = curl_exec($curl);
		curl_close($curl);
		// 解析数据
		$result = unserialize($result);
		if($result === false) {
			$result['error'] = 1;
			$result['error_message'] = '获取信息失败';
		} else {
			$result['error'] = 0;
			$result['error_message'] = '';
		}

		return $result;
	}

	/**
	 * 获取相关站点数据
	 * @return array 相关站点数据
	 */
	private function _getSiteData()
	{
		$result['site'] = SITE_URL;
		$result['version'] = '3.0';
		$result['output_format'] = '';

		return $result;
	}
}