<feed app='weiba' type='weiba_repost' info='微吧转发'>
	<title> 
		<![CDATA[{$actor} ]]>
	</title>
	<body>
		<![CDATA[
		<eq name='body' value=''>微博分享<else />{$body|t|replaceUrl}</eq>
		<dl class="comment">
			<dt class="arrow bgcolor_arrow"><em class="arrline">◆</em><span class="downline">◆</span></dt>
			<php>if($sourceInfo['is_del'] == 0 && $sourceInfo['source_user_info'] != false):</php>
			<dd class="name">
				@{$sourceInfo.source_user_info.uname}
				<volist name="sourceInfo['groupData'][$sourceInfo['source_user_info']['uid']]" id="v2">
    				<img style="width:auto;height:auto;display:inline;cursor:pointer" src="{$v2['user_group_icon_url']}" title="{$v2['user_group_name']}" /> 
				</volist>
			</dd>
			<dd>
				<p>
				{$sourceInfo.source_content|t}
				<php>if(APP_NAME == 'public' || APP_NAME == 'widget'){</php>
				<a href="javascript:void(0)" class="ico-details" event-node ='loadPost' event-args='feed_id={$feedid}&post_id={$app_row_id}' id="{$feedid}"><!--查看全文--></a>
				<div class="feed_img_lists" style="display:none;" id="post_{$feedid}_{$sourceInfo['post_id']}">
				</div>
				<php>}else{</php>
				<a href="{:U('weiba/Index/postDetail',array('post_id'=>$app_row_id))}" class="ico-details" target="_blank"></a>
				<php>}</php>
				</p>
			</dd>
			<php>else:</php>
			<dd class="name">内容已被删除</dd>
			<php>endif;</php>
		</dl>
		]]>
	</body>
	<feedAttr comment="true" repost="true" like="false" favor="true" delete="true" />
</feed>