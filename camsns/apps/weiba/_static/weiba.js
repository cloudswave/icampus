/**
 * 异步提交表单
 * @param object form 表单DOM对象
 * @return void
 */
var ajaxSubmit = function(form) {
	var args = M.getModelArgs(form);
	M.getJS(THEME_URL + '/js/jquery.form.js', function() {
        var options = {
        	dataType: "json",
            success: function(txt) {
        		if(1 == txt.status) {
        			if("function" ===  typeof form.callback) {
        				form.callback(txt);
        			} else {
        				if("string" == typeof(args.callback)) {
        					eval(args.callback+'()');
        				} else {
        					ui.success(txt.info);
        				}
        			}
        		} else {
        			ui.error(txt.info);
        		}
            }
        };
        $(form).ajaxSubmit(options);
	});
};

/**
 * 处理ajax返回数据之后的刷新操作
 */
var ajaxReload = function(obj,callback){
    if("undefined" == typeof(callback)){
        callback = "location.href = location.href";
    }else{
        callback = 'eval('+callback+')';
    }
    if(obj.status == 1){
        ui.success(obj.data);
        setTimeout(callback,1500);
     }else{
        ui.error(obj.data);
    }
};

var getChecked = function() {
    var ids = new Array();
    $.each($('#list input:checked'), function(i, n){
        if($(n).val() !='0' && $(n).val()!='' ){
            ids.push( $(n).val() );    
        }
    });
    return ids;
};

var checkAll = function(o){
    if( o.checked == true ){
        $('#list input[name="checkbox"]').attr('checked','true');
    }else{
        $('#list input[name="checkbox"]').removeAttr('checked');
    }
};

M.addModelFns({
	weiba_post:{  //发布帖子
		callback:function(txt){
			ui.success('发布成功');
			setTimeout(function() {
				location.href = U('weiba/Index/postDetail')+'&post_id='+txt.data;
			}, 1500);
		}
	},
	weiba_post_edit:{  //编辑帖子
		callback:function(txt){
			ui.success('编辑成功');
			setTimeout(function() {
				location.href = U('weiba/Index/postDetail')+'&post_id='+txt.data;
			}, 1500);
		}
	},
	drop_weiba_search:{
		load:function(){
			var _this = this;
			search.init();
			$(this.childEvents['searchKey'][0]).click(function(){
				search.searchInit(this);
			});	
		}
	},
	weiba_reply_edit:{   //编辑帖子回复
		callback:function(txt){
			ui.success('编辑成功');
			setTimeout(function() {
				location.href = U('weiba/Index/postDetail')+'&post_id='+txt.data;
			}, 1500);
		}
	},
	weiba_apply:{   //申请吧主
		callback:function(txt){
			ui.success('申请成功，请等待管理员审核');
			setTimeout(function() {
				location.href = U('weiba/Index/detail')+'&weiba_id='+txt.data;
			}, 1500);
		}
	}
});

