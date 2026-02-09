<?php

use WebSocket\Client;

require_once 'classes/database.php';
require_once 'classes/clients.php';

class smsMessages {

	public $table_name = "sms_messages";
	public $id = 'id';
	public $requested_date = 'requested_date';
	public $no_sms_to_send = 'no_sms_to_send';
	public $phone_numbers = 'phone_numbers';
	public $token = 'token';
	public $sent_status = 'sent_status';
	public $message_content = 'message_content';

	public $databaseObject;
	public $QueryResult;

	public $error = array();

	public function __construct(){
		
		$this->databaseObject = new Database;
		$this->initilise_sms_messages();
	}

	function initilise_sms_messages(){

		//Table structure for table `sms_messages`
		
		$Query = "CREATE TABLE IF NOT EXISTS `".$this->table_name."` (".
				"`".$this->id."` int(11) NOT NULL AUTO_INCREMENT,".
				"`".$this->message_content."` LONGTEXT NOT NULL,".
				"`".$this->no_sms_to_send."` int(6) NOT NULL,".
				"`".$this->phone_numbers."` LONGTEXT NOT NULL,".
				"`".$this->token."` varchar(30) NOT NULL,".
				"`".$this->sent_status."` int(1) NOT NULL,".
				"`".$this->requested_date."` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
				"PRIMARY KEY (`id`) ".
				") ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

		$execute_query_result = $this->databaseObject->PerformQuery($Query);
		if (!$execute_query_result) {
			$this->error[] = " create ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		return true;
	}

	public function get_sms_messages(){
		return $this->databaseObject->get_all($this->table_name);
	}

	public function get_sms_message_by_id($id){

		$Query = "select * from ".$this->table_name." where id='".$id."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return mysqli_fetch_assoc($this->QueryResult);

		}
		return false;
	}

	public function get_sms_messages_by_Token($token){

		$Query = "select * from ".$this->table_name." where ".$this->token."='".$token."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return $this->QueryResult;
		}
		$this->error[] = " error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
		
		return false;
	}
	
	public function add_sms_messages( $token, $phone_numbers, $message_content, $no_sms_to_send, $sent_status){

		$Query = "INSERT INTO `sms_messages` ( `".$this->token."`".", `".$this->phone_numbers."`, `".
				$this->message_content."`, `".$this->no_sms_to_send."`, `".$this->sent_status."`)".
				" VALUES ('".$token."', '".$phone_numbers."', '".$message_content."', '"
				.$no_sms_to_send."', '".$sent_status."');";
		
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if (!$this->QueryResult) {

			$this->error[] = "insert data in ".$this->table_name
			." table error.<br/> Error Description :<br/> client_id : " 
			. mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			return false;
		}

		return true;
	}

	public function update_status($id, $sent_status){

		$Query = "UPDATE `".$this->table_name."` SET ".$this->sent_status." = '".$sent_status.
		"' WHERE ".$this->id."=".$id.";";

		if (!$this->databaseObject->PerformQuery($Query)) {
			$this->error[] = "update data in ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		else return true;
	}
	
	public function test(){
		return "the sms_messages class is working";
	}

	public function insert_default_sms_messages(){
		if ($this->initilise_sms_messages()) {
			if ($this->add_sms_messages( "token", "phone_numbers", "message_content", "no_sms_to_send", "sent_status")) {
			return true;
			}
		}
		return false;
	}
}
?>
