<feed app='weiba' type='weiba_post' info='微吧原创'>
	<title comment="feed标题"> 
		<![CDATA[ {$actor}  ]]>
	</title>
	<body comment="feed详细内容/引用的内容">
		<![CDATA[ {$body|t|replaceUrl}
		<php>if(APP_NAME != 'channel'){</php>
		<a href="javascript:void(0)" class="ico-details" event-node ='loadPost' event-args='feed_id={$feedid}&post_id={$app_row_id}' id="{$feedid}"><!--查看全文--></a><div class="feed_img_lists" style="display:none;" id="post_{$feedid}_{$app_row_id}">
		</div>
		<php>}else{</php>
		<a href="{:U('weiba/Index/postDetail',array('post_id'=>$app_row_id))}" class="ico-details" target="_blank"></a>
		<php>}</php>
		]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>