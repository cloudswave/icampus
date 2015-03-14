function callback(fun,argum){
	fun(argum);
}

// 删除类型框
function delTypeBox(){
	$('input[name="publish_type"]').val( 0 );
	$('.talkPop').remove();
}

$(document).ready(function(){
	  // 评论切换
	  $("a[rel='comment']").live('click',function(){
	      var id = $(this).attr('minid');
	      var gid = $(this).attr('groupid');
	      var $comment_list = $("#comment_list_"+id);
		  if( $comment_list.html()=='' ){
			  $comment_list.html('<div class="feed_quote feed_wb" style="text-align:center"><img src="'+ _THEME_+'/images/icon_waiting.gif" width="15"></div>');
			$.post( U("group/WeiboIndex/loadcomment"),{gid:gid,id:id},function(txt){
				$comment_list.html( txt ) ;
			});
		  }else{
			  $comment_list.html('');
		  }
	  });


	// 发布评论
	$("form[rel='miniblog_comment']").live("submit", function(){
		var _this = $(this);
		var callbackfun = _this.attr('callback');
		var _comment_content = _this.find("textarea[name='comment_content']");
		var _comment_content_new =  _comment_content.val().replace(/(^\s*)|(\s*$)/g, "");
        if(_comment_content_new =='' ){
            ui.error('内容不能为空');
            return false;
        }
		_this.find("input[type='submit']").val( '发送中...').attr('disabled','true') ;
		var options = {
		    success: function(txt) {
				if(txt=='submitlocked'){
					_this.find("input[type='submit']").val( '确定');
					_this.find("input[type='submit']").removeAttr('disabled') ;
					ui.error('您的操作太频繁了，请稍后再发布！');
                    return false;
				}else if(txt=='duplicatecontent'){
                    _this.find("input[type='submit']").val( '确定');
                    _this.find("input[type='submit']").removeAttr('disabled') ;
                    ui.error('亲，请不要连续发布相同的内容哦！');
                    return false;
                }else if(txt=='emptycontent'){
                    _this.find("input[type='submit']").val( '确定');
                    _this.find("input[type='submit']").removeAttr('disabled') ;
                    ui.error('评论内容不能为空！');
                    return false;
                }
				txt = eval('('+txt+')');
				_this.find("input[type='submit']").val( '确定');
			       _this.find("input[type='submit']").removeAttr('disabled') ;
				   _comment_content.val('');
				if(callbackfun){
					callback(eval(callbackfun),txt);
				}else{
					_comment_content.css('height','');
			       $("#comment_list_before_"+txt.data['weibo_id']).after( txt.html );
			       
				   $("#replyid_" + txt.data['weibo_id'] ).val('');
				   //更新评论数
				   $("a[rel='comment'][minid='"+txt.data['weibo_id']+"']").html("评论("+txt.data['comment']+")");
				 //  _this.find("textarea[name='comment_content']").focus();
				   
				}
		    }
		};
		_this.ajaxSubmit( options );
	    return false;
	});
});

weibo = function(){
	
};

