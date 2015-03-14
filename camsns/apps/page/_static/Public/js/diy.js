var frameCount = 0;
var frameArray = new Array();
var frameLayoutList;
var frameTemplate = new Array();
var frameSortAble = new Array();

$(function(){
    /**
     * 顶部排版bar的布局设定
     */
    var iebrws = document.all;
    var cssfixedsupport = !iebrws || iebrws && document.compatMode == "CSS1Compat" && window.XMLHttpRequest;
    var id = "diy_adaptable";
    $('#themeheader').css('margin-top', ($('#' + id).height() + 10) + 'px');
    
    if (cssfixedsupport) {
        $('#' + id).css('position', 'fixed');
    }
    else {
        $('#' + id).css('position', 'absolute');
        keepfixed(id);
    }
    
    $(window).bind('scroll resize', function(e){
        if (!cssfixedsupport) {
        
            if ($(window).scrollTop() !== 0) {
                $('#' + id).css('position', 'absolute');
                keepfixed(id);
            }
            else {
                //$('#' + id).removeAttr('style');
                $('#' + id).css('top', '0px');
            }
        }
    });
    $('#' + id).show();
    
    frameLayoutList = $('#diy_layout_list').clone();
    $('#diy_layout_list').remove();
    frameTemplate['placeholder'] = frameLayoutList.children('#placeholder');
    /**
     * 将页面上的布局渲染成js数组
     */
    $('.addFrame').each(function(){
        var rel = $(this).attr('rel');
        frameTemplate[rel] = frameLayoutList.children('.diy_' + rel);
        /**
         * 页面加载的时候对页面进行样式渲染
         */
        frameSortAble.push('.diy_' + rel + '_L');
        frameSortAble.push('.diy_' + rel + '_R');
        frameSortAble.push('.diy_' + rel + '_C');
		frameSortAble.push('.diy_' + rel + '_P');
        
        $('.diy_' + rel).each(function(){
            $(this).prepend('<div class="bg_diy_tit">\
			<a href="javascript:void(0)" onClick="deleteDiy(\'' + $(this).attr('id') + '\')" class="ico_diydel R mt5 mr5" title="删除" >删除</a> 布局框架\
			</div>');
        });
        $('.diy_' + rel).addClass('bg_eee');
    });
    /**
     * 将页面已有的布局组件添加进入数组
     */
    $('#tempDiyData').children().each(function(){
        var initDiyId = $(this).attr('id');
        var needId = initDiyId.split('F');
        frameArray[initDiyId] = new Array();
        if ('undefined' !== saveLayoutData[initDiyId]) {
            for (var one in saveLayoutData[initDiyId]) {
                frameArray[initDiyId][one] = saveLayoutData[initDiyId][one];
            }
        }
        
        frameArray[initDiyId]['html'] = frameTemplate[needId[0]].clone().html();
    })
    $('#tempDiyData').remove();
    $(frameSortAble.join(',')).each(function(){
        $(this).addClass('line_E');
        $(this).children().each(function(){
            var tempId = $(this).attr('id'), widget = $(this).attr('rel');
            $(this).prepend('<div class="diy_edit"><div class="ico_edit"><a href="javascript:void(0)" onClick="updateDiyModel(\'' + tempId + '\',\'' + widget + '\')" class="ico_diyedit" title="设置">设置</a> <a href="javascript:void(0)" class="ico_diydel" onClick="deleteDiy(\'' + tempId + '\')" title="删除">删除</a></div></div>');
        });
    });
    /**
     * 顶部切换
     */
    $('#diy_nav').children().each(function(){
        $(this).click(function(){
            var rel = $(this).attr('rel');
            var _this = $(this);
            $(this).addClass('on');
            $('#' + rel).show();
            $('#diy_nav').children().each(function(){
                if ($(this).attr('rel') == rel) 
                    return;
                $('#' + $(this).attr('rel')).hide();
                $(this).removeClass('on');
            });
        });
    });
    
    
    $('.diy_content').sortable({
        connectWith: '.diy_content',
        delay: 100,
        cursor: 'move',
        handle: '.bg_diy_tit',
        placeholder: 'bg_p',
        tolerance: 'pointer',
        start: function(event, ui){
        	//alert(ui.item.css('height'));
            ui.helper.width(70);
            ui.helper.height(ui.item.height());
            //ui.placeholder.width($(this).width());
            ui.placeholder.height(ui.item.height());
        }
    });

    $(frameSortAble.join(',')).sortable({
        connectWith: frameSortAble,
        delay: 200,
        cursor: 'move',
        placeholder: 'bg_p',
        tolerance: 'pointer',
        start: function(event, ui){
            //alert(ui.item.css('height'));
            ui.helper.width(ui.item.width());
            ui.helper.height(ui.item.height());
            //ui.placeholder.width($(this).width());
            ui.placeholder.height(ui.item.height());
        }
    }).disableSelection();
    
    $(".addFrame").draggable({
        connectToSortable: '.diy_content',
        helper: 'clone',
        revert: 'invalid',
        dragItem: frameTemplate,
        start: function(event, ui){
        },
        stop: function(event, ui){
            $(frameSortAble.join(',')).sortable({
                connectWith: frameSortAble,
                delay: 200,
                cursor: 'move',
                placeholder: 'bg_p',
                tolerance: 'pointer',
                start: function(event, ui){
                    //alert(ui.item.css('height'));
                    //ui.helper.width(ui.item.width());
                    ui.helper.height(ui.item.height());
                    //ui.placeholder.width($(this).width());
                    ui.placeholder.height(ui.item.height());
                }
            }).disableSelection();
        }
    });
    
    $(".addModel").draggable({
        connectToSortable: frameSortAble.join(','),
        helper: 'clone',
        revert: 'invalid',
        dragItem: frameTemplate,
        stop: function(event, ui){
        
        }
    });
});

