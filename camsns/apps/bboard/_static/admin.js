admin.checkAddTopic = function(form){
	if(getLength(form.content.value) < 1){
		ui.error('请输入内容。');
		return false;
	}
};


/**
 * 删除内容
 * @param integer weiba_id 微吧ID
 * @return void
 */
admin.delTopic = function(topic_id){
    if("undefined" == typeof(topic_id) || topic_id=='') topic_id = admin.getChecked();
    if(topic_id==''){
        ui.error('请选择要删除的内容');return false;
    }  
    if(confirm('确定要删除此内容吗？')){
        $.post(U('bboard/Admin/delTopic'),{topic_id:topic_id},function(msg){
            admin.ajaxReload(msg);
        },'json');
    }
};