M.addEventFns({
	doFollowWeiba: {
		click: function() {		
			followWeiba.doFollow( this );
			return false;
		},
		load: function() {
			followWeiba.createBtn( this );
		}
	},
	unFollowWeiba: {
		click: function() {
			followWeiba.unFollow( this );
			return false;
		},
		load: function() {
			followWeiba.createBtn( this );
		}
	},
	submit_btn: {
		click: function(){
			var args  = M.getEventArgs(this);
			if ( args.info && ! confirm( args.info )) {
				return false;
			}
			try{
				(function( node ) {
					var parent = node.parentNode;
					// 判断node 类型，防止意外循环
					if ( "FORM" === parent.nodeName ) {
						if ( "false" === args.ajax ) {
							( ( "function" !== typeof parent.onsubmit ) || ( false !== parent.onsubmit() ) ) && parent.submit();
						} else {
							if(parent.name == 'weibaPost'){
								E.sync();
							}
							if(parent.name == 'reply_edit'){
								$('#content').val($('.s-textarea').html());
								var strlen = getLength($('#content').val())
								var leftnums = initNums - strlen;
								if(leftnums < 0){
									ui.error('不能超过'+initNums+'个字');
									return false;
								}
							}
							ajaxSubmit(parent);
						}
					} else if ( 1 === parent.nodeType ) {
						arguments.callee( parent );
					}
				})(this);
			}catch(e){
				return true;
			}
			return false;
		}
	},
	do_weiba_reply:{	//回复帖子操作
		click:function(){
			if ( this.noreply == 1 ){
				return;
			}else{
				this.noreply = 1;
			}
			var _this = this;
			setTimeout(function (){
				_this.noreply = 0;
			},5000);
			var attrs = M.getEventArgs(this);
			attrs.weiba_id = $(this).attr('weiba_id');
			attrs.post_id = $(this).attr('post_id');
			attrs.post_uid = $(this).attr('post_uid');
			attrs.to_reply_id = $(this).attr('to_reply_id');
			attrs.to_uid = $(this).attr('to_uid');
			attrs.feed_id = $(this).attr('feed_id');
			attrs.addtoend = $(this).attr('addtoend');
			var comment_list = this.parentModel.parentModel;

			var commentListObj = comment_list;
			this.comment_textarea = commentListObj.childModels['comment_textarea'][0];
		    var mini_editor = this.comment_textarea.childModels['mini_editor'][0];
			var _textarea = $(mini_editor).find('textarea').get(0);
			var content = _textarea.value;
			var strlen = core.getLength(content);
			var leftnums = initNums - strlen;
			if(leftnums < 0 || leftnums == initNums) {
				flashTextarea(_textarea);
				return false;
			}
			$(_this).html('<span>回复中...</span>');
			// if(getLength(content) == '') {
			// 	flashTextarea(_textarea);
			// 	return false;
			// }
			if("undefined" != typeof(this.addComment) && (this.addComment == true)) {
				return false;	//不要重复评论
			}
			// 如果转发到自己的微博
			var ischecked = $(this.comment_textarea).find("input[name='shareFeed']").get(0).checked;
			if(ischecked == true) {
				var ifShareFeed = 1;
			} else {
				var ifShareFeed = 0;
			}
     		$.post(U('widget/WeibaReply/addReply'),{widget_appname:'weiba',weiba_id:attrs.weiba_id,post_id:attrs.post_id,post_uid:attrs.post_uid,to_reply_id:attrs.to_reply_id,to_uid:attrs.to_uid,feed_id:attrs.feed_id,content:content,ifShareFeed:ifShareFeed},function(msg){

				if(msg.status == "0"){
					ui.error(msg.data);
				}else{
					if("undefined" != typeof(commentListObj.childModels['comment_list']) ){
						ui.success('评论成功');
						if(attrs.addtoend == 1){
							$(commentListObj).find('.comment_lists').eq(0).append(msg.data);
						}else{
							$(msg.data).insertBefore($(commentListObj.childModels['comment_list'][0]));
						}
					}else{
						ui.success('评论成功');
						$(commentListObj).find('.comment_lists').eq(0).html(msg.data);
					}
					M(commentListObj);
					//重置
					_textarea.value = '';
					this.to_reply_id = 0;
					this.to_uid = 0;
					// if("function" == typeof(afterComment)){
					// 	afterComment();
					// }
				}
				$(_this).html('<span>回复</span>');
				addComment = false;
			},'json');
		}
	},
	reply_del:{
		click:function(){
			var attrs = M.getEventArgs(this);
			$(this.parentModel).fadeOut('normal', function () {
				var $commentList = $(this).parent();
				if ($commentList.length > 0) {
					// 获取微博ID
					var wid = parseInt($commentList.attr('id').split('_')[1]);
					var $commentListVisible = $commentList.find('dl:visible');
					var len = $commentListVisible.length;
					$commentListVisible.each(function (i, n) {
						$(this).find('span.floor').html((len - i)+'楼');
					});
				}
			});
			$.post(U('widget/WeibaReply/delReply'),{widget_appname:'weiba',reply_id:attrs.reply_id},function(msg){
			//什么也不做吧
		});
		}
	},
	reply_reply:{	//点某条回复
		click:function(){ 
			var attrs = M.getEventArgs(this);
			ui.box.load(U('widget/WeibaReply/reply_reply')+'&widget_appname=weiba'+'&weiba_id='+attrs.weiba_id+'&post_id='+attrs.post_id+'&post_uid='+attrs.post_uid+'&to_reply_id='+attrs.to_reply_id+'&to_uid='+attrs.to_uid+'&to_comment_uname='+attrs.to_comment_uname+'&feed_id='+attrs.feed_id+'&addtoend='+attrs.addtoend+'&comment_id='+attrs.comment_id,L('PUBLIC_RESAVE'),function(){
				$('#at-view').hide();
			});
		}
	},
	post_del:{
		click:function(){
			var attrs = M.getEventArgs(this);
			var _this = this;
			var post_del = function(){
				$.post(U('weiba/Index/postDel'),{post_id:attrs.post_id,weiba_id:attrs.weiba_id,log:attrs.log},function(res){
				if(res == 1){
					ui.success('删除成功');
					location.href=U('weiba/Index/detail') + '&weiba_id='+ attrs.weiba_id;
				}else{
					ui.error('删除失败');
				}
				});
			}		
			ui.confirm(this,L('PUBLIC_DELETE_THISNEWS'),post_del);
		}
	},
	post_set:{
		click:function(){
			var attrs = M.getEventArgs(this);
			$.post(U('weiba/Index/postSet'),{post_id:attrs.post_id,type:attrs.type,currentValue:attrs.currentValue,targetValue:attrs.targetValue},function(res){
				if(res == 1){
					ui.success('设置成功');
					setTimeout("location.reload()",1000);
				}else{
					ui.error('设置失败');
				}
			});
		}
	},
	post_favorite:{
		click:function(){
			var attrs = M.getEventArgs(this);
			$.post(U('weiba/Index/favorite'),{post_id:attrs.post_id,weiba_id:attrs.weiba_id,post_uid:attrs.post_uid},function(res){
				if(res == 1){
					ui.success('收藏成功');
					setTimeout("location.reload()",1000);
				}else{
					ui.error('收藏失败');
				}
			});
		}	
	},
	post_unfavorite:{
		click:function(){
			var attrs = M.getEventArgs(this);
			$.post(U('weiba/Index/unfavorite'),{post_id:attrs.post_id,weiba_id:attrs.weiba_id,post_uid:attrs.post_uid},function(res){
				if(res == 1){
					ui.success('取消成功');
					setTimeout("location.reload()",1000);
				}else{
					ui.error('取消失败');
				}
			});
		}	
	}
});
	/**
 * 关注操作Js对象
 */
