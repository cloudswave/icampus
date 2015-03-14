<?php
class plus_webserver extends iswaf {
	
	function webserver($conf = array()) {
		if(self::filext($_SERVER['SCRIPT_FILENAME'])!=='php') {
			$array = array('key'=>'server','value'=>$_SERVER['REQUEST_URI'],'hash'=>substr(md5($_SERVER['SCRIPT_FILENAME']),10,8));
			self::addlog($key,$array);
			self::deny('webserver_fixer');
		}	
	}
}
?>