/**
 * 表情选择
 */
core.face = {
		//给工厂调用的接口
		_init:function(attrs){
			if(attrs.length == 5) {
				core.face.init(attrs[1],attrs[2],attrs[3],attrs[4]);
			}else if(attrs.length == 4){
				core.face.init(attrs[1],attrs[2],attrs[3]);
			}else if(attrs.length == 3){
				core.face.init(attrs[1],attrs[2]);
			}else if(attrs.length == 2){
				core.face.init(attrs[1]);
			}else{
				return false;
			}
		},
		init:function(faceObj,textarea,parentDiv,callback){
			this.faceObj = faceObj,
			this.textarea = textarea;
			this.callback = callback;
			if("undefined" == typeof(parentDiv)){
				this.parentDiv = $(faceObj).parent();
			}else{
				this.parentDiv = parentDiv;
			}
			core.face.createDiv();
		},
		createDiv:function(){
			if($('#emotions').length>0){
				return false;
			}
			var html = '<div class="talkPop alL" id="emotions" style="*padding-top:20px;">'
				 + '<div class="wrap-layer">'
				 + '<div class="arrow arrow-t">'
				 + '</div>'
				 + '<div class="talkPop_box">'
				 + '<div class="close hd"><a onclick=" $(\'#emotions\').remove()" class="ico-close" href="javascript:void(0)" title="'+L('PUBLIC_CLOSE')+'"> </a><span>'+L('PUBLIC_FACEING')+'</span></div>'
				 + '<div class="faces_box" id="emot_content"><img src="'+ THEME_URL+'/image/load.gif" class="alM"></div></div></div></div>';
			
			//$(this.parentDiv).append(html);
			$('body').append(html);
			
			var pos = $(this.faceObj).offset();
			
			$('#emotions').css({top:(pos.top+5)+"px",left:(pos.left-5)+"px","z-index":1001});
			

			core.createImageHtml();
			
			$('body').bind('click',function(event){
				var obj = "undefined" != typeof(event.srcElement) ? event.srcElement : event.target;
				if($(obj).hasClass('face')){
					return false;
				}
				if($(obj).attr('event-node') !='insert_face' && $(obj).attr('event-node') !='share_insert_face' &&  $(obj).attr('event-node') != 'comment_insert_face'){
					$('#emotions').remove();
				}
			});
			
			
		},
		face_chose:function(obj){
			var imgtitle = $(obj).attr('title');
			//var args =  M.getModelArgs(this.textarea);
			this.textarea.inputToEnd( '['+imgtitle+']' );
			
//			if("undefined" == typeof(args.t) || args.t == 'feed'){
//				this.textarea.focus();
//				document.execCommand("insertImage",false,imgsrc);
//				this.textarea.focus();
//			}else{
//				var emotion = '<img src="'+imgsrc+'"  data="'+imgsrc+'" />';
//				var html = this.textarea.innerHTML + emotion;
//				this.textarea.inputHTML = html; 
//				this.textarea.innerHTML = html;
//				this.textarea.focus();
//			}

			if("undefined" != typeof(core.weibo)){
				if (typeof this.callback !== 'undefined') {
					this.callback();
				} else {
					core.weibo.checkNums(this.textarea.get(0));				
				}
			}
		    return false;
		}		
};