weibo.prototype = {
	//初始化微博发布
	init:function(){
		var __THEME__ = "<?php echo __THEME__;?>";
		var Interval;
		$("#publish_type_content_before").html("<a href=\"javascript:void(0)\" target_set=\"content_publish\" onclick=\"ui.emotions(this)\" class=\"a52\"><img class=\"icon_add_face_d\" src=\""+__THEME__+"/images/zw_img.gif\" />表情</a> <a href=\"javascript:void(0)\" onclick=\"addtheme()\" class=\"a52\"><img class=\"icon_add_topic_d\" src=\""+__THEME__+"/images/zw_img.gif\" />话题</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.image.click(169)\" class=\"a52\"><img class=\"icon_add_img_d\" src=\""+__THEME__+"/images/zw_img.gif\" />图片</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.video.click(221)\" class=\"a52\"><img class=\"icon_add_video_d\" src=\""+__THEME__+"/images/zw_img.gif\" />视频</a> <a href=\"javascript:void(0)\" onclick=\"weibo.plugin.music.click(271)\" class=\"a52\"><img class=\"icon_add_music_d\" src=\""+__THEME__+"/images/zw_img.gif\" />音乐</a>");

		$("#content_publish").keypress(function(event){
			var key = event.keyCode?event.keyCode:event.which?event.which:event.charCode;
	        if (key == 27) {
	        	clearInterval(Interval);
	        }
			weibo.checkInputLength(this,_LENGTH_);
		}).blur(function(){
			clearInterval(Interval);
			weibo.checkInputLength(this,_LENGTH_);
		}).focus(function(){
			//微博字数监控
			clearInterval(Interval);
		    Interval = setInterval(function(){
		    	weibo.checkInputLength('#content_publish',_LENGTH_);
			},300);
		});
		weibo.checkInputLength('#content_publish',_LENGTH_);
		shortcut('ctrl+return',	function(){weibo.do_publish();clearInterval(Interval);},{'target':'miniblog_publish'});
	},
	//发布前的检测
	before_publish:function(){
		if( $.trim( $('#content_publish').val() ) == '' ){
            alert('内容不能为空');		
			return false;
		}
		return true;
	},
	//发布操作
	do_publish:function(){
		if( weibo.before_publish() ){
			weibo.textareaStatus('sending');
			var options = {
			    success: function(txt) {
			      if(txt){
			    	   weibo.after_publish(txt);
			      }else{
	                  alert( '发布失败' );
			      }
				}
			};		
			$('#miniblog_publish').ajaxSubmit( options );
		    return false;
		}
	},
	//发布后的处理
	after_publish:function(txt){
		if(txt==0) {
			ui.success('您发布的微博含有敏感词，请等待审核！');
		}else {
			delTypeBox();
		    $("#feed_list").prepend( txt ).slideDown('slow');
		    var sina_sync = $('#sina_sync').attr('checked');
		    $('#miniblog_publish').clearForm();
		    if (sina_sync) {
		    	$('#sina_sync').attr('checked', true);
		    }
		    weibo.upCount('weibo');
		    ui.success('微博发布成功');
		    weibo.checkInputLength('#content_publish',_LENGTH_);
		}
	},
	//发布按钮状态
	textareaStatus:function(type){
		var obj = $('#publish_handle');
		if(type=='on'){
			obj.removeAttr('disabled').attr('class','btn_big_disable hand');
		//}else if( type=='sending'){
		//	obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}else{
			obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}
	},
	//删除一条微博
	deleted:function(weibo_id, gid){
		$.post(U("group/WeiboOperate/delete"),{gid:gid,id:weibo_id},function(txt){
			if( txt ){
				$("#list_li_"+weibo_id).slideUp('fast');
				weibo.downCount('weibo');
			}else{
				alert('删除失败');
			}
		});
	},
	// 转发
	transpond:function(id, gid, upcontent){
		upcontent = ( upcontent== undefined ) ? 1 : 0;
		ui.box.load( U("group/WeiboOperate/transpond",["id="+id,"gid="+gid,"upcontent="+upcontent] ),{title:'转发到群聊',closeable:true});
	},
	// 分享微博
	share:function(id, gid, upcontent){
		upcontent = ( upcontent== undefined ) ? 1 : 0;
		ui.box.load( U("group/WeiboOperate/shareWeibo",["id="+id,"gid="+gid,"upcontent="+upcontent] ),{title:'分享到我的微博',closeable:true});
	},
	// 关注话题
	followTopic:function(name){
		$.post(U('group/WeiboOperate/followtopic'),{name:name},function(txt){
			txt = eval( '(' + txt + ')' );
			if(txt.code==12){
				$('#followTopic').html('<span class="ico_follow"></span>已关注该话题 <a href="javascript:void(0)" onclick="weibo.unfollowTopic(\''+txt.topicId+'\',\''+name+'\')">取消</a>');
			}
		});
	},
	unfollowTopic:function(id,name){
		$.post(U('group/WeiboOperate/unfollowtopic'),{topicId:id},function(txt){
			if(txt=='01'){
				$('#followTopic').html('<span class="ico_follow"></span><a href="javascript:void(0)" onclick="weibo.followTopic(\''+name+'\')">关注该话题</a>');
			}
		});	
	},
	quickpublish:function(text, gid){
		$.post(U('group/Weiboperate/quickpublish'),{gid:gid,text:text},function(txt){
			ui.box.show(txt,{title:'说几句',closeable:true});
		});
	},
	//更新计数器
	upCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())+1 );
		}
	},
	downCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())-1 );
		}
	},
	//检查字数输入
	checkInputLength:function(obj,num){
		var len = getLength($(obj).val(), true);
		var wordNumObj = $('.wordNum');
		
		if(len==0){
			wordNumObj.css('color','').html('你还可以输入<strong id="strconunt">'+ (num-len) + '</strong>字');
			weibo.textareaStatus('off');
		}else if( len > num ){
			wordNumObj.css('color','red').html('已超出<strong id="strconunt">'+ (len-num) +'</strong>字');
			weibo.textareaStatus('off');
		}else if( len <= num ){
			wordNumObj.css('color','').html('你还可以输入<strong id="strconunt">'+ (num-len) + '</strong>字');
			weibo.textareaStatus('on');
		}
	},
	publish_type_box:function(type_num,content,mg_left){
		var __THEME__ = "<?php echo __THEME__;?>";
		var html = '<div class="talkPop" ><div  style="position: relative; height: 7px; line-height: 3px;z-index:99">'
		     + '<img class="talkPop_arrow" style="margin-left:'+ mg_left +'px;position:absolute;" src="'+__THEME__+'/images/zw_img.gif" /></div>'
             + '<div class="talkPop_box">'
			 + '<div class="pop_tit close" id="weibo_close_handle"><a href="javascript:void(0)" class="del" onclick=" delTypeBox()" > </a></div>'
			 + '<div id="publish_type_content">'+content+'</div>'
			 + '</div></div>';
		$('input[name="publish_type"]').val( type_num );
		$('div .talkPop').remove();
		$("#publish_type_content_before").after( html );		
	}, 
	initForm:function(id,callback,systemType){

		  var formObj     = $('#'+id),
			  buttonObj  = formObj.find('.buttonObj'),
			  contentObj = formObj.find('.contentObj'),
			  numObj     = formObj.find('.numObj');
			userAutoTips({id:contentObj.attr('id'),url:U('home/User/searchTips')});
			buttonObj.click(function(){
				var url = formObj.attr('action');
				var gid = $('#postGid').val();
				var content = contentObj.val();
				$.post(url,{gid:gid,content:content},function(msg){
					ui.box.close();
					if(msg=='1'){
						ui.success('分享成功！');
					}else{
						ui.error('分享失败！');
					}	
				});	
			});
	}

}

/**
weibo.prototype.plugin = function(name, fn) {
    this.prototype[name] = fn;  
    return this;  
}
**/
weibo = new weibo();

weibo.plugin = {};

function addtheme(){
	var text = '#请在这里输入自定义话题#';
	var   patt   =   new   RegExp(text,"g");  
	var content_publish = $('#content_publish');
	var result;
				
	if( content_publish.val().search(patt) == '-1' ){
		content_publish.val( content_publish.val() + text);
	}
	
	var textArea = document.getElementById('content_publish');
	
	result = patt.exec( content_publish.val() );
	
	var end = patt.lastIndex-1 ;
	var start = patt.lastIndex - text.length +1;
	
	if (document.selection) { //IE
		 var rng = textArea.createTextRange();
		 rng.collapse(true);
		 rng.moveEnd("character",end)
		 rng.moveStart("character",start)
		 rng.select();
	}else if (textArea.selectionStart || (textArea.selectionStart == '0')) { // Mozilla/Netscape…
        textArea.selectionStart = start;
        textArea.selectionEnd = end;
    }
    textArea.focus();
	weibo.checkInputLength('#content_publish',_LENGTH_);
	return ;
}


