<?php
	$creditSet 	 = array();
	//其中 score=>5 表示默认 积分变化为5,其他扩展类型为0
	$creditSet['weibo'] = array(
							array('action'=>'add','info'=>'发表微博','score'=>'10','experience'=>'10'),
							array('action'=>'share','info'=>'分享微博','score'=>'10','experience'=>'10'),
							array('action'=>'reply','info'=>'评论微博','score'=>'10','experience'=>'10'),
						);
	$creditSet['todo'] = array(
							array('action'=>'add','info'=>'添加任务','score'=>'10','experience'=>'10'),
							array('action'=>'finish','info'=>'完成任务','score'=>'10','experience'=>'10'),
						);
	$creditSet['directory'] = array(
			array('action'=>'add','info'=>'收藏联系人','score'=>'10','experience'=>'10'),
	);
	$creditSet['ask'] = array(
			array('action'=>'add','info'=>'发表问题','score'=>'10','experience'=>'10'),
			array('action'=>'reply','info'=>'回到问题','score'=>'10','experience'=>'10'),
	);
	$creditSet['doc'] = array(
			array('action'=>'add','info'=>'上传文档','score'=>'10','experience'=>'10'),
	);
	
	return $creditSet;