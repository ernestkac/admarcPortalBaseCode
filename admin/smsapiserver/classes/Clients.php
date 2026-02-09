<?php
require_once 'classes/database.php';

class Clients {

	public $table_name = "clients";
	public $id = 'id';
	public $name = 'name';
	public $shortname = 'shortname';
	public $phone = 'phone';
	public $email = 'email';
	public $password = 'password';
	public $status = 'status';
	public $error_time = 'error_time';

	public $databaseObject;
	public $QueryResult;

	public $error = array();

	public function __construct(){
		
		$this->databaseObject = new Database;
		$this->initilise_clients();
	}

	function initilise_clients(){

		//Table structure for table `clients`
		
		$Query = "CREATE TABLE IF NOT EXISTS `".$this->table_name."` (".
				"`".$this->id."` int(11) NOT NULL AUTO_INCREMENT,".
				"`".$this->name."` varchar(20) NOT NULL,".
				"`".$this->shortname."` varchar(10) NOT NULL,".
				"`".$this->phone."` varchar(13) NOT NULL,".
				"`".$this->email."` varchar(30) NOT NULL,".
				"`".$this->password."` varchar(15) NOT NULL,".
				"`".$this->status."` int(1) NOT NULL DEFAULT 1,".
				"`time_stamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
				"PRIMARY KEY (`id`), ".
				" UNIQUE INDEX (`".$this->name."`),".
				" UNIQUE INDEX (`".$this->shortname."`)".
				")AUTO_INCREMENT=1000 ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

		$execute_query_result = $this->databaseObject->PerformQuery($Query);
		if (!$execute_query_result) {
			$this->error[] = " create ".$this->table_name." table error.<br/> Error Description :<br/> name : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		return true;
	}

	public function get_clients(){
		return $this->databaseObject->get_all($this->table_name);
	}

	public function get_client_by_id($id){

		$Query = "select * from ".$this->table_name." where id='".$id."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return mysqli_fetch_assoc($this->QueryResult);

		}
		return false;
	}

	public function get_client_by_name($name){

		$Query = "select * from ".$this->table_name." where ".$this->shortname."='".$name."';";
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if(mysqli_affected_rows($this->databaseObject->dbConnection)>0){

			return mysqli_fetch_assoc($this->QueryResult);
		}
		$this->error[] = " error.<br/> Error Description :<br/> name : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
		
		return false;
	}

	public function add_client($name, $shortname, $phone, $email, $password){

		$Query = "INSERT INTO `clients` (`".$this->name."`, `".$this->shortname."`".
				", `".$this->phone."`, `".$this->email."`, `".$this->password."`)".
				" VALUES ('".$name."', '".$shortname."', '".$phone."', '".$email."', '".$password."');";
		
		$this->QueryResult = $this->databaseObject->PerformQuery($Query);
		if (!$this->QueryResult) {

			$this->error[] = "insert data in ".$this->table_name." table error.<br/> Error Description :<br/> name : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			return false;
		}

		return true;
	}


	public function update_client($id, $name, $shortname, $phone, $email, $password){

		$Query = "UPDATE `".$this->table_name."` SET ".$this->name." = '".$name."', ".$this->shortname." = '"
		.$shortname."', ".$this->email." = '".$email."', ".$this->password." = '".$password."', ".$this->phone." = ".$phone."' WHERE id=".$id.";";

		if (!$this->databaseObject->PerformQuery($Query)) {
			$this->error[] = "update data in ".$this->table_name." table error.<br/> Error Description :<br/> name : " . mysqli_error($this->databaseObject->dbConnection) . 
			"<br/>error no. " . mysqli_errno($this->databaseObject->dbConnection). "<br>Query : ".$Query."<br>";
			
			return false;
		}
		else return true;
	}

	public function test(){
		return "the clients class is working";
	}

	public function insert_default_clients(){
		if ($this->initilise_clients()) {
			if ($this->add_client('Green Switch Digital', 'GSD', '0994544873' , 'ernestkac.work@gmail.com', '1234567890')) {
			return true;
			}
		}
		return false;
	}
}
?>
