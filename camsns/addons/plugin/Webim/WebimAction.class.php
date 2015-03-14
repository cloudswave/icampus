<?php

class WebimAction {

	/*
	 * Webim Ticket
	 */
	private $ticket;

	/*
	 * Webim Client
	 */
	private $client;

	/*
	 * 与ThinkPHP接口类实例
	 */
	private $thinkim;

	/*
	 * Setting Model
	 */
	private $settingModel;

	/*
	 * History Model
	 */
	private $historyModel;

	public function __construct() {
		global $IMC;

		//IM Ticket
		$imticket = $this->input('ticket');
		if($imticket) $imticket = stripslashes($imticket);	
		$this->ticket = $imticket;

		//Initialize ThinkIM
		$this->thinkim = new ThinkIM();

		//IM Client
		$this->client = new WebimClient($this->thinkim->user(), 
			$this->ticket, $IMC['domain'], $IMC['apikey'], $IMC['host'], $IMC['port']);

		//IM Models
		$this->settingModel = new SettingModel();
		$this->historyModel = new HistoryModel();
	}

	public function run() {

		global $IMC;

		//插件关闭
		if(!$IMC['isopen']) exit();
		//用户未登录
		if(!$this->thinkim->logined()) exit();

		$fields = array(
			'version',
			'theme', 
			'local', 
			'emot',
			'opacity',
			'enable_room', 
			'enable_chatlink', 
			'enable_shortcut',
			'enable_noti',
			'enable_menu',
			'show_unavailable',
			'upload');

		$scriptVar = array(
			'production_name' => WEBIM_PRODUCTION_NAME,
			'path' => WEBIM_URL,
			'is_login' => '1',
			'login_options' => '',
			'user' => $this->thinkim->user(),
			'setting' => $this->settingModel->get($this->thinkim->uid()),
			'min' => $IMC['debug'] ? "" : ".min"
		);

		foreach($fields as $f) {
			$scriptVar[$f] = $IMC[$f];	
		}

		header("Content-type: application/javascript");
		header("Cache-Control: no-cache");

		echo "var _IMC = " . json_encode($scriptVar) . ";" . PHP_EOL;

		$script = <<<EOF
_IMC.script = window.webim ? '' : ('<link href="' + _IMC.path + '/static/webim.' + _IMC.production_name + _IMC.min + '.css?' + _IMC.version + '" media="all" type="text/css" rel="stylesheet"/><link href="' + _IMC.path + '/static/themes/' + _IMC.theme + '/jquery.ui.theme.css?' + _IMC.version + '" media="all" type="text/css" rel="stylesheet"/><script src="' + _IMC.path + '/static/webim.' + _IMC.production_name + _IMC.min + '.js?' + _IMC.version + '" type="text/javascript"></script><script src="' + _IMC.path + '/static/i18n/webim-' + _IMC.local + '.js?' + _IMC.version + '" type="text/javascript"></script>');
_IMC.script += '<script src="' + _IMC.path + '/webim.js?' + _IMC.version + '" type="text/javascript"></script>';
document.write( _IMC.script );

EOF;
		exit($script);
	}

