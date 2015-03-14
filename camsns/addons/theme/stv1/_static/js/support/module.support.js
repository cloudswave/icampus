(function(){

M.addModelFns({

	support_feedback_form: {
	callback: function( txt ) {
		ui.success( txt.info );
		ui.box.close();
	}
}

}).addEventFns({


invite_addemail:{
	click: function() {
		var input1 = document.getElementById("email_input").value,
			$email_input = $("#email_input"),
			dInput = this.parentModel.childEvents["email"][0],
			dInputClone = dInput.cloneNode( true );

		dInputClone.value = "";
		$email_input.append( dInputClone );
		M( dInputClone );

		return false;
	}
}

});

})();