<?php 
return  array(
	'gift_send'   => array(
		'title' => '{actor}给您送了一个礼物',
		'body'  =>$img.'<br />'.$sendback.'<br /> <div class="quote"><p><span class="quoteR">'.$content.'</span></p></div><br /><a href="'.U('gift/Index/receivebox').'" target="_blank">去看看</a>',
	),
);
?>