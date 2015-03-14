var gSetColorType = ""; 
var gIsIE = document.all; 
var gIEVer = fGetIEVer();
var gLoaded = false;
var ev = null;

function fGetEv(e){
	ev = e;
}
function fGetIEVer(){
	var iVerNo = 0;
	var sVer = navigator.userAgent;
	if(sVer.indexOf("MSIE")>-1){
		var sVerNo = sVer.split(";")[1];
		sVerNo = sVerNo.replace("MSIE","");
		iVerNo = parseFloat(sVerNo);
	}
	return iVerNo;
}
function fSetEditable(){
	var f = window.frames["HtmlEditor"];
	f.document.designMode="on";
	if(!gIsIE)
		f.document.execCommand("useCSS",false, true);
}
function fSetFrmClick(){
	var f = window.frames["HtmlEditor"];
	f.document.onmousemove = function(){
		window.onblur();
	}
	f.document.onkeydown = function(){
		if(f.event.ctrlKey && f.event.keyCode == 13 && oForm) {
			SaveContent();
			if (oForm.onsubmit) {
				if(oForm.onsubmit()) oForm.submit();
			} else {
				 oForm.submit();
			}
			return false;
		}
	}
	f.document.onclick = function(){
		fHideMenu();
	}
}
function fSetHtmlContent(){
	var html = oLinkField.value;
	if (html)
	{
		var f = window.frames["HtmlEditor"];
		f.document.open();
		f.document.write(oLinkField.value);
		f.document.close();
	}
}

var oURL = location.href;
var oIdMatchResult = oURL.match(/ID=([\w]+)/i);
var oHeightMatchResult = oURL.match(/HEIGHT=([\d]+)/i);
var oAdminMatchResult = oURL.match(/OP=([\d]+)/i);
var sLinkFieldName = oHeightMatchResult ? oIdMatchResult[1] : 'content';
var sEditorHeight = oHeightMatchResult ? oHeightMatchResult[1] : 400;
var sShowHtml = oAdminMatchResult ? oAdminMatchResult[1] : 0;
var oLinkField = parent.document.getElementsByName(sLinkFieldName)[0];
var oForm = oLinkField.form ;
var sSubmitF = oForm.onsubmit;

