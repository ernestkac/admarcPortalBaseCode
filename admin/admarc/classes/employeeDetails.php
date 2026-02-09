<?php

require_once 'database.php';
require_once 'downloadReport.php';
class EmployeeDetail {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseName;

	public $error = array();

	public function __construct($databaseName, $PerPost){
		$this->databaseName = $databaseName;
		$this->databaseObject = new Database($databaseName);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			$query = "select b.EmpId,b.Name,b.Status,b.User6 as JobCode,c.JobTitle,c.Grade,b.DfltWrkloc as Loc_Code,d.Descr as Location
			,b.StdSlry as BasicPay,b.PayGrpId, e.Descr as PayGroup, f.NameSuffix as Gender
			from Employee b, XHR_Job c, WorkLoc d, PayGroup e, W2EmpName f
			where  b.User6 = c.jobid and b.DfltWrkloc = d.WrkLocId and b.PayGrpId = e.PayGrpId
			and b.EmpId = f.EmpId 
			and b.Status = 'A'";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {

				$this->fileName = "Employee Details for ".$this->databaseName.".csv";
				
				$this->reportFileManagerObject->generateCSVFile($results, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
			}else{
				var_dump(sqlsrv_errors());
			}
		
		}
	}

}
?>