var followWeiba = {
	// 按钮样式
	btnClass: {
		doFollow: "btn-cancel",
		unFollow: "btn-att-white",
		haveFollow: "btn-att-white"
	},
	// 按钮图标
	flagClass: {
		doFollow: "ico-add-black",
		unFollow: "ico-minus-gray",
		haveFollow: "ico-already"
	},
	// 按钮文字
	btnText: {
		doFollow: '关注',
		unFollow: L('PUBLIC_ERROR_FOLLOWING'),
		haveFollow: '已关注'
	},
	/**
	 * 创建关注按钮
	 * @param object node 按钮节点对象
	 * @param string btnType 按钮类型，4种
	 * @return void
	 */
	createBtn: function(node, btnType) {
		var args = M.getEventArgs(node);
		//alert(args.following);
		var btnType = (0 == args.following) ? "doFollow" : "haveFollow";
		var btnClass = this.btnClass[btnType];
		var flagClass = this.flagClass[btnType];
		var btnText = this.btnText[btnType];
		var btnHTML = ['<span><b class="', flagClass, '"></b>', btnText, '</span>'].join( "" );
		// 按钮节点添加HTML与样式
		node.innerHTML = btnHTML;
		node.className = btnClass;
		// 选择按钮类型
		switch(btnType) {
			case "haveFollow":
				M.addListener(node, {
					mouseenter: function() {
						var b = this.getElementsByTagName( "b" )[0];
						var text = b.nextSibling;
						this.className = follow.btnClass.unFollow;
						b.className = follow.flagClass.unFollow;
						text.nodeValue = follow.btnText.unFollow;
					},
					mouseleave: function() {
						var b = this.getElementsByTagName( "b" )[0];
						var text = b.nextSibling;
						this.className = btnClass;
						b.className = flagClass;
						text.nodeValue = btnText;
					}
				});
				break;
			default:
				M.addListener(node, {
					mouseleave: function() {
					}
				});
			break;
		}
	},
	/**
	 * 添加关注操作
	 * @param object node 关注按钮的DOM对象
	 * @return void
	 */
	doFollow: function(node) {
		var _this = this;
		var args = M.getEventArgs(node);
		var url = node.getAttribute("href") || [U('weiba/Index/doFollowWeiba'), '&weiba_id=', args.weiba_id].join("");
		$.post(url, {}, function(txt) {
			if(1 == txt.status ) {
				ui.success('关注成功');
				if(args.isrefresh==1){
					setTimeout("location.reload()",1000);
				}else{
					node.setAttribute("event-node", "unFollowWeiba");
					node.setAttribute("href", [U('weiba/Index/unFollowWeiba'), '&weiba_id=', args.weiba_id].join(""));
					M.setEventArgs(node, ["weiba_id=", args.weiba_id, "&following=1"].join(""));
					M.removeListener(node);
					M(node);
				}
			} else {
				ui.error(txt.info);
			}
		}, 'json');
	},
	/**
	 * 取消关注操作
	 * @param object node 关注按钮的DOM对象
	 * @return void
	 */
	unFollow: function(node) {
		var _this = this;
		var args = M.getEventArgs(node);
		var url = node.getAttribute("href") || [U('weiba/Index/unFollowWeiba'), '&weiba_id=', args.weiba_id].join("");
		$.post(url, {}, function(txt) {
			if(1 == txt.status ) {
				ui.success('取消成功');
				if(args.isrefresh==1){
					setTimeout("location.reload()",1000);
				}else{
					node.setAttribute("event-node", "doFollowWeiba");
					node.setAttribute("href", [U('weiba/Index/doFollowWeiba'), '&weiba_id=', args.weiba_id].join(""));
					M.setEventArgs(node, ["weiba_id=", args.weiba_id, "&following=0"].join(""));
					M.removeListener(node);
					M(node);
				}
			} else {
				ui.error(txt.info);
			}
		}, 'json');
	}
};

