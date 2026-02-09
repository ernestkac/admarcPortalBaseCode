<?php
require_once 'config.php';
class Database {

	private $counter = 0;
	public $dbConnection;
	public $databaseName;

	const databases = ["AdmarcSLAppLive",
    "AdmarcSLAppPayRolHQ",
    "AdmarcSLAppPayRolSN",
    "AdmarcSLAppPayRollHW",
    "AdmarcSLAppPayRollSS",
    "ADMARCSLAPPPAYROLLC",
    "AdmarcSLAppPayRollN"];

	public $databaseNames = ["solomon",
    "executive",
    "senior",
    "junior HQ",
    "south",
    "centre",
    "north"];
	
	
	public $Query = "";
	public $ReturnData = "";
	public $QueryResult;
	public $TableName;
	

	function __construct($databaseNumber){
		$this->databaseName = Database::databases[$databaseNumber];
		$this->GetConnection();
	}

	function initializeDB(){
	
	}

	function GetConnection(){

		$ConnectionOption = [
			"Database" => $this->databaseName,
			"UID" => DB_UID,
			"PWD" => DB_PWD	
		];

		$this->dbConnection = sqlsrv_connect(DBHost, $ConnectionOption);

		if($this->dbConnection){
			
			return $this->dbConnection;
		}
		else{
			//die(print_r(sqlsrv_errors(), true));
			die("Failed connecting to the server!");
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

	public function PerformQuery($query, $params = []){
		$this->QueryResult = sqlsrv_query($this->dbConnection ,$query, $params);
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
