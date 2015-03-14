function getMessage() {
	var newCategory;
	var br="<br>";
	ui.box.load(U('blog/Index/addCategorys'), '添加分类');
}

function check(){
  if( $( '#title' ).val().length>30 ){
    alert( "标题不得大于30个中文字符" );
    return false;
  }
  if($('#select').val()=='0'){
	  alert('请选择分类');
	  return false;
  }
  return true;
}
$( document ).ready( function(){
    $( '#title' ).blur(function(){
      if( $( '#title' ).val().length>30  ){
        Alert( "标题不得大于30个中文字符" );
      }
      })

    });

    //判断是刷新还是关闭 
function   CloseOpen()   { 
    if(event.clientX <=0   &&   event.clientY <0){ 
    alert   ( "关闭 ") ;
    } 
} 
/**
 * addCategory
 * 添加分类
 * @param category $category
 * @access public
 * @return void
 */
function addCategory(tp){
    if( tp!="ok" ){
        var select = $( '#select' );
        select.children( ':first' ).attr( 'selected',true );
        return ui.box.close();
    }

    // 在发表日志时, 添加新分类
    var category = $('#newCategory').val();
    if($('#newCategory').val().length>10){
    	alert('分类名称不能大于10个中文字符');
        $("#newCategory").focus();
    	return false;
    }
    if(getLength(category.replace(/\s+/g,"")) == 0){
        alert('分类名不能为空');
        $("#newCategory").focus();
    	return false;
    }

	if( category != "" ){
    $.post( U('blog/Index/addCategory'),{name:category},function(txt){
        ui.box.close();

    	if( -1 == txt ){
        	ui.error( '添加失败' );
        }else if(  -2  == txt){
        	ui.error( '分类名冲突' );
        }else if( -3 == txt  ){
        	ui.error( '添加失败，分类名不能为空' );
        }else{
          $.post(U('blog/Index/filterCategory'), {name:category}, function(msg) {
        	  select = $( '#group' );
          	select.before( select.children(':first').clone().val(txt).html(msg));
          	$("#select option[value='"+txt+"']").attr( 'selected',true );
          	return;
          });
        }
  });
	}else{
		alert( '请输入分类名' );
        $("#newCategory").focus();
        return false;
	}

    var select = $( '#select' );
    select.children( ':first' ).attr( 'selected',true );
    return false;
}

/**
 * changePrivacy
 * 隐私按钮改变时
 * @param _this  $_this
 * @access public
 * @return void
 */
function changePrivacy( _this ){
  if( 3 == _this.val() ){
    $( '#password' ).show();
  }else{
    $( '#password' ).hide();
  }
}

function changeCategory( _this ){
	if( 0 == _this.val()){
		getMessage();
	}
}

/**
 * autosave
 * 自动保存
 * @param inst $inst
 * @param time  $time
 * @access public
 * @return void
 */
function autosave(){
  if( $( '#saveButton' ).attr( 'disabled' ) == "true" ){
    return;
  }
  $( '#saveButton' ).attr( 'disabled',true );
  //TODO 更换编辑器，这里必须修改；
  var content  = KE.util.getData('content');
  var title    = $( "input[name='title']" ).val( );
  var category = $( "select[name='category']" ).val();
  var privacy  = $( "select[name='privacy']" ).val();
  var mention  = $( "#ui_fri_ids" ).val();
  var password = $( "#password" ).val();
  var cc     = $( "#cc" ).val();
  //自动保存
  if( content.length>0 && $( '#saveId' ).val() == ""){
    //buttonclass = $( '#saveButton' ).attr( 'class' );
    //$( '#saveButton' ).attr( 'class','btn_w' );
    $.ajax( {
      type: 'POST',
      url: APP+'/Index/autosave',
      data:"title="+title+"&content="+content+"&category="+category+"&privacy="+privacy+"&password="+password+"&mention="+mention+"&cc="+cc,
      success:function( result ){
        if( result != -1 ){
          string = result.split( ',' );
          $( '#autoSave' ).html( title+" 已于"+string[0]+"保存在草稿箱" );
          $( '#saveId' ).val( string[1] );
          $( '#autoSave' ).fadeIn('slow');
              setTimeout( function(){
                $( '#autoSave' ).fadeOut( 'slow' );
                },3000 );
        }
        $( '#saveButton' ).removeAttr( 'disabled');
        //$( '#saveButton' ).attr( 'class',butonclass );
      }
      })
  }else{
	  $( '#saveButton' ).removeAttr( 'disabled');
    if($( '#saveId' ).val() == "") return false;
  }
  //修改自动保存的记录
  if ( $( '#saveId' ).val() != "" ){

    //buttonclass = $( '#saveButton' ).attr( 'class' );
    //$( '#saveButton' ).attr( 'class','btn_w' );
    var updata = $( '#saveId' ).val();
    $.ajax( {
      type: 'POST',
      url: APP+'/Index/autosave',
      data:"title="+title+"&content="+content+"&category="+category+"&privacy="+privacy+"&password="+password+"&mention="+mention+"&cc="+cc+"&updata="+updata,
      success:function( result ){
        if( result != -1 ){
          string = result.split( ',' );
          $( '#autoSave' ).html( title+" 已于"+string[0]+"保存在草稿箱" );
          $( '#saveId' ).val( string[1] );
          $( '#autoSave' ).fadeIn('slow');
              setTimeout( function(){
                $( '#autoSave' ).fadeOut( 'slow' );
                },3000 );
          $( '#saveButton' ).removeAttr( 'disabled');
        }
       // $( '#saveButton' ).removeAttr( 'disabled');
        //$( '#saveButton' ).attr( 'class',butonclass );
      }
      })

  }
  $( '#saveButton' ).removeAttr( 'disabled');
  return true;
}