var search = {
	init: function(){
		this.searchKey = '';
		return true;
	},

	searchInit: function(obj){
		$(obj).keyup(function(){
			search.displayList(obj);
		});
	},

	displayList: function(obj){
		this.searchKey = obj.value.replace(/(^\s*)|(\s*$)/g,"");
		if(getLength(this.searchKey)>0){
			var html = '<div class="search-box" id="search-box"><dd id="s_2" class="current" onclick="search.dosearch(2);" onmouseover="$(\'#s_2\').addClass(\'current\');$(\'#s_1\').removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关帖子&raquo;</dd>'
						+'<dd id="s_1" onclick="search.dosearch(1);" onmouseover="$(\'#s_1\').addClass(\'current\');$(\'#s_2\').removeClass(\'current\');">搜“<span>'+this.searchKey+'</span>”相关微吧&raquo;</dd></div>';
					//+'<dd class="more"><a href="#"" onclick="core.search.dosearch();">点击查看更多结果&raquo;</a></dd>';
		}else{
			var html = '';
		}
		$(obj).parent().nextAll().remove();
		$(html).insertAfter($(obj).parent());
	},

	//查找数据
	dosearch:function(type){
		 var url = U('weiba/Index/search')+'&k='+this.searchKey;
		 if("undefined" != typeof(type)){
		 	url+='&type='+type;	
		 }
		 location.href = url;
	}
};


var	upload = function(type,obj){
	    if("undefined"  != typeof(core.uploadFile)){
	        core.uploadFile.filehash = new Array();
	    }
		core.plugInit('uploadFile',obj,function(data){
			//alert(data.src);
	        //$('.input-content').remove();
	        $('#show_'+type).html('<img src="'+data.src+'" width="150" height="150">');
	        $('#form_'+type).val(data.attach_id);    
	    },'image');
	};

