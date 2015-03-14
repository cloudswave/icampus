//获取选中的ID
function getSelectValues() {
	  id = [];
	  $("input[type='checkbox']:checked").each(function(){
		  id.push($(this).val());
	  });
	  return id.join(',');
}
//选反选
function checkall(strSection,thisabout){
	var i;
	var	colInputs = document.getElementById(strSection).getElementsByTagName("input");
	for	(i=0; i < colInputs.length; i++)
	{
		if(thisabout){
			colInputs[i].checked=true;
		}else{
			colInputs[i].checked=false;
		}
	}
}
//删除DOM元素
function remove_list(values){
	var i;
	var ids	=	values.split(',');
	for	(i in ids)
	{
		$('#list_'+ids[i]).remove();
	}
}
//删除图片
function del_photo(id,URL){
	var keyValue;
	if (id!=0)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{	
		$.post(URL+"/delete_photo",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('删除失败！');
			}
		});	
	}
	return true;
}
//删除专辑
function del_album(id,URL){
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{	
		$.post(URL+"/delete_album",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('删除失败！');
			}
		});	
	}
	return true;
}
//清空图片
function clean_photo(id,URL){
	var keyValue;
	if (id!=0)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{	
		$.post(URL+"/clean_photo",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('删除失败！');
			}
		});		
	}
	return true;
}
//清空专辑
function clean_album(id,URL){
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{	
		$.post(URL+"/clean_album",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('删除失败！');
			}
		});	
	}
	return true;
}
//还原图片
function restore_photo(id,URL){
	var keyValue;
	if (id!=0)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要还原选择项吗？'))
	{	
		$.post(URL+"/restore_photo",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('还原失败！');
			}
		});		
	}
	return true;
}
//还原图片
function restore_album(id,URL){
	var keyValue;
	if (id!=0)
	{
		keyValue = id;
	}else {
		keyValue = getSelectValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要还原选择项吗？'))
	{	
		$.post(URL+"/restore_album",{ajax:1,id:keyValue},function(data){
			if(data){
				//成功
				remove_list(keyValue);
			}else{
				//失败
				alert('还原失败！');
			}
		});		
	}
	return true;
}