<?php
class BlogStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias		= getAppAlias('blog');
    	$blog_count		= M('blog')->where('`status`=1')->count();
    	$recycle_count	= M('blog')->where('`status`=2')->count();
    	return array(
    		"{$app_alias}数"	=> $blog_count . '篇',
    		'回收站'			=> $recycle_count . '篇',
    	);
	}
}