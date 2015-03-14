<?php
class EventStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('event');
		$eventDao = M('event');
		$userDao  = M('event_user');

        $allCount     = $eventDao->field('COUNT(*) AS event,AVG(joinCount) AS joinIn')->find();
        $onGoingCount = $eventDao->field('COUNT(*) AS event,AVG(joinCount) AS joinIn')->where('deadline>'.time())->find();
		return array(
			"{$app_alias}总数"       => $allCount['event'],
            '平均参与人次'           => number_format($allCount['joinIn'],1,'.',''),
            "当前进行的{$app_alias}数" => $onGoingCount['event'],
            '当前平均参与人次'    => number_format($onGoingCount['joinIn'],1,'.',''),
		);
	}
}