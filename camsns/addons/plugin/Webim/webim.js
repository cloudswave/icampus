//custom
(function(webim) {
	var path = _IMC.path;
	webim.extend(webim.setting.defaults.data, _IMC.setting );
	var webim = window.webim;
	webim.route( {
		online: path + "/index.php?action=online",
		offline: path + "/index.php?action=offline",
		deactivate: path + "/index.php?action=refresh",
		message: path + "/index.php?action=message",
		presence: path + "/index.php?action=presence",
		status: path + "/index.php?action=status",
		setting: path + "/index.php?action=setting",
		history: path + "/index.php?action=history",
		clear: path + "/index.php?action=clear_history",
		download: path + "/index.php?action=download_history",
		members: path + "/index.php?action=members",
		join: path + "/index.php?action=join",
		leave: path + "/index.php?action=leave",
		buddies: path + "/index.php?action=buddies",
		notifications: path + "/index.php?action=notifications"
	} );

	webim.ui.emot.init({"dir": path + "/static/images/emot/default"});
	var soundUrls = {
		lib: path + "/static/assets/sound.swf",
		msg: path + "/static/assets/sound/msg.mp3"
	};
	var ui = new webim.ui(document.body, {
		imOptions: {
			jsonp: _IMC.jsonp
		},
		soundUrls: soundUrls
	}), im = ui.im;

	if( _IMC.user ) im.setUser( _IMC.user );
	if( _IMC.menu ) ui.addApp("menu", { "data": _IMC.menu } );
	if( _IMC.enable_shortcut ) ui.layout.addShortcut( _IMC.menu );

	ui.addApp("buddy", {
		showUnavailable: _IMC.show_unavailable,
		is_login: _IMC['is_login'],
		disable_login: true,
		loginOptions: _IMC['login_options']
	} );
	if( _IMC.enable_room )ui.addApp("room", { discussion: false});
	if( _IMC.enable_noti )ui.addApp("notification");
	ui.addApp("setting", {"data": webim.setting.defaults.data});
	//if( _IMC.enable_chatlink )ui.addApp("chatlink", { off_link_class: /r_option|spacelink/i });
	ui.render();
	_IMC['is_login'] && im.autoOnline() && im.online();
})(webim);
