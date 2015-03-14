// 说明 ：用 Javascript 实现锚点(Anchor)间平滑跳转
// 来源 ：ThickBox 2.1
// 整理 ：Yanfu Xie [xieyanfu@yahoo.com.cn]
// 网址 ：http://www.codebit.cn
// 日期 ：07.01.17

// 转换为数字
function intval(v)
{
    v = parseInt(v);
    return isNaN(v) ? 0 : v;
}

// 获取元素信息
function getPos(e)
{
    var l = 0;
    var t  = 0;
    var w = intval(e.style.width);
    var h = intval(e.style.height);
    var wb = e.offsetWidth;
    var hb = e.offsetHeight;
    while (e.offsetParent){
        l += e.offsetLeft + (e.currentStyle?intval(e.currentStyle.borderLeftWidth):0);
        t += e.offsetTop  + (e.currentStyle?intval(e.currentStyle.borderTopWidth):0);
        e = e.offsetParent;
    }
    l += e.offsetLeft + (e.currentStyle?intval(e.currentStyle.borderLeftWidth):0);
    t  += e.offsetTop  + (e.currentStyle?intval(e.currentStyle.borderTopWidth):0);
    return {x:l, y:t, w:w, h:h, wb:wb, hb:hb};
}

// 获取滚动条信息
function getScroll() 
{
    var t, l, w, h;
    
    if (document.documentElement && document.documentElement.scrollTop) {
        t = document.documentElement.scrollTop;
        l = document.documentElement.scrollLeft;
        w = document.documentElement.scrollWidth;
        h = document.documentElement.scrollHeight;
    } else if (document.body) {
        t = document.body.scrollTop;
        l = document.body.scrollLeft;
        w = document.body.scrollWidth;
        h = document.body.scrollHeight;
    }
    return { t: t, l: l, w: w, h: h };
}

// 锚点(Anchor)间平滑跳转
function scroller(el, duration)
{
   el = document.getElementById(el);

    var z = this;
    z.el = el;
    z.p = getPos(el);
    z.s = getScroll();
    z.clear = function(){window.clearInterval(z.timer);z.timer=null};
    z.t=(new Date).getTime();

    z.step = function(){
        var t = (new Date).getTime();
        var p = (t - z.t) / duration;
        if (t >= duration + z.t) {
            z.clear();
            window.setTimeout(function(){z.scroll(z.p.y, z.p.x)},13);
        } else {
            st = ((-Math.cos(p*Math.PI)/2) + 0.5) * (z.p.y-z.s.t) + z.s.t;
            sl = ((-Math.cos(p*Math.PI)/2) + 0.5) * (z.p.x-z.s.l) + z.s.l;
            z.scroll(st, sl);
        }
    };
    z.scroll = function (t, l){window.scrollTo(l, t)};
    z.timer = window.setInterval(function(){z.step();},13);
}


function selectItems(id){
    $('.gift_items').each(function(test){
        $(this).attr('class','gift_items');
    });
    $('.giftblock').each(function(){
        $(this).css('display','none');
    });
    $('#gifts'+id).css('display','block');
    $('#item'+id).attr('class','gift_items current');
}

function sendGift(id,price){
    var clickid = 'gift'+id;
    $('.gifts').each(function(){
        if($(this).attr('id')==clickid){
            $(this).attr('class','gifts hand on');
        }else{
            $(this).attr('class','gifts hand');
        }
    });
    var temp_gift = $('#gift'+id).clone();
    $('#gift_info').html('');
    $('#gift_id').html('');
    $('#gift_price').html('');
    $('#gift_info').append(temp_gift.html());
    $('#gift_id').val(id);
    $('#gift_price').val(price);
    scroller('sendto', 1000) 
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
M.addEventFns({
  submit_btn: {
    click: function(){
      if(!$('#gift_id').val()){
        ui.error('请选择礼物！');
        return false;
      }
      var si = $('#sendInfo').val();
      if(si.length > 200 ){
          ui.error('附加消息不能超过200个字符！');
          return false;
      }
      // 测试代码  下
      if(!($('#search_uids').val())){
          ui.error('请选择礼物发送对象！');
          return false;
      }
      // var gift_ID = $('#gift_id').val();
      // var gift_Price = $('#gift_price').val();
      // var money_Alias = $('#money_alias').val();
      // var money_Value = $('#money_value').val();
      // if(gift_Price-money_Value > 0){
      //   ui.error(money_Alias+'不足，不能赠送~~');
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
  event_post:{  //
    callback:function(txt){
      ui.success('赠送成功');
      setTimeout(function() {
        location.href = txt.data['jumpUrl'];
      }, 1500);
    }
  }

});