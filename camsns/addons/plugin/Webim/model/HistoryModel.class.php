<?php

class HistoryModel extends Model {

	protected $tableName = 'webim_histories';
	protected $fields = array(
			0 => 'id',
			1 => 'send',
			2 => 'type',
			3 => 'to',
			4 => 'from',
			5 => 'nick',
			6 => 'body',
			7 => 'style',
			8 => 'timestamp',
			9 => 'todel',
			10 => 'fromdel',
			11 => 'created_at',
			12 => 'updated_at',
			'_autoinc' => true,
			'_pk' => 'id' 
	);

	public function get($uid, $with, $type='chat', $limit=30) {
		if( $type == "chat" ) {
			$where = "`type` = 'chat' AND ((`to`='$with' AND `from`='$uid' AND fromdel != 1) 
					 OR (send = 1 AND `from`='$with' AND `to`='$uid' AND todel != 1))";
		} else {
			$where = "`to`='$with' AND `type`='grpchat' AND send = 1";
		}
		$data = $this->where($where)->order('timestamp DESC')->limit($limit)->findAll();
		if($data) return array_reverse( $data );
		return array();
	}

	public function getOffline($uid, $limit = 50) {
		$where = array("to" => "$uid", "send" => 0);
		$data = $this->where($where)->limit($limit)->order('timestamp DESC ')->findAll();
		if($data) return array_reverse( $data );
		return array();
	}

	public function insert($user, $message) {
		$this->create($message);
		$this->from = $user->id;
		$this->nick = $user->nick;
		$this->created_at = date( 'Y-m-d H:i:s' );
		$this->add();
	}

	public function clear($uid, $with) {
		$this->where( array('from' => "$uid", 'to' => "$with") )->save( array( "fromdel" => 1, "type" => "chat" ) );
		$this->where( array('to' => "$uid", 'from' => "$with") )->save( array( "todel" => 1, "type" => "chat" ) );
		$this->where( array('todel' => 1, 'fromdel' => 1) )->delete();
	}

	public function offlineReaded($uid) {
		$this->where( array('to' => '$uid', 'send' => 0) )->save( array('send' => 1) );
	}

}



