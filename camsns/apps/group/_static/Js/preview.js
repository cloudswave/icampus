/*
 * Image preview script 
 * powered by jQuery (http://www.jquery.com)
 * 
 * written by Alen Grakalic (http://cssglobe.com)
 * 
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 *
 */
 
this.imagePreview = function(){	
	/* CONFIG */
		
		xOffset		=	15;
		yOffset		=	30;
		maxWidth	=	400;
		maxHeight	=	500;
		sHeight		=	document.documentElement.clientHeight;
		sWidth		=	document.documentElement.clientWidth;
		
	/* END CONFIG */
	$(".preview").hover(function(e){
		this.t = this.title;
		var c = (this.t != "") ? "<br /><br />" + this.t : "";
		$("body").append("<p id='preview' style='display:none;position:absolute;border:#ccc 1px solid;background:#333;padding:5px;color:#fff;display:none;'><img class='preview_image' src='"+ this.rel +"' alt='Image preview' onload='if(this.width>="+maxWidth+")this.width="+maxWidth+"' />"+ c +"</p>");							 

		iw	=	$("#preview").width();
		ih	=	$("#preview").height();

		if(iw<=maxWidth){
			niw	=	iw;
		}else{
			niw	=	maxWidth;
		}
		nih	=	(ih*niw)/iw;

		if( (e.pageX+iw-sWidth) > 20 ){
			oleft	=	(e.pageX - yOffset - niw);
		}else{
			oleft	=	(e.pageX + yOffset);
		}

		if( (e.pageY+nih-yOffset-sHeight) > 20 ){
			otop	=	(sHeight - nih - yOffset);
		}else{
			otop	=	(e.pageY - yOffset - yOffset);
		}

		$("#preview")
			.css("top",otop + "px")
			.css("left",oleft + "px")
			//.fadeIn("fast");
    
	},
	function(){
		$("#preview").remove();
    });	
	
	$(".preview").mousemove(function(e){

		iw	=	$("#preview").width();
		ih	=	$("#preview").height();

		if(iw<=maxWidth){
			niw	=	iw;
		}else{
			niw	=	maxWidth;
		}
		nih	=	(ih*niw)/iw;

		if( (e.pageX+iw-sWidth) > 20 ){
			oleft	=	(e.pageX - yOffset - niw);
		}else{
			oleft	=	(e.pageX + yOffset);
		}

		if( (e.pageY+nih-yOffset-sHeight) > 20 ){
			otop	=	(sHeight - nih - yOffset);
		}else{
			otop	=	(e.pageY - yOffset - yOffset);
		}

		$("#preview")
			.css("top",otop + "px")
			.css("left",oleft + "px")
			.show();
	});			
};


// starting the script on page load
$(document).ready(function(){
	imagePreview();
});