function saveLayout(){
	var url = '';
	if(template == "1"){
		url = SITE_URL + '/index.php?app=page&mod=Diy&act=saveTemplateLayout'
	}else{
		url = SITE_URL + '/index.php?app=page&mod=Diy&act=saveLayout';
	}
	//alert(url);

    $.post(url, {
        toChange: 'diy_content',
        page: targetPage,
        layout: jsonEncode(getLayout())
    }, function(result){
        //alert(result)
		location.href = result;
    })
}

function previewLayout(){
	var url = '';
	if(template == "1"){
		url = SITE_URL + '/index.php?app=page&mod=Diy&act=previewTemplate&page=' + targetPage;
	}else{
		url = SITE_URL + '/index.php?app=page&mod=Diy&act=preview&page=' + targetPage + '&toChange=diy_content';
	}

    var layout = jsonEncode(getLayout());
    $.post(SITE_URL + '/index.php?app=page&mod=Diy&act=setSession', {
        name: 'layout_' + targetPage,
        layout: layout
    }, function(result){
        window.open(url);
    });
}

function getLayout(){
    var sorted = $('.diy_content').sortable('toArray');
    
    var postLayoutArray = new Array();
    for (var one in sorted) {
        if (sorted[one] == '') 
            continue;
        if ('undefined' == typeof(frameArray[sorted[one]])) 
            continue;
        postLayoutArray[sorted[one]] = new Array();
        postLayoutArray[sorted[one]]['html'] = frameArray[sorted[one]]['html'];
    }
    $('.addFrame').each(function(){
        var rel = $(this).attr('rel'), classes = '.diy_' + rel, inst = $(classes);
        
        if (inst.length != 0) {
            inst.each(function(){
                var self = $(this), sortL = new Array(), sortR = new Array(), sortC = new Array(),sortP = new Array(), parentId = self.attr('id');
                self.children().each(function(){
                    if ($(this).is(classes + '_L') && $.trim($(this).html()) != "") {
                        $(this).children().each(function(){
                            sortL.push($(this).attr('id'));
                        });
                    }
                    if ($(this).is(classes + '_R') && $.trim($(this).html()) != "") {
                        $(this).children().each(function(){
                            sortR.push($(this).attr('id'));
                        });
                    }
                    if ($(this).is(classes + '_C') && $.trim($(this).html()) != "") {
                        $(this).children().each(function(){
                            sortC.push($(this).attr('id'));
                        });
                    }
					if ($(this).is(classes + '_P') && $.trim($(this).html()) != "") {
                        $(this).children().each(function(){
                            sortP.push($(this).attr('id'));
                        });
                    }
                });
                postLayoutArray = appendSortLayout(sortL, postLayoutArray, 'diy_' + rel + '_L', parentId);
                postLayoutArray = appendSortLayout(sortR, postLayoutArray, 'diy_' + rel + '_R', parentId);
                postLayoutArray = appendSortLayout(sortC, postLayoutArray, 'diy_' + rel + '_C', parentId);
				postLayoutArray = appendSortLayout(sortP, postLayoutArray, 'diy_' + rel + '_P', parentId);
            })
            
        }
    });
    return postLayoutArray;
}

function appendSortLayout(sortL, postLayoutArray, nowLayout, nowParentId){
    if (sortL.length == 0) 
        return postLayoutArray;
    for (var one in sortL) {
        if (sortL[one] == '') 
            continue;
        var needId = sortL[one];
        needId = needId.split('-');
        var parentId = needId[0], layout = needId[1], index = parseInt(needId[2]) - 1;
        
        if ('undefined' == typeof(frameArray[parentId][layout])) 
            continue;
        if (frameArray[parentId][layout][index] == "" || frameArray[parentId][layout][index] == "0") 
            continue;
        if ('object' != typeof(postLayoutArray[nowParentId][nowLayout])) {
            postLayoutArray[nowParentId][nowLayout] = new Array();
        }
        
        var temp = frameArray[parentId][layout];
        postLayoutArray[nowParentId][nowLayout].push(temp[index]);
        //alert(postLayoutArray[parentId][layout]);
    }
    return postLayoutArray;
}

