/**
 * 赞核心Js
 * @type {Object}
 */
core.digg = {
	// 给工厂调用的接口
	_init: function (attrs) {
		core.digg.init();
	},
	init: function () {
		core.digg.digglock = 0;
	},
	addDigg: function (feed_id) {
		if (core.digg.digglock == 1) {
			return false;
		}
		core.digg.digglock = 1;
		$.post(U('public/Feed/addDigg'), {feed_id:feed_id}, function (res) {
			if (res.status == 1) {
				$digg = {};
				if (typeof $('#digg'+feed_id)[0] === 'undefined') {
					$digg = $('#digg_'+feed_id);
				} else {
					$digg = $('#digg'+feed_id);
				}
				var num = $digg.attr('rel');
				num++;
				$digg.attr('rel', num);
				$('#digg'+feed_id).html('<a href="javascript:;" onclick="core.digg.delDigg('+feed_id+')">已赞('+num+')</a>');
				$('#digg_'+feed_id).html('<a href="javascript:;" onclick="core.digg.delDigg('+feed_id+')">已赞('+num+')</a>');
			} else {
				ui.error(res.info);
			}
			core.digg.digglock = 0;
		}, 'json');
	},
	delDigg: function (feed_id) {
		if (core.digg.digglock == 1) {
			return false;
		}
		core.digg.digglock = 1;
		$.post(U('public/Feed/delDigg'), {feed_id:feed_id}, function(res) {
			if (res.status == 1) {
				$digg = {};
				if (typeof $('#digg'+feed_id)[0] === 'undefined') {
					$digg = $('#digg_'+feed_id);
				} else {
					$digg = $('#digg'+feed_id);
				}
				var num = $digg.attr('rel');
				num--;
				$digg.attr('rel', num);
				var content;
				if (num == 0) {
					content = '<a href="javascript:;" onclick="core.digg.addDigg('+feed_id+')">赞</a>';
				} else {
					content = '<a href="javascript:;" onclick="core.digg.addDigg('+feed_id+')">赞('+num+')</a>';
				}
				$('#digg'+feed_id).html(content);
				$('#digg_'+feed_id).html(content);
			} else {
				ui.error(res.info);
			}
			core.digg.digglock = 0;
		}, 'json');
	}
};