<?php
class VoteUserModel extends Model
{
	var $tableName = "vote_user";

	protected $fields	=   array(
        'id','vote_id','uid','opts','cTime','name','feedId',
		'_autoInc'	=>	true,
		'_pk'		=>	'id',
	);
    //字段类型
	protected $type	=	array(
		'id'			=>	'int(11)' ,
        'vote_id'	=>	'int(11)' ,
        'uid'	=>	'int(11)' ,
        'name'	=>	'varchar(32)' ,
		'opts'	    =>  'text' ,
		'cTime'		=>	'int(11)' ,
        'feedId' => 'int(11)',
	);

} 
?>
