<?php
class AppConfigModel extends Model{
	public $tableName	=	'system_data';
	public $appname	=	'blog';
	/**
	 * getAppname
	 * 获得当前应用名
	 * @access public
	 * @return string
	 */
	public function getAppname(){
		return $this->appname;
	}
	/**
	 * setAppname
	 * 设置当前应用名
	 * @access public
	 * @return string
	 */
	public function setAppname($appname){
	   $this->appname	=	$appname;
	   return $this->appname;
	}
	/**
	 * getConfig
	 * 获得配置
	 * @access public
	 * @return void
	 */
	public function getConfig(){
		return $this->getConfigData(true); 
	}

	/**
	 * getConfigData
	 * 获得数据库中的配置信息
	 * @access public
	 * @return array
	 */
	public function getConfigData($cache = false){

		//查询所有配置
		$request = $this->where("list='$this->appname'")->findAll();
		//组装成标准数组
		foreach ( $request as $value ){
			$result[$value['key']] = $value['value'];
		}

		//重建缓存
		//$this->rebuildCache();

		return $result;
	}

	/**
	 * addConfig
	 * 添加配置
	 * @param mixed $data
	 * @access public
	 * @return void
	 */
	public function addConfig($data){

		foreach ( $data as $key => $value ){
			$value = is_array( $value ) ? serialize( $value ):$value;
			$map['name']	=	$key;
			$map['value']	=	$value;
			$map['appname']	=	$this->appname;
			$result = $this->add($map);
		}

		return $result;
	}

	/**
	 * editConfig
	 * 编辑配置
	 * @param mixed $data
	 * @access public
	 * @return void
	 */
	public function editConfig($data){
		$cache = true; //修改配置是需要刷新缓存的
		if( !is_array( $data ) ){
			throw new ThinkException( "参数必须是数组" );
		}
		$temp_map['list'] = $this->appname;
		$config = $this->where($temp_map)->getField( 'key' ) ;
		//循环数组。如果有这个字段，则是修改。如果没有这个字段，添加新的配置
		foreach( $data as $key => $value ){
			$addConfig = array();  //添加配置的条件数组

			//如果没有这个字段，添加配置
			if( false == in_array($key,$config) || is_null( $config ) ){
				$addConfig[$key]=$value;
				if($this->addConfig( $addConfig )){
					continue;
				}
			}

			//修改条件
			$condition['key']		=	$key;
			$condition['list']  =	$this->appname;
			//数组需要被序列化存储
			if( is_array( $value ) ){
				$value = serialize( $value );
			}

			//修改的值
			$map['value'] = $value;
			$result = $this->where( $condition )->save($map);
		}

		//重建缓存
		// $this->rebuildCache();
		return true;
	}

	//重建缓存
	private function rebuildCache(){
//		ts_cache( $this->appname.'config',"" );
//		$request = $this->where("appname='$this->appname'")->findAll();
//		if( !$request ){
//			return false;
//		}
//		foreach ( $request as $value ){
//		   $result[$value['name']] = $value['value'];
//		}
//		ts_cache( $this->appname.'_config',$result );
//		return true;
 	}

}