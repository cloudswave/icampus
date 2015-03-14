/**
 * 全站供用的Js监听
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
M.addModelFns({
	invite_colleague_form: {
		callback: function( txt ) {
			ui.success( txt.info );
			ui.box.close();
		}
	},
	drop_menu_list: {
		load: function() {
			var parentModel = this.parentNode,
				list = this;
			// 鼠标进入父Model，显示Menu；反之，则隐藏Menu。
			M.addListener( parentModel, {
				mouseenter: function() {
					var className = this.className;
					this.className = [ className, " drop" ].join( "" );
					list.style.display = "block";
				},
				mouseleave: function() {
					var className = this.className;
					this.className = className.replace(/(\s+drop)+(\s+|$)/g, "");
					list.style.display = "none";
					
				}
			});
		}
	},
	search_icon:{
		mouseleave:function(){
			// $('.search_footer').attr('ison','no');
			// setTimeout(function(){
			// 	if($('.search_footer').attr('ison')=='no'){
			// 		$('.search_footer').hide();
			// 	}
			// },150);
		},
		click:function(){
			if($('.search_footer').attr('ison') == 'yes'){
				$('.search_footer').attr('ison','no');
				$('.search_footer').hide();
				return true;
			}
			$('.search_footer').attr('ison','yes');
			$('.search_footer').show();
		}
	},
	search_menu_footer:{
	  	click:function(){
	  		var offset = $(this).offset();
	  		$('#search_menu').css({'left':offset.left+'px','top':offset.top-35+'px','width':'81px'}).show();
	  		$('#search_menu').attr('ison','yes');
		   },
	  	mouseleave:function(){
	  		//setTimeout(core.search.hideMenu,300);
	  	},
	  	blur:function(){
  			core.search.dohide();
  		}
  	},
  	search_menu:{
	  	click:function(){
	  		var offset = $(this).offset();
	  		$('#search_menu').css({'left':offset.left+'px','top':offset.top+$(this).height()+12+'px','width':'81px'}).show();
	  		$('#search_menu').attr('ison','yes');
		   },
	  	mouseleave:function(){
	  		setTimeout(core.search.hideMenu,300);
	  	},
	  	blur:function(){
	  		core.search.dohide();
	  	}
	  },
	search_menu_ul:{
	  	mouseleave:function(){
	  		$('.search_footer').attr('ison','yes');
	  		core.search.dohide();
	  	},
	  	mouseenter:function(){
	  		core.search.doshow(); 
	  	}
	 },
	search_footer:{
		mouseleave:function(){
			// $(this).attr('ison','no');
			// var _this = this;
			// setTimeout(function(){
			// 	$(_this).hide();
			// },'250');
		},
		mouseenter:function(){
			$(this).attr('ison','yes');
		}
	},
	drop_search:{
		load:function(){
			var _this = this;
			core.plugInit('search');
			$(this.childEvents['searchKey'][0]).click(function(){
				core.search.searchInit(this);
			});	
		}
	},
	wigdet_setform:{
		callback:function(data){
			core.widget.afterSet(data);
		}
	},
	diy_widget:{
		load:function(data){
			var child = this.childModels['widget_box'];
			var _this = this;
			var args  = M.getModelArgs(this);
			core.plugFunc('widget',function(){
				//拖动处理
				core.loadFile(THEME_URL+'/js/ui.sortable.js',function(){
					$(_this).sortable({
						items: '.ui-state-disabled',
						placeholder: 'ui-selected',
						revert: 0.01,
						helper: 'clone', 
						update:function(){
							core.widget.dosort(args,_this);
						}
					});
					$(child).disableSelection();	
				});
			});
		}
	}
});
M.addEventFns({
	widget_toggle:{
		click:function(){
			$(this.parentModel.childModels['widget_child'][0]).toggle('500');
		}
	},
	widget_setup:{
		click:function(){
			$(this.parentModel.childModels['wigdet_setbox'][0]).toggle('500');	
		}
	},
	widget_cancel_set:{
		click:function(){
			$(this.parentModel).hide('500');
		}
	},
	widget_close:{
		click:function(){
			var args = M.getModelArgs(this.parentModel);
			core.widget.removeWidget(this,args,this.parentModel);
		}
	},
	widget_add:{
		click:function(){
			var args = M.getEventArgs(this);
			core.widget.addWidget(args);
		}
	},
	invite_colleague: {
		click: function() {
			ui.box.load( this.href, L('PUBLIC_INVITE_COLLEAGUE') );
			return false;
		}
	},	
	invite_addemail:{
		click: function() {
			var input1 = document.getElementById("email_input").value,
				$email_input = $("#email_input"),
				dInput = this.parentModel.childEvents["email"][0],
				dInputClone = dInput.cloneNode( true );

			dInputClone.value = "";
			$email_input.append( dInputClone );
			M( dInputClone );
			return false;
		}
	},
	face_card:{
		load:function(){
			//载入小名片js
			core.plugInit('facecard'); //只初始化那个框体
		},
		mouseenter:function(){
			var uid = $(this).attr('uid');
			if(MID<1 || (MID == uid) || (UID ==uid)){
				return false;
			}
			var obj = $(this);
			//setTimeout(function(){
			if("undefined" == typeof(core.facecard)){
				core.plugFunc('facecard',function(){
					core.facecard.show(obj,uid);
				})
			}else{
				core.facecard.show(obj,uid);
			}
			//},'250');
			
		},
		mouseleave:function(){
			if(MID<1){
				return false;
			}
			core.facecard.hide();
		},
		blur:function(){
			core.facecard.hide();	
		}
	},
	//个人信息
	more_person_info: {
		click: function() {
			var li;

			li = this.parentNode;
			li.style.display = "block";
			
			if($(this).attr('rel')=='hide'){
				var _display = 'block';
				$(this).attr('rel','show');
				$('.mod-person .person-info a').text(L('PUBLIC_PUT')+"↑")
			}else{
				var _display = 'none';
				$(this).attr('rel','hide');
				$('.mod-person .person-info a').text(L('PUBLIC_OPEN_MORE')+"↓")
			}
			
			while ( li = li.nextSibling ) {
				( "LI" === li.tagName ) && ( li.style.display = _display );

			}
			
			return false;
		}
	},
	ico_wallet:{//财富
		mouseenter:function(){
			this._model.style.display = 'block';
		},
		mouseleave:function(){
			this._model.style.display = 'none';
		},
		load:function(){
			var _model = M.getModels('layer_wallet');
			this._model = _model[0];
		}
	},
	ico_level:{//等级
		mouseenter:function(){
			this._model.style.display = 'block';
		},
		mouseleave:function(){
			this._model.style.display = 'none';
		},
		load:function(){
			var _model = M.getModels('layer_level');
			this._model = _model[0];
		}
	},
	share:{//分享操作
		click : function(){
			var attrs =M.getEventArgs(this);
			//alert(typeof(attrs));exit;
			// if(attrs.appname == 'weiba' && attrs.feedtype == 'weiba_post'){
			// 	var sid = attrs.curid;
			// }else{
			var sid = attrs.sid;
			//}
			var stable = attrs.stable;
			var initHTML = attrs.initHTML;
			var curtable =attrs.curtable;
			var curid = attrs.curid;
			var appname = attrs.appname;
			var cancomment = attrs.cancomment;
			var is_repost = attrs.is_repost;
			share(sid,stable,initHTML,curid,curtable,appname,cancomment,is_repost);
			return false;
		}
	},
	share_to_feed:{//分享操作
		load: function () {
			var attrs = M.getEventArgs(this);
			if (attrs.isLoad == 1) {
				var initHTML = attrs.initHTML;
				var attachId = attrs.attachId;
				var from = attrs.from;
				var appname = attrs.appname;
				var source_url = attrs.url;
				var url = U('public/Share/shareToFeed')+'&initHTML='+initHTML+'&attachId='+attachId+'&from='+from+'&appname='+appname+'&source_url='+source_url;
				ui.box.load(url,'分享');
			}
			return false;
		},
		click : function(){
			var attrs =M.getEventArgs(this);
			var initHTML = attrs.initHTML;
			var attachId = attrs.attachId;
			var from = attrs.from;
			var appname = attrs.appname;
			var source_url = attrs.url;
			var url = U('public/Share/shareToFeed')+'&initHTML='+initHTML+'&attachId='+attachId+'&from='+from+'&appname='+appname+'&source_url='+source_url;
			ui.box.load(url,'分享');
			return false;
		}
	},
	setremark:{	//设置备注
		click:function(){
			var remark = $(this).attr('remark');
			var uid = $(this).attr('uid');
			ui.box.load(U('widget/Remark/edit')+'&remark='+remark+'&uid='+uid,L('PUBLIC_EDIT_FOLLWING'));
		}
	},
	/**
	 * 添加关注
	 * @type {Object}
	 */
	doFollow: {
		click: function() {
			follow.doFollow(this);
			return false;
		},
		load: function() {
			follow.createBtn(this);
		}
	},
	unFollow: {
		click: function() {
			follow.unFollow( this );
			return false;
		},
		load: function() {
			follow.createBtn( this );
		}
	},
	setFollowGroup:{
		click:function(){
			var args = M.getEventArgs(this);
			follow.setFollowGroup(this,args.fid);
		}
	},
	follow_check: {
		click: function( e ) {
			var check = this.getElementsByTagName( "input" )[0];
			setTimeout( function() {
				check.checked = !check.checked;
				check = undefined;
			}, 1);
			return false;
		}
	},
	comment:{	
		click:function(){	//点击评论的时候
			var attrs = M.getEventArgs(this);
			var comment_list = this.parentModel.childModels['comment_detail'][0];
			
//			if("undefined" == typeof(core.comment)){
//				core.plugInit('comment',attrs,comment_list);
//				core.setTimeout("core.comment.display()",150);
//			}else{
			
				core.comment.init(attrs,comment_list);
				core.comment.display();
//			}
			return false;
		}
	},
	reply_comment:{	//点某条回复
		click:function(){
			var attrs = M.getEventArgs(this);
			var comment_list = this.parentModel.parentModel;
			var docomment = comment_list.childModels['comment_textarea'][0].childEvents['do_comment'][0];
			$(docomment).attr('to_comment_id',attrs.to_comment_id);
			$(docomment).attr('to_uid',attrs.to_uid);
			$(docomment).attr('to_comment_uname',attrs.to_comment_uname);
			core.plugFunc('comment',function(){
				core.comment.init(attrs,comment_list);
				core.comment.initReply();
			});
			//core.plugInit('comment',attrs,comment_list);
			//core.setTimeout("core.comment.initReply()",150);
		}
	},
	comment_del:{
		click:function(){
			var attrs = M.getEventArgs(this);
			// 添加删除后的楼层统计数变化
			$(this.parentModel).fadeOut('normal', function () {
				var $commentList = $(this).parent();
				if ($commentList.length > 0) {
					// 获取微博ID
					var wid = parseInt($commentList.attr('id').split('_')[1]);
					var $commentListVisible = $commentList.find('dl:visible');
					var len = parseInt($commentListVisible.eq(0).find('span.floor').html());
					$commentListVisible.each(function (i, n) {
						$(this).find('span.floor').html((len - i)+'楼');
					});
				}
			});
			if("undefined"==typeof(core.comment)){
				core.plugFunc('comment',function(){
					core.comment.delComment(attrs.comment_id);
				});
			}else{
				core.comment.delComment(attrs.comment_id);	
			}
		}
	},
	do_comment:{	//回复操作
		click:function(){
			if ( this.noreply == 1 ){
				return;
			}
			var attrs = M.getEventArgs(this);
			attrs.to_comment_id = $(this).attr('to_comment_id');
			attrs.to_uid = $(this).attr('to_uid');
			attrs.to_comment_uname = $(this).attr('to_comment_uname');
			attrs.addToEnd = $(this).attr('addtoend');
			
			var comment_list = this.parentModel.parentModel;
			core.comment.init(attrs,comment_list);

			var _this = this;
			var after = function(){
				$(_this).attr('to_uid','0');
				$(_this).attr('to_comment_id','0');
				$(_this).attr('to_comment_uname','');
				if(attrs.closeBox == 1){
					ui.box.close();
					ui.success( L('PUBLIC_CENTSUCCESS') );
				}
			}
			core.comment.addComment(after,this);
			this.noreply = 1;
			setTimeout(function (){
				_this.noreply = 0;
			},5000);
		},
		load:function(){
			var attrs = M.getEventArgs(this);
			attrs.to_comment_id = $(this).attr('to_comment_id');
			attrs.to_uid = $(this).attr('to_uid');
			attrs.to_comment_uname = $(this).attr('to_comment_uname');
			attrs.addToEnd = $(this).attr('addtoend');
			var comment_list = this.parentModel.parentModel;
			core.plugInit('comment',attrs,comment_list);
		}
	},
	comment_insert_face:{
		click:function(){
			var target = this.parentModel.childModels["mini_editor"][0];		
			var _faceDiv = this.parentModel.childModels['faceDiv'][0];
			core.plugInit('face',this,$(target).find('textarea'),_faceDiv);
		}
	},
	showCategory:{
		click:function(){
			var attrs = M.getEventArgs(this);
			//显示分类
			var obj = this;
			core.plugFunc('category',function(){
				core.category.loadSelect(obj,attrs.model_name,attrs.app_name,attrs.method,attrs.id,attrs.inputname,attrs.callback);
			});
		}
	}
});

