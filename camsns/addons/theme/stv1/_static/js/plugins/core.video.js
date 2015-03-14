/**
 * 插入视频
 */
core.video = {
		//给工厂调用的接口
		_init:function(attrs){
			if(attrs.length == 4){
				core.video.init(attrs[1],attrs[2],attrs[3]);
			}else if(attrs.length == 3){
				core.video.init(attrs[1],attrs[2]);
			}else if(attrs.length == 2){
				core.video.init(attrs[1]);
			}else{
				return false;
			}
		},
		init:function(videoObj,textarea,postfeed){
			this.videoObj = videoObj;
			this.postfeed = postfeed;
			this.textarea = textarea;
			core.video.createDiv();
		},
		createDiv:function(){
			if($('#videos').length>0){
				return false;
			}
			var html = '<div class="talkPop alL" id="videos" event-node="videos" style="*padding-top:20px;">'
				 + '<div class="wrap-layer">'
				 + '<div class="arrow arrow-t">'
				 + '</div>'
				 + '<div class="talkPop_box">'
				 + '<div class="close hd"><a onclick=" $(\'#videos\').remove()" class="ico-close" href="javascript:void(0)" title="'+L('PUBLIC_CLOSE')+'"></a><span>请输入<a href="http://video.sina.com.cn" target="_blank">新浪播客</a>、<a href="http://www.youku.com" target="_blank">优酷网</a>、<a href="http://www.tudou.com" target="_blank">土豆网</a>、<a href="http://www.ku6.com" target="_blank">酷6网</a>、<a href="http://v.blog.sohu.com" target="_blank">搜狐</a>等播放页的链接</span></div>'
				 + '<div class="video-box" id="video_content"><input type="text" style="width: 320px;" id="videourl" class="s-txt left"/><input type="button" onclick="core.video.video_add();" value="添加" class="btn-green-big"/></div></div></div></div>';
			
			//$(this.parentDiv).append(html);
			$('body').append(html);
			
			var pos = $(this.videoObj).offset();
			
			$('#videos').css({top:(pos.top+5)+"px",left:(pos.left-5)+"px","z-index":1001});
			
			$('body').bind('click',function(event){
				var obj = "undefined" != typeof(event.srcElement) ? event.srcElement : event.target;
				if($(obj).hasClass('face')){
					return false;
				}
				if($(obj).parents("div[event-node='videos']").get(0) == undefined){
					$('#videos').remove();
				}
			});
			
			
		},
		video_add:function(){
			var url = $('#videourl').val();
			
			var _this = this;
			$.post(U('widget/Video/paramUrl'),{url:url},function(res){
				eval("var data="+res);
				if(data.boolen==1 && data.title==1){
					$('#postvideourl').val(url);
					_this.textarea.inputToEnd( data.data+' ' );
					$('#videos').remove();
					var args = $(_this.postfeed).attr('event-args');
					var setargs = args.replace('type=post','type=postvideo');
					M.setArgs(_this.postfeed,setargs);
				}else{
					ui.error(data.message);
				}
			});
			
			if("undefined" != typeof(core.weibo)){
				core.weibo.checkNums(this.textarea.get(0));				
			}
		    return false;
		}		
};