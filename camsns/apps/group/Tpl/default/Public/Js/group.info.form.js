group_info = function(){};
group_info.prototype = {
	$input_tags:'',
	init:function()
	{
		this.$input_tags = $('input[name="tags"]');
	},
	text_length:function(o, length)
	{
		$o = $(o);
		if (getLength($o.val()) > length) {
			$('#group_' + $o.attr('name') + '_tips').html('不能超过' + length + '个字');
		} else {
			$('#group_' + $o.attr('name') + '_tips').html('');
		}
	},
	add_tag:function(e)
	{
		var tag = $(e).html().replace(/\s/g, '');
		var tags = this.$input_tags.val();
		if (tags.indexOf(tag) == -1) {
			this.$input_tags.val((tags?(tags.replace(/,$/g, '') + ','):'') + tag);
			this.tag_num();
		}
	},
	tag_num:function()
	{
		var tags	= this.$input_tags.val().split(',');
		var tag_num = tags.length;
		var $tag_change = $('#tags_change');
		var i;
		var _tag_num;
		for (i = 0, _tag_num = 0; i < tag_num; i++) {
			if (tags[i] != '') {
				_tag_num++;
			}
		}
		if (_tag_num > 5) {
			$tag_change.html('添加标签最多可设置5个');
			this.$input_tags.focus();
		} else {
			$tag_change.html('');
		}
		return _tag_num;
	},
	change_verify:function()
	{
	    var date = new Date();
	    var ttime = date.getTime();
	    //var url = U('home/Public/verify');
	    var url = _PUBLIC_+'/captcha.php';
		$('#verifyimg').attr('src',url+'&'+ttime);
	},
	check_form:function(v_form)
	{
		if (getLength(v_form.name.value) == 0) {
			ui.error("群组名称不能为空");
			v_form.name.focus();
			return false;
		} else if (getLength(v_form.name.value) > 30) {
			ui.error("群组名称不能超过30个字");
			v_form.name.focus();
			return false;
		} else if (v_form.cid0.value <= 0) {
			ui.error("请选择群组分类");
			v_form.cid0.focus();
			return false;
		} else if (getLength(v_form.intro.value) > 200) {
			ui.error("群组简介不能超过200个字");
			v_form.intro.focus();
			return false;
		} else if (getLength(v_form.tags.value.replace(/,/ig,'')) == 0) {
			ui.error("群组标签不能为空");
			v_form.tags.focus();
			return false;
		} else if (this.tag_num() >5) {
			ui.error("标签个数不能超过5个");
			return false;
		}else if (v_form.verify.value == '') {
			ui.error('请输入验证码');
			return false;
		}else if (v_form.verify.value == 1) {
			return true;
		}
		$.post(U('group/Index/code'),{verify:v_form.verify.value},function(data){	 	
			if(data == 1) {
				v_form.submit();
			}
	    	if(data == 0){
				ui.error("验证码错误，请重新输入");
            }
		});
		return false;	
	}
};
group_info = new group_info();