	public function online() {
		$uid = $this->thinkim->uid();
		$domain = $this->input("domain");
		if ( !$this->thinkim->logined() ) {
			$this->jsonReturn(array( 
				"success" => false, 
				"error_msg" => "Forbidden" ));
		}
		$im_buddies = array(); //For online.
		$im_rooms = array(); //For online.
		$strangers = $this->idsArray( $this->input('stranger_ids') );
		$cache_buddies = array();//For find.
		$cache_rooms = array();//For find.

		$active_buddies = $this->idsArray( $this->input('buddy_ids') );
		$active_rooms = $this->idsArray( $this->input('room_ids') );

		$new_messages = $this->historyModel->getOffline($this->thinkim->uid());
		$online_buddies = $this->thinkim->buddies();
		
		$buddies_with_info = array();
		//Active buddy who send a new message.
		foreach($new_messages as $msg) {
			if(!in_array($msg['from'], $active_buddies)) {
				$active_buddies[] = $msg['from'];
			}
		}

		//Find im_buddies
		$all_buddies = array();
		foreach($online_buddies as $k => $v){
			$id = $v->id;
			$im_buddies[] = $id;
			$buddies_with_info[] = $id;
			$v->presence = "offline";
			$v->show = "unavailable";
			$cache_buddies[$id] = $v;
			$all_buddies[] = $id;
		}

		//Get active buddies info.
		$buddies_without_info = array();
		foreach($active_buddies as $k => $v){
			if(!in_array($v, $buddies_with_info)){
				$buddies_without_info[] = $v;
			}
		}
		if(!empty($buddies_without_info) || !empty($strangers)){
			//FIXME
			$bb = $this->thinkim->buddiesByIds(implode(",", $buddies_without_info), implode(",", $strangers));
			foreach( $bb as $k => $v){
				$id = $v->id;
				$im_buddies[] = $id;
				$v->presence = "offline";
				$v->show = "unavailable";
				$cache_buddies[$id] = $v;
			}
		}
		if(!$IMC['enable_room']){
			$rooms = $this->thinkim->rooms();
			$setting = $this->settingModel->get($this->thinkim->uid());
			$blocked_rooms = $setting && is_array($setting->blocked_rooms) ? $setting->blocked_rooms : array();
			//Find im_rooms 
			//Except blocked.
			foreach($rooms as $k => $v){
				$id = $v->id;
				if(in_array($id, $blocked_rooms)){
					$v->blocked = true;
				}else{
					$v->blocked = false;
					$im_rooms[] = $id;
				}
				$cache_rooms[$id] = $v;
			}
			//Add temporary rooms 
			$temp_rooms = $setting && is_array($setting->temporary_rooms) ? $setting->temporary_rooms : array();
			for ($i = 0; $i < count($temp_rooms); $i++) {
				$rr = $temp_rooms[$i];
				$rr->temporary = true;
				$rr->pic_url = (WEBIM_PATH . "static/images/chat.png");
				$rooms[] = $rr;
				$im_rooms[] = $rr->id;
				$cache_rooms[$rr->id] = $rr;
			}
		}else{
			$rooms = array();
		}

		//===============Online===============
		//

		$data = $this->client->online( implode(",", array_unique( $im_buddies ) ), implode(",", array_unique( $im_rooms ) ) );

		if( $data->success ){
			$data->new_messages = $new_messages;

			if(!$IMC['enable_room']){
				//Add room online member count.
				foreach ($data->rooms as $k => $v) {
					$id = $v->id;
					$cache_rooms[$id]->count = $v->count;
				}
				//Show all rooms.
			}
			$data->rooms = $rooms;

			$show_buddies = array();//For output.
			foreach($data->buddies as $k => $v){
				$id = $v->id;
				if(!isset($cache_buddies[$id])){
					$cache_buddies[$id] = (object)array(
						"id" => $id,
						"nick" => $id,
						"incomplete" => true,
					);
				}
				$b = $cache_buddies[$id];
				$b->presence = $v->presence;
				$b->show = $v->show;
				if( !empty($v->nick) )
					$b->nick = $v->nick;
				if( !empty($v->status) )
					$b->status = $v->status;
				#show online buddy
				$show_buddies[] = $id;
			}
			#show active buddy
			$show_buddies = array_unique(array_merge($show_buddies, $active_buddies, $all_buddies));
			$o = array();
			foreach($show_buddies as $id){
				//Some user maybe not exist.
				if(isset($cache_buddies[$id])){
					$o[] = $cache_buddies[$id];
				}
			}

			//Provide history for active buddies and rooms
			foreach($active_buddies as $id){
				if(isset($cache_buddies[$id])){
					$cache_buddies[$id]->history = $this->historyModel->get($uid, $id, "chat" );
				}
			}
			foreach($active_rooms as $id){
				if(isset($cache_rooms[$id])){
					$cache_rooms[$id]->history = $this->historyModel->get($uid, $id, "grpchat" );
				}
			}

			$show_buddies = $o;
			$data->buddies = $show_buddies;
			$this->historyModel->offlineReaded($this->thinkim->uid());
			$this->jsonReturn($data);
		} else {
			$this->jsonReturn(array( 
				"success" => false, 
				"error_msg" => empty( $data->error_msg ) ? "IM Server Not Found" : "IM Server Not Authorized", 
				"im_error_msg" => $data->error_msg)); 
		}
	}

	public function offline() {
		$this->client->offline();
		$this->okReturn();
	}

	public function message() {
		$type = $this->input("type");
		$offline = $this->input("offline");
		$to = $this->input("to");
		$body = $this->input("body");
		$style = $this->input("style");
		$send = $offline == "true" || $offline == "1" ? 0 : 1;
		$timestamp = $this->microtimeFloat() * 1000;
		if( strpos($body, "webim-event:") !== 0 ) {
			$this->historyModel->insert($this->thinkim->user(), array(
				"send" => $send,
				"type" => $type,
				"to" => $to,
				"body" => $body,
				"style" => $style,
				"timestamp" => $timestamp,
			));
		}
		if($send == 1){
			$this->client->message($type, $to, $body, $style, $timestamp);
		}
		$this->okReturn();
	}

