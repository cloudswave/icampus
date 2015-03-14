
function doSwitch(){
	id = $('#select_group').val();
	name = $('#select_group').find('option:selected').text();
	$('#group_name').hide();
	$('#group_name').html('<a href="/apps/group/index.php?s=/Group/index/gid/'+id+'>'+name+'</a> &gt; 发表话题');

	$("#group_name").slideDown("slow",function(){

   });

}