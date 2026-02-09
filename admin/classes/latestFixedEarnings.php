<?php

require_once 'database.php';
require_once 'downloadReport.php';
class latestFixedEarnings {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseName;
	private $empids;

	public $error = array();

	public function __construct($databaseName, $empids){
		$this->empids = $empids;
		$this->databaseName = $databaseName;
		$this->databaseObject = new Database($databaseName);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			if($this->testEmpids()){
		
				$query = "IF OBJECT_ID('dbo.paye_tax_return', 'V') IS NOT NULL ".
					"DROP VIEW dbo.paye_tax_return \n".
					"GO \n".
					"IF OBJECT_ID('dbo.paye_tax_return', 'V') IS NOT NULL \n".
					"DROP VIEW dbo.fixed_earnings_transpose \n".
					"GO ".
			
					"create view paye_tax_return \n".
					"as ".
					"select PRTran.empid, name, EarnDedId, prtran.User1 as earning,  MAX(perpost) as max_perpost ".
					"from PRTran ".
					"left join Employee on Employee.EmpId = PRTran.EmpId ".
					"where TranAmt != 0 AND EarnDedId in ".
					"(select Id from EarnType where S4Future09 = 0) ";
				
					
				if ($this->empids != "") {
					$query .= " and Employee.EmpId in " . $this->empids;
				}
					
				$query .= " group by PRTran.EmpId, name, EarnDedId, PRTran.user1 ".
					
					"GO ".
					
					"select distinct EarnDedId,  earning ".
					"from paye_tax_return";

					$querysParts = explode("GO", $query);
			
					$results;
					foreach ($querysParts as $queryGenerated){
						
						$results = $this->databaseObject->PerformQuery( $queryGenerated);
						/*if(!$results){
							var_dump($queryGenerated);
						echo "******error*****\n";
						}*/
					}
			
			
				if ($results) {
					/*while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
						var_dump($row );
						echo "\n ";
					}*/
					$queryForFixedEarningsTranspose = "create view fixed_earnings_transpose ".
						"as ".
						"select paye_tax_return.EmpId, paye_tax_return.EarnDedId,paye_tax_return.earning, PRTran.TranAmt ".
						"from paye_tax_return, PRTran ".
						"where paye_tax_return.EmpId = PRTran.EmpId ".
						"and paye_tax_return.max_perpost = PRTran.PerPost ".
						"and paye_tax_return.earndedid = PRTran.EarnDedId ";

					$resultsForFixedEarningsTranspose = $this->databaseObject->PerformQuery( $queryForFixedEarningsTranspose);
	/*
					if($resultsForFixedEarningsTranspose){
						echo "ok";
					}else{echo "bad";
						var_dump($queryForFixedEarningsTranspose);
					}*/
					$querysGenerated = explode("GO", $this->generateQuery($results));
			
					$outputResult;
					foreach ($querysGenerated as $queryGenerated){
						//var_dump($queryGenerated);
						//echo "***********\n";
						$outputResult = $this->databaseObject->PerformQuery( $queryGenerated);
						
					}
					if($outputResult){
						/*while ($row =sqlsrv_fetch_array($outputResult, SQLSRV_FETCH_ASSOC))
						var_dump($row );*/
						$this->fileName = "latest fixed Earnings for ".$this->databaseObject->databaseNames[$this->databaseName].".csv";
						
						$this->reportFileManagerObject->generateCSVFile($outputResult, $this->fileName);
						
						$this->reportFileManagerObject->downloadFile($this->fileName);
					}
				}else{
					var_dump(sqlsrv_errors());
				}
			}
		
		}
	}

	public function testEmpids(){
		if ($this->empids != "") {
			$testEmpIdList = "select empid from prtran where EmpId in " . $this->empids;
			$testResult = $this->databaseObject->PerformQuery( $testEmpIdList);
			if ($testResult) {
				while ($row =sqlsrv_fetch_array($testResult, SQLSRV_FETCH_ASSOC))
					return true;
				return false;
			} else {
				return false;
			}
			
		}else{
			
			return true;
		}
	}


	public	function generateQuery($results) {

		$outputCode = 'IF OBJECT_ID(\'dbo.fixed_earnings_view\', \'V\') IS NOT NULL  ' .
			'DROP VIEW dbo.fixed_earnings_view ' .
			'GO ' .
			'create view fixed_earnings_view ' .
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
			$earnDedName = str_replace(' ', '_',trim($row['earning']));
			$earnDedName = str_replace('.', '_',$earnDedName);
			$earnDedName = str_replace('-', '_',$earnDedName);
			$earnDedName = str_replace('(', '_',$earnDedName);
			$earnDedName = str_replace(')', '_',$earnDedName);
			$earnDedName = str_replace('&', '_',$earnDedName);
			$earnDedName = str_replace('\'', '_',$earnDedName);
			$earnDedName = str_replace('\\', '_',$earnDedName);
			$earnDedName = str_replace('/', '_',$earnDedName);
	
			/*echo '*' . $earnDedId . '*';
			echo '*' . $earnDedName . '*';*/
	
			$outputCode .= ',MAX(CASE WHEN EarnDedId = \'' . $earnDedId . '\' THEN TranAmt END) AS ' . $earnDedName . ' ';
	
			$selectPerPostPart = ' FROM fixed_earnings_transpose ';
			
			if ($this->empids != "") {
				$selectPerPostPart .= " where EmpId in " . $this->empids;
			}

	
			$selectHeader .= ',' . $earnDedName;
	
			$lastPartOfSelectStatement = ' from ' .
				'Employee ' .
				'left join W2EmpName on Employee.EmpId = W2EmpName.EmpId ' .
				'inner join fixed_earnings_view on Employee.EmpId = fixed_earnings_view.EmpId ' .
				'left join XHR_Job on Employee.User6 = XHR_Job.JobId ' .
				'order by Name';
		
		}
		
		$outputCode .= $selectPerPostPart . $selectHeader . $lastPartOfSelectStatement;
	
		return $outputCode;
		
	}

}
?>
