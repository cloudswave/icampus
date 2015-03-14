<?php

/**
 * WebIM Client PHP Lib
 *
 * Author: Hidden <zzdhidden@gmail.com>
 * Date: Mon Aug 23 15:15:41 CST 2010
 *
 *
 */

class WebimClient
{
    const apivsn = "v5";

	private $user;
	private $domain;
	private $apikey;
	private $host;
	private $port;
	private $client;
	private $ticket;
	private $version = 5;

	/**
	 * New
	 *
	 * @param object $user
	 * 	-id:
	 * 	-nick:
	 * 	-show:
	 * 	-status:
	 *
	 * @param string $ticket
	 * @param string $domain
	 * @param string $apikey
	 * @param string $host
	 * @param string $port
	 *
	 */

	function webim_client($user, $ticket, $domain, $apikey, $host, $port = 8000) {
		return $this->__construct( $user, $ticket, $domain, $apikey, $host, $port );
	}

	function __construct($user, $ticket, $domain, $apikey, $host, $port = 8000) {
		register_shutdown_function( array( &$this, '__destruct' ) );
		$this->user = $user;
		$this->domain = trim($domain);
		$this->apikey = trim($apikey);
		$this->ticket = trim($ticket);
		$this->host = trim($host);
		$this->port = trim($port);
		$this->client = new HttpClient($this->host, $this->port);
	}

	/**
	 * PHP5 style destructor and will run when database object is destroyed.
	 *
	 * @see webim_client::__construct()
	 * @return bool true
	 */
	function __destruct() {
		return true;
	}


	/**
	 * Join group.
	 *
	 * @param string $gid
	 *
	 * @return object group_info
	 * 	-id
	 * 	-count
	 */

