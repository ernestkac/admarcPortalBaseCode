<?php

require_once 'database.php';
require_once 'downloadReport.php';
class Employee {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseName){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);
		$this->reportFileManagerObject = new ReportFileManager();

	}

	public function getEmployeeDetails(){
			
			$query = "select b.EmpId,b.Name,b.Status,b.User6 as JobCode,c.JobTitle,c.Grade,b.DfltWrkloc as Loc_Code,d.Descr as Location
			,b.StdSlry as BasicPay,b.PayGrpId, e.Descr as PayGroup, f.NameSuffix as Gender
			from Employee b, XHR_Job c, WorkLoc d, PayGroup e, W2EmpName f
			where  b.User6 = c.jobid and b.DfltWrkloc = d.WrkLocId and b.PayGrpId = e.PayGrpId
			and b.EmpId = f.EmpId 
			and b.Status = 'A'";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {

				$this->fileName = "Employee Details for ".$this->databaseObject->databaseNames[$this->databaseNumber].".csv";
				
				$this->reportFileManagerObject->generateCSVFile($results, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
			}else{
				var_dump(sqlsrv_errors());
			}
		
		
	}

	public function changeName($empid,$newName){
		$query = "update ADMARCPortalUsers set name = '".$newName."' ".
			"where empid = '".$empid."';";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		$query = "update Employee set name = '".$newName."' ".
			"where empid = '".$empid."';";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return true;
		
		return false;
	}
	public function changeAccessLevel($empid,$newAccessLevel){
		$query = "update ADMARCPortalUsers set accessLevel = '".$newAccessLevel."' ".
			"where empid = '".$empid."';";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return true;
		
		return false;
	}
	public function changeDivision($empid,$newDivision){
		$query = "update ADMARCPortalUsers set division = '".$newDivision."' ".
			"where empid = '".$empid."';";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return true;
		
		return false;
	}
	public function search($searchString, $searchSourceID = 0){
		$searchSource = 'employee';
		if($searchSourceID == 1)
			$searchSource = 'employeeArchive';
		$query = "
		SELECT top 10 users_db1.empid, users_db1.name, hr_db1.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[1].".dbo.ADMARCPortalUsers users_db1
		inner join ".Database::databases[1].".dbo.".$searchSource." emp_db1 on emp_db1.EmpId = users_db1.empid
		inner join ".Database::databases[1].".dbo.XHR_Job hr_db1 on emp_db1.user6 = hr_db1.JobId
        WHERE users_db1.empid LIKE ?
           OR users_db1.name LIKE ? 
           OR hr_db1.JobTitle LIKE ?
		   UNION
		SELECT top 10 users_db2.empid, users_db2.name, hr_db2.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[2].".dbo.ADMARCPortalUsers users_db2
		inner join ".Database::databases[2].".dbo.".$searchSource." emp_db2 on emp_db2.EmpId = users_db2.empid
		inner join ".Database::databases[2].".dbo.XHR_Job hr_db2 on emp_db2.user6 = hr_db2.JobId
        WHERE users_db2.empid LIKE ?
           OR users_db2.name LIKE ? 
           OR hr_db2.JobTitle LIKE ?
		   UNION
		SELECT top 10 users_db3.empid, users_db3.name, hr_db3.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[3].".dbo.ADMARCPortalUsers users_db3
		inner join ".Database::databases[3].".dbo.".$searchSource." emp_db3 on emp_db3.EmpId = users_db3.empid
		inner join ".Database::databases[3].".dbo.XHR_Job hr_db3 on emp_db3.user6 = hr_db3.JobId
        WHERE users_db3.empid LIKE ?
           OR users_db3.name LIKE ? 
           OR hr_db3.JobTitle LIKE ?
		   UNION   
		SELECT top 10 users_db4.empid, users_db4.name, hr_db4.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[4].".dbo.ADMARCPortalUsers users_db4
		inner join ".Database::databases[4].".dbo.".$searchSource." emp_db4 on emp_db4.EmpId = users_db4.empid
		inner join ".Database::databases[4].".dbo.XHR_Job hr_db4 on emp_db4.user6 = hr_db4.JobId
        WHERE users_db4.empid LIKE ?
           OR users_db4.name LIKE ? 
           OR hr_db4.JobTitle LIKE ?
		   UNION   
		SELECT top 10 users_db5.empid, users_db5.name, hr_db5.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[5].".dbo.ADMARCPortalUsers users_db5
		inner join ".Database::databases[5].".dbo.".$searchSource." emp_db5 on emp_db5.EmpId = users_db5.empid
		inner join ".Database::databases[5].".dbo.XHR_Job hr_db5 on emp_db5.user6 = hr_db5.JobId
        WHERE users_db5.empid LIKE ?
           OR users_db5.name LIKE ? 
           OR hr_db5.JobTitle LIKE ?
		   UNION   
		SELECT top 10 users_db6.empid, users_db6.name, hr_db6.JobTitle as position , searchSourceID = ".$searchSourceID."
        FROM ".Database::databases[6].".dbo.ADMARCPortalUsers users_db6
		inner join ".Database::databases[6].".dbo.".$searchSource." emp_db6 on emp_db6.EmpId = users_db6.empid
		inner join ".Database::databases[6].".dbo.XHR_Job hr_db6 on emp_db6.user6 = hr_db6.JobId
        WHERE users_db6.empid LIKE ?
           OR users_db6.name LIKE ? 
           OR hr_db6.JobTitle LIKE ?
		         
		   ";
		//var_dump($query);
		$searchString = '%'.$searchString.'%';
		$results = $this->databaseObject->PerformQuery( $query, array_fill(0, 18, $searchString));
		
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return $results;
		
		return false;
	}

}
?>
