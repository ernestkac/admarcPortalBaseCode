<?php

require_once 'database.php';
require_once 'downloadReport.php';
class BankSchedule {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseName, $PerPost){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			$query = "select a.EmpId,a.Name,a.User2 as Bank_Account,
			a.User5 as SortCode,
			b.Descr as Bank_Name, 
			PayGroup.Descr, PRDoc.NetAmt
			from employee a, MiscCharge b, PRDoc, PayGroup
			
			
			where a.status = 'A'
			and a.User5 = b.MiscChrgID
			and a.EmpId = PRDoc.EmpId
			and a.PayGrpId = PayGroup.PayGrpId
			and PRDoc.PerEnt = '".$PerPost."'";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {

				$this->fileName = "Bank Schedule for ".$this->databaseObject->databaseNames[$this->databaseNumber]." ".$PerPost.".csv";
				
				$this->reportFileManagerObject->generateCSVFile($results, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
			}else{
				var_dump(sqlsrv_errors());
			}
		
		}
	}

}
?>
