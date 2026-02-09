<?php

use WebSocket\Client;

require_once 'classes/database.php';
require_once 'classes/clients.php';

class Subscriptions {

	public $table_name = "Subscriptions";
	public $id = 'id';
	public $client_id = 'client_id';
	public $subscription_date = 'subscription_date';
	public $sms_subscribed = 'sms_subscribed';
	public $sms_subscription_used = 'sms_subscription_used';
	public $token = 'token';
	public $status = 'status';
	public $expiration_date = 'expiration_date';

	public $databaseObject;
	public $QueryResult;

	public $error = array();

	public function __construct(){
		
		$this->databaseObject = new Database;
		$this->initilise_Subscriptions();
	}

	function initilise_Subscriptions(){

		//Table structure for table `Subscriptions`
		
		$Query = "CREATE TABLE IF NOT EXISTS `".$this->table_name."` (".
				"`".$this->id."` int(11) NOT NULL AUTO_INCREMENT,".
				"`".$this->client_id."` int(11) NOT NULL,".
				"`".$this->expiration_date."` date NOT NULL,".
				"`".$this->sms_subscribed."` int(6) NOT NULL,".
				"`".$this->sms_subscription_used."` int(6) NOT NULL DEFAULT 0,".
				"`".$this->token."` varchar(30) NOT NULL,".
				"`".$this->status."` int(1) NOT NULL DEFAULT 1,".
				"`".$this->subscription_date."` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
				"PRIMARY KEY (`id`), ".
				" UNIQUE INDEX (`".$this->id."`)".
				")AUTO_INCREMENT=100 ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

		$execute_query_result = $this->databaseObject->PerformQuery($Query);
		if (!$execute_query_result) {
			$this->error[] = " create ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		return true;
	}

	public function get_Subscriptions(){
		return $this->databaseObject->get_all($this->table_name);
	}

	public function get_subscription_by_id($id){

		$Query = "select * from ".$this->table_name." where id='".$id."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return mysqli_fetch_assoc($this->QueryResult);

		}
		return false;
	}

	public function get_subscription_by_client_id($client_id){

		$Query = "select * from ".$this->table_name." where ".$this->client_id."='".$client_id."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return $this->QueryResult;
		}
		$this->error[] = " error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
		
		return false;
	}
	public function verifySender($token, $no_of_text){


		$Query = "select ".$this->id." from ".$this->table_name." where ".$this->token."='".$token."'".
		"AND ".$this->status." = '1' and (".$this->sms_subscribed." - ".$this->sms_subscription_used.
		") - ".$no_of_text." >= 0;";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return $this->QueryResult;
		}
		$this->error[] = " error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
		
		return false;
	}
	
	function createNewSubscription($client_id, $no_of_sms, $expiration_date = '2027-12-31'){
		
		if (!$this->add_subscription($client_id, $no_of_sms,$expiration_date)) {
			return false;
		
		}
		return $this->get_subscription_by_id($this->databaseObject->dbConnection->insert_id);

	  }
	

	public function add_subscription( $client_id,$sms_subscribed, $expiration_date ='2027-12-31' ){

		$token = $this->generateUniqueToken();

		$Query = "INSERT INTO `Subscriptions` ( `".$this->client_id."`".
				", `".$this->expiration_date."`, `".$this->sms_subscribed."`, `".$this->token."`)".
				" VALUES ('".$client_id."', '".$expiration_date."', '".$sms_subscribed."', '".$token."');";
		
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if (!$this->QueryResult) {

			$this->error[] = "insert data in ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			return false;
		}

		return true;
	}

	public function update_client($id, $client_id, $sms_subscribed, $token, $expiration_date='2027-12-31'){

		$Query = "UPDATE `".$this->table_name."` SET ".$this->client_id." = '".$client_id."', "
		.$this->expiration_date." = '".$expiration_date."', ".$this->token." = '".$token."', "
		.$this->sms_subscribed." = ".$sms_subscribed."' WHERE id=".$id.";";

		if (!$this->databaseObject->PerformQuery($Query)) {
			$this->error[] = "update data in ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		else return true;
	}
	
	public function incrementSmsUsage($token, $no_of_text){

		$Query = "UPDATE `".$this->table_name."` SET ".$this->sms_subscription_used.
		" = ".$this->sms_subscription_used." + ".$no_of_text." WHERE ".$this->token."='".$token."';";

		if (!$this->databaseObject->PerformQuery($Query)) {
			$this->error[] = "update data in ".$this->table_name." table error.<br/> Error Description :<br/> client_id : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		else return true;
	}

	function generateUniqueToken() {
		return bin2hex(random_bytes(15));
	}

	function generateUniqueToken1() {
		return substr(str_replace(['+', '/', '='], '', base64_encode(openssl_random_pseudo_bytes(24))), 0, 30);
	}
	

	public function test(){
		return "the Subscriptions class is working";
	}

	public function insert_default_Subscriptions(){
		if ($this->initilise_Subscriptions()) {
			if ($this->add_subscription('1', '1', '2027-12-31', '5')) {
			return true;
			}
		}
		return false;
	}
}
?>
