<?php

require_once 'database.php';
require_once 'downloadReport.php';
class fixedEarnings {


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
			$query = "select distinct EarnDedId, user1, PerPost from PRTran ".
			"where PerPost = '".$PerPost."' ".
			"and TranAmt <> 0  and EarnDedId in (select Id from EarnType where S4Future09 = 0);";
		
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
				$this->fileName = "Fixed Earnings for ".$this->databaseName." as per period ".$PerPost.".csv";
				
				$this->reportFileManagerObject->generateCSVFile($outputResult, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
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
			'select Employee.empid, Employee.Name , CONVERT(VARCHAR(10),BirthDate,104) birthday,W2EmpName.NameSuffix as Gender, ' .
			' XHR_Job.Grade, XHR_Job.JobTitle as designation';
	
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
				'left join XHR_Job on Employee.User6 = XHR_Job.JobId ' .
				'where Employee.EmpId in ' .
				'(select EmpId from PRTran where PerPost = \'' . $perpost . '\' and PRTran.PerEnt = \'' . $perpost . '\') ' .
				'order by Name';
		
		}
		
		$outputCode .= $selectPerPostPart . $selectHeader . $lastPartOfSelectStatement;
	
		return $outputCode;
		
	}

}
?>
