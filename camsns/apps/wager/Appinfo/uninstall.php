<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(

);

foreach ($sql as $v)
	M('')->execute($v);