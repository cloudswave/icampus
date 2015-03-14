/**
 * 微博多图插入Js核心插件
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
core.multimage = {
	/**
	 * 工厂模式调用初始化
	 * @param object attrs 初始化参数对象
	 * @return void
	 */
	_init: function (attrs) {
		if (attrs.length === 4) {
			core.multimage.init(attrs[1], attrs[2], attrs[3]);
		} else if (attrs.length === 3) {
			core.multimage.init(attrs[1], attrs[2]);
		} else if (attrs.length === 2) {
			core.multimage.init(attrs[1]);
		} else {
			return false;
		}
	},
	/**
	 * 初始化操作执行
	 * @param object obj 点击的DOM节点对象 
	 * @param object textarea 输入框DOM对象
	 * @param object postbtn 发布按钮DOM对象
	 * @return {[type]}          [description]
	 */
	init: function (obj, textarea, postbtn) {
		this.obj = obj;
		this.textarea = textarea;
		this.postbtn = postbtn;
		// 创建显示弹窗DIV
		core.multimage.createDiv();
	},
	/**
	 * 创建图片显示DIV弹窗
	 * @return void
	 */
	createDiv: function () {
		var _this = this;
		// 判断弹窗是否存在
		if ($('#multi_image').length > 0) {
			return false;
		}
		$('.attach-file').remove();
		// 异步获取弹窗结构
		$.get(U('public/Feed/multimageBox'), {}, function (res) {
			if (res.status === 1) {
				// 弹窗的HTML结构
				var html = '<div class="talkPop alL" id="multi_image" style="*padding-top:20px;">\
										<div class="wrap-layer">\
										<div class="arrow arrow-t"></div>\
										<div class="talkPop_box">\
										<div class="close hd"><a onclick="core.multimage.removeDiv()" class="ico-close" href="javascript:;" title="'+L('PUBLIC_CLOSE')+'"></a>\
										<span>共&nbsp;<em id="upload_num_'+res.unid+'">0</em>&nbsp;张，还能上传&nbsp;<em id="total_num_'+res.unid+'">'+res.total+'</em>&nbsp;张（按住ctrl可选择多张）</span></div>'
										+res.html+
										'</div></div></div>';
				// 插入到body底部
				$('body').append(html);
				// 定位属性
				var pos = $(_this.obj).offset();
				$('#multi_image').css({top:(pos.top+5)+"px",left:(pos.left-5)+"px","z-index":998});
			}
		}, 'json');
		// body点击事件绑定
		$('body').bind('click',function(event){
			var obj = ('undefined' !== typeof event.srcElement) ? event.srcElement : event.target;
			if ($(obj).attr('event-node') === 'insert_file') {
				core.multimage.removeDiv();
			}
		});
	},
	/**
	 * 移除多图窗口
	 * @return void
	 */
	removeDiv: function () {
		var multiImageNode = $('#multi_image')[0];
		if (multiImageNode != null) {
			multiImageNode.parentNode.removeChild(multiImageNode);
		}
		$('#attach_ids').remove();
	},
	/**
	 * 移除图片接口
	 * @param string unid ID的字符串
	 * @param integer index 索引数
	 * @param integer attachId 附件ID
	 * @return void
	 */
	removeImage: function (unid, index, attachId) {
		// 移除附件ID数据
		core.multimage.upAttachVal('del', attachId);
		// 移除图像
		$('#li_'+unid+'_'+index).remove();
		// 移除附件ID项
		($('#ul_'+unid).find('li').length - 1 === 0) && $('#attach_ids').remove();
		// 动态设置数目
		core.multimage.upNumVal(unid, 'dec');
	},
	/**
	 * 更新附件表单值
	 * @return void
	 */
	upAttachVal: function (type, attachId) {
		var attachVal = $('#attach_ids').val();
		var attachArr = attachVal.split('|');
		var newArr = [];
		type === 'add' && attachArr.push(attachId);;
		for (var i in attachArr) {
			if (attachArr[i] !== '' && attachArr[i] !== attachId.toString()) {
				newArr.push(attachArr[i]);
			}
		}
		$('#attach_ids').val('|' + newArr.join('|') + '|');
	},
	/**
	 * 更新上传显示数目
	 * @param string unid 唯一ID
	 * @param string type 更新类型，inc增加；dec减少
	 * @return void
	 */
	upNumVal: function (unid, type) {
		var $uploadNum = $('#upload_num_'+unid),
			$totalNum = $('#total_num_'+unid);
		switch (type) {
			case 'inc':
				// 动态设置数目 - 增加
				$uploadNum.html(parseInt($uploadNum.html()) + 1);
				$totalNum.html(parseInt($totalNum.html()) - 1);
				break;
			case 'dec':
				// 动态设置数目 - 减少
				$uploadNum.html(parseInt($uploadNum.html()) - 1);
				$totalNum.html(parseInt($totalNum.html()) + 1);
				break;
		}
	},
	/**
	 * 添加loading效果
	 * @param string unid 唯一ID
	 * @return void
	 */
	addLoading: function (unid) {
		var loadingHtml = '<li id="loading_'+unid+'" class="load"><span><img src="'+THEME_URL+'/image/loading.gif" /></span></li>';
		$('#btn_'+unid).before(loadingHtml);
	},
	/**
	 * 移除loading效果
	 * @param string unid 唯一ID
	 * @return void
	 */
	removeLoading: function (unid) {
		$('#loading_'+unid).remove();
	}
};