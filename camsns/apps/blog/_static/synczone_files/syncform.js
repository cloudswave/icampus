jQuery(function() {
	
	// show a simple loading indicator
	var loader = jQuery('<div id="loader"><img src="http://planbus.com/images/loading2.gif" border="0" /> Loading... </div>')
		.hide()
		.appendTo("#synczoneappsloader");
	jQuery().ajaxStart(function() {
		loader.show();
	}).ajaxStop(function() {
		loader.hide();
	});

	jQuery().ajaxError(function(a, b, e) {
		throw e;
	});

	jQuery.validator.setDefaults({
		debug: true
	});
	
	var vtwitter = jQuery("#formtwitter").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vtwitter.showErrors(result.data);
						vtwitter.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vbaidu = jQuery("#formbaidu").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vbaidu.showErrors(result.data);
						vbaidu.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vsohublog = jQuery("#formsohublog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vsohublog.showErrors(result.data);
						vsohublog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var v163blog = jQuery("#form163blog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						v163blog.showErrors(result.data);
						v163blog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var v51blog = jQuery("#form51blog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						v51blog.showErrors(result.data);
						v51blog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vchinablog = jQuery("#formchinablog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vchinablog.showErrors(result.data);
						vchinablog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vblogbus = jQuery("#formblogbus").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vblogbus.showErrors(result.data);
						vblogbus.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vbokee = jQuery("#formbokee").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vbokee.showErrors(result.data);
						vbokee.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vhexun = jQuery("#formhexunblog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vhexun.showErrors(result.data);
						vhexun.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vtianyablog = jQuery("#formtianyablog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vtianyablog.showErrors(result.data);
						vtianyablog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vycoolblog = jQuery("#formycoolblog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vycoolblog.showErrors(result.data);
						vycoolblog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
		
	var vyoutube = jQuery("#formyoutube").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vyoutube.showErrors(result.data);
						vyoutube.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vku6 = jQuery("#formku6").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vku6.showErrors(result.data);
						vku6.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var v56cn = jQuery("#form56cn").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						v56cn.showErrors(result.data);
						v56cn.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vsinavideo = jQuery("#formsinavideo").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vsinavideo.showErrors(result.data);
						vsinavideo.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var v6cn = jQuery("#form6cn").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						v6cn.showErrors(result.data);
						v6cn.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vflickr = jQuery("#formflickr").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vflickr.showErrors(result.data);
						vflickr.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vpoco = jQuery("#formpoco").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vpoco.showErrors(result.data);
						vpoco.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vyupoo = jQuery("#formyupoo").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vyupoo.showErrors(result.data);
						vyupoo.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vphotolog = jQuery("#formphotolog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vphotolog.showErrors(result.data);
						vphotolog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vbababian = jQuery("#formbababian").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vbababian.showErrors(result.data);
						vbababian.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	var vblog = jQuery("#formblog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vblog.showErrors(result.data);
						vblog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vlivespace = jQuery("#formlivespace").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vlivespace.showErrors(result.data);
						vlivespace.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vfanfou = jQuery("#formfanfou").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vfanfou.showErrors(result.data);
						vfanfou.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	var vsinablog = jQuery("#formsinablog").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vsinablog.showErrors(result.data);
						vsinablog.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	var vqzone = jQuery("#formqzone").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vqzone.showErrors(result.data);
						vqzone.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vyouku = jQuery("#formyouku").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vyouku.showErrors(result.data);
						vyouku.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});
	
	var vtudou = jQuery("#formtudou").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vtudou.showErrors(result.data);
						vtudou.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});


	
	var v56 = jQuery("#form56").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						v56.showErrors(result.data);
						v56.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	
	var vouou = jQuery("#formouou").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vouou.showErrors(result.data);
						vouou.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	
	var vzuosa = jQuery("#formzuosa").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vzuosa.showErrors(result.data);
						vzuosa.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	
	var vjiwai = jQuery("#formjiwai").validate({
		submitHandler: function(form) {
			jQuery(form).ajaxSubmit({
				dataType: "json",
				after: function(result) {
					if(result.status) {
						$("#remoteId").css("border","2px dotted red");
						$("#remoteId").mouseout( function() { $("#remoteId").css("border","1px solid #7C7C7C"); } );
						vjiwai.showErrors(result.data);
						vjiwai.focusInvalid();
					}
					if(result.success) {
						location.href="http://planbus.com/synczone.php/?&msg=success";
					}
				}
			});
		}
	});

	jQuery("#reset").click(function() {
		v.resetForm();
	});
});