	public function presence() {
		$show = $this->input('show');
		$status = $this->input('status');
		$this->client->presence($show, $status);
		$this->okReturn();
	}

	public function history() {
		$uid = $this->thinkim->uid();
		$with = $this->input('id');
		$type = $this->input('type');
		$histories = $this->historyModel->get($uid, $with, $type);
		$this->jsonReturn($histories);
	}

	public function status() {
		$to = $this->input("to");
		$show = $this->input("show");
		$this->client->status($to, $show);
		$this->okReturn();
	}

	public function members() {
		$id = $this->input('id');
		$re = $this->client->members( $id );
		if($re) {
			$this->jsonReturn($re);
		} else {
			$this->jsonReturn("Not Found");
		}
	}

	public function join() {
		$id = $this->input('id');
		$room = $this->thinkim->roomsByIds( $id );
		if( $room && count($room) ) {
			$room = $room[0];
		} else {
			$room = (object)array(
				"id" => $id,
				"nick" => $this->input('nick'),
				"temporary" => true,
				"pic_url" => (WEBIM_PATH . "static/images/chat.png"),
			);
		}
		if($room){
			$re = $this->client->join($id);
			if($re){
				$room->count = $re->count;
				$this->jsonReturn($room);
			}else{
				header("HTTP/1.0 404 Not Found");
				exit("Can't join this room right now");
			}
		}else{
			header("HTTP/1.0 404 Not Found");
			exit("Can't found this room");
		}
	}

	public function leave() {
		$id = $this->input('id');
		$this->client->leave( $id );
		$this->okReturn();
	}

	public function buddies() {
		$ids = $this->input('ids');
		$this->jsonReturn($this->thinkim->buddiesByIds($ids));
	}

	public function rooms() {
		$ids = $this->input("ids");
		$this->jsonReturn($this->thinkim->roomsByIds($ids));	
	}

	public function refresh() {
		$this->client->offline();
		$this->okReturn();
	}

	public function clear_history() {
		$id = $this->input('id'); //$with
		$this->historyModel->clear($this->thinkim->uid(), $id);
		$this->okReturn();
	}

	public function download_history() {
		$uid = $this->thinkim->uid();
		$id = $this->input('id');
		$type = $this->input('type');
		$histories = $this->historyModel->get($uid, $id, $type, 1000 );
		$date = date( 'Y-m-d' );
		if($this->input('date')) {
			$date = $this->input('date');
		}
		//FIXME Later
		//$client_time = (int)$this->input('time');
		//$server_time = webim_microtime_float() * 1000;
		//$timedelta = $client_time - $server_time;
		header('Content-Type',	'text/html; charset=utf-8');
		header('Content-Disposition: attachment; filename="histories-'.$date.'.html"');
		echo "<html><head>";
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
		echo "</head><body>";
		echo "<h1>Histories($date)</h1>".PHP_EOL;
		echo "<table><thead><tr><td>用户</td><td>消息</td><td>时间</td></tr></thead><tbody>";
		foreach($histories as $history) {
			$nick = $history['nick'];
			$body = $history['body'];
			$style = $history['style'];
			$time = date( 'm-d H:i', (float)$history['timestamp']/1000 ); 
			echo "<tr><td>{$nick}</td><td style=\"{$style}\">{$body}</td><td>{$time}</td></tr>";
		}
		echo "</tbody></table>";
		echo "</body></html>";
		exit();
	}

	public function setting() {
		if(isset($_GET['data'])) {
			$data = $_GET['data'];
		} 
		if(isset($_POST['data'])) {
			$data = $_POST['data'];
		}
		$uid = $this->thinkim->uid();
		$this->settingModel->set($uid, $data);
		$this->okReturn();
	}

	public function notifications() {
		$notifications = $this->thinkim->notifications();
		$this->jsonReturn($notifications);
	}

	public function openchat() {
		$grpid = $this->input('group_id');
		$nick = $this->input('nick');
		$this->jsonReturn($this->client->openchat($grpid, $nick));	
	}

	public function closechat() {
		$grpid = $this->input('group_id');
		$buddy_id = $this->input('buddy_id');
		$this->jsonReturn($this->client->closechat($grpid, $buddy_id));
	}

	public function input($name, $default=NULL) {
		if( isset( $_GET[$name] ) ) return $_GET[$name]; 
		if( isset( $_POST[$name] ) ) return $_POST[$name];
		return $default;
	}

	private function okReturn() {
		$this->jsonReturn('ok');
	}

	private function jsonReturn($data) {
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($data));
	}

	private function idsArray( $ids ){
		return ($ids===NULL || $ids==="") ? array() : (is_array($ids) ? array_unique($ids) : array_unique(explode(",", $ids)));
	}

	private function microtimeFloat() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}
