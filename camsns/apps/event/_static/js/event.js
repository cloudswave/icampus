function EventAction( id,allow,action ){
  $.post( U('event/Index/doAction'),{id:id,allow:allow,action:action},function( text ){
      if( text == 1 ){
        if( allow == 1 ){
        	ui.success( '申请成功，该活动需要发起人审核，请耐心等待...' );
        }else{
        	ui.success( '操作成功' );
        }
      	if( action == 'joinIn' ){
      		if( allow == 1 ){
      			$('.list_joinIn_'+id).html('<a href="javascript:EventDelAction( '+id+','+allow+',\'joinIn\' )">取消申请</a>');
      			$('.detail_joinIn_'+id).html(
      					'<span class="cGreen lh35">已提交申请,等待审核中,'+
      					'<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+','+allow+',\'joinIn\' )">取消申请</button>'
      			);
      		}else{
      			$('.list_joinIn_'+id).html('<a href="javascript:EventDelAction( '+id+',null,\'joinIn\' )">取消参加</a>');
      			$('.detail_joinIn_'+id).html('<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+',null,\'joinIn\' )">取消参加</button>'
            );
      		}
      	}
        if( action == 'attention' ){
      		$('.list_attention_'+id).html('<a href="javascript:EventDelAction( '+id+',null,\'attention\')">取消关注</a>');
      		$('.detail_attention_'+id).html('<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+',null,\'attention\')">取消关注</button>'
          );
          $("span").remove(".old_detail_attention");
        }
      }
      if( text == -2 ){
        if( allow == 1 ){
            ui.error( '已经提交申请，请不要重复申请' );
        }else{
            ui.error( '操作已经执行，请不要重复操作' );
        }
      }
      if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
          location.reload();
      }
      if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }
      if(text == -5){
        ui.error('参加活动人数已满，不能再参加');
      }
      if(text == -6){
        ui.error('活动已结束');
        setTimeout(location.reload(),1500);
      }
  });
}