//分享
var share=function(sid,stable,initHTML,curid,curtable,appname,cancomment,is_repost){	
	if("undefined" == typeof(cancomment)){
		cancomment = 0;
	}
	var url = U('public/Share/index')+'&sid='+sid+'&stable='+stable+'&curid='+curid+'&curtable='+curtable+'&appname='+appname+'&initHTML='+initHTML+'&cancomment='+cancomment+'&is_repost='+is_repost;
	if($('#tsbox').length>0){
		return false;
	}
	ui.box.load(url,L('PUBLIC_SHARE'),function(){
		$('#at-view').hide();
		var share_id="feed"+curid;
		window.location.hash=share_id;
	});
	return false;
};

/**
 * 关注操作Js类
 * @type {Object}
 */
var follow = {
	// 按钮样式
	btnClass: {
		doFollow: "btn-cancel",
		unFollow: "btn-att-white",
		haveFollow: "btn-att-white",
		eachFollow: "btn-att-white"
	},
	// 按钮图标
	flagClass: {
		doFollow: "ico-add-black",
		unFollow: "ico-minus-gray",
		haveFollow: "ico-already",
		eachFollow: "ico-connect"
	},
	// 按钮文字
	btnText: {
		doFollow: '关注',
		unFollow: L('PUBLIC_ERROR_FOLLOWING'),
		haveFollow: '已关注',
		eachFollow: '相互关注'
	},
	/**
	 * 创建关注按钮
	 * @param object node 按钮节点对象
	 * @param string btnType 按钮类型，4种
	 * @return void
	 */
	createBtn: function(node, btnType) {
		var args = M.getEventArgs(node);
		var btnType = (0 == args.following) ? "doFollow" : ((0 == args.follower) ? "haveFollow" : "eachFollow");
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
			case "eachFollow":
				$(node).bind({
					mouseover: function() {
						var b = this.getElementsByTagName( "b" )[0];
						var text = b.nextSibling;
						this.className = follow.btnClass.unFollow;
						b.className = follow.flagClass.unFollow;
						text.nodeValue = follow.btnText.unFollow;
					},
					mouseout: function() {
						var b = this.getElementsByTagName( "b" )[0];
						var text = b.nextSibling;
						this.className = btnClass;
						b.className = flagClass;
						text.nodeValue = btnText;
					}
				});
				break;
			default:
				$(node).unbind('mouseover');
				$(node).unbind('mouseout');
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
		var url = node.getAttribute("href") || U('public/Follow/doFollow');
		$.post(url, {fid:args.uid}, function(txt) {
			if(1 == txt.status ) {
				if("undefined" != typeof(core.facecard)){
					core.facecard.deleteUser(args.uid);
				}
				node.setAttribute("event-node", "unFollow");
				node.setAttribute("href", [U('public/Follow/unFollow'), '&fid=', args.uid].join(""));
				M.setEventArgs(node, ["uid=", args.uid, "&uname=", args.uname, "&following=", txt.data.following, "&follower=", txt.data.follower].join(""));
				M.removeListener(node);
				M(node);
				_this.updateFollowCount(1);
				updateUserData('follower_count', 1, args.uid);
				if("following_right" == args.refer) {
					var item = node.parentModel;
					// item.parentNode.removeChild(item);
					$(item).slideUp('normal', function() {
						$(this).remove();
					});
					$.post(U('widget/RelatedUser/changeRelate'), {uid:args.uid, limit:1}, function(msg) {
						var _model = M.getModels("related_list");
						$(_model[0]).append(msg);
						M(_model[0]);
					}, 'json');
					ui.success("关注成功");
				} else {
					followGroupSelectorBox(args.uid, args.isrefresh);
				}
			} else {
				ui.error(txt.info);
			}
		}, 'json');
	},
	/**
	 * 选择关注分组下拉窗
	 * @param object node 关注按钮的DOM对象
	 * @param integer fid 关注人ID
	 * @return void
	 */
	setFollowGroup: function(node, fid) {
		var url = U('public/FollowGroup/selectorBox')+'&fid='+fid;
		ui.box.load(url, L('PUBLIC_SET_GROUP'));	
	},
	/**
	 * 取消关注操作
	 * @param object node 关注按钮的DOM对象
	 * @return void
	 */
	unFollow: function(node) {
		var _this = this;
		var args = M.getEventArgs(node);
		var url = node.getAttribute( "href" ) || U('public/Follow/unFollow');
		// 取消关注操作
		$.post(url, {fid:args.uid}, function(txt) {
			txt = eval( "(" + txt + ")" );
			if ( 1 == txt.status ) {
				ui.success( txt.info );
				if("undefined" != typeof(core.facecard) ){
					core.facecard.deleteUser(args.uid);
				}
				if ( "following_list" == args.refer ) {
					var item = node.parentModel;
					// 移除
					item.parentNode.removeChild( item );
				} else {					
					node.setAttribute( "event-node", "doFollow" );
					node.setAttribute( "href", [U('public/Follow/doFollow'), '&fid=', args.uid].join( "" ) );
					M.setEventArgs( node, ["uid=", args.uid, "&uname=", args.uname, "&following=", txt.data.following, "&follower=", txt.data.follower].join( "" ) );
					M.removeListener( node );
					M( node );
				}
				_this.updateFollowCount( - 1 );
				updateUserData('follower_count', -1, args.uid);
				if(args.isrefresh==1) location.reload();
			} else {
				ui.error( txt.info );
			}
		});
	},
	/**
	 * 更新关注数目
	 * @param integer num 添加的数值
	 * @return void
	 */
	updateFollowCount: function(num) {
		var l;
		var following_count = M.getEvents("following_count");
		if(following_count) {
			l = following_count.length;
			while(l-- > 0) {
				following_count[l].innerHTML = parseInt(following_count[l].innerHTML) + num;
			}
		}
	}
};
/**
 * 好友分组选择，下拉框
 * @param object obj 点击按钮DOM对象
 * @param integer fid 关注人ID
 * @return void
 */
