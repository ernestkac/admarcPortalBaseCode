<?php

require_once "classes\database.php";
class tevetCalculation {


	private $earningsDeductions;
	private $employeeInfo;
	private $bankScheduleInfo;
	public $rate;
	
	private $databaseObject;
	private $currentPerpost;


	private $databaseName;

	public $error = array();

	public function __construct($databaseName){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);

		$this->rate = $this->getTevetRate();
		
		$this->currentPerpost = $this->getCurrentPayrollPeriod();
	}

	public function getCurrentPayrollPeriod(){
		$query = "SELECT PerNbr FROM PRSetup";
		$result = $this->databaseObject->PerformQuery( $query);
		
		if (sqlsrv_has_rows($result)) {
			$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
			return $row["PerNbr"];
		}
		return 0;
	}

	public function getTotalEarnings($perpost){
		$query = "SELECT 
				t1.EmpId, 
				t2.tevet,
				t1.TotalEarn,
				t3.netamt,
				t2.perent
			FROM 
				(SELECT EmpId, SUM(TranAmt) AS TotalEarn 
				FROM PRTran
				WHERE PerEnt = '".$perpost."'
				AND DrCr = 'D'
				GROUP BY EmpId) t1
			LEFT JOIN 
				(SELECT EmpId, TranAmt AS tevet, perent
				FROM PRTran
				WHERE PerEnt = '".$perpost."'
				AND EarnDedId = 'tv01') t2
			ON t1.EmpId = t2.EmpId
			LEFT JOIN 
			(select empid, netamt from PRDoc
			WHERE PerEnt = '".$perpost."'
			) t3
			ON t1.EmpId = t3.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);

		return $results ? $results : false;
	}
		
	public function checkingTevet(){
		$message = "";
		$totalEarnings = $this->getTotalEarnings($this->currentPerpost);

		while ($row = sqlsrv_fetch_array($totalEarnings, SQLSRV_FETCH_ASSOC)) {
			
			$TotalEarn = $row['TotalEarn'];
			$storedTevet = $row['tevet'];
			$storedNetpay = $row['netamt'];
			$empid = $row['EmpId'];

			$calculatedTevet = $this->calculateTevet($TotalEarn);
			
			if($storedTevet != $calculatedTevet){

				$newNetPay = $storedNetpay + $storedTevet - $calculatedTevet;
				
				If($this->correctTevet($empid, $calculatedTevet,$this->currentPerpost)){
					$message .= $empid. " : ";
				}
			}
		}
		return $message;
    }

	public function correctTevet($empid, $tevet, $perpost){
		$query = "update PRtran
		set TranAmt = ".$tevet."
		where
		PerEnt = '".$perpost."'
		   AND EarnDedId = 'TV01'
		and empid = '".$empid."'";

		$results = $this->databaseObject->PerformQuery( $query);

		return $results ? true : false;

	}
	public function getTevetRate(){
		$query = "SELECT user4 as rate FROM Deduction where DedId = 'TV01' ";
		
		$results = $this->databaseObject->PerformQuery( $query);
		
		while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
			return $row['rate']/100;
		}
	}

	function calculateTevet($totalEarnings) {
		
		return $totalEarnings * $this->rate;
	
	}


}
?>
