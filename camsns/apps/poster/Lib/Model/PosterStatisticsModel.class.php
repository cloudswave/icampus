<?php
class posterStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('post');
		$posterDao = M('poster');

        $allCount     = $posterDao->field('COUNT(*) AS poster')->find();
		return array(
			"招贴总数"       => $allCount['poster'],
		);
	}
}