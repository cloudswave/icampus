M.addModelFns({

gotoDetail: {
	click:function(e){
		var event = "undefined" == typeof(e) ? window.event : e;
	 	var nodeName = event.srcElement ? event.srcElement.nodeName : event.target.nodeName;
	 	var args = M.getModelArgs(this);
	 	if(nodeName == 'A' || nodeName == "IMG"){
	 		return true;
	 	}else{
	 		location.href = U('public/Message/detail')+'&type='+args.type+'&id='+args.id;
	 		return false;
	 	}
	}
}

});
