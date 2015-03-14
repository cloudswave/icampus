
var admin = {};

/**
 * 收缩展开某个DOM
 */
admin.fold = function(id){
  	$('#'+id).slideToggle('fast');
};

/**
 * 处理ajax返回数据之后的刷新操作
 */
admin.ajaxReload = function(obj,callback){
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

admin.getChecked = function() {
    var ids = new Array();
    $.each($('#list input:checked'), function(i, n){
        if($(n).val() !='0' && $(n).val()!='' ){
            ids.push( $(n).val() );    
        }
    });
    return ids;
};

admin.checkon = function(o){
    if( o.checked == true ){
        $(o).parents('tr').addClass('bg_on');
    }else{
        $(o).parents('tr').removeClass('bg_on');
    }
};

admin.checkAll = function(o){
    if( o.checked == true ){
        $('#list input[name="checkbox"]').attr('checked','true');
        $('tr[overstyle="on"]').addClass("bg_on"); 
    }else{
        $('#list input[name="checkbox"]').removeAttr('checked');
        $('tr[overstyle="on"]').removeClass("bg_on");
    }
};
//绑定tr上的on属性
admin.bindTrOn = function(){
    $("tr[overstyle='on']").hover(
      function () {
        $(this).addClass("bg_hover");
      },
      function () {
        $(this).removeClass("bg_hover");
      }
    );
};

admin.upload = function(type,obj){
    if("undefined"  != typeof(core.uploadFile)){
        core.uploadFile.filehash = new Array();
    }
    core.plugInit('uploadFile',obj,function(data){
        $('.input-content').remove();
        $('#show_'+type).html('<img src="'+UPLOAD_URL+'/'+data.save_path+data.save_name+'" width="100" height="100">');
        $('#form_'+type).val(data.attach_id);    
    },'image');
};

admin.delshop = function(shopid){
	$.post(U('shop/Admin/delshop'),{sid:shopid},function(data){
		if(data.status){
			admin.ajaxReload(data);
		}
	},'json');
}


admin.setget = function(cid){
	$.post(U('shop/Admin/setget'),{cid:cid},function(data){
			if(data.status){
				admin.ajaxReload(data);
			}
		},'json');
}

