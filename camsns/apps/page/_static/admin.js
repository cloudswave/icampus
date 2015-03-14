var diy = {};

diy.pageCheck = function(form) {
    if(getLength(form.page_name.value) < 1) {
        ui.error('名称不能为空');
        return false;
    }
    if(getLength(form.domain.value) < 1) {
        ui.error('域名不能为空');
        return false;
    }
    return true;
};
diy.addManager = function(id){
	var url = U('page/Admin/addManager')+'&id='+id;
	ui.box.load(url,'添加管理员');
};

diy.deletePage = function(id){
	if (id==null){
		id = admin.getChecked();
		if ( id == '' ){
			ui.error('请选择删除项');
			return false;
		}
	}
	$.post(U('page/Admin/doDeletePage'),{id:id},function (res){
		if(res>0){
			ui.success('删除成功');
			setTimeout(function (){
				location.reload();
				},1000);
		}else{
			ui.error('不能删除系统默认页面')
		}
	});
};
diy.deleteCanvas = function(id){
	if (id==null){
		id = admin.getChecked();
		if ( id == '' ){
			ui.error('请选择删除项');
			return false;
		}
	}
	$.post(U('page/Admin/doDeleteCanvas'),{id:id},function (res){
		if(res==1){
			location.reload();
		}
	});
}
diy.canvasCheck = function(form){
	if(getLength(form.title.value) < 1) {
        ui.error('名称不能为空');
        return false;
    }
    if(getLength(form.canvas_name.value) < 1) {
        ui.error('画布名称不能为空');
        return false;
    }
    if(getLength(form.data.value) < 1) {
        ui.error('画布内容不能为空');
        return false;
    }
    return true;
}