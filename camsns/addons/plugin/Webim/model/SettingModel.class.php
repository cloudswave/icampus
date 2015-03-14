<?php

class SettingModel extends Model {

	protected $tableName = 'webim_settings';

	protected $fields = array(
			0 => 'id',
			1 => 'uid',
			2 => 'web',
			3 => 'air',
			4 => 'created_at',
			5 => 'updated_at',
	);

	public function set($uid, $data, $type='web') {
		$setting = $this->where("uid='$uid'")->find();
		if( $setting ) {
			if ( !is_string( $data ) ){
				$data = json_encode( $data );
			}
			$setting[$type] = $data;
			$this->save($setting);
		} else {
			$setting = $this->create(array(
				'uid' => $uid,
				$type => $data,
				'created_at' => date( 'Y-m-d H:i:s' ),
			));
			$this->add();
		}
	}

	public function get($uid, $type = "web") {
		$setting = $this->where("uid='$uid'")->find();	
		if($setting) {
			return json_decode($setting[$type]);
		} 
		return new stdClass();
	}
	
}
