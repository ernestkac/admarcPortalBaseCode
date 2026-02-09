<?php
class Database {

	private $counter = 0;
	public $dbConnection;
	private $DBHost = "localhost";
	private $DBName = "smsapi";
	private $DBUsername = "root";
	private $DBPassword = "";
	public $Query = "";
	public $ReturnData = "";
	public $QueryResult;
	public $TableName;
	

	function __construct(){
		$this->GetConnection();
	}

	function initializeDB(){
		$this->dbConnection = mysqli_connect($this->DBHost ,$this->DBUsername ,$this->DBPassword);

		$SQL = "CREATE DATABASE IF NOT EXISTS `smsapi`";
		$execute_query_result = mysqli_query($this->dbConnection,$SQL);
		if (!$execute_query_result) {
			die("create databae error with error name : " . mysqli_connect_error() . 
			"<br/>error no. " . mysqli_connect_errno());
			return false;
		}

		//reconnecting with the database name
		$this->GetConnection();
		header("Location: index");
	}

	function GetConnection(){

		$this->dbConnection = mysqli_connect($this->DBHost ,$this->DBUsername ,$this->DBPassword ,$this->DBName);

		if(mysqli_connect_errno()){
			$this->initializeDB();
			return $this->dbConnection;
			
		}
		else{
			return $this->dbConnection;
		}
	}

	public function get_all($table_name){
		$query ="select * from {$table_name};";
		$this->QueryResult = $this->PerformQuery($query);
		if(mysqli_affected_rows($this->dbConnection)>0){
			return $this->QueryResult;
		}
		return false;
	}

	public function PerformQuery($query){
		$this->QueryResult = mysqli_query($this->dbConnection ,$query);
		return $this->QueryResult;
	}

	/*function LookUp($phrase){
		//$name = mysqli_real_escape_string($this->dbConnection,$username);
		$name = $username;

		while ($this->TableName = $this->UserTableArray[$this->counter]) {

			$Query = "select pass from {$this->TableName} where user_name='{$username}';";
			$this->QueryResult = $this->PerformQuery($Query);
			if(mysqli_affected_rows($this->dbConnection)>0){
				$this->UserType = $this->DetermineUser($this->TableName);
				break;
			}
			$this->counter++;
		}
		
		if($this->QueryResult){
			$this->ReturnData = mysqli_fetch_assoc($this->QueryResult);
			return $this->ReturnData;
		}
		else{

			return false;
		}
	}*/

	public function test(){
		return "the database class is working";
	}

}
?>
