//admin = {};
admin.bindTrOn = function(){};
//删除资讯
admin.deleteInfo = function()
{
	 var id = admin.getChecked();
     var title = L('PUBLIC_ACCONTMENT');
	 if(id==''){
    	ui.error( '请选择要删除的信息');
        return false;
	 }
    if(confirm( '确定要删除所选择的信息?' ))
    {
	   $.post(U('news/Admin/delNews'),{id:id},function(msg){
			admin.ajaxReload(msg);
   	 },'json');
    }
};
 