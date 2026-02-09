<?php
class Database {

	private $counter = 0;
	public $dbConnection;
	public $databaseName;

	private $DBHost = "solomon.admarc.co.mw";
	
	
	public $Query = "";
	public $ReturnData = "";
	public $QueryResult;
	public $TableName;
	

	function __construct($databaseName){
		$this->databaseName = $databaseName;
		$this->GetConnection();
	}

	function initializeDB(){
	
	}

	function GetConnection(){

		$ConnectionOption = [
			"Database" => $this->databaseName,
			"UID" => "e.kachingwe",
			"PWD" => "Ernkacadmarc1"
		];

		$this->dbConnection = sqlsrv_connect($this->DBHost, $ConnectionOption);

		if($this->dbConnection){
			
			return $this->dbConnection;
		}
		else{
			//die(print_r(sqlsrv_errors(), true));
			return false;
		}
	}

	public function get_all($table_name){
		$query ="SELECT * from {$table_name};";
		$this->QueryResult = $this->PerformQuery($query);
		if(mysqli_affected_rows($this->dbConnection)>0){
			return $this->QueryResult;
		}
		return false;
	}

	public function PerformQuery($query){
		$this->QueryResult = sqlsrv_query($this->dbConnection ,$query);
		return $this->QueryResult;
	}

	function LookUp($phrase){
		//$name = mysqli_real_escape_string($this->dbConnection,$username);
		$name = $username;

		while ($this->TableName = $this->UserTableArray[$this->counter]) {

			$Query = "SELECT * from {$this->TableName} where user_name='{$username}';";
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
	}

	public function test(){
		return "the database class is working";
	}

}
?>
