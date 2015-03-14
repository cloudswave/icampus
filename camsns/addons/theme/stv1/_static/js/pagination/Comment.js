function replaceComment( vo ){
  var quietly = "";
  if( vo.quietly == 1 ){
    quietly = "<font color=\"red\"><b>[悄悄话]</b></font>";
  }
  sub = "<div class=\"subcomment\"></div>";
  var result = "\
            <li class=\"comlist\" id=\'comm"+vo.id+"\'>\
            <div class=\"left\" style=\"width: 65px;\"><span class=\"headpic50\"><a href=\"#\"><img src=\""+vo.face+"\" /></a></span></div>\
            <div style=\"margin-left: 65px;\">\
            <div style=\"padding-bottom: 20px;\"><h3 class=\"tit_Critique lh25 mb5\"><span class=\"right f12px mr5\"><span><a  href=\"javascript:replay( \'"+vo.name+"\',"+vo.id+" )\">回复</a></span><span class=\"ml5\"><a href=\"###\" onclick=\"deleteComment( "+vo.id+","+vo.appid+" )\">删除</a></span></span><a href=\"#\">"+vo.name+"</a>   <em class=\"cGray2\">"+vo.cTime+"</em>"+quietly+"</h3>\
              <p>"+vo.comment+"</p></div>"+sub+"</div></li>\
  "
  return result;
}



function subComment( vo ){
 var result = "\
<div class=\"sublist pt5 clear\" id=\"comm"+vo.id+"\">\
                  	<div style=\"width: 50px;\" class=\"left\"><span class=\"pic38\"><a href=\"#\"><img src=\""+vo.face+"\"/></a></span></div>\
          			<div style=\"margin-left:50px;\">\
                      <h3 class=\"tit_Critique lh20 mb5\"><span class=\"right f12px mr5\"><a href=\"###\" onclick=\"deleteComment( "+vo.id+","+vo.appid+" )\">删除</a></span><a href=\"#\">"+vo.name+"</a>  <em class=\"cGray2\">"+vo.cTime+"</em> </h3>\
                      <p>"+vo.comment+"</p>\
   			  		</div>\
                  </div>\
  "
  return result;
}


function deleteComment( id,appid ){
    Confirm({message:'是否删除此评论',handler:function(button){
      if ( 'ok' == button ){
            $.post( U("home/Comment/doDeleteComment/"),{id:id,appid:appid},function( result ){
                if( result != -1 ){
                  $( '#comm'+id ).hide("slow");
                  try{
                    deleteCommentCount( appid );
                  }catch( err ){
                    
                  }
                }else{
                  Alert( '删除失败' );
                  return;
                }
            });
      }
    }});
}

function pageselectCallback(page_id, jq){
  //ajax获取评论json数据
  var page = page_id+1; //鼠标按的页数
  $( '#comment >ul' ).html("");
  $( '#loadding' ).html( "<img src= \""+_THEME_+"/images/logging.gif\">" );
  $( '#comment > ul' ).load( U("home/Comment/getComment",["p="+page]),{type:type,id:appid,mid:mid},function(){
        $( '#loadding' ).html( '' );
      
      });

  $('#Searchresult').text("当前显示"+((page_id*10)+1)+"-"+((page_id*10)+10));
}

    //function bq_show(){
      //  $("#smileylist").toggle();
    //}
    
    function bq_show(){
        $("#smileylist").toggle().mouseover(function(){
             $("#content").unbind("blur");
        }).mouseout(function(){
           $("#content").blur(function(){
               $("#smileylist").hide();
           });
        });
    }

    function insertBQ(_this,bid){
        var emotion = $(_this).attr("emotion");
        var old_con = $("#content").val();
        var new_con = old_con+emotion;
        $("#content").val(new_con);
        $( '#smileylist' ).hide();
    }


/**
 * deleteMouse 
 * 鼠标移动效果
 * @access public
 * @return void
 */
function deleteMouse(){
              $( '.sublist' ).bind( "mouseover",function(){
                  var id = $( this ).attr( 'id' );
                  $( '#d-'+id ).show();
                });

              $( '.sublist' ).bind( "mouseout",function(){
                  var id = $( this ).attr( 'id' );
                  $( '#d-'+id ).hide();
                });
              $( '.comlist' ).bind( "mouseover",function(){
                  var id = $( this ).attr( 'id' );
                  $( '#d-'+id ).show();
                });

              $( '.comlist' ).bind( "mouseout",function(){
                  var id = $( this ).attr( 'id' );
                  $( '#d-'+id ).hide();
                });
}

var obj_tmp="";
$(document).ready(function(){
    getComments();

   // $('#content').focus(function(){

    //});
	//$('#content').blur(function(){
//		$(document).unbind('keydown',"ctrl+1",function(evt){});
//	});
});

