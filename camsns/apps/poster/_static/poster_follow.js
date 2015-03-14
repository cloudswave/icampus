var poster = {};
/**
 * 更改关注分类状态
 * @param integer uid 关注用户ID
 * @param integer cid 分类ID
 * @param string type 更新类型，add or del
 * @param object obj 按钮DOM对象
 * @param f_target [大0 或 小1分类]
 * @return void
 */
poster.upFollowStatus = function(uid, cid, type, f_target,obj)
{
	// 数据验证
	if(typeof uid == 'undefined' || typeof cid == 'undefined' || typeof type == 'undefined'||f_target=='undefined') {
		return false;
	}
  

	
	// 异步提交处理
	$.post(U('poster/Follow/upFollowStatus'), {uid:uid, cid:cid, type:type,f_target:f_target}, function(res) {
		if(res.status == 1) {
			if(type === 'del') {
				ui.success('取消关注成功');
				$(obj).html('<span><i class="ico-add-black"></i>关注</span>');
				$(obj).attr('onclick', "poster.upFollowStatus('"+uid+"', '"+cid+"', 'add', "+f_target+",this)");
			} else if(type === 'add') {
				ui.success('关注成功');
				$(obj).html('<span><i class="ico-already"></i>已关注</span>');
				$(obj).attr('onclick', "poster.upFollowStatus('"+uid+"', '"+cid+"', 'del', "+f_target+",this)");
			}
		} else {
			ui.error('关注失败');
		}
	}, 'json');
	return false;
};