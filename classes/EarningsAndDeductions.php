<?php

require_once 'database.php';
require_once 'downloadReport.php';
class EarningsAndDeductions {

	private $earningsTable = 'BenEmp';
	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseNumber = 3){
		$this->databaseNumber = $databaseNumber;
		$this->databaseObject = new Database($databaseNumber);
		
	}
	function getEarningsAndDeductionsReport($PerPost){
		$this->PerPost = $PerPost;
		$query = "select distinct EarnDedId, user1, DrCr, PerPost from PRTran ".
		"where PerPost = '".$PerPost."' ".
		"and TranAmt <> 0 order by DrCr DESC;";
	
		$results = $this->databaseObject->PerformQuery( $query);
	
		if ($results) {
			/*while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				var_dump($row );
				echo " ";
			}*/
			$querysGenerated = explode("ernest_kac", $this->generateQuery($results));
	
			$outputResult;
			foreach ($querysGenerated as $queryGenerated){
				//var_dump($queryGenerated);
				//echo "***********\n";
				$outputResult = $this->databaseObject->PerformQuery( $queryGenerated);
			}
	
			/*while ($row =sqlsrv_fetch_array($outputResult, SQLSRV_FETCH_ASSOC))
			var_dump($row );*/
			if ($outputResult) {
				
			$this->reportFileManagerObject = new ReportFileManager();
				
				$this->fileName = "Earnings and deductions for ".$this->databaseObject->databaseNames[$this->databaseNumber]." period ".$PerPost.".csv";
				
				$this->reportFileManagerObject->generateCSVFile($outputResult, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
			}else{
				echo $queryGenerated;
				var_dump(sqlsrv_errors());
			}
			
		}else{
			var_dump(sqlsrv_errors());
		}
	
	}

	function getCurrentPeriods() :string {
		$query = "select pernbr from prsetup";

		$results = $this->databaseObject->PerformQuery( $query);
		if ($results) {
			$period = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
			return $period['pernbr'];
		}
		$errors = sqlsrv_errors();
		if($errors)
		$this->error[] = $errors[0]['message'];

		return false;
	}

	function getEarnings($empid){
		$query = "SELECT empid, benid, Descr, BenEmp.user3 AS amount, 
		BenEmp.user1 AS perpost, BenEmp.user2 AS NofPrds, EarnType.S4Future06 AS taxable ".
		"FROM ".$this->earningsTable.
		" inner join EarnType on benid = id WHERE empid ='".$empid."'";

		$results = $this->databaseObject->PerformQuery( $query);
		$errors = sqlsrv_errors();
		if($errors)
		$this->error[] = $errors[0]['message'];
		return ($results) ? $results : false ;
	}

	function getDeductions($empid){
		$query = "SELECT earndedid, Descr, earnded.user3 AS amount, AddlExmptAmt as principal,
		 earnded.user4 AS balance,	earnded.user1 AS perpost, earnded.user2 AS NofPrds,
		  deduction.EmpleeDed AS netpay ".
		"FROM earnded".
		" inner join deduction on earndedid = dedid WHERE empid ='".$empid."'";

		$results = $this->databaseObject->PerformQuery( $query);
		$errors = sqlsrv_errors();
		if($errors)
		$this->error[] = $errors[0]['message'];
		//var_dump($errors);
		return ($results) ? $results : false ;
	}

	public	function generateQuery($results) {

		$outputCode = "
			IF OBJECT_ID('dbo.employee_net', 'V') IS NOT NULL
			DROP VIEW dbo.employee_net
			ernest_kac

			create view employee_net
			as

			select emp.empid,emp.Name, 
				sum(CASE WHEN drcr = 'D' THEN  TranAmt ELSE 0 END)  -
				sum(CASE WHEN drcr = 'C' THEN  TranAmt ELSE 0 END) AS netpay, NetAmt as NetFrmBankSc
			from PRTran
			inner join Employee emp on emp.EmpId = PRTran.EmpId
			inner join PRDoc on emp.EmpId = PRDoc.EmpId
			where PRTran.S4Future06 != 0 
			and prtran.PerEnt = '".$this->PerPost."'
			and PRDoc.PerEnt = '".$this->PerPost."'
			group by emp.EmpId,emp.Name,NetAmt
			ernest_kac ".
			
			'IF OBJECT_ID(\'dbo.paye_tax_return\', \'V\') IS NOT NULL  ' .
			'DROP VIEW dbo.paye_tax_return ' .
			'ernest_kac ' .
			'create view paye_tax_return ' .
			'as ' .
			'SELECT EmpId ';
	
		$selectPerPostPart = '';
	
		$selectHeader = 'GROUP BY EmpId; ' .
			'ernest_kac ' .
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
			$DrCr = trim($row['DrCr']);
	
			/*echo '*' . $earnDedId . '*';
			echo '*' . $earnDedName . '*';*/
	
			$outputCode .= ',MAX(CASE WHEN EarnDedId = \'' . $earnDedId . '\' and DrCr = \''.$DrCr.'\' THEN TranAmt END) AS ' . $earnDedName . ' ';
	
			$selectPerPostPart = ' FROM PRTran ' .
				'where PRTran.PerPost = \'' . $perpost . '\' ';
	
			$selectHeader .= ',' . $earnDedName;
	
			$lastPartOfSelectStatement = ' ,netpay,NetFrmBankSc,round(netpay,2) '.
				'- round(NetFrmBankSc,2) as diff from ' .
				'Employee ' .
				'left join employee_net on employee_net.empid = Employee.EmpId '.
				'left join W2EmpName on Employee.EmpId = W2EmpName.EmpId ' .
				'left join paye_tax_return on Employee.EmpId = paye_tax_return.EmpId ' .
				'left join XHR_Job on Employee.User6 = XHR_Job.JobId ' .
				'where Employee.EmpId in ' .
				'(select EmpId from PRTran where PerPost = \'' . $perpost . '\' and PRTran.PerEnt = \'' . $perpost . '\') ' .
				'order by Employee.Name';
		
		}
		
		$outputCode .= $selectPerPostPart . $selectHeader . $lastPartOfSelectStatement;
		//var_dump($outputCode);
		return $outputCode;
		
	}

}
?>