function EventDelAction( id,allow,action ){
  $.post( U('event/Index/doDelAction'),{id:id,allow:allow,action:action},function( text ){
      if( text == 1 ){
        if( allow == 1 ){
        	ui.success( '撤销申请成功' );
        }else{
        	ui.success( '操作成功' );
        }
    	if( action == 'joinIn' ){
    		$('.list_joinIn_'+id).html(
    				'<a href="javascript:EventAction( '+id+','+allow+',\'joinIn\' )">我要参加</a>'+
    				'<span class="list_attention_'+id+'">'+
    				'<a href="javascript:EventAction( '+id+',null,\'attention\')">我要关注</a>'+
                    '</span>'
    		);
    		$('.detail_joinIn_'+id).html(
    				'<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+','+allow+',\'joinIn\' )">我要参加</button>'+
    				'<span class="detail_attention_'+id+'">'+
    				'<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+',null,\'attention\')">我要关注</button>'
    		);
    	}else if( action == 'attention' ){
        	$('.list_attention_'+id).html('<a href="javascript:EventAction( '+id+',null,\'attention\')">我要关注</a>');
        	$('.detail_attention_'+id).html('<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+',null,\'attention\')">我要关注</button>'
            );
          $("span").remove(".old_detail_attention");
    	}
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
    	  location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
    	  location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });
}

function agree( id,eventId,uid ,join_uid){
  $.post( U('event/Index/doAgreeAction'),{id:id,eventId:eventId,uid:uid,join_uid:join_uid},function( text ){
      if( text == -5){
        ui.error('参加活动人数已满，不能再参加');
      }else if( text == 1 ){
    	  ui.success( '操作成功' );
        location.reload();
      }else if( text == -3 ){
    	  ui.error( '未知错误' );
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
        location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
        location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });
}

function adminDelAction( id,uid,action,opts ){
  $.post( U('event/Index/doAdminAction'),{eventId:id,uid:uid,action:action,admin:'user',opts:opts},function( text ){
      if( text == 1 ){
    	  ui.success( '操作成功' );
        location.reload();
      }else if( text == -3 ){
    	  ui.error( '未知错误' );
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
        location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
        location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });

}

function endEvent( id ){
	if(confirm('是否提前结束此活动?')){
		$.post( U('event/Index/doEndAction'),{id:id},function( text ){
            if( text == 1 ){
              $("span").remove(".old_detail_attention");
              ui.success('提前结束活动成功');
              $('#event_satus_' + id).html('活动结束');//活动列表
              $('#event_satus').html('此活动已经结束');//活动详细页
              $('#event_edit_button').html('');//活动详细页
              $('#share_button').html('');
            }else if( text == -1 ){
              ui.error( '非法访问' );
            }else if( text == 0 ){
              ui.error( '结束活动失败。请稍后再试' );
            }else{
              ui.error( '未知错误' );
            }
        });
	}
}

function delEvent(eventId,jump){
    var jump = jump==true?true:false;
	if(confirm('确认删除此活动?')){
		$.post( U('event/Index/doDeleteEvent'),{id:eventId},function( text ){
            if( text == 1 ){
              ui.success('删除活动成功');
              if(jump == true){
            	  location.href=U('event/Index/personal');
              }else{
            	  $('#event_'+eventId).remove();
              }
            }else if( text == 0 ){
              ui.error( '删除活动失败！' );
            }else{
              ui.error( '未知错误，请稍后再试' );
            }
        });
	}
}

function removeHTMLTag(str) {
    str = str.replace(/<\/?[^>]*>/g,'');
    str = str.replace(/[ | ]*\n/g,'\n');
    str=str.replace(/&nbsp;/ig,'');
    return str;
}

var selectArea = function(){
    var typevalue = $("#current").val();
    ui.box.load(U('event/Area/area')+'&selected='+typevalue,'选择城市');
}

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

var ShareEvent = function(eventId){
    if("undefined" == typeof(eventId)){
      return false;
    }
    $.post(U('event/Index/ShareEvent'),{eventId:eventId},function(msg){
      if(msg == -6){
        ui.error('活动已结束,分享失败');
        setTimeout(location.reload(),1500);
      }else if(msg==1){
        ui.success('分享成功');
      }else{
        ui.success('分享失败');
      }
    });
};

function JSTime(time){//格式  2013-04-07 14:22:00
  var timeY = time.substring(0, 10).split('-');
      time1 = timeY[0]+ timeY[1]+ timeY[2];
  var timeH =  time.substring(11, 19).split(':');
      time2 = timeH[0]+ timeH[1]+ timeH[2];
  return time1+time2;
}
M.addEventFns({
  submit_btn: {
    click: function(){
      E.sync();
    // 当前时间
      var myDate = new Date();
      var month = myDate.getMonth()+1;
      if(month < 10){month = '0'+month;}
      if(myDate.getDate() < 10){ date = '0' + myDate.getDate();}else{date = myDate.getDate();}
      if(myDate.getHours() < 10){ hour = '0' + myDate.getHours();}else{hour = myDate.getHours();}
      if(myDate.getMinutes() < 10){ minu = '0' + myDate.getMinutes();}else{minu = myDate.getMinutes();}
      if(myDate.getSeconds() < 10){ sec = '0' + myDate.getSeconds();}else{sec = myDate.getSeconds();}

      var ctime = myDate.getFullYear()+'-'+month +'-'+date+' '+hour+':'+minu+':'+sec;
      if($('#title').val()== '' || 　getLength($('#title').val()) < 1){
        ui.error('活动名称不能为空');return false;
      }
      if($('#address').val()== '' || 　getLength($('#address').val()) < 1){
        ui.error('活动地点不能为空');return false;
      }
      if($('#type').val()== 0){
        ui.error('请选择活动分类');return false;
      }
      if($('#sTime').val() == ''){
        ui.error('活动开始时间不能为空');return false;
      }
      if($('#eTime').val() == ''){
        ui.error('请选择活动结束时间');return false;
      }
      var sTime = JSTime($('#sTime').val());
      var eTime = JSTime($('#eTime').val());
      if( sTime > eTime ){
        ui.error('活动结束时间不能早于活动开始时间');return false;
      }
      var ctime = JSTime(ctime);
      var deadline = JSTime($('#deadline').val());
      if( ctime > deadline){
        ui.error('报名截止时间不得早于当前时间');return false;
      }  
      if(deadline > eTime){
        ui.error('报名截止时间不能晚于活动结束时间');return false;
      }
      E.sync();//提交时编辑器需要先执行的方法
      if($('#explain').val()== '' && getLength($('#explain').val()) < 1){
        ui.error('活动介绍不能为空');return false;
      }

      // if(getLength($('#explain').val()) < 10){
      //   ui.error('活动介绍不得小于20个字符');return false;
      // }

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
  }

});

M.addModelFns({
  event_post:{  //发布帖子
    callback:function(txt){
      ui.success('发布成功');
      setTimeout(function() {
        location.href = U('event/Index/eventDetail')+'&id='+txt.data['id']+'&uid='+txt.data['uid'];
      }, 1500);
    }
  },
  event_edit:{  //编辑帖子
    callback:function(txt){
      ui.success('编辑成功');
      setTimeout(function() {
        location.href = U('event/Index/eventDetail')+'&id='+txt.data['id']+'&uid='+txt.data['uid'];
      }, 1500);
    }
  }

});