<?php
class giftStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	   = getAppAlias('gift');
		$giftCount     = M('gift')->count();
		$giftuserCount = M('gift_user')->count();
		return array(
			"{$app_alias}总数"	 =>$giftCount,
			"{$app_alias}赠送人次"=>	$giftuserCount,
		);
	}
}