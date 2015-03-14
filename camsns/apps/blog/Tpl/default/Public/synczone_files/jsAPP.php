function hideInfo(){
	$("#psuccess").fadeOut("slow");
	$("#pfailure").fadeOut("slow");
}

function filteruserapp(ele, input, input2, input3){
	$.post("http://planbus.com/filter.php?",
		{ action: "filteruserapp", id: input, uid: input2, status: input3 },
		function(data){
			if(data == "success"){
				var a = '<a href="javascript:;" onclick="filteruserapp(this,' + input + ', ' + input2 + ', 1);" class="action filter_action"><span style="color:#666666;">取消过滤</span></a>';
				$("#title-"+input).fadeTo("slow", 0.8);
				$("#title-"+input).css("border-left","4px solid #E37200");
				$("#filter-"+input).html(a);
			}else if(data == "successundo"){
				var a = '<a href="javascript:;" onclick="filteruserapp(this,' + input + ', ' + input2 + ', 0);" class="action filter_action">过滤</a>';
				$("#title-"+input).fadeTo("slow", 1.0);
				$("#title-"+input).css("border-left","0px");
				$("#filter-"+input).html(a);
			}
		}
	);
}

function filteruser(ele, input, input2){
	$.post("http://planbus.com/filter.php?",
		{ action: "filteruser", uid: input, status: input2 },
		function(data){
			if(data == "success"){
				var a = '<a href="javascript:;" onclick="filteruser(this,' + input + ', 1);" class="action filter_action"><span style="color:#666666;">取消过滤</span></a>';
				$("#title-"+input).fadeTo("slow", 0.8);
				$("#title-"+input).css("border-left","4px solid #E37200");
				$("#filter-action-"+input).html(a);
			}else if(data == "successundo"){
				var a = '<a href="javascript:;" onclick="filteruser(this,' + input + ', 0);" class="action filter_action">过滤</a>';
				$("#title-"+input).fadeTo("slow", 1.0);
				$("#title-"+input).css("border-left","0px");
				$("#filter-action-"+input).html(a);
			}
		}
	);
}

function starPulseItem(id){
	$("#star-"+id).html("<span><span>&nbsp;</span><img src=http://planbus.com/images/loads.gif></img></span>");
	$.post("http://planbus.com/star.php?",
		{ action: "dostar", id: id },
		function(data){
			if(data == "successstarred"){
				$("#star-"+id).addClass("star_active");
				$("a > span > img").fadeOut("slow");
			}else if(data == "successunstarred"){
				$("#star-"+id).removeClass("star_active");
				$("a > span > img").fadeOut("slow");
			}
		}
	);
}

function hidePulseItem(id){
	$.post("http://planbus.com/filter.php?",
		{ action: "hideEvent", id: id },
		function(data){
			if(data == "success"){
				$.facebox.close();
				$("#div-"+id).fadeOut("slow");
			}else if(data == "fail"){
				alert("failed");
			}
		}
	);
}

function _playerAdd(anchor) {
    var url = anchor.href;
    var code = '<object type="application/x-shockwave-flash" data="http://planbus.com/includes/player/musicplayer_f6.swf?song_url=' + url +'&amp;b_bgcolor=ffffff&amp;b_fgcolor=000000&amp;b_colors=0000ff,0000ff,ff0000,ff0000&buttons=http://planbus.com/includes/player/load.swf,http://planbus.com/includes/player/play.swf,http://planbus.com/includes/player/stop.swf,http://planbus.com/includes/player/error.swf" width="14" height="14">';
    var code = code + '<param name="movie" value="http://planbus.com/includes/player/musicplayer.swf?song_url=' + url +'&amp;b_bgcolor=ffffff&amp;b_fgcolor=000000&amp;b_colors=0000ff,0000ff,ff0000,ff0000&amp;buttons=http://planbus.com/includes/player/load.swf,http://planbus.com/includes/player/play.swf,http://planbus.com/includes/player/stop.swf,http://planbus.com/includes/player/error.swf" />';
    var code = code + '</object>';
    anchor.parentNode.innerHTML = code +' '+ anchor.parentNode.innerHTML;
}

