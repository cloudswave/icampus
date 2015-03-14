<?php
/**
 * 日志模型 - 数据对象模型
 * @example
 * load($type) 						链式指定类型
 * action($action)  				链式指定行为
 * record($content, $isAdminLog)	记录日志
 * get($map,$limit=30, $table) 		获取日志
 * cleanLogs($m)					清理m个月之前的日志
 * getMenuList()					获取所有权限节点列表
 * logsArchive()					归档1个月之前的日志
 * dellogs($id, $date)				删除某张表中的某条记录
 * getMenuList($app)				获取应用下的日志节点
 * 请直接使用函数库中的LogRecord($type, $action, $data, $isAdmin);进行日志存储                          
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class LogsModel extends Model {

	protected $tableName = 'x_logs';
	protected $fields = array('id','uid','uname','app_name','group','action','data','ctime','url','isAdmin','ip','keyword');

    public $option;				// 日志配置字段
	public $keyword;			// 日志关键字

	/**
	 * 链式指定日志类型
	 * @param string $type 日志类型，用“_”进行分割
	 * @return object 日志模型对象
	 */
	public function load($type) {
		$type = explode('_', $type, 2);
		$this->option['app'] = $type[0];
		$this->option['group'] = $type[1];
		return $this;
	}

	/**
	 * 链式指定日志行为
	 * @param string $type 行为字段
	 * @return object 日志模型对象
	 */
	public function action($type) {
		$this->option['action'] = $type;
		return $this;
	}

	/**
	 * 记录日志
	 * @param string $content 日志内容
	 * @param integer $isAdminLog 是否是管理员日志，默认为1
	 * @return mix 添加失败返回false，添加成功返回日志ID
	 */
    public function record($content, $isAdminLog) {
    	$this->parseKeyWord($content);
    	$user = $GLOBALS['ts']['user'];
		$data['uid'] = $user['uid'];				// TODO:临时写死
		$data['uname'] = $user['uname'];		// TODO:临时写死
		$data['app_name'] = $this->option['app'];
		$data['group'] = $this->option['group'];
		$data['action'] = $this->option['action'];
		$data['data'] = serialize( $content );
		$data['ctime'] = time();
		$data['url'] = $_SERVER['REQUEST_URI'];
        $data['isAdmin'] = intval( $isAdminLog );
        $data['ip'] = get_client_ip();
        $data['keyword'] = ($this->keyword) ? implode(' ',$this->keyword) : '' ;
		return $this->add($data);
    }

    /**
     * 将数值转换为字符串
     * @param string $content 日志内容
     * @return void
     */
    private function parseKeyWord($content) {
        if(is_array($content)) {
            foreach($content as $key => $value) {
            	!in_array($value, $this->keyword) && $this->parseKeyWord($value);
            }
        } else {
            $this->keyword[] = $content;
        }
    }

	/**
	 * 获取指定日志的列表信息
	 * @param array $map 查询条件
	 * @param integer $limit 结果集数目，默认为30
	 * @param string $table 指定日志表，默认为系统日志表
	 * @return array 指定日志的列表信息
	 */
    public function get($map, $limit = 30, $table = false) {
        $table = $table ? $this->tablePrefix.'x_logs_'.$table : $this->tablePrefix.'x_logs';
		$list = $this->table($table)->where($map)->order('id DESC')->findPage($limit);
	    foreach($list['data'] as $key => $v) {
    		$tempData = $this->__paseTemplate($v);
    		$list['data'][$key]['data'] = $tempData['data'];
    		$list['data'][$key]['type_info'] = $tempData['info'];
    	}

		return $list;
	}

	/**
	 * 获取指定应用下所有权限节点列表
	 * @param string $app 应用名称
	 * @return array 指定应用下所有权限节点列表
	 */
	public function getMenuList($app){
		$logsXml = SITE_PATH.'/apps/'.$app.'/Conf/logs.xml';
		if(!file_exists($logsXml)) {
			$this->error = L('PUBLIC_SETTING_FILE', array('file'=>$logsXml));			// 配置文件：{file}不存在
			return false;
		}
        $xml = simplexml_load_file($logsXml);
		if($xml->group) {
			foreach($xml->group as $k=>$v) {
				unset($rule);
				foreach($v->action as $kk => $vv) {
					$rule[(string)$vv['type']] = (string)$vv['info'];
				}

				$data['_group'][(string)$v['name']] = array(
					'info'=>(string)$v['info'],
					'_rule'=>$rule
				);
			}
		} else {
			foreach($xml->action as $kk => $vv) {
				$data['_rule'][(string)$vv['type']] = (string)$vv['info'];
			}
        }

		return $data;
	}

	/**
	 * 清除日志数据，删除几个月前的日志信息
	 * @param integer $m 月数，删除几个月前的日志信息
	 * @return mix 删除失败返回false，删除成功返回1
	 */
	public function cleanLogs($m) {
		$m = intval($m);
		if($m == 0) {
			return false;
		}
		// 获取日志表列表
		$tableList = D('')->query("SHOW TABLE STATUS LIKE '".$this->tablePrefix."x_logs_%'");
        $todayInfo = getDate(time());
        $diff = getDate(mktime(0,0,0,$todayInfo['mon'] - $m,1,$todayInfo['year']));

        foreach($tableList as $k => $value) {
            $table = explode('_',$value['Name']);
            if($table[3] == $diff['year']) {
				if($table[4] <= $diff['mon']) {
                    $dropTables[] = $value['Name'];
                }
            } else if($table[3] < $diff['year']) {
				$dropTables[] = $value['Name'];
            }
        }

        if($dropTables) {
            return D('')->query("DROP TABLE ".implode(',',$dropTables));
        } else {
        	return false;
        }
	}

	/**
	 * 重建日志归档，重建后的日志只存在归档表中，主日志表不在有该日志信息
	 * @return boolean 是否重建成功
	 */
	public function logsArchive() {
		$logsTableName = $this->tablePrefix.'x_logs';
    	$today = getDate(time());
    	// 上个月底的时间
    	$dayBeforeTime = strtotime(date('Y-m-t 23:59:59', strtotime('-1 month')));
    	// 主表是否存在31天前的日志
    	if($this->where('ctime<='.$dayBeforeTime)->count()) {
    		// 搜索下有多少个月的数据需要归档
    		$findDate = D('')->query("SELECT DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m') AS lists FROM {$logsTableName} WHERE ctime <= ".$dayBeforeTime." GROUP BY DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m')");
    		// 每个月归档
    		foreach($findDate as $value) {
    			$dataInfo = explode('-', $value['lists']);
    			// 归档表的名称
    			$archiveTableName = $logsTableName.'_'.$dataInfo[0].'_'.$dataInfo[1];
    			// 先创建表
    			if(D('')->query("DESC $archiveTableName") == false) {
    				D('')->query("CREATE TABLE $archiveTableName LIKE $logsTableName");
    			}
    			$querySql = "DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m') = '".$value[lists]."'";
    			$result = D('')->query("INSERT INTO $archiveTableName SELECT * FROM $logsTableName WHERE $querySql");
    		}
    		$this->where($querySql)->delete();
    		return true;
    	} else {
    		return false;
    	}
	}

	/**
	 * 删除指定的日志记录信息
	 * @param integer $id 日志ID
	 * @param string $date 时间字段
	 * @return mix 删除失败返回false，删除成功返回删除的日志ID
	 */
	public function dellogs($id, $date = '') {
		$logsTableName = $this->tablePrefix.'x_logs'.(empty($date) ? "" : "_".$date);
		$map['id'] = is_array($id) ? array('IN', $id) : $id;
		return D('')->table($logsTableName)->where($map)->delete();
	}

	/**
	 * 渲染日志模板变量
	 * @param array $_data 日志相关数据
	 * @return array 渲染后的日志模板变量
	 */
    protected function __paseTemplate($_data) {
        $app = $_data['app_name'];
		$var = unserialize($_data['data']);
		$logFile = SITE_PATH.'/apps/'.$app.'/Conf/logs.xml';
		if(!file_exists($logFile)) {
			$this->error = L('PUBLIC_SETTING_FILE', array('file'=>$logFile));			// 配置文件：{file}不存在
			return false;
		}
		$content = fetch($logFile, $var,'UTF8','text/xml');

		$dom = new domDocument;
		$dom->loadXml($content);
		unset($content);

        $s = simplexml_import_dom($dom);

        if($_data['group']) {
        	$result = $s->xpath("//root/group[@name='".$_data['group']."']/action[@type='".$_data['action']."']");
        } else {
			$result = $s->xpath("//root/action[@type='".$_data['action']."']");
        }
        // 异常情况
        $return = array('info'=>L('PUBLIC_PERMISSION_POINT_NOEXIST'),'data'=>L('PUBLIC_PERMISSION_POINT_NOEXIST'));			// 权限节点不存在，权限节点不存在

       	if($result) {
			$return['info'] = (string)$result[0]['info'];
        	$return['data'] = trim((string)$result[0]);
       	}

		return $return;
	}
}