/**
 * Image preview script 
 *
 * powered by jQuery (http://www.jquery.com)
 * written by Alen Grakalic (http://cssglobe.com)
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 */
this.imagePreview = function() {	
	/* CONFIG */
	xOffset = 20;
	yOffset = 50;
	maxWidth = 400;
	maxHeight = 400;
	sHeight = $(document.body).height();
	sWidth = $(document.body).width();
		
	/* END CONFIG */
	$(".preview").hover(function (e) {
		this.t = this.title;
		var c = (this.t != "") ? "<br />" + this.t : "";
		$("body").append("<p id='preview' style='overflow:hidden;display:none;position:absolute;border:#ccc 1px solid;background:#333;padding:5px;color:#fff;display:none;'><img class='preview_image' src='"+ this.rel +"' alt='Image preview' onload=' if(this.width>=this.height && this.width>"+maxWidth+"){this.width="+maxWidth+"}else if(this.height>"+maxHeight+"){this.height="+maxHeight+";}' />"+ c +"</p>");

		iw = $("#preview").width();
		ih = $("#preview").height();

		if (iw <= maxWidth) {
			niw = iw;
		} else {
			niw = maxWidth;
		}
		nih = (ih * niw) / iw;

		if ((e.pageX + iw - sWidth) > 20) {
			oleft = (e.pageX - yOffset - niw);
		} else {
			oleft = (e.pageX + yOffset);
		}

		if ((e.pageY + nih - yOffset - sHeight) > 20) {
			otop = (sHeight - nih - yOffset);
		} else {
			otop = (e.pageY - yOffset - yOffset);
		}

		$("#preview")
			.css("top", otop + "px")
			.css("left", oleft + "px")
			.fadeIn("slow");
	},
	function () {
		$("#preview").remove();
    });	
	
	$(".preview").mousemove(function (e) {
		iw = $("#preview").width();
		ih = $("#preview").height();

		if (iw <= maxWidth) {
			niw = iw;
		} else {
			niw = maxWidth;
		}
		nih = (ih * niw) / iw;

		if ((e.pageX + iw - sWidth) > 20) {
			oleft = (e.pageX - yOffset - niw);
		} else {
			oleft = (e.pageX + yOffset);
		}

		if ((e.pageY + nih - yOffset - sHeight) > 20) {
			otop = (sHeight - nih - yOffset);
		} else {
			otop = (e.pageY - yOffset - yOffset);
		}

		$("#preview")
			.css("top", otop + "px")
			.css("left", oleft + "px")
			.fadeIn("slow");
	});			
};

// starting the script on page load
$(document).ready(function(){
	if(typeof(photo_preview) != 'undefined' && photo_preview==1){
		imagePreview();
	}
});