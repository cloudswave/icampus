/**
 * 相册核心Js对象
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
var photo = {};
// 用于存储相册的配置信息
photo.setting = {};
/**
 * 相册初始化
 * $param object option 相册配置相关数据
 * @return void
 */
photo.init = function(option) {
	this.setting.container = '#'+option.container;				// 容器ID
	this.setting.loadcount = option.loadcount || 0;				// 加载数目
	this.setting.loadmax = option.loadmax || 4;					// 加载最大次数
	this.setting.loadId = option.loadId || 0;					// 加载起始ID
	this.setting.loadlimit = option.loadlimit || 20;			// 每次加载的数目，默认为20
	this.setting.canload = option.canload || true;				// 是否能加载
	this.setting.page = 1;										// 分页页数
	this.setting.newload = 0;									// 是否是新加载
	this.setting.type = option.type;							// 展示数据类型

	photo.bindScroll();

	if($(photo.setting.container).length > 0 && this.setting.canload){
		$(photo.setting.container).append("<div class='loading' id='loadMore'>" + L('PUBLIC_LOADING') + "<img src='" + THEME_URL + "/image/load.gif' class='load'></div>");
		photo.loadMore();
	}
};

/**
 * 页面底部触发事件
 * @return void
 */
photo.bindScroll = function() {
	// 底部触发事件绑定
	$(window).bind('scroll resize', function() {
		// 加载指定次数后，将不能自动加载相册信息
		if(photo.isLoading()) {
			var bodyTop = document.documentElement.scrollTop + document.body.scrollTop;
			var bodyHeight = $(document.body).height();
			if(bodyTop + $(window).height() > bodyHeight - 250) {
				if($(photo.setting.container).length > 0) {
					// 加载载入样式
					$(photo.setting.container).after('<div class="loading" id="loadMore">'+L('PUBLIC_LOADING')+'<img src="'+THEME_URL+'/image/load.gif" class="load"></div>');
					// 加载数据
					photo.loadMore();
				}
			}
		}
	});
};
/**
 * 判断是否相册时候能自动加载
 * @return boolean 相册是否能自动加载
 */
photo.isLoading = function() {
	var status = (this.setting.loadcount >= this.setting.loadmax || this.setting.canload == false) ? false : true;
	return status;
};
/**
 * 获取加载的数据信息
 * @return void
 */
photo.loadMore = function() {
	if (photo.setting.loadId === null) {
		$('#loadMore').remove();
		photo.dynamicLoading('', false);
		return false;
	}
	// 将能加载参数关闭
	photo.setting.canload = false;
	photo.setting.loadcount++;
	// 异步提交，获取相关相册数据
	var postArgs = {};
	postArgs.widget_appname = 'photo';
	postArgs.loadId = photo.setting.loadId;
	postArgs.loadlimit = photo.setting.loadlimit;
	postArgs.loadcount = photo.setting.loadcount;
	postArgs.p = photo.setting.page;
	postArgs.newload = photo.setting.newload;
	postArgs.type = photo.setting.type;
	$.get(U('widget/PhotoList/loadMore'), postArgs, function(res) {
		if(res.status == 1) {
			photo.setting.newload = 0;
			// 开启加载参数
			photo.setting.canload = true;
			// 修改加载ID
			photo.setting.loadId = res.loadId;
			// 动态加载数据
			photo.dynamicLoading(res.html, false);
			// 分页操作
			if(photo.setting.loadcount >= photo.setting.loadmax || photo.setting.loadId === null) {
				$(photo.setting.container).after('<div id="page" class="page" style="display:none;">'+res.pageHtml+'</div>');
				if($('#page').find('a').size() > 2) {
					var href = false;
					$('#page').find('a').each(function() {
						href = $(this).attr('href');
					});
					// 重组分页结构
					$('#page').html(res.pageHtml).show();
					var now = parseInt($('#page').children('.current').html());
					$('#page').find('a').each(function() {
						var href = $(this).attr('href');
						if(href) {
							$(this).attr('href', '#');
							$(this).click(function() {
								photo.setting.loadcount = 0;
								$(photo.setting.container).remove();
								$('#tab_menu').after('<div id="container" class="mb10 photo-list-masonry clearfix"></div>');
								if($(this).is('.pre')) {
									photo.setting.page = now - 1;
								} else if($(this).is('.next')) {
									photo.setting.page = now + 1;
								} else {
									photo.setting.page = parseInt($(this).html());
								}
								photo.setting.newload = 1;
								photo.setting.loadId = 0;
								photo.loadMore();
								$('#page').remove();
							});
						}
					});
				}
			}
		} else {
			$('#loadMore').remove();
			photo.dynamicLoading('', false);
		}
	}, 'json');
	return false;
};
/**
 * 动态加载HTML相册数据
 * @param DOM html 新加载HTML数据
 * @param boolean page 是否分页
 * @return void
 */
photo.dynamicLoading = function(html, page) {
	if(page) {
		$(photo.setting.container).html(html).masonry('reload');
	} else {
		if(photo.setting.loadcount == 1) {
			// 载入瀑布流
			$(photo.setting.container).html(html);
			$(photo.setting.container).masonry({itemSelector: ".box",gutterWidth: 20}); 
		} else {
			var domDiv = $('<div></div>').append(html);
			var box = [];
			domDiv.find('div').filter('.box').each(function() {
				box.push(this);
			});
			$(photo.setting.container).append($(box)).masonry('appended', $(box));
		}
	}
	$('#loadMore').remove();
	M($(photo.setting.container)[0]);
};
/**
 * 编辑图片弹窗
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
photo.editphotoTab = function (albumId, photoId) {
	ui.box.load(U('photo/Manage/edit_photo_tab') + '&aid=' + albumId + '&pid=' + photoId, '编辑图片');
};
/**
 * 删除单张图片
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
photo.delphoto  = function (albumId, photoId) {
	if (confirm('你确定要删除这张图片么？')) {
		$.post(U('photo/Manage/delete_photo'), {id:photoId, albumId:albumId}, function(data) {
			if (data == 1) {
				location.href = U('photo/Index/album') + '&id=' + albumId + '&uid=' + _UID_;
				return false;
			} else {
				ui.error('删除失败！');
			}
		});
	}
};
/**
 * 设置封面操作
 * @param integer albumId 相册ID
 * @param integer photoId 图片ID
 * @return void
 */
photo.setcover = function (albumId, photoId) {
	if(confirm('你要将这张图片设置为封面么？')) {
		$.post(U('photo/Manage/set_cover'), {photoId:photoId,albumId:albumId}, function(data) {
			if (data == 1) {
				ui.success('封面设置成功！');
			} else if (data == -1) {
				ui.error('该图片不存在！');
			} else {
				ui.error('当前封面已是该图片，或设置失败！');
			}
		});
	}
};