function jsonEncode(aaa){
    function je(str){
        var a = [], i = 0;
        var pcs = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for (; i < str.length; i++) {
            if (pcs.indexOf(str[i]) == -1) 
                a[i] = "\\u" + ("0000" + str.charCodeAt(i).toString(16)).slice(-4);
            else 
                a[i] = str[i];
        }
        return a.join("");
    }
    var i, s, a, aa = [];
    if (typeof(aaa) != "object") {
        alert("ERROR json");
        return;
    }
    for (i in aaa) {
        s = aaa[i];
        a = '"' + je(i) + '":';
        if (typeof(s) == 'object') {
            a += jsonEncode(s);
        }
        else {
            if (typeof(s) == 'string') 
                a += '"' + je(s) + '"';
            else 
                if (typeof(s) == 'number') 
                    a += s;
        }
        aa[aa.length] = a;
    }
    return "{" + aa.join(",") + "}";
}

function keepfixed(){
    //获得浏览器的窗口对象
    var $window = jQuery(window);
    //获得#topcontrol这个div的x轴坐标
    var controlx = $window.scrollLeft();
    //获得#topcontrol这个div的y轴坐标
    var controly = $window.scrollTop();
    //随着滑动块的滑动#topcontrol这个div跟随着滑动
    $('#diy_adaptable').css({
        left: controlx + 'px',
        top: controly + 'px'
    });
}

/**
 * 删除布局以及模块
 */
function deleteDiy(id){
    var tempId = id.split('-');
    if (tempId.length == 1) {
        if (window.confirm('是否删除布局？布局内模块将同时删除') == true) {
			 var result = removeArray(tempId[0], frameArray);
            if (result) {
                frameArray = result;
                $('#' + id).remove();
            }
        }
        
    }
    else {
		if (window.confirm('是否删除模块') == true) {
			var result;
			if (frameArray[tempId[0]][tempId[1]].length == 1) {
				result = removeArray(tempId[1], frameArray[tempId[0]]);
				if (result) {
					frameArray[tempId[0]] = result;
					$('#' + id).remove();
				}
			}
			else {
				result = removeArray(parseInt(tempId[2]) - 1, frameArray[tempId[0]][tempId[1]]);
				if (result) {
					frameArray[tempId[0]][tempId[1]] = result;
					$('#' + id).remove();
				}
			}
		}
    }
    
    return;
}

/**
 * 修改模块
 */
function updateDiyModel(id, namespace){
    var tempId = id.split('-'), index = tempId[2], parentId = tempId[0], needClass = tempId[1],sign = $('#'+id).attr('sign');
	$.tbox.popup(SITE_URL + "/index.php?app=page&mod=Diy&sign="+sign+"&act=getPopUp&gid=false&tagName=" + namespace + "&index=" + (index - 1) + "&parentId=" + parentId + "&needClass=" + needClass + "&id=" + parentId + "-" + needClass + "-" + index, "添加自定义模块");
    var button = '<p><input class="btn_sea mr10" id="savemodel"  name="" type="button" value="确定" /><input class="btn_sea mr10" name="" id="preview_button" type="button" value="预览"/></p>';
    $('#tbox .tb_button_list').show().html(button);
    $('#preview_button').click(function(){
        preview();
        
    });
    $('#savemodel').click(function(){
        savemodel();
        $.tbox.close();
        
    });
}

/*
 *  方法:Array.remove(dx) 通过遍历,重构数组
 *  功能:删除数组元素.
 *  参数:dx删除元素的下标.
 */
function removeArray(dx, data){
    var array = data;
    var count = 0;
    var result = new Array();
    for (var one in array) {
        count++;
    }
    if (dx > count) {
        return false;
    }
    for (var one in array) {
        if (one != dx) {
            result[one] = array[one];
        }
    }
    return result;
    //array.length -= 1
}

function setCookie(name, value, exp){
    var LargeExpDate = new Date();
    expires = exp ? exp : (3600 * 1000 * 24 * 30);
    LargeExpDate.setTime(LargeExpDate.getTime() + (3600 * 1000 * 24 * 30));
    path = ';/';
    document.cookie = name + "=" + escape(value) + ";expires=" + LargeExpDate.toGMTString() + path;
    alert(name + "=" + escape(value) + ";expires=" + LargeExpDate.toGMTString() + path);
    return document.cookie;
}

function getCookie(name){
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) 
        return unescape(arr[2]);
    return null;
}

function innerfunction(){
	E = KISSY.Editor("content");
}