var followGroupSelectorList = function(obj, fid)
{
	var x = obj.offset();
	// 获取数据列表
	$.post(U('public/FollowGroup/selectorList'), {fid:fid}, function(res) {
		if($('#followGroupList').length > 0) {
			if($('#followGroupList').attr('rel') == fid) {
				$('#followGroupList').remove();
			} else {
				$('#followGroupList').attr('rel', fid);
				$('#followGroupList').html(res);
			}
		} else {
			$('body').append('<div id="followGroupList" rel="' + fid + '" class="layer-follow-list">'+res+'</div>');
		}
		// 下拉定位
		$('#followGroupList').css({'left':x.left + 'px', 'top':x.top + obj.height() + 8 +'px', 'display':'block'});
		$('#followGroupSelector').find('label').hover(function() {
			$(this).addClass('hover');	
		}, function() {
			$(this).removeClass('hover');
		});

		$('body').bind('click',function(event){
			var obj = "undefined" != typeof(event.srcElement) ? event.srcElement : event.target;
			var isBox = $('#followGroupList').find(obj).length;
			if (isBox === 0) {
				$('#followGroupList').remove();
			}
		});
	});
};
/**
 * 好友分组选择，弹出框
 * @param integer fid 关注人ID
 * @param integer isrefresh 确定后是否刷新页面
 * @return void
 */
