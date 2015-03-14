<?php 
return array(
	'group_audit'    => array(
		'title' => '您创建的群组"' . $title . '"通过了审核',
		'other' =>'<a href="'.U('group/Group/index',array('gid'=>$group_id)).'" target="_blank">去看看</a>',
	),
	'group_delaudit' => array(
		'title' => '您创建的群组"' . $title . '"被驳回',
		'other' => '原因：' . $reason,
	),
	'group_del' => array(
		'title' => '您的群组"' . $title . '"被删除',
	),
	'group_topic_quote' => array(
		'title' => '{actor} 引用了您' . $post,
		'body'  => '引用内容：<p class="quote" style="display:inline;"><span class="quoteR">' . $quote . '</span></p><br /><br />
					<p>回复内容：“' . $content . '”</p>',
		'other' => '<a href="' . U('group/Topic/topic',array('gid'=>$gid, 'tid'=>$tid)).'" target="_blank">去看看</a>',
	),
	'group_topic_reply' => array(
		'title' => '{actor} 回复了您的帖子“' . $title . '”',
		'body'  => '回复内容：“' . $content . '”',
		'other' => '<a href="' . U('group/Topic/topic',array('gid'=>$gid, 'tid'=>$tid)).'" target="_blank">去看看</a>',
	),
	'group_topic_dist' => array(
		'title' => '{actor} 已将您的帖子 “' . $title . '” 设置为精华',
		'other' => '<a href="' . U('group/Topic/topic',array('gid'=>$gid, 'tid'=>$tid)).'" target="_blank">去看看</a>',
	),
	'group_topic_top'  => array(
		'title' => '{actor} 已将您的帖子 “' . $title . '” 置顶',
		'other' => '<a href="' . U('group/Topic/topic',array('gid'=>$gid, 'tid'=>$tid)).'" target="_blank">去看看</a>',
	),
);