function getComments(){

    obj_tmp = $( '#replay2' ).clone();
    $( '#replay2' ).remove();

    $( '#loadding' ).html( "<img src= \""+_THEME_+"/images/logging.gif\">" );
    //加载初始化的数据
    $( '#comment > ul' ).load( U("home/Comment/getComment"),{type:type,id:appid,mid:mid},function( result ){
      $( '#loadding' ).html( "" );
        $.post( U("home/Comment/getCount"),{type:type,id:appid,mid:mid},function( count ){
          if( count != -1 && count >100 ){
            var data_count = count;
            var items_per_page = 10; //每页显示多少条数据
            // Create pagination element
            $("#Pagination").pagination(data_count, {
              num_edge_entries: 2,
              num_display_entries: 8,
              items_per_page:items_per_page,
              next_text:next_text,
              prev_text:prev_text,
              link_to:"#comment",
              callback: pageselectCallback
            });
          }
        });

    });

}

/**
 * replay 
 * 回复某人
 * @param uid $uid 
 * @param mini_id  $mini_id  
 * @access public
 * @return void
 */
function replay( name,toId ){
  $( '#replay2' ).remove();
  if( $( '#comm'+toId+"> div >.subcomment" ).find( 'input' ).html() == null ){
    $( '#comm'+toId+"> div >.subcomment" ).append( obj_tmp );
    $( '#replay2' ).show();
  }

  var obj = $( '#content2' );

  $( '#toId' ).val(toId);
  obj.focus();
}

function cancel( _this ){
  $( '#replay2' ).remove();
}

/**
 * addComment 
 * 添加评论
 * @access public
 * @return void
 */
function addComment(_this){
  var Abottom = _this.val();
  var quietly = $( '#quietly' ).is( ":checked" )?1:0;
  var toId    = 0;
  var content = $( '#content' ).val();
  _this.val( 'loadding...' );
  _this.attr( 'disabled',true );

  //检查字数
  if(  content.length >= 4000 ) {
  Alert( "最多2000个中文字符" );
  _this.attr( 'disabled',false );
  _this.val( Abottom );
  return;
  }
  if(  content.length < 1  ){
    Alert( "必须填写评论内容" );
  _this.removeAttr( 'disabled');
  _this.val( Abottom );
  return;
  }
  $.post( U("home/Comment/doAddComment"),{comment:content,type:type,appid:appid,toId:toId,quietly:quietly,filed:filed},function( txt ){
      if( txt != -1 ){
          var a = new Array();
          $.each( eval('('+txt+')'),function( key,value ){
              a[key] = value;
            });
          $( '#comment > ul' ).prepend( replaceComment(a) );
          $( '#content' ).val( "" );
          
          _this.val( Abottom );
          _this.removeAttr( 'disabled');
          
          //发送动态回调函数
          try{
        	commentSuccess(txt);
          }catch(err){
        	  
          }
          
      }else{
        Alert( '添加失败，请稍后再试' );
          _this.val( Abottom );
          _this.removeAttr( 'disabled');
      }
      });
  
}



/**
 * addComment 
 * 添加评论
 * @access public
 * @return void
 */
function ReplayComment(_this){
  var Abottom = _this.val();
  var quietly = $( '#quietly2' ).is( ":checked" )?1:0;
  var toId    = $( '#toId' ).val();
  var content = $( '#content2' ).val();
  _this.val( 'loadding...' );
  _this.attr( 'disabled',true );

  //检查字数
  if( JHshStrLen( content ) >= 4000 ){
Alert( "最多2000个中文字符" );
  _this.attr( 'disabled',false );
  _this.val( Abottom );
return
  }
  if( content.length <3 ){
    Alert( "最少10个字符" );
          _this.removeAttr( 'disabled');
  _this.val( Abottom );
  return
  }
  $.post(U("home/Comment/doAddComment"),{comment:content,type:type,appid:appid,toId:toId,quietly:quietly,filed:filed},function( txt ){
      if( txt != -1 ){
          var a = new Array();
          $.each(eval('('+txt+')'),function( key,value ){
              a[key] = value;
            });
          $( '#replay2' ).before( subComment(a) );
          $( '#content2' ).val( "" );
          $( '#replay2' ).remove();
          _this.val( Abottom );
          _this.removeAttr( 'disabled');
          //发送动态回调函数
          try{
        	commentSuccess(txt);
          }catch(err){
        	  
          }
      }else{
        Alert( '添加失败，请稍后再试' );
          _this.val( Abottom );
          _this.removeAttr( 'disabled');
      }

      });
}