/**
 * 修改吧内成员等级
 * @param integer weiba_id 微吧ID
 * @param integer follower_uid 当前成员UID
 * @param integer targetLevel 目标等级
 * @return void
 */
var editLevel = function(weiba_id,follower_uid,targetLevel){
	$.post(U('weiba/Manage/editLevel'), {weiba_id:weiba_id,follower_uid:follower_uid,targetLevel:targetLevel}, function(msg) {
		ajaxReload(msg);
	},'json');
};

/**
 * 将用户移出微吧
 * @param integer weiba_id 微吧ID
 * @param integer follower_uid 微吧成员UID
 * @return void
 */
var moveOut = function(weiba_id,follower_uid){
	if("undefined" == typeof(follower_uid) || follower_uid=='') follower_uid = getChecked();
    if(follower_uid==''){
        ui.error('请选择要移出的用户');return false;
    }
	$.post(U('weiba/Manage/moveOut'), {weiba_id:weiba_id,follower_uid:follower_uid}, function(msg) {
		ajaxReload(msg);
	},'json');
};

/**
 * 解散微吧
 * @param integer weiba_id 微吧ID
 * @return void
 */
var delWeiba = function(weiba_id){
	if(confirm('确定要解散此微吧吗？')){
        $.post(U('weiba/Manage/delWeiba'),{weiba_id:weiba_id},function(msg){
            if(msg == 1) {
            	ui.success('解散成功');
            	location.href = U('weiba/Index/index');
            }else if(msg == -1){
            	ui.error('微吧ID不能为空');
            }else{
            	ui.error('解散失败');
            }
        });
    }
};

/**
 * 检查是否有发帖权限
 * @param integer weiba_id 微吧ID
 * @param boolean who_can_post 发帖权限 0：所有人  1：关注本吧的人
 */
var check_post = function(weiba_id, who_can_post){
//	if(who_can_post){
//		$.post(U('weiba/Index/checkPost'),{weiba_id:weiba_id},function(txt){
//			if(txt==1){
//				location.href = U('weiba/Index/post')+'&weiba_id='+weiba_id;
//			}else{
//				ui.box.load(U('weiba/Index/joinWeiba')+'&weiba_id='+weiba_id,'您没有发帖权限');
//			}
//		});
//	}else{
		location.href = U('weiba/Index/post')+'&weiba_id='+weiba_id;
//	}
};

var weiba_apply = function(weiba_id,type){
	$.post(U('weiba/Index/checkApply'),{weiba_id:weiba_id,type:type},function(txt){
		if(txt==1){
			location.href = U('weiba/Index/apply')+'&weiba_id='+weiba_id+'&type='+type;
		}else if(txt==-1){
			ui.error('您已经提交了申请，请等待审核');
		}else if(txt==-2){
			ui.error('您已经是吧主，不能重复申请');
		}else if(txt==2){
			ui.error('该吧已经设置了吧主');
			setTimeout("location.reload()",2000);
		}else{
			ui.error('您需要发布5篇以上帖子才能申请');
		}
	});
};

/**
 * 处理申请吧主或小吧申请
 */
var verify = function(weiba_id, uid, value){
	$.post(U('weiba/Manage/verify'),{weiba_id:weiba_id,uid:uid,value:value},function(msg){
		ajaxReload(msg);
	},'json');
};

var saveWeibaInfo = function(){
	var weiba_id = $('#weiba_id').val();
	var weiba_name = $('#weiba_name').val();
	var cid = $('#cid').val();
	var intro = $('#intro').val();
	var logo = $('#form_logo').val();
	var who_can_post = $('input:checked[name="who_can_post"]').val();
	$.post(U('weiba/Manage/doWeibaEdit'),{weiba_id:weiba_id,cid:cid,weiba_name:weiba_name,intro:intro,logo:logo,who_can_post:who_can_post},function(msg){
		if(msg=='1'){
			ui.success('保存成功');
		}else{
			ui.error(msg);
		}
	});
}



