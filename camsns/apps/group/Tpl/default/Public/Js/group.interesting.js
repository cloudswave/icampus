var change_interesting_group = function(next_page,display_interesting_group){
	if(typeof(display_interesting_group) === 'undefined') {
		display_interesting_group = 'main';
	}
	var $interesting_group_list = $('#interesting_group_list');
	$.post(U('group/Index/interesting') + '&display=' + display_interesting_group + '&p=' + next_page,{},function(res){
		$interesting_group_list.html(res);
	});
};

var join_interesting_group = function(gid, o, interesting_now_page)
{
	joingroup(gid);	
	$(document).ready(function(){
		$('#tsbox').click(function(e){
			if ($(e.target).attr('name') == 'input') {
				ui.box.close();
				change_interesting_group(interesting_now_page);
			}
		});
	});
};