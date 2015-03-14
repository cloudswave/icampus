// 加入群组
function joingroup(gid) {
	// 未登录则弹出登录框
	if ($CONFIG['mid'] <= 0) {
		ui.quicklogin();
		return ;
	}
    ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,'加入群组');
}
// 删除群组
function delgroup(gid) {
    ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,'解散群组');
}
// 退出群组
function quitgroup(gid) {
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,'脱离群组');
}
// 过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}