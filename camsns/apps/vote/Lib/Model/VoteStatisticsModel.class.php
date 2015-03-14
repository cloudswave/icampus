<?php
class VoteStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('vote');
		$voteDao     = M('vote');
		$voteUserDao = M('vote_user');
		$voteNum     = $voteDao->count();
		$times       = $voteUserDao->where(' opts<>"" ')->count();
		return array(
			"发起{$app_alias}数"	=>	$voteNum,
			"参与{$app_alias}的人次"	=>	$times,
		);
	}
}