    #FIXME: fix json decode
	function join($gid){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'room' => $gid,
			'group' => $gid,
		);
		$this->client->post($this->apiurl('group/join'), $data);
		$cont = $this->client->getContent();
		if($this->client->status == "200"){
			$da = json_decode($cont);
			return (object)array(
				"id" => $gid,
				"count" => $da ->{$gid},
			);
		}else{
			return null;
		}
	}

	/**
	 * Leave group.
	 *
	 * @param string $gid
	 *
	 * @return ok
	 *
	 */

	function leave($gid){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'room' => $gid,
			'group' => $gid,
		);
		$this->client->post($this->apiurl('group/leave'), $data);
		return $this->client->getContent();
	}

	/**
	 * Get room members.
	 *
	 * @param string $gid
	 *
	 * @return array $members
	 * 	array($member_info)
	 *
	 */

    #FIXME: check json return
	function members($gid){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'room' => $gid,
			'group' => $gid,
		);
		$this->client->get($this->apiurl('group/members'), $data);
		$cont = $this->client->getContent();
		if($this->client->status == "200"){
			$da = json_decode($cont);
			return $da ->{$gid};
		}else{
			return null;
		}
	}

	/**
	 * Send user chat status to other.
	 *
	 * @param string $to status receiver
	 * @param string $show status
	 *
	 * @return ok
	 *
	 */

	function status($to, $show){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'to' => $to,
			'show' => $show,
		);
		$this->client->post($this->apiurl('statuses'), $data);
		return $this->client->getContent();
	}

	/**
	 * Send message to other.
	 *
	 * @param string $type chat or grpchat or boardcast
	 * @param string $to message receiver
	 * @param string $body message
	 * @param string $style css
	 *
	 * @return ok
	 *
	 */

	function message($type, $to, $body, $style="", $timestamp=null){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'type' => $type,
			'to' => $to,
			'body' => $body,
			'style' => $style,
			'timestamp' => empty($timestamp) ? (string)webim_microtime_float()*1000 : $timestamp,
		);
		$this->client->post($this->apiurl('messages'), $data);
		return $this->client->getContent();
	}


	/**
	 * Send user presence
	 *
	 * @param string $show
	 * @param string $status
	 *
	 * @return ok
	 *
	 */

	function presence($show, $status = ""){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'nick' => $this->user->nick,
			'show' => $show,
			'status' => $status,
		);
		$this->client->post($this->apiurl('presences/show'), $data);
		return $this->client->getContent();
	}


	/**
	 * User offline
	 *
	 * @return ok
	 *
	 */

	function offline(){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain
		);
		$this->client->post($this->apiurl('presences/offline'), $data);
		return $this->client->getContent();
	}

	/**
	 * User online
	 *
	 * @param string $buddy_ids
	 * @param string $group_ids
	 *
	 * @return object
	 * 	-success: true
	 * 	-connection:
	 * 	-user:
	 * 	-buddies: [&buddyInfo]
	 * 	-groupss: [&groupInfo]
	 * 	-error_msg:
	 *
	 */
	function online($buddy_ids, $group_ids){
		$data = array(
			'version' => $this->version,
			'rooms'=> $group_ids, 
			'groups'=> $group_ids, 
			'buddies'=> $buddy_ids, 
			'domain' => $this->domain, 
			'apikey' => $this->apikey, 
			'name'=> $this->user->id, 
			'nick'=> $this->user->nick, 
			'status'=> $this->user->status, 
			'show' => $this->user->show
		);
		if ( isset( $this->user->visitor ) ) {
			$data['visitor'] = $this->user->visitor;
		}
		$this->client->post($this->apiurl('presences/online'), $data);
		$cont = $this->client->getContent();
		$da = json_decode($cont);
		if($this->client->status != "200" || empty($da->ticket)){
			return (object)array("success" => false, "error_msg" => $cont);
		}else{
			$ticket = $da->ticket;
			$this->ticket = $ticket;
			$buddies = array();
			foreach($da->buddies as $buddy){
				$buddies[] = (object)array("id" => $buddy->name, "nick" => $buddy->nick, "show" => $buddy->show, "presence" => "online", "status" => $buddy->status);
			}
			$groups = array();
			foreach($da->groups as $group){
				$groups[] = (object)array("id" => $group->name, "count" => $group->total);
			}
			$connection = (object)array(
				"ticket" => $ticket,
				"domain" => $this->domain,
                #FIXME: should return from im server
				"server" => $da->jsonpd,
				"jsonpd" => $da->jsonpd,
				"websocket" => $da->websocket,
				"mqttd" => $da->mqttd,
			);
			return (object)array(
				"success" => true, 
				"connection" => $connection, 
				"buddies" => $buddies, 
				"rooms" => $groups, 
				"groups" => $groups, 
				"server_time" => microtime(true)*1000, 
				"user" => $this->user
			);
		}
	}

	/**
	 * Open chat
	 *
	 * @param string $group_id
	 *
	 * @return 
	 *
	 */

	function openchat($group_id, $nick){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'group' => $group_id,
			'nick' => $nick,
			'timestamp' => (string)webim_microtime_float()*1000,
		);
		$this->client->post($this->apiurl('chats/open'), $data);
		$da = json_decode( $this->client->getContent() );
		if($this->client->status != "200" || empty($da->status)){
			return array();
		} else {
			return $da->buddies;
		}
	}

	/**
	 * Open chat
	 *
	 * @param string $group_id
	 *
	 * @return 
	 *
	 */

	function closechat($group_id, $buddy_id){
		$data = array(
			'version' => $this->version,
			'ticket' => $this->ticket,
			'apikey' => $this->apikey,
			'domain' => $this->domain,
			'group' => $group_id,
			'buddyid' => $buddy_id,
		);
		$this->client->post($this->apiurl('chats/close'), $data);
		return json_decode( $this->client->getContent() );
	}

	/**
	 * Check the server is connectable or not.
	 *
	 * @return object
	 * 	-success: true
	 * 	-error_msg:
	 *
	 */

	function check_connect(){
		$data = array(
			'version' => $this->version,
			'rooms'=> "", 
			'buddies'=> "", 
			'domain' => $this->domain, 
			'apikey' => $this->apikey, 
			'name'=> $this->user->id, 
			'nick'=> $this->user->nick, 
			'show' => $this->user->show
		);
		$this->client->post($this->apiurl('presences/online'), $data);
		$cont = $this->client->getContent();
		$da = json_decode($cont);
		if($this->client->status != "200" || empty($da->ticket)){
			return (object)array("success" => false, "error_msg" => $cont);
		}else{
			$this->ticket = $da->ticket;
			return (object)array("success" => true, "ticket" => $da->ticket);
		}
	}

    private function apiurl($path) {
        return '/' . self::apivsn . '/' . $path;
    }

}

?>
