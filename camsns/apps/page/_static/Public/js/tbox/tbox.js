(function($) {
	var tempEmbed;
	$.tbox = function(data) {
		$.tbox.init()
		$.tbox.loading()
		$.isFunction(data) ? data.call($) : $.tbox.reveal(data)
	}

	$.tbox.popup = function(url,title,hasbutton,fun,fun2) {
		//tempEmbed = $('embed').clone();
		$.tbox.init()
		$.tbox.loading()
		if (url.match(/#/)) {
			var urls    = url.split('#')[0];
			var target = url.replace(urls,'')
			$.tbox.reveal($(target).clone().show(),title,hasbutton);
		} else {
			$.get(url,{},function(data){
				$.tbox.reveal(data,title,hasbutton,fun2);
				if(fun){
					eval(fun+"()");
				}
			})
		}
	}
	$.tbox.popBox = function(data,title,hasbutton) {
		$.tbox.init()
		$.tbox.loading()
		$.tbox.reveal(data,title,hasbutton)
	}	
	
	$.tbox.html = function(data,title,hasbutton) {
		$.tbox.init()
		$.tbox.loading()
		$.tbox.reveal(data,title,hasbutton)
	}	

	$.tbox.settings = {
		tbox_html  : '\
		<div id="tbox" style="display:none;"> \
			<table id="tbx_table" class="tb_popup" align="center" border="0" border="0" cellpadding="0" cellspacing="0"><tr><td> \
			  <div class="tb_body"> \
				<div class="tb_header" style="display:none;"> \
				  <div class="tb_close"><a href="javascript:void(0)">x</a></div> \
				  <div class="tb_title">标题</div> \
				</div> \
				<div id="tb_content" class="tb_content"></div> \
				 <div class="tb_button_list" id="tb_content_list" style="display:none;"></div>\
			  </div> \
			</td></tr></table> \
		</div> \
		<div class="tb_background" style="display:none;"></div>'

	}

	$.tbox.init = function() {
		$("select").each(function(i,n){
			$(this).hide();						 
		});		
		$('embed').css('display','none');
		if ($.tbox.settings.inited) {
			return true
		} else {
			$.tbox.settings.inited = true
		}
		$('body').append($.tbox.settings.tbox_html);
	}

	$.tbox.loading = function() {

		$('.tb_background').css({
			height:	document.body.clientHeight + 100
		}).show()

		var pageScroll = $.tbox.getPageScroll()

		$('#tbox').css({
			top:	pageScroll[1]	+	($.tbox.getPageHeight() / 5),
			left:	pageScroll[0]	+	document.body.clientWidth/2 - $('#tbox').width()/2
		}).show()

	
		
		$('#tbox .tb_content').html('<div class="tb_loading">loading...</div>');
		$('#tbox .tb_button_list').hide();
	}

	//向tbox中填充数据
	$.tbox.reveal = function(data,title,hasbutton,fun) {
		$('#tbox .tb_content').empty().html(data).show()
		if(title!='') {
			$('#tbox .tb_title').html(title);
			$('#tbox .tb_header').show();
			$('#tbox .tb_close').click(function(){
				if(fun){
					fun.closed();
				}
					$.tbox.close();
			})

			if(hasbutton){
				hasbutton = hasbutton == true?{ok:"ok",no:"no"}:hasbutton;
				$.tbox.setButton(3,hasbutton);
			}
			$("#tbox").draggable({handle:".tb_header"});
		}
	}
	
	//执行关闭
	$.tbox.close = function() {
		$("select").each(function(i,n){
			$(this).show();						 
		});
		$('.tb_background').hide();
		$('embed').show();
		$('#tbox').hide();
		return false
	}

  // getPageScroll() by quirksmode.com
  $.tbox.getPageScroll = function() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft;
    }
    return new Array(xScroll,yScroll)
  }

  // adapter from getPageSize() by quirksmode.com
  $.tbox.getPageHeight = function() {
    var windowHeight
    if (self.innerHeight) {	// all except Explorer
      windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
      windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
      windowHeight = document.body.clientHeight;
    }
    return windowHeight
  }
  
  $.tbox.alert  = function(info,title,type,button){
	  $.tbox.init();
	  $.tbox.loading();
	  var alert_image_url;
				switch(type){
					case 1:
						alert_image_url = THEME+"/images/right_icon.gif";
						break;
					case 2:
						alert_image_url = THEME+"/images/wrong_icon.gif";
						break;
					case 3:
						alert_image_url = THEME+"/images/clew_icon.gif";
						$.tbox.setButton(type,button);
						break;
				}
	  
	  
	  var data = '<div class="tb_alert_info">\
				<p class="lh25">\
	  				<img src="'+alert_image_url+'" class="alM" id="tb_alert_info" >\
					<span id="tb_alert_info">'+info+'</span></p>\
	  </div>';
					
					
	  $('#tbox .tb_content').empty().html(data).show();
	  var title = title||Lang.tbox_info;
	  
	  $('#tbox .tb_title').html(title);
	  $('#tbox .tb_header').show();
			$('#tbox .tb_close').click(function(){
					$.tbox.close();
			})
  }
  
  $.tbox.setButton = function (type,functionName,input_type){
	  var button;
	  switch(type){
		  case 1:
			  button = '<p><input class="mr10" id="tb_button_yes" name="" type="button" value="确定" /></p>'
			  break;
		  case 2:
			  button = '<p><input class="mr10" id="tb_button_no" name="" type="button" value="确定" /></p>'
			  break;
		  case 3:
			  button = '<p><input class="mr10" id="tb_button_ok"  name="" type="button" value="确定" /><input class="mr10" name="" id="tb_button_no" type="button" value="取消"/></p>'
			  break;
	  }
			
	  $('#tbox .tb_button_list').show().html(button);

	  $('#tb_button_ok').click(function(){
		  $.tbox.close();
		  try{
			  if(input_type){
				  var value =$("#tb_input_"+input_type).val();
				  eval(functionName.ok+"('"+value+"')");
			  }else{
				  eval(functionName.ok+"()");
			  }

		  }catch(e){
		  }
	  })
	  
	  $('#tb_button_no').click(function(){
		  $.tbox.close();
		  try{
			  eval(functionName.no);
		  }catch(e){
		  
		  }
	  })
  }

  
  
  $.tbox.input = function (info,title,type,button){
	  $.tbox.init();
	  $.tbox.loading();
	  var input;
      var html;
	  switch(type){
	  	case "textarea":
	  		html = '<textarea id="tb_input_textarea"  cols="" rows=""></textarea>';
	  		break;
	  	case "text":
	  		html = '<input id="tb_input_text" value="">';
	  		break;
	  }
		input = '<div class="tb_input">\
				<div class="tb_input_info"> '+info+'</div>\
				<div class="tb_input_type">'+html+'</div>\
			<div class="C"></div>\
		</div>';
	  var title = title||Lang.tbox_input;
	  $('#tbox .tb_content').empty().html(input).show();
	  $('#tbox .tb_title').html(title);
	  $('#tbox .tb_header').show();
	  $.tbox.setButton(3,{ok:"ok",no:"no"},type);
  }
  
  
  
  $.tbox.text = function(info,title){
	  $.tbox.input(info,title,"text");
  }
  
  $.tbox.yes = function(info,title){
	  $.tbox.alert(info,title,1);
  }
  $.tbox.no = function(info,title){
	  $.tbox.alert(info,title,2);
  }
  $.tbox.confirm = function(info,title,button){
	  $.tbox.alert(info,title,3,button);
  }

})(jQuery);

function refreshTimeOut(){
	window.location.reload();
}