String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
};

var deleted = false;
function deleteItem(ele, input){
    var confirmDelete = "<span>确定删除？ <a href=\"#\" onclick=\"deleteConfirmed(this, " + input + ", \'\'); return false;\">是</a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\">否</a></span>";
    ele.style.display = 'none';
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}

function deletePulse(ele, input){
    var confirmDelete = "<span>标记已阅？ <a href=\"#\" onclick=\"deletePulseConfirmed(this, " + input + ", \'\'); return false;\">是</a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\">否</a></span>";
    ele.style.display = 'none';
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}

function deletePulseConfirmed(ele, input, response) {
    if (deleted == false) {
        deleted = ele.parentNode.parentNode.parentNode;
    }
    var post = deleted;
    post.className = 'xfolkentry deleted';
    if (response != '') {
        post.style.display = 'none';
        deleted = false;
    } else {
        loadXMLDoc('http://planbus.com/ajaxDelete.php?bid=' + input);
    }
}

function deletePulseEvent(ele, input){
    var confirmDelete = "<span>确定删除这条内容？ <a href=\"#\" onclick=\"deletePulseEventConfirmed(this, " + input + ", \'\'); return false;\">是</a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\">否</a></span>";
    ele.style.display = 'none';
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}

function deletePulseEventConfirmed(ele, input, response) {
	$.post("http://planbus.com/star.php?",
		{ action: "deletePulseEvent", pid: input, status: "do" },
		function(data){
			if(data == "success"){
				$("#div-"+input).fadeOut("slow");
				$("#psuccess").css("display","block").html(" 这条消息已经删除，将不会再被显示给任何人。<a href=\"#\" onclick=\"deletePulseEventConfirmedUndo(this, " + input + ", \'\'); return false;\">恢复？</a> | <a href=\"#\" onclick=\"hideInfo();\">关闭！</a> ");
			}else if(data == "fail"){
				$("#pfailure").css("display","block").html("操作失败！");
			}
		}
	);
	$.facebox.close();
}

function deletePulseEventConfirmedUndo(ele, input, response) {
	$.post("http://planbus.com/star.php?",
		{ action: "deletePulseEvent", pid: input, status: "undo" },
		function(data){
			if(data == "success"){
				$("#div-"+input).fadeIn("slow");
				$("#psuccess").css("display","none");
			}else if(data == "fail"){
				$("#pfailure").css("display","block").html("操作失败！");
			}
		}
	);
}

function deleteUnseen(ele, input){
    var confirmDelete = "<span>已阅？ <a href=\"#\" onclick=\"deleteUnseenConfirmed(this, " + input + ", \'\'); return false;\">是</a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\">否</a></span>";
    ele.style.display = 'none';
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}

function deleteBigaction(ele, input) {
    deleted = ele.parentNode;
    deleted.style.display = 'none';
    $.post("http://planbus.com/ajaxDelete.php", { actionid: input } );
}

function deleteUnseenConfirmed(ele, input, response) {
    if (deleted == false) {
        deleted = ele.parentNode.parentNode.parentNode;
    }
    var post = deleted;
    post.className = 'xfolkentry deleted';
    if (response != '') {
        post.style.display = 'none';
        deleted = false;
    } else {
        loadXMLDoc('http://planbus.com/ajaxDelete.php?unseen=' + input);
    }
}

function deleteCancelled(ele) {
    var del = previousElement(ele.parentNode);
    del.style.display = 'inline';
    ele.parentNode.parentNode.removeChild(ele.parentNode);
    return false;
}

function deleteConfirmed(ele, input, response) {
    if (deleted == false) {
        deleted = ele.parentNode.parentNode.parentNode;
    }
    var post = deleted;
    post.className = 'xfolkentry deleted';
    if (response != '') {
        post.style.display = 'none';
        deleted = false;
    } else {
        loadXMLDoc('http://planbus.com/ajaxDelete.php?action=deleteItem&id=' + input);
    }
}

