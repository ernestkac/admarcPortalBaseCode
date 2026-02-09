<?php

require_once 'database.php';
require_once 'downloadReport.php';
class EarningsAndDeductions {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseNumber, $PerPost){
		$this->databaseNumber = $databaseNumber;
		$this->databaseObject = new Database($databaseNumber);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			$query = "select distinct EarnDedId, user1, PerPost from PRTran ".
			"where PerPost = '".$PerPost."' ".
			"and TranAmt <> 0;";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {
				/*while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
					var_dump($row );
					echo " ";
				}*/
				$querysGenerated = explode("GO", $this->generateQuery($results));
		
				$outputResult;
				foreach ($querysGenerated as $queryGenerated){
					//var_dump($queryGenerated);
					//echo "***********\n";
					$outputResult = $this->databaseObject->PerformQuery( $queryGenerated);
				}
		
				/*while ($row =sqlsrv_fetch_array($outputResult, SQLSRV_FETCH_ASSOC))
				var_dump($row );*/
				if ($outputResult) {
					
					$this->fileName = "Earnings and deductions for ".$this->databaseObject->databaseNames[$this->databaseNumber]." period ".$PerPost.".csv";
					
					$this->reportFileManagerObject->generateCSVFile($outputResult, $this->fileName);
					
					$this->reportFileManagerObject->downloadFile($this->fileName);
				}
				
			}else{
				var_dump(sqlsrv_errors());
			}
		
		}
	}


	public	function generateQuery($results) {

		$outputCode = 'IF OBJECT_ID(\'dbo.paye_tax_return\', \'V\') IS NOT NULL  ' .
			'DROP VIEW dbo.paye_tax_return ' .
			'GO ' .
			'create view paye_tax_return ' .
			'as ' .
			'SELECT EmpId ';
	
		$selectPerPostPart = '';
	
		$selectHeader = 'GROUP BY EmpId; ' .
			'GO ' .
			'select Employee.empid, Employee.Name , PayGroup.Descr as payGroup, WorkLoc.Descr as location, ' .
			' XHR_Job.JobTitle as designation, prdoc.NetAmt, employee.user5 as bankcode, MiscCharge.Descr as bankName, employee.user2 as acctNbr';
	
		$lastPartOfSelectStatement = '';
	
		while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
			//echo $row;
	
			$earnDedId = trim($row['EarnDedId']);
			$earnDedName = str_replace(' ', '_',trim($row['user1']));
			$earnDedName = str_replace('.', '_',$earnDedName);
			$earnDedName = str_replace('-', '_',$earnDedName);
			$earnDedName = str_replace('(', '_',$earnDedName);
			$earnDedName = str_replace(')', '_',$earnDedName);
			$earnDedName = str_replace('&', '_',$earnDedName);
			$earnDedName = str_replace('\'', '_',$earnDedName);
			$earnDedName = str_replace('\\', '_',$earnDedName);
			$earnDedName = str_replace('/', '_',$earnDedName);
			$perpost = trim($row['PerPost']);
	
			/*echo '*' . $earnDedId . '*';
			echo '*' . $earnDedName . '*';*/
	
			$outputCode .= ',MAX(CASE WHEN EarnDedId = \'' . $earnDedId . '\' THEN TranAmt END) AS ' . $earnDedName . ' ';
	
			$selectPerPostPart = ' FROM PRTran ' .
				'where PRTran.PerPost = \'' . $perpost . '\' ';
	
			$selectHeader .= ',' . $earnDedName;
	
			$lastPartOfSelectStatement = ' from ' .
				'Employee ' .
				'left join W2EmpName on Employee.EmpId = W2EmpName.EmpId ' .
				'left join paye_tax_return on Employee.EmpId = paye_tax_return.EmpId ' .
				'left join prdoc on Employee.EmpId = prdoc.EmpId ' .
				'left join XHR_Job on Employee.User6 = XHR_Job.JobId ' .
				'left join PayGroup on Employee.PayGrpId = PayGroup.PayGrpId ' .
				'left join WorkLoc on Employee.DfltWrkloc = WorkLoc.WrkLocId ' .
				'left join MiscCharge on Employee.user5 = MiscCharge.MiscChrgID ' .
				'where prdoc.perent = \'' . $perpost . '\' and Employee.EmpId in ' .
				'(select EmpId from PRTran where PerPost = \'' . $perpost . '\' and PRTran.PerEnt = \'' . $perpost . '\') ' .
				'order by Name';
		
		}
		
		$outputCode .= $selectPerPostPart . $selectHeader . $lastPartOfSelectStatement;
	
		return $outputCode;
		
	}

}
?>
