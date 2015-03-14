<?php
class plus_info extends iswaf {
	
	function info() {
		$conf = array();
		$conf = array('server'=>$_SERVER);
		$conf['writeable'] = self::create_file(iswaf_database.'keys/'.time(),'');
		$conf['database']  = iswaf_database;
		$conf['version']   = self::$version;
		return serialize($conf);
	}
	
}
?>