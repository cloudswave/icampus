<?php
class VoteOptModel extends BaseModel
{
	var $tableName = "vote_opt";

	protected $fields	=   array(
        'id','vote_id','name','num',
		'_autoInc'	=>	true,
		'_pk'		=>	'id',
	);
    //字段类型
	protected $type	=	array(
		'id'			=>	'int(11)' ,
        'vote_id'	=>	'int(11)' ,
		'name'	    =>  'text' ,
		'num'		=>	'int(11)' ,
	);

} 
?>