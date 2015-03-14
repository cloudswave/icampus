function quoteSubmit() {
	var gid		=	parent.document.delform.gid.value;
	var tid		=	parent.document.delform.tid.value;
	var token	=	$("input[name=thinksns_html_token]").val();

	$.ajax({
		type: 'POST',
		url: APP+'/Topic/post/',
		data:"gid="+gid+"&tid="+tid+"&quote=1&content="+$('#content').val()+"&qid="+$('#qid').val()+"&thinksns_html_token="+token,
		success:function( result ){
			parent.window.location.reload();
			parent.ymPrompt.close();
		}
	})
}

function bq_show(){
  $(".phiz").show().mouseover(function(){
      $("#mini-coment").unbind("blur");
      }).mouseout(function(){
        $("#mini-coment").blur(function(){
          if(!$("#mini-coment").val()){
            $(".phiz").hide();
            $( ".jishuan" ).hide();
          }
          });
        });
}

function insertBQ(_this,bid){
    var emotion = $(_this).attr("emotion");

   var frm = window.frames["Editor"];
   var frm2= frm.window.frames["HtmlEditor"].document;


	 frm2.body.innerHTML += emotion ;
	$( '#smileylist' ).hide();
}


function editPost(id){
	ymPrompt.win({message:APP+'/Topic/editPost/gid/'+gid+'/pid/'+id,width:600,height:400,title:'修改',iframe:true})

}



function collect(gid,tid){
	ymPrompt.confirmInfo({message:'你确定收藏该话题？',width:320,height:200,handler:function(txt){
		if(txt == 'ok'){
			$.post(APP+'/Topic/collect',{gid:gid,tid:tid},function(txt){

			if(txt == '1'){
				ymPrompt.succeedInfo({message:'收藏成功',width:320,height:200,handler:null});
				window.location.reload();
			}else if(txt == '-1'){
				ymPrompt.errorInfo({message:'你已经收藏过！',width:320,height:200,handler:null});

			}else{
				ymPrompt.errorInfo({message:'操作失败',handler:null});
			}

		})
		}

	}  });

}


function cancel_collect(gid,tid){
	ymPrompt.confirmInfo({message:'你确定要取消收藏该话题？',width:320,height:200,handler:function(txt){
		if(txt == 'ok'){
			$.post(APP+'/Topic/cancel_collect',{gid:gid,tid:tid},function(txt){

			if(txt == '1'){
				ymPrompt.succeedInfo({message:'取消成功',width:320,height:200,handler:null});
				window.location.reload();

			}else{
				ymPrompt.errorInfo({message:'操作失败',handler:null});
			}

		})
		}

	}  });
}
