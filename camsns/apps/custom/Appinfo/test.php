<?php
 echo 'html';
include_once  'camsns/core/core.php';
include_once  'camsns/core/OpenSociax/functions.inc.php';
 echo 'html';
$pc = D('User')->getUserList() ;
 echo $pc['html'];
