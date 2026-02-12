<?php

require_once 'database.php';
require_once 'downloadReport.php';
class EventLog {

	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;
	private $databaseName;

	public $error = array();

	public function __construct($databaseNumber = 0){
		$this->financelist = array("482217", "759144", "711342", "728470","764115","729663");
		$this->databaseNumber = $databaseNumber;
		$this->databaseObject = new Database($databaseNumber);
		$this->databaseName = $this->databaseObject->databaseName;
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			//echo "object ready";
		}else{
			var_dump(sqlsrv_errors());
		}
		
	}

	public function __destruct(){
		unset($this->databaseObject);
	}


	public	function addLog($empid, $name, $accessLevel, $operation) {
		$query = "INSERT INTO dbo.ADMARCPortalLogs
			( EmpId,  Name,  accessLevel,CpnyID , operation)".
			" values ('".$empid."','".$name."','".$accessLevel."','".$this->databaseName."','".$operation."')";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
		
	}

	public	function getUsageFrequncyByMonth($month) {
		$query = "
				SELECT name, log_time, COUNT(empid) AS usage_count
				FROM ADMARCPortalLogs
   				WHERE CONVERT(VARCHAR(7), log_time, 120) = '".$month."'
				GROUP BY name, log_time
			";


		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		($results);
		//(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
		
	}

	public	function getUsageFrequncyByDay($month) {
		$query = "
				SELECT DAY(log_time) AS day, COUNT(*) AS usage_count
				FROM ADMARCPortalLogs
   				WHERE CONVERT(VARCHAR(7), log_time, 120) = '".$month."'
				GROUP BY  DAY(log_time)
			";


		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		($results);
		//(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
		
	}

	public	function getUsageFrequncyInMonth($year) {
		$query = "
				SELECT MONTH(log_time) AS month, COUNT(empid) AS usage_count
        FROM ADMARCPortalLogs
        WHERE YEAR(log_time) = '".$year."'
        GROUP BY MONTH(log_time)
			";


		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		($results);
		//(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
		
	}
	public	function getUsageFrequncyInMonthByName($year) {
		$query = "
				SELECT name, MONTH(log_time) AS month, COUNT(empid) AS count
        FROM ADMARCPortalLogs
        WHERE YEAR(log_time) = '".$year."'
        GROUP BY name, MONTH(log_time)
			";


		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		($results);
		//(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
		
	}
	
	public	function financeGroup($empid) {

		if (in_array($empid, $this->financelist))
			return true;
		return false;
		
	}


}
?>
