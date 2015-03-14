
//显示照片的exif信息
function exif(){
	alert(photo_id);
}

//设为头像
function setface(){
	ymPrompt.confirmInfo({message:'你要将这张照片设置为头像么？',handler:ajax_set_face});
}
function ajax_set_face(e){
	if(e=='ok'){
		$.post(APP+'/Manage/set_face',{ajax:1,photoId:photo_id,albumId:album_id},function(data){
			if(data){
				//设置数据
				ymPrompt.close();
				ymPrompt.succeedInfo('头像设置成功！');
			}else{
				ymPrompt.close();
				ymPrompt.errorInfo('头像设置失败！');
			}
		});
	}else{
		ymPrompt.close();
	}
	return false;
}

//将我的一张照片设置为该专辑的封面
function setcover(){
	ymPrompt.confirmInfo({message:'你要将这张照片设置为封面么？',handler:ajax_set_cover});
}
function ajax_set_cover(e){
	
	if(e=='ok'){
		
		$.post(APP+'/Photo/setCover/gid/'+gid,{ajax:1,photoId:photo_id,albumId:album_id},function(data){
			
			if(data==1){
				//设置数据
				ymPrompt.close();
				ymPrompt.succeedInfo('封面设置成功！');
			}else{
				ymPrompt.close();
				ymPrompt.errorInfo('封面设置失败！');
			}
		});
	}else{
		ymPrompt.close();
	}
	return false;
}

//编辑照片
function editphoto(){
	ymPrompt.win({message:APP+'/Photo/editPhoto/albumId/'+album_id+'/photoId/'+photo_id+'/gid/'+gid,width:340,height:160,title:'编辑照片',iframe:true})
}
function ajax_submit_update_photo(){

	var	ran		=	Math.random();
	var id		=	document.update_photo.photoId.value;
	var name	=	document.update_photo.name.value;
	var albumId	=	document.update_photo.albumId.value;
	var gid		=	document.update_photo.gid.value;
	if(!name)	{ 
		alert('照片名字不能为空！');
		return false;
	}
	
	$.post(APP+'/Photo/do_update_photo/gid/'+gid,{ajax:1,id:id,name:name,albumId:albumId,ran:ran},function(data){
		
	    if(data){
			//刷新页面
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

//删除单张照片
function delphoto(){
	ymPrompt.confirmInfo({message:'你确定要删除这张照片么？',handler:ajax_delete_photo});
}
function ajax_delete_photo(e){
	if(e=='ok'){
		
		$.post(APP+'/Photo/delPhoto/gid/'+gid,{ajax:1,id:photo_id,albumId:album_id},function(data){
			
			if(data==1){
				//设置数据
				parent.location.href = APP+'/Album/getAlbum/gid/'+gid+'/albumId/'+album_id;
				
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

//删除单张照片
function del(){
	ymPrompt.confirmInfo({message:'你确定要删除这张照片么？',handler:ajax_delete_photo});
}