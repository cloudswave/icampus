/*
  tinyMCE.init({
  theme : "advanced",
  mode : "textareas",
  language: "zh",
  dialog_type: "modal",
  theme_advanced_buttons1: "bold,italic,underline,|,justifyleft,justifyright,justifyfulla,|,bullist,numlist,|,cut,copy,paste,|,outdent,indent,|,link,unlink,|,image,html,fullscreen,preview",
  theme_advanced_buttons2: "formatselect,fontselect,fontsizeselect,styleselect,forecolor,backcolor",
  remove_script_host : false,
  auto_focus : "mce_editor_0",
  theme_advanced_buttons3:"",
  theme_advanced_toolbar_align : "left",
  theme_advanced_toolbar_location: "top",
  theme_advanced_statusbar_location : "bottom",
  onchange_callback: "autoSave",
  plugins: "bbcode,fullscreen,preview",
	theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle"

  });
  */
$(pageInit);
function pageInit()
{
	$('#content').xheditor(true,{tools:'simple',uploadUrl:"upload.php",uploadExt:"jpg,jpeg,gif,png"});
}


function getMessage(){
  ymPrompt.confirmInfo({
          icoCls:'',
          msgCls:'confirm',
          message:'请输入分类名：<br><input type=\'text\' id=\'newCategory\' onfocus=\'this.select()\' />',
          title:'新添加分类',
          height:150,
          handler:addCategory,
          autoClose:false
          });
}
function check(){
  if( $( '#content' ).val() == "" ){
    Alert( "请填写内容再提交" );
    return false;
  }
  if( JHshStrLen( $( '#title' ).val() ) >62 ){
    Alert( "标题不得大于30个中文字符" );
    return false;
  }
  return true;
}
$( document ).ready( function(){
    $( '#title' ).blur(function(){
      if( JHshStrLen( $( '#title' ).val())>62  ){
        Alert( "标题不得大于30个中文字符" );
      }
      })

    });
/**
 * addCategory 
 * 添加分类
 * @param category $category 
 * @access public
 * @return void
 */
function addCategory(tp){
    if( tp!="ok" ){
      return ymPrompt.close();
    }

    category = $( '#newCategory' ).val();
    if( category != "" ){
      $.post( APP+"/Index/addCategory",{name:category},function( txt ){
        if( txt == -1 ){
          Alert( '添加失败' );
          }else if( txt == -2 ){
          Alert( '分类名冲突' );
        }else{
          select = $( '#select' );
          select.append( select.children(':first').clone().val(txt).html(category).attr( 'selected',true ));
          ymPrompt.close();
        }
      })
    
    }else{
    Alert( '请输入分类名' );
  }
}

/**
 * changePrivacy 
 * 隐私按钮改变时
 * @param _this  $_this  
 * @access public
 * @return void
 */
function changePrivacy( _this ){
  if( 2 == _this.val() ){
    $( '#password' ).show();
  }else{
    $( '#password' ).hide();
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
  var content = $( '#content' ).val();
  var title = $( "input[name='title']" ).val( );
  var category = $( "select[name='category']" ).val();
  var privacy = $( "select[name='privacy']" ).val();
  var mention = $( "#ui_fri_ids" ).val();
  var password = $( "#password" ).val();

  //自动保存
  if( JHshStrLen( content )>0 && $( '#saveId' ).val() == ""){
    $.ajax( {
      type: 'POST',
      url: APP+'/Index/autosave',
      data:"title="+title+"&content="+content+"&category="+category+"&privacy="+privacy+"&password="+password+"&mention="+mention,
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
      }
      })
  }
  //修改自动保存的记录
  if ( $( '#saveId' ).val() != "" ){
    var updata = $( '#saveId' ).val();
    $.ajax( {
      type: 'POST',
      url: APP+'/Index/autosave',
      data:"title="+title+"&content="+content+"&category="+category+"&privacy="+privacy+"&password="+password+"&mention="+mention+"&updata="+updata,
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
      }
      })

  }
  $( '#saveButton' ).removeAttr( 'disabled');
}