var followGroupSelectorBox = function(fid, isrefresh)
{
	if(isrefresh==1){
		var r = 'location.reload();';
	}else{
		var r = '';
	}
	ui.box.load(U('public/FollowGroup/selectorBox')+'&fid='+fid+'&isrefresh='+isrefresh, L('PUBLIC_FOLLOWING_SUCCESS'), r);
};
/**
 * 好友设置分组，弹出框
 * @param integer fid 关注人ID
 * @param integer isrefresh 确定后是否刷新页面
 */
var setFollowGroup = function(fid, isrefresh)
{
	if(isrefresh==1){
		var r = 'location.reload();';
	}else{
		var r = '';
	}
	ui.box.load(U('public/FollowGroup/selectorBox')+'&fid='+fid+'&isrefresh='+isrefresh, '设置分组', r);
};
/**
 * 关闭好友分组选择
 * @param integer fid 关注人ID
 * @return void
 */
var followGroupSelectorClose = function(fid)
{
	$('.followGroupStatus'+fid).hide();
	$('.followGroupStatus'+fid).html('');
};
/**
 * 添加、编辑关注分组，弹出框
 * @return void
 */
var setFollowGroupTab = function(gid)
{
	var title = gid ? L('PUBLIC_EDIT_GROUP') : L('PUBLIC_CREATE_GROUP');
	gid = gid ? '&gid='+gid : '';
	ui.box.load(U('public/FollowGroup/setGroupTab') + gid, title);
};