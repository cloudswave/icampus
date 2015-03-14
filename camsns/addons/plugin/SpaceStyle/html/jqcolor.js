/**
 * @author alan (代加工，不算原创！！)
 * QQ:8510001
 */
var ColorHex=new Array('00','33','66','99','CC','FF');
var SpColorHex=new Array('FF0000','00FF00','0000FF','FFFF00','00FFFF','FF00FF');
$(function(){
    initColor();
    $("#colorpanel").hide();
})

function initColor(){
    $("body").append('<div id="colorpanel" style="position: absolute; display: none;"></div>');
    var colorTable='';
    for(i=0;i<2;i++){
        for(j=0;j<6;j++){
            colorTable=colorTable+'<tr height=12>'
            colorTable=colorTable+'<td width=11 style="background-color:#000000">'
        
            if (i==0){
                colorTable=colorTable+'<td width=11 style="background-color:#'+ColorHex[j]+ColorHex[j]+ColorHex[j]+'">'
            }else{
                colorTable=colorTable+'<td width=11 style="background-color:#'+SpColorHex[j]+'">'
            } 

            colorTable=colorTable+'<td width=11 style="background-color:#000000">'
            for (k=0;k<3;k++){
                   for (l=0;l<6;l++){
                    colorTable=colorTable+'<td width=11 style="background-color:#'+ColorHex[k+i*3]+ColorHex[l]+ColorHex[j]+'">'
                   }
             }
        }
    }
    
    colorTable='<table width=253 border="0" cellspacing="0" cellpadding="0" style="border:1px #000000 solid;border-bottom:none;border-collapse: collapse" bordercolor="000000">'
               +'<tr height=30><td colspan=21 bgcolor=#cccccc>'
               +'<table cellpadding="0" cellspacing="1" border="0" style="border-collapse: collapse">'
               +'<tr><td width="3"><td><input type="text" id="DisColor" size="6" disabled style="border:solid 1px #000000;background-color:#ffff00"></td>'
               +'<td width="3"><td><input type="text" id="HexColor" size="7" style="border:inset 1px;font-family:Arial;" value="#000000"><a href=### id="_cclose">关闭</a></td></tr></table></td></table>'
               +'<table id="CT" border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse" bordercolor="000000"  style="cursor:pointer;">'
               +colorTable+'</table>';          
    $("#colorpanel").html(colorTable);
}

function showColorPanel(obj,txtobj){
    $('#'+obj).click(function(){
        //定位
        var ttop  = $(this).offset().top;     //控件的定位点高
        var thei  = $(this).height();  //控件本身的高
        var tleft = $(this).offset().left;    //控件的定位点宽
        
        $("#colorpanel").css({
            top:ttop+thei+5,
            left:tleft
        })        

        $("#colorpanel").show();
        
        $("#CT tr td").unbind("click").mouseover(function(){
            var aaa=$(this).css("background-color");
            $("#DisColor").css("background-color",aaa);
            $("#HexColor").val(aaa);
        }).click(function(){
            var aaa=$(this).css("background-color");
            $('#'+txtobj).val(aaa).css("color",aaa);
            $("#colorpanel").hide();
        })

        $("#_cclose").click(function(){$("#colorpanel").hide();}).css({"font-size":"12px","padding-left":"20px"})
    })
}

jQuery.extend({
    showcolor:function(btnid,txtid){showColorPanel(btnid,txtid);  }
})