window.onload = function(){
	try{
		gLoaded = true;
		fSetEditable();
		fSetFrmClick();
		fSetHtmlContent();
		setLinkedField();
	}catch(e){}
}
function fSetColor(){
	var dvForeColor =$("dvForeColor");
	if(dvForeColor.getElementsByTagName("TABLE").length == 1){
		dvForeColor.innerHTML = drawCube() + dvForeColor.innerHTML;
	}
}
window.onblur =function(){
	return;
	var dvForeColor =$("dvForeColor");
	dvForeColor.style.display = "none";
	if(!gIsIE || 1==1){
		fHideMenu();
	}
}
document.onmouseout =function(){
	SaveContent();
}
document.onmousemove = function(e){
	if(gIsIE) var el = event.srcElement;
	else var el = e.target;
	var tdView = $("tdView");
	var tdColorCode = $("tdColorCode");
	var dvForeColor =$("dvForeColor");
	var fontsize =$("fontsize");
	var fontface =$("fontface");
	if(el.tagName == "IMG"){
		try{
			if(fCheckIfColorBoard(el)){
				tdView.bgColor = el.parentNode.bgColor;
				tdColorCode.innerHTML = el.parentNode.bgColor;
			}
		}catch(e){}
	}else{
		return;
		dvForeColor.style.display = "none";
		if(!fCheckIfFontFace(el)) fontface.style.display = "none";
		if(!fCheckIfFontSize(el)) fontsize.style.display = "none";
	}
}
document.onclick = function(e){
	if(gIsIE) var el = event.srcElement;
	else var el = e.target;
	var dvForeColor =$("dvForeColor");

	if(el.tagName == "IMG"){
		try{
			if(fCheckIfColorBoard(el)){
				format(gSetColorType, el.parentNode.bgColor);
				dvForeColor.style.display = "none";
				return;
			}
		}catch(e){}
	}
	fHideMenu();
	switch(el.id){
		case "imgFontface":
			var fontface = $("fontface");
			if(fontface) fontface.style.display = "";
			break;
		case "imgFontsize":
			var fontsize = $("fontsize");
			if(fontsize) fontsize.style.display = "";
			break;
		case "imgFontColor":
			var dvForeColor = $("dvForeColor");
			if(dvForeColor) dvForeColor.style.display = "";
			break;
		case "imgBackColor":
			var dvForeColor = $("dvForeColor");
			if(dvForeColor) dvForeColor.style.display = "";
			break;
		case "imgAlign":
			var divAlign = $("divAlign");
			if(divAlign) divAlign.style.display = "";
			break;
		case "imgList":
			var divList = $("divList");
			if(divList) divList.style.display = "";
			break;
		case "imgMedia":
			var divMedia = $("divMedia");
			if(divMedia) divMedia.style.display = "";
			break;
	}
}
function format(type, para){
	var f = window.frames["HtmlEditor"];
	var sAlert = "";
	if(!gIsIE){
		switch(type){
			case "Cut":
				sAlert = "你的浏览器安全设置不允许编辑器自动执行剪切操作,请使用键盘快捷键(Ctrl+X)来完成";
				break;
			case "Copy":
				sAlert = "你的浏览器安全设置不允许编辑器自动执行拷贝操作,请使用键盘快捷键(Ctrl+C)来完成";
				break;
			case "Paste":
				sAlert = "你的浏览器安全设置不允许编辑器自动执行粘贴操作,请使用键盘快捷键(Ctrl+V)来完成";
				break;
		}
	}
	if(sAlert != ""){
		alert(sAlert);
		return;
	}
	f.focus();
	if(!para){
		if(gIsIE){
			f.document.execCommand(type);
		}else{
			f.document.execCommand(type,false,false);
		}
	}else{
		f.document.execCommand(type,false,para);
	}
	f.focus();
}
function setMode(bStatus){
	var sourceEditor = $("sourceEditor");
	var HtmlEditor = $("HtmlEditor");
	var divEditor = $("divEditor");
	var f = window.frames["HtmlEditor"];
	var body = f.document.getElementsByTagName("BODY")[0];
	if(bStatus){
		sourceEditor.style.display = "";
		HtmlEditor.style.height = "0px";
		divEditor.style.height = "0px";
		sourceEditor.value = body.innerHTML;
	}else{
		sourceEditor.style.display = "none";
		if(gIsIE){
			HtmlEditor.style.height = sEditorHeight - 34 +"px";
			divEditor.style.height = sEditorHeight - 34 +"px";
		}else{
			HtmlEditor.style.height = sEditorHeight - 37 +"px";
			divEditor.style.height = sEditorHeight - 37 +"px";
		}
		body.innerHTML = sourceEditor.value;
	}
}
function foreColor(e) {
	fDisplayColorBoard(e);
	gSetColorType = "foreColor";
}
function backColor(e){
	var sColor = fDisplayColorBoard(e);
	if(gIsIE){
		gSetColorType = "backcolor";
	}
	else{
		gSetColorType = "backcolor";
	}
}
function fDisplayColorBoard(e){
	if(gIsIE){
		var e = window.event;
	}
	var dvForeColor =$("dvForeColor");
	fSetColor();
	var iX = e.clientX;
	var iY = e.clientY;
	dvForeColor.style.display = "";
	dvForeColor.style.left = (iX-60) + "px";
	dvForeColor.style.top = 33 + "px";
	return true;
}
function createLink() {
	var sURL=window.prompt("请输入链接地址(例如 http://www.thinksns.com):", "http://");
	if ((sURL!=null) && (sURL!="http://")){
		format("CreateLink", sURL);
	}
}
function createImg()	{
	//alert(2);
	var sPhoto=prompt("请输入图片位置:", "http://");
	if ((sPhoto!=null) && (sPhoto!="http://")){
		format("InsertImage", sPhoto);
	}
}

function uploadImg(url){ 
	format("InsertImage", url);
}

function createAudio()	{
	var sAudio=prompt("请输入音频位置:", "http://");
	if ((sAudio!=null) && (sAudio!="http://")){
		var mp3 = '\
					<div><OBJECT id=videowindow1 height=45 width=480 classid=CLSID:6BF52A52-394A-11D3-B153-00C04F79FAA6>\
					<PARAM NAME="URL" VALUE="'+sAudio+'">\
					<PARAM NAME="playCount" VALUE="infinite">\
					<PARAM NAME="autoStart" VALUE="1">\
					<PARAM NAME="volume" VALUE="50">\
					<PARAM NAME="uiMode" VALUE="full">\
					<PARAM NAME="stretchToFit" VALUE="1">\
					</OBJECT></div>';
		var frm2= window.frames["HtmlEditor"].document;
		frm2.body.innerHTML += mp3;
		return;
	}
}
function createVedio()	{
	var sVideo=prompt("请输入视频位置(以swf结尾):", "http://");
	if ((sVideo!=null) && (sVideo!="http://")){
		//setMedia(sVideo, 1);
		var video = '\
		<center><embed height="400" width="480" src="'+sVideo+'" \
		scale="ShowAll" loop="loop" menu="menu" wmode="Window" quality="1" type="application/x-shockwave-flash">\
		</embed></center>';
		var frm2= window.frames["HtmlEditor"].document;
		frm2.body.innerHTML += video;
		return;
	}
}
function setMedia(vSrc, vType) {
	var sBody = window.frames["HtmlEditor"].document.getElementsByTagName("BODY")[0];
	var sHtml = sBody.innerHTML;
	var sRmRe = /(\.rm)|(\.rmvb)|(\.ra)|(\.ram)$/i;
	var sFlvRe = /(\.flv)$/i;
	if (sRmRe.test(vSrc)) {
		if (vType) {
			sHtml += "<object WIDTH='400' HEIGHT='300' CLASSID='CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA'>\n<param name='_ExtentX' value='9657'>\n<param name='_ExtentY' value='847'>\n<param name='AUTOSTART' value='0'>\n<param name='SHUFFLE' value='0'>\n<param name='PREFETCH' value='0'>\n<param name='NOLABELS' value='0'>\n<param name='SRC' value='" + vSrc + "'>\n<param name='CONTROLS' value='Imagewindow'>\n<param name='LOOP' value='0'>\n<param name='NUMLOOP' value='0'>\n<param name='CENTER' value='0'>\n<param name='MAINTAINASPECT' value='0'>\n<param name='BACKGROUNDCOLOR' value='#000000'>\n<param name='AllowScriptAccess' value='never'>\n</object>\n<BR />\n<object WIDTH='400' HEIGHT='50' CLASSID='CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA'>\n<param name='_ExtentX' value='9657'>\n<param name='_ExtentY' value='847'>\n<param name='AUTOSTART' value='0'>\n<param name='SHUFFLE' value='0'>\n<param name='PREFETCH' value='0'>\n<param name='NOLABELS' value='0'>\n<param name='SRC' value='" + vSrc + "'>\n<param name='CONTROLS' value='StatusBar,controlpanel'>\n<param name='LOOP' value='0'>\n<param name='NUMLOOP' value='0'>\n<param name='CENTER' value='0'>\n<param name='MAINTAINASPECT' value='0'>\n<param name='BACKGROUNDCOLOR' value='#000000'>\n<param name='AllowScriptAccess' value='never'>\n</object>\n";
		} else {
			sHtml += "<object WIDTH='300' HEIGHT='50' CLASSID='CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA'>\n<param name='_ExtentX' value='9657'>\n<param name='_ExtentY' value='847'>\n<param name='AUTOSTART' value='0'>\n<param name='SHUFFLE' value='0'>\n<param name='PREFETCH' value='0'>\n<param name='NOLABELS' value='0'>\n<param name='SRC' value='" + vSrc + "'>\n<param name='CONTROLS' value='StatusBar,controlpanel'>\n<param name='LOOP' value='0'>\n<param name='NUMLOOP' value='0'>\n<param name='CENTER' value='0'>\n<param name='MAINTAINASPECT' value='0'>\n<param name='BACKGROUNDCOLOR' value='#000000'>\n<param name='AllowScriptAccess' value='never'>\n</object>\n";
		}
	} else if (sFlvRe.test(vSrc)) {
		sHtml += "<EMBED pluginspage='http://www.macromedia.com/go/getflashplayer' src='/images/flvPlayer.swf' width='400' height='300' type='application/x-shockwave-flash' quality='high' menu='false' FlashVars='vcastr_file=" + vSrc + "' allowFullScreen='true'  allowScriptAcces='never'>\n";
	} else {
		if (vType) {
			sHtml += "<EMBED src='" + vSrc + "' HEIGHT='370' WIDTH='400' AutoStart='0' ShowStatusBar='1'  allowScriptAcces='never'></EMBED>\n";
		} else {
			sHtml += "<EMBED src='" + vSrc + "' HEIGHT='69' WIDTH='300' AutoStart='0' ShowStatusBar='1'  allowScriptAcces='never'></EMBED>\n";
		}
	}
	
	sBody.innerHTML = sHtml;
}
function fCheckIfColorBoard(obj){
	if(obj.parentNode){
		if(obj.parentNode.id == "dvForeColor") return true;
		else return fCheckIfColorBoard(obj.parentNode);
	}else{
		return false;
	}
}
function fCheckIfFontFace(obj){
	if(obj.parentNode){
		if(obj.parentNode.id == "fontface") return true;
		else return fCheckIfFontFace(obj.parentNode);
	}else{
		return false;
	}
}
function fCheckIfFontSize(obj){
	if(obj.parentNode){
		if(obj.parentNode.id == "fontsize") return true;
		else return fCheckIfFontSize(obj.parentNode);
	}else{
		return false;
	}
}
function fImgOver(el){
	if(el.tagName == "IMG"){
		el.style.borderRight="1px #cccccc solid";
		el.style.borderBottom="1px #cccccc solid";
	}
}
function fImgMoveOut(el){
	if(el.tagName == "IMG"){
		el.style.borderRight="1px #F3F8FC solid";
		el.style.borderBottom="1px #F3F8FC solid";
	}
}
String.prototype.trim = function(){
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
function fSetBorderMouseOver(obj) {
	obj.style.borderRight="1px solid #aaa";
	obj.style.borderBottom="1px solid #aaa";
	obj.style.borderTop="1px solid #fff";
	obj.style.borderLeft="1px solid #fff";
} 

function fSetBorderMouseOut(obj) {
	obj.style.border="none";
}

function fSetBorderMouseDown(obj) {
	obj.style.borderRight="1px #F3F8FC solid";
	obj.style.borderBottom="1px #F3F8FC solid";
	obj.style.borderTop="1px #cccccc solid";
	obj.style.borderLeft="1px #cccccc solid";
}

function fDisplayElement(element,displayValue) {
	fHideMenu();
	if ( typeof element == "string" )
		element = $(element);
	if (element == null) return;
	element.style.display = displayValue;
	if(gIsIE){
		var e = event;
	}else{
		var e = ev;
	}
	var iX = e.clientX;
	var iY = e.clientY;
	element.style.display = "";
	element.style.left = (iX-30) + "px";
	element.style.top = 33 + "px";
	return true;
}
function fSetModeTip(obj){
	var x = f_GetX(obj);
	var y = f_GetY(obj);
	var dvModeTip = $("dvModeTip");
	if(!dvModeTip){
		var dv = document.createElement("DIV");
		dv.style.position = "absolute";
		dv.style.top = 33 + "px";
		dv.style.left = (x-40) + "px";
		dv.style.zIndex = "999";
		dv.style.fontSize = "12px";
		dv.id = "dvModeTip";
		dv.style.padding = "2 2 0 2px";
		dv.style.border = "1px #000000 solid";
		dv.style.backgroundColor = "#FFFFCC";
		dv.style.height = "12px";
		dv.innerHTML = "编辑源码";
		document.body.appendChild(dv);
	}else{
		dvModeTip.style.display = "";
	}
}
function fHideTip(){
	$("dvModeTip").style.display = "none";
}
function f_GetX(e)
{
	var l=e.offsetLeft;
	while(e=e.offsetParent){
		l+=e.offsetLeft;
	}
	return l;
}
function f_GetY(e)
{
	var t=e.offsetTop;
	while(e=e.offsetParent){
		t+=e.offsetTop;
	}
	return t;
}
function fHideMenu(){
	var fontface = $("fontface");
	var fontsize = $("fontsize");
	var dvForeColor =$("dvForeColor");
	var divAlign =$("divAlign");
	var divList =$("divList");
	var divMedia =$("divMedia");
	if(dvForeColor) dvForeColor.style.display = "none";
	if(fontface) fontface.style.display = "none";
	if(fontsize) fontsize.style.display = "none";
	if(divAlign) divAlign.style.display = "none";
	if(divList) divList.style.display = "none";
	if(divMedia) divMedia.style.display = "none";
}
function $(id){
	return document.getElementById(id);
}
window.onerror = function(){
	return true;
}
function fGetList(){

}
function fGetAlign(){
	
}
function fHide(obj){
	obj.style.display="none";
}
function cleanWord(sHtml){
	if(sHtml.match(/class="?mso/i)) {
		//区块清理
		sHtml = sHtml.replace(/<!--([\s\S]*?)-->|<style(\s+[^>]+)?>[\s\S]*?<\/style>/ig, "");
		sHtml = sHtml.replace(/<(meta|link)(\s+[^>]+)?>/ig, "");
		sHtml = sHtml.replace(/<\??xml(:\w+)?( [^>]+)?>([\s\S]*?<\/xml>)?/ig, "");
		sHtml = sHtml.replace(/<\/?\w+:[^>]*>/ig, "");
		
		//属性清理
		sHtml = sHtml.replace(/<(\w+[^>]*?)\s+class="?mso[^ >]*([^>]*?)>/ig, "<$1$2>");//删除所有mso开头的样式
		sHtml = sHtml.replace(/<(\w+[^>]*?)\s+lang="?[^ \>]*([^>]*?)>/ig, "<$1$2>");//删除lang属性
		sHtml = sHtml.replace(/<(\w+[^>]*?)\s+align="?left"?([^>]*?)>/ig, "<$1$2>");//取消align=left
		//sHtml = sHtml.replace(/<(\w+[^>]*?) style="?[^">]+"?([^>]*?)>/ig, "<$1$2>");//删除所有style
		
		//样式清理
		sHtml = sHtml.replace(/\s*mso-[^:]+:[^;"]+;?\s*/ig,'');
		sHtml = sHtml.replace(/\s*margin: 0cm 0cm 0pt\s*;\s*/ig,'');
		sHtml = sHtml.replace(/\s*margin: 0cm 0cm 0pt\s*"/ig,'"');
		sHtml = sHtml.replace(/\s*text-align:[^;"]+;?\s*/ig,'');
		sHtml = sHtml.replace(/\s*font-variant:[^;"]+;?\s*/ig,'');
		sHtml = sHtml.replace(/<(\w+[^>]*?)\s+style="?"?(\s+|>)/ig,'<$1$2');//空style
		sHtml = sHtml.replace(/<(\w+[^>]*?)\s+style="?\s*([^">]+)\s*"?([^>]*?)>/ig, function(all,attr1,styles,attr2){return '<'+attr1+' style="'+styles.replace(/&quot;/ig,'')+'"'+attr2+'>';});//清理style里的&quot;
	}
	return sHtml;
}
function HtmlFilter(html){
	html = cleanWord(html);
	html = html.replace(/<script.*?\/script[\s]*>/ig, ' ');
	html = html.replace(/<style.*?\/style[\s]*>/ig, ' ');
	html = html.replace(/<iframe.*?\/iframe[\s]*>/ig, ' ');
	html = html.replace(/<[\/]?div.*?>/ig, ' ');
	html = html.replace(/<[\/]?form.*?>/ig, ' ');
	html = html.replace(/[\s]+on[\w]+[\s]*=[\s]*(?:\'[^\']*\'|\"[^\"]*\"|[^\s]*)/ig, ' ') ;
	html = html.replace(/[\s]+([\w]+)[\s]*=[\s]*(?:\'[\s]*javascript.*?\'|\"[\s]*javascript.*?\"|[\s]*javascript.*?[\s]*)/ig, ' $1=""');
	html = html.replace(/<a (.*?)>/ig, '<a $1 target="_blank">');
	return html;
}
function setLinkedField() {
	if (!oForm) return ;
	//oForm.attachEvent("onreset", AttachReset);
	//oForm.attachEvent("onsubmit", AttachSubmit);
}
function SaveContent() { 
	var html = window.frames["HtmlEditor"].document.getElementsByTagName("BODY")[0].innerHTML;
	if ( (html.toLowerCase() == "<p>&nbsp;</p>") || (html.toLowerCase() == "<p></p>") ){
		html = "";
	}
	oLinkField.value = HtmlFilter(html);
} 
function AttachReset() {
	window.frames["HtmlEditor"].document.getElementsByTagName("BODY")[0].innerHTML = oLinkField.value;
}
function AttachSubmit() {
	SaveContent();
	return;
	//return oForm.sSubmitF();
}

// Draw color selector
// create 6-element array
var s = "";
var hex = new Array(6);
// assign non-dithered descriptors
hex[0] = "FF";
hex[1] = "CC";
hex[2] = "99";
hex[3] = "66";
hex[4] = "33";
hex[5] = "00";
// draw a single table cell based on all descriptors
function drawCell(red, green, blue) {
	// open cell with specified hexadecimal triplet background color
	var color = '#' + red + green + blue;
	if(color == "#000066") color = "#000000";
	s += '<TD BGCOLOR="' + color + '" style="height:12px;width:12px;" >';
	// print transparent image (use any height and width)
	s += '<img src="place.gif" height=12 width=12>';
	// close table cell
	s += '</TD>';
}
// draw table row based on red and blue descriptors
function drawRow(red, blue) {
	// open table row
	s += '<TR>';

	// loop through all non-dithered color descripters as green hex
	for (var i = 0; i < 6; ++i) {
		drawCell(red, hex[i], blue);
	}
	// close current table row
	s += '</TR>';
}
// draw table for one of six color cube panels
function drawTable(blue) {
	// open table (one of six cube panels)
	s += '<TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0>';
	// loop through all non-dithered color descripters as red hex
	for (var i = 0; i < 6; ++i) {
		drawRow(hex[i], blue);
	}
	// close current table
	s += '</TABLE>';
}
// draw all cube panels inside table cells
function drawCube() {
	// open table
	s += '<TABLE CELLPADDING=0 CELLSPACING=0 style="border:1px #888888 solid"><TR>';
	// loop through all non-dithered color descripters as blue hex
	for (var i = 0; i < 2; ++i) {
		// open table cell with white background color
		s += '<TD BGCOLOR="#FFFFFF">';
		// call function to create cube panel with hex[i] blue hex
		drawTable(hex[i]);
		// close current table cell
		s += '</TD>';
	}
	s += '</TR><TR>';
	for (var i = 2; i < 4; ++i) {
		// open table cell with white background color
		s += '<TD BGCOLOR="#FFFFFF">';
		// call function to create cube panel with hex[i] blue hex
		drawTable(hex[i]);
		// close current table cell
		s += '</TD>';
	}
	// close table row and table
	s += '</TR></TABLE>';
	return s;
}