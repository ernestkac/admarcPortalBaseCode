<?php

require_once "classes\database.php";
class payeCalculation {


	private $earningsDeductions;
	private $employeeInfo;
	private $bankScheduleInfo;
	public $taxBrackets = [];
	
	private $databaseObject;
	private $currentPerpost;


	private $databaseName;

	public $error = array();

	public function __construct($databaseName){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);

		$this->taxBrackets = $this->getTaxTable();
		
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

	public function getTotalTaxTableEarnings($perpost){
		$query = "SELECT 
				t1.EmpId, 
				t2.PAYE,
				t1.TotalTaxableEarn,
				t3.netamt,
				t2.perent
			FROM 
				(SELECT EmpId, SUM(TranAmt) AS TotalTaxableEarn 
				FROM PRTran
				WHERE PerEnt = '".$perpost."'
				AND DrCr = 'D'
				AND EarnDedId IN (SELECT Id FROM EarnType WHERE S4Future06 > 0)
				GROUP BY EmpId) t1
			LEFT JOIN 
				(SELECT EmpId, TranAmt AS PAYE, perent
				FROM PRTran
				WHERE PerEnt = '".$perpost."'
				AND EarnDedId = 'PAYE') t2
			ON t1.EmpId = t2.EmpId
			LEFT JOIN 
			(select empid, netamt from PRDoc
			WHERE PerEnt = '".$perpost."'
			) t3
			ON t1.EmpId = t3.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);

		return $results ? $results : false;
	}
		
	public function checkingPaye(){
		$message = "";
		$totalTaxableEarnings = $this->getTotalTaxTableEarnings($this->currentPerpost);

		while ($row = sqlsrv_fetch_array($totalTaxableEarnings, SQLSRV_FETCH_ASSOC)) {
			
			$TotalTaxableEarn = $row['TotalTaxableEarn'];
			$storedPaye = $row['PAYE'];
			$storedNetpay = $row['netamt'];
			$empid = $row['EmpId'];

			$calculatedPaye = $this->calculatePAYE($TotalTaxableEarn);
			
			if($storedPaye != $calculatedPaye){

				$newNetPay = $storedNetpay + $storedPaye - $calculatedPaye;
				
				if($this->correctNetPay($empid, $newNetPay,$this->currentPerpost)){
					//$message .= $empid. " changed netPay : ". $storedNetpay ." -> ".$newNetPay."\n";
				}
				If($this->correctPAYE($empid, $calculatedPaye,$this->currentPerpost)){
					$message .= $empid. " : ";
				}
			}
		}
		return $message;
    }

	public function correctNetPay($empid, $netPay, $perpost){
		$query = "update prdoc
		set netamt = ".$netPay."
		where
		PerEnt = '".$perpost."'
		and empid = '".$empid."'";

		$results = $this->databaseObject->PerformQuery( $query);

		return $results ? true : false;
	}

	public function correctPAYE($empid, $paye, $perpost){
		$query = "update PRtran
		set TranAmt = ".$paye.",
		S4Future06 = -1 * ".$paye."
		where
		PerEnt = '".$perpost."'
		   AND EarnDedId = 'PAYE'
		and empid = '".$empid."'";

		$results = $this->databaseObject->PerformQuery( $query);

		return $results ? true : false;

	}
	public function getTaxTable(){
		$query = "SELECT user6 as limit, s4future04 as rate FROM creditMgr ORDER BY user6 ASC";
		
		$results = $this->databaseObject->PerformQuery( $query);
		
		$taxBrackets = [];

		while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
			// Handle the last bracket where limit might be NULL
			$limit = $row['limit'] === null ? PHP_FLOAT_MAX : $row['limit']/12;
			$taxBrackets[] = ['limit' => $limit, 'rate' => $row['rate']/100];
		}
		return $taxBrackets;
	}

	function calculatePAYE($totalTaxableEarnings) {
		// Define tax brackets and rates (as of October 2023)
		
		$taxableIncome = $totalTaxableEarnings;
		$paye = 0;
		foreach ($this->taxBrackets as $bracket) {
			if ($taxableIncome <= 0) {
				break;
			}
	
			$bracketAmount = min($taxableIncome, $bracket['limit']);
			$paye += $bracketAmount * $bracket['rate'];
			$taxableIncome -= $bracketAmount;
		}
	
		return $paye;
	}


}
?>
