function render_themes(){
	var themes = window.themes || "base,black-tie,blitzer,cupertino,dark-hive,dot-luv,eggplant,excite-bike,flick,hot-sneaks,humanity,le-frog,mint-choc,overcast,pepper-grinder,redmond,smoothness,south-street,start,sunny,swanky-purse,trontastic,ui-darkness,ui-lightness,vader", theme, href;
	themes = themes.split(",");
	var content = '<ul style="width: 100%; overflow: hidden;padding:0;margin:0;">', wrapper = document.getElementById("themes"); 
	for(var i in themes){
		theme = themes[i];
		href = 'href="javascript:void(select_theme(\'' + theme + '\'));"';
		content += '<li style="float: left;list-style:none;margin: 5px;"><h4 style="margin: 0;"><a ' + href + '>' + theme + '</a>' + clippy(theme) + '</h4><p style="margin:0;"><a ' + href + '><img border="0" width="" alt="' + theme + '" title="' + theme + '" src="images/' + theme + '.png" /></a></p></li>';
	}
	content += "</ul>";
	if(wrapper)wrapper.innerHTML = content;

}
function select_theme(theme){
	var style = document.getElementById("webim-theme-style"), href = theme + "/jquery.ui.theme.css";
	if(style){
		style.setAttribute("href", href);
	}else{
		style = document.createElement("link");
		style.setAttribute("id", "webim-theme-style");
		style.setAttribute("href", href);
		style.setAttribute("media", "all");
		style.setAttribute("type", "text/css");
		style.setAttribute("rel", "stylesheet");
		document.body.appendChild(style);
	}
}

render_themes();

function clippy(text){
	return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="110" height="14" id="clippy"><param name="movie" value="clippy.swf"/><param name="allowScriptAccess" value="always" /><param name="quality" value="high" /><param name="scale" value="noscale" /><param NAME="FlashVars" value="text=' + text + '"><param name="bgcolor" value="#ffffff"><embed src="clippy.swf" width="110" height="14" name="clippy" quality="high" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" FlashVars="text=' + text + '" bgcolor="#ffffff" /> </object>'
}

webim = window.webim;
//webim.extend(webim.setting.defaults.data,{});
//webim.extend(webim.setting.defaults.data,{block_list: ["1000001"]});
_path = "../";
webim.ui.emot.init({"dir": _path + "images/emot/default"});
var ui = new webim.ui(document.body, {
	soundUrls: {
		lib: _path + "assets/sound.swf",
		msg: _path + "assets/sound/msg.mp3"
	}
}), im = ui.im;
im.buddy.bind("online", function(data){
	webim.each(data, function(n,d){ d.pic_url = _path + "test/" + d.pic_url;});
});
im.bind("go", function(data){
	data.connection.server =  _path + "im/test/" + data.connection.server;
});
//ui.addApp("menu", {"data": menu});
//ui.layout.addShortcut( menu);
ui.addApp("buddy");
ui.addApp("room");
ui.addApp("notification");
ui.addApp("setting", {"data": webim.setting.defaults.data});
ui.render();
//im.online();
