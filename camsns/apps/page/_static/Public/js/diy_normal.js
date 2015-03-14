$(function(){
    var iebrws = document.all;
    var cssfixedsupport = !iebrws || iebrws && document.compatMode == "CSS1Compat" && window.XMLHttpRequest;
    var id = "openDiy";
    
    if (cssfixedsupport) {
        //$('#themeheader').css('margin-top', ($('#' + id).height() + 10) + 'px');
        $('#' + id).css('position', 'fixed').css('right', '120px').css('top','0px');
    }else{
		$('#' + id).css('position', 'absolute').css('right','120px').css('top','0px');
	}
    $('#' + id).css('z-index' , 1000);
    $(window).bind('scroll resize', function(e){
        if (!cssfixedsupport) {
            keepfixed(id);
        }
    });
	$('#'+id).show();
})

function keepfixed(id){
    //获得浏览器的窗口对象
    var $window = jQuery(window);
    //获得#topcontrol这个div的x轴坐标
    //获得#topcontrol这个div的y轴坐标
    var controly = $window.scrollTop();
    //随着滑动块的滑动#topcontrol这个div跟随着滑动
    $('#'+id).css({
        right: '120px',
        top: controly + 'px'
    });
}