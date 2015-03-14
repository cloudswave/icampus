/**
 * 弹出创建专辑窗口
 * @param integer uid 用户UID
 * @param boolean isRefresh 是否刷新
 * @return void
 */
function create_album_tab (uid, isRefresh) {
	isRefresh = (typeof isRefresh === 'undefined') ? 0 : isRefresh;
	ui.box.load(U('photo/Manage/create_album_tab') + '&uid=' + uid + '&isRefresh=' + isRefresh, '创建' + APP_NAME);
};
/**
 * 执行创建专辑操作
 * @param boolean isRefresh 是否刷新，默认为false
 * @return void
 */
function do_create_album (isRefresh) {
	isRefresh = (typeof isRefresh === 'undefined') ? 0 : isRefresh;
	var name = $('#name').val().replace(/\s+/g,""),
		privacy = $('#privacy').val(),
		password = $('#textfield3').val();

	if (!name) { 
		ui.error('名称不能为空');
		return false;
	} else if (name.length > 12) { 
		ui.error('名称不能超过12个字');
		return false;
	}
	$.post(U('photo/Manage/do_create_album'), {name:name,privacy:privacy,privacy_data:password}, function(res) {
		if (res.status == -1) {
			ui.error('该相册名已存在');
		} else if (res.status == 1) {
			if (isRefresh) {
				location.reload();
			} else {
				parent.setAlbumOption(res.data);
			}
			ui.box.close();
			ui.success('创建成功');
		} else if (res.status == 0) {
			ui.box.close();
			ui.error('创建失败');
		}
	}, 'json');
};
/**
 * 添加专辑下拉菜单
 * @param object data 专辑名称与专辑ID
 */
function setAlbumOption (data) {
	var albumId = data.albumId,
		albumName = data.albumName;
	$('#albumlist').append('<option value="' + albumId + '" selected="selected" style="background-color:yellow">' + albumName + '</option>');
};