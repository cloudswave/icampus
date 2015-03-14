
//弹出创建相册窗口
function create_album(gid,isAddOption){
	ymPrompt.win({message:APP+'/Album/createAlbum/gid/'+gid,width:340,height:160,title:'创建相册',iframe:true})
}
//ajax执行创建相册操作
function doCreateAlbum(isAddOption){

	var	ran		=	Math.random();
	var name	=	document.create_album_form.name.value;
	var gid = document.create_album_form.gid.value;

	if(!name)	{ 
		alert('相册名字不能为空！');
		return false;
	}
	
	$.post(APP+'/Album/doCreateAlbum/gid/'+gid,{ajax:1,name:name,ran:ran},function(data){
	    if(data){
			//设置数据
			
			parent.setAlbumOption(data);
			//parent.window.location.reload();
			
			
			parent.ymPrompt.close();
			//parent.ymPrompt.succeedInfo('创建成功！');
		}else{
			parent.ymPrompt.close();
			parent.ymPrompt.errorInfo('创建失败！');
		}
	});
	return false;
}
//添加相册下拉菜单
function setAlbumOption(data){
	var obj	=	eval('(' + data + ')');
	if(!document.getElementById('albumlist')) window.location.reload();
	$('#albumlist').append('<option value="'+ obj.albumId +'" selected="selected" style="background:red;">'+ obj.albumName +'</option>');
}

//修改相册

function editAlbum(gid,albumId) {
	
	ymPrompt.win({message:APP+'/Album/editAlbum/gid/'+gid+'/albumId/'+albumId,width:340,height:160,title:'修改相册',iframe:true})
}
	//ajax执行创建相册操作
function doEditAlbum(){

	var	ran		=	Math.random();
	var name	=	document.edit_album_form.name.value;
	var albumId	=	document.edit_album_form.albumId.value;
	var gid = document.edit_album_form.gid.value;

	if(!name)	{ 
		alert('相册名字不能为空！');
		return false;
	}
	
	
	$.post(APP+'/Album/editAlbum/gid/'+gid+'/albumId/'+albumId,{doSubmit:1,name:name,ran:ran},function(data){
		
	    if(data){
			//设置数据
	
			parent.window.location.reload();
			
			
			//parent.ymPrompt.close();
			//parent.ymPrompt.succeedInfo('修改成功！');
		}else{
			parent.ymPrompt.close();
			parent.ymPrompt.errorInfo('修改失败！');
		}
	});
	return false;
}



//删除相册
function delAlbum(){
	ymPrompt.confirmInfo({message:'你确定要删除这个相册么？',handler:ajax_delete_album});
}
function ajax_delete_album(e){
	
	if(e=='ok'){
		$.post(APP+'/Album/delAlbum/gid/'+gid,{ajax:1,id:albumId},function(data){
			
			//alert(data);
			if(data==1){
				//设置数据
				parent.location.href = APP+'/Album/index/gid/'+gid;
			}else{
				ymPrompt.close();
				ymPrompt.errorInfo('删除失败！');
			}
		});
	}else{
		ymPrompt.close();
	}
	return false;
}