function previousElement(ele) {
    ele = ele.previousSibling;
    while (ele.nodeType != 1) {
        ele = ele.previousSibling;
    }
    return ele;
}

function isAvailable(input, response){
    var usernameField = document.getElementById("username");
    var username = usernameField.value;
    username = username.toLowerCase();
    username = username.trim();
    var availability = document.getElementById("availability");
    if (username != '') {
        usernameField.style.backgroundImage = 'url(http://planbus.com/loading.gif)';
        if (response != '') {
            usernameField.style.backgroundImage = 'none';
            if (response == 'true') {
                availability.className = 'available';
                availability.innerHTML = '<img src="http://planbus.com/images/check_icon.gif" /> <span style="color:#74B75C;">可以使用</span>';
            } else {
                availability.className = 'not-available';
                availability.innerHTML = '<img src="http://planbus.com/images/icon_error.gif" /> <span style="color:#E6584D;">已被占用</span>';
            }
        } else {
            loadXMLDoc('http://planbus.com/ajaxIsAvailable.php?username=' + username);
        }
    }
}

function isAvailable2(input, response){
    var urlnameField = document.getElementById("urlname");
    var urlname = urlnameField.value;
    urlname = urlname.trim();
    var availability2 = document.getElementById("availability2");
    if (urlname != '') {
        urlnameField.style.backgroundImage = 'url(http://planbus.com/loading.gif)';
        if (response != '') {
            urlnameField.style.backgroundImage = 'none';
            if (response == 'true') {
                availability2.className = 'available';
                availability2.innerHTML = '<img src="http://planbus.com/images/check_icon.gif" /> <span style="color:#74B75C;">可以使用</span>';
            } else {
                availability2.className = 'not-available';
                availability2.innerHTML = '<img src="http://planbus.com/images/icon_error.gif" /> <span style="color:#E6584D;">已被占用</span>';
            }
        } else {
            loadXMLDoc('http://planbus.com/ajaxIsAvailable.php?urlname=' + urlname);
        }
    }
}

function useAddress(ele) {
    var address = ele.value;
    if (address != '') {
        if (address.indexOf(':') < 0) {
            address = 'http:\/\/' + address;
        }
        getTitle(address, null);
        ele.value = address;
    }
}

function getTitle(input, response){
    var title = document.getElementById('titleField');
    if (title.value == '') {
        title.style.backgroundImage = 'url(http://planbus.com/loading.gif)';
        if (response != null) {
            title.style.backgroundImage = 'none';
            title.value = response;
        } else if (input.indexOf('http') > -1) {
            loadXMLDoc('http://planbus.com/ajaxGetTitle.php?url=' + input);
        } else {
            return false;
        }
    }
}

var xmlhttp;
function loadXMLDoc(url) {
    // Native
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = processStateChange;
        xmlhttp.open("GET", url, true);
        xmlhttp.send(null);
    // ActiveX
    } else if (window.ActiveXObject) {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        if (xmlhttp) {
            xmlhttp.onreadystatechange = processStateChange;
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
    }
}

function processStateChange() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        response = xmlhttp.responseXML.documentElement;
        method = response.getElementsByTagName('method')[0].firstChild.data;
        result = response.getElementsByTagName('result')[0].firstChild.data;
        eval(method + '(\'\', result)');
    }
}

function playerLoad() {
    var anchors = document.getElementsByTagName('a');
    var anchors_length = anchors.length;
    for (var i = 0; i < anchors_length; i++) {
        if (anchors[i].className == 'taggedlink' && anchors[i].href.match(/\.mp3$/i)) {
            _playerAdd(anchors[i]);
        }
    }
}

function deleteFriend(ele, input){
    var confirmDelete = "<span>确定？ <a href=\"" + input + "\">是</a> - <a href=\"#\" onclick=\"deleteCancelled(this); return false;\">否</a></span>";
    ele.style.display = 'none';
    ele.parentNode.innerHTML = ele.parentNode.innerHTML + confirmDelete;
}
