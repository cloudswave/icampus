function deletePoster(id){
	if(confirm('确定要删除这份招贴吗？')){
		  $.post(U('poster/Index/doDeletePoster'),{id:id},function(result){
				switch(result){
					case "0":
						ui.error('删除失败');
						break;
					case "1":
						location.href=U('poster/Index/personal');
						break;
					case "-1":
						ui.error("没有权限删除这条信息");
						break;
					case "-2":
						ui.error("网络故障，此信息已被删除");
						break;
					case "-3":
						ui.error("参数错误。请刷新页面再试");
						location.reload();
						break;
				}
		   });
	}
}

function photo_size(name){
    $(name +" img").each(function(){

        var width = 500;
        var height = 500;
        var image = $(this);
        image.addClass('hand');
        image.bind('click',function(){
            window.open(image.attr('src'),"图片显示",'width='+image.width()+',height='+image.height());
        });
        if (image.width() > image.height()){
            if(image.width()>width){
                image.width(width);
                image.height(width/image.width()*image.height());
            }
        }
        else{
            if(image.height()>height){
                image.height(height);
                image.width(height/image.height()*image.width());
            }
        }


    });
}
$(function(){
	photo_size("#poster");
});

function check(type){
    var title = $('#title').val();
	if(type==true){
		if($(':radio[name=type][checked]').val() == null){
			ui.error("类别没有选择");
			return false;
		}
	}
	if(!title || getLength(title.replace(/\s+/g,"")) == 0){
		ui.error('请输入标题');
		$("#title").focus();
		return false;
	}
	if($('#title').val().length >30) {
		ui.error('标题字数不能超过30个字符');
		return false;
	}
	if($('#current').val() == ""){
		ui.error("请选择地区");
		return false;
	}
	if($('#deadline').val() && $('#deadline').val() <= currentDate()){
		ui.error("截止时间不能在当前时间之前");
		return false;
	}
	if(getEditorContent('explain') == '' || checkPostContent(getEditorContent('explain')) == 0){
		ui.error("详细介绍不能为空");
		$("#explain").focus();
		return false;
	}

	return true;
}
//过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}

function currentDate(){
	//获取当前时间
	   var myDate= new Date();
	   var Year = 0;
	   var Month = 0;
	   var Day = 0;
	   var Hour = 0;
	   var Minute = 0;
	   var Second = 0;
	   var currentDate = "";
	   //初始化时间
	   Year       = myDate.getFullYear();
	   Month      = myDate.getMonth()+1;
	   Day        = myDate.getDate();
	   Hour        = myDate.getHours();
	   Minute        = myDate.getMinutes();
	   Second        = myDate.getSeconds();

	   currentDate += Year + "-";
	   if (Month >= 10 )
	   {
	    currentDate += Month + "-";
	   }
	   else
	   {
	    currentDate += "0" + Month + "-";
	   }
	   if (Day >= 10 )
	   {
	    currentDate += Day + " ";
	   }
	   else
	   {
	    currentDate += "0" + Day + " ";
	   }
	   if (Hour >= 10 )
	   {
	    currentDate += Hour + ":";
	   }
	   else
	   {
	    currentDate += "0" + Hour + ":";
	   }
	   if (Minute >= 10 )
	   {
	    currentDate += Minute + ":";
	   }
	   else
	   {
	    currentDate += "0" + Minute + ":";
	   }
	   if (Second >= 10 )
	   {
	    currentDate += Second ;
	   }
	   else
	   {
	    currentDate += "0" + Second ;
	   }
	   return currentDate ;
}