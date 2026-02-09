<?php

require_once 'database.php';
class Payslip {


	private $earningsDeductions;
	private $employeeInfo;
	private $allEarnings;
	private $allDeductions;
	private $allEmployeeInfo;
	private $bankScheduleInfo;

	private $smsSendingUrlApi = 'http://snapstash.store/api/v1/send';
	private $smsApiKey = 'ES-2MwgBbKwfbW./i8dTB0y';
	
	public $aprovedPerPost;
	public $culPerPost;
	
	private $databaseObject;

	private $employeeTable = 'employee';

	const divisions = [
		NULL => "HQ",
		"100" => "HQ",
		"201" => "Ngabu",
		"202" => "Blantyre",
		"203" => "Luchenza",
		"205" => "Liwonde",
		"207" => "Balaka",
		"308" => "Lilongwe",
		"310" => "Mponera",
		"311" => "Kasungu",
		"312" => "Salima",
		"413" => "Mzuzu",
		"415" => "Karonga",
	];


	private $databaseName;

	public $error = array();

	public function __construct($databaseName, $searchSourceID = 0){
		if($searchSourceID == 1){
			$this->employeeTable = 'employeeArchive';
		}
		$this->databaseName = $databaseName;
		$this->databaseObject = new Database($databaseName);
		$this->getAprovedPerPost();

	}

	
	private function getAprovedPerPost(){
		$query = "select pernbr, user2 as aprovedPerPost from prsetup";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				
					$this->aprovedPerPost =  $row['aprovedPerPost'];
					$this->culPerPost =  $row['pernbr'];
				
			}
		//return $employeeInfo = $results;
		}
	}
		public function aprovePerPost(){
		$query = "update prsetup set user2 = '".$this->culPerPost."'";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				
					$this->aprovedPerPost =  $row['aprovedPerPost'];
					$this->culPerPost =  $row['pernbr'];
				
			}
		//return $employeeInfo = $results;
		}
	}

	
	public function disaprovePerPost(){
		$dateFormat = 'Ym';
        $date = DateTime::createFromFormat($dateFormat,trim($this->aprovedPerPost));
        $date->modify('-1 month');
        //$date->modify('-1 month');
        $this->aprovedPerPost = $date->format('Ym');
		$query = "update prsetup set user2 = '".$this->aprovedPerPost."'";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			return true;
		//return $employeeInfo = $results;
		}
	}
	public function getLastPerPost($Empid){
		$query = " select MAX(perent) as perpost
		from prdoc
		where  EmpId = '".$Empid."'";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				return $row['perpost'];
			}
		//return $employeeInfo = $results;
		}
	}
	public function getEmployeeInfo($Empid) {

		//select from employeeDetails
		$query = " select b.EmpId,b.Name,b.Status,b.User6 as JobCode,c.JobTitle,c.Grade,b.DfltWrkloc as Loc_Code,d.Descr as Location
		,b.StdSlry as BasicPay,b.PayGrpId, e.Descr as PayGroup, f.NameSuffix as Gender
		from ".$this->employeeTable." b, XHR_Job c, WorkLoc d, PayGroup e, W2EmpName f
		where  b.User6 = c.jobid and b.DfltWrkloc = d.WrkLocId and b.PayGrpId = e.PayGrpId
		and b.EmpId = f.EmpId and b.EmpId = '".$Empid."'";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			/*while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				var_dump($row );
				echo "\n ";
			}*/
		return $employeeInfo = $results;
		}
	}
	public function getAllEmployeeInfo($perpost, $division = 100) {

	
			$query = "select 
					b.EmpId,b.Name,c.JobTitle,c.Grade,d.Descr as Location,b.User2 as Bank_Account,
					e.Descr as PayGroup, b.User5 as SortCode, g.division,
					a.Descr as Bank_Name,  PRDoc.NetAmt, PRDoc.ChkDate
				
				from 
					".$this->employeeTable." b, XHR_Job c, WorkLoc d, PayGroup e, W2EmpName f
					, MiscCharge a, PRDoc,ADMARCPortalUsers g
				where 
					b.User6 = c.jobid 
					and b.DfltWrkloc = d.WrkLocId 
					and b.PayGrpId = e.PayGrpId
					and b.EmpId = f.EmpId 
					and b.User5 = a.MiscChrgID
					and b.EmpId = PRDoc.EmpId
					and b.EmpId = g.empid
					and b.Status = 'A'
					and PRDoc.PerEnt = '".$perpost."'
					
					and g.division = '".$division."'
				
				order by
					b.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			/*while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				var_dump($row );
				echo "\n ";
			}*/
		return $allEmployeeInfo = $results;
		}
	}

	public function getBankScheduleInfo($Empid, $perpost, $toPeriod) {
		//select from bankScheduleDetails
		$query = "select a.EmpId,a.Name,a.User2 as Bank_Account,
		a.User5 as SortCode,
		b.Descr as Bank_Name,  PRDoc.NetAmt, PRDoc.ChkDate, PerEnt
		from ".$this->employeeTable." a, MiscCharge b, PRDoc
		
		where a.User5 = b.MiscChrgID
		and a.EmpId = PRDoc.EmpId
		and PRDoc.PerEnt >= '".$perpost."'and PRDoc.PerEnt <= '".$toPeriod."' and a.EmpId = '".$Empid."' order by PerEnt";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
		return $bankScheduleInfo = $results;
		}
	}

	public function getPreiodRange($Empid, $perpost, $toPeriod) {
		//select from prTran
		$query = "select DISTINCT PerEnt 
		from PRTran
		
		where TranAmt != 0 and PerEnt >= '".$perpost."' and PerEnt <= '".$toPeriod."' and PRTran.EmpId = '".$Empid."' order by PerEnt";
		
		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
		return $earningsDeductions = $results;
		}
	}
	public function getEarnings($Empid, $perpost, $toPeriod) {
		//select from prTran
		$query = "select PRTran.empid, NAME, EarnDedId, TranAmt, EarnType.Descr, PerEnt 
		from PRTran
		inner join EarnType on EarnDedId = Id
		LEFT JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
		where drcr = 'D' and TranAmt != 0 and PerEnt >= '".$perpost."' and PerEnt <= '".$toPeriod."' and PRTran.EmpId = '".$Empid."' order by PerEnt";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
		return $earningsDeductions = $results;
		}
	}
	public function getDeductions($Empid, $perpost, $toPeriod) {
		//select from prTran
		$query = "select PRTran.empid, NAME, EarnDedId, TranAmt,
		CASE WHEN prtran.user3 > 0 THEN prtran.user3 - TranAmt ELSE prtran.user3 END AS
  		LoanBalance, Deduction.Descr, PerEnt from PRTran
		inner join Deduction on EarnDedId = DedId
		LEFT JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
		where drcr = 'C' and TranAmt != 0 and PRTran.S4Future06 != 0 and PerEnt >= '".$perpost."' and PerEnt <= '".$toPeriod."' and PRTran.EmpId = '".$Empid."' order by PerEnt";

		$results = $this->databaseObject->PerformQuery( $query);
	
		if ($results) {
		return $earningsDeductions = $results;
		}
	}
	public function getEmployerContributions($Empid, $perpost, $toPeriod) {
		//select from prTran
		$query = "select PRTran.empid, NAME, EarnDedId, TranAmt, Deduction.Descr, PerEnt from PRTran
		inner join Deduction on EarnDedId = DedId
		LEFT JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
		where TranAmt != 0 and PRTran.S4Future06 = 0 and PerEnt >= '".$perpost."' and PerEnt <= '".$toPeriod."' and PRTran.EmpId = '".$Empid."' order by PerEnt";

		$results = $this->databaseObject->PerformQuery( $query);
	
		if ($results) {
		return $earningsDeductions = $results;
		}
	}
	public function getAllEarnings($perpost, $division = 100) {
		//select from prTran
		
			$query = "select 
			prtran.empid, PRTran.EarnDedId, PRTran.TranAmt, EarnType.Descr
		from
			PRTran
		inner join EarnType on PRTran.EarnDedId = EarnType.Id
		inner JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
		inner join ADMARCPortalUsers a on a.empid = PRTran.EmpId
		where 
			PRTran.drcr = 'D' and PRTran.TranAmt != 0 
			and PRTran.PerEnt = '".$perpost."'
			and a.division = '".$division."'
			and ".$this->employeeTable.".Status = 'A'
		
		order by
			PRTran.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
		return $allEarnings = $results;
		}
	}
	public function getAllDeductions($perpost, $division = 100) {
		//select from prTran
		
			$query = "select
					PRTran.empid, EarnDedId, TranAmt,
					prtran.user3 as LoanBalance, Deduction.Descr
				from PRTran
				inner join Deduction on EarnDedId = DedId
				inner JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
				inner join ADMARCPortalUsers a on a.empid = PRTran.EmpId
				
				where 
					PRTran.drcr = 'C' 
					and PRTran.TranAmt != 0 
					and PRTran.S4Future06 != 0	
					and PRTran.PerEnt = '".$perpost."'
					and a.division = '".$division."'
					and ".$this->employeeTable.".Status = 'A'
				
				order by
					PRTran.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);
	
		if ($results) {
		return $allDeductions = $results;
		}
	}
	public function getAllEmployerContributions($perpost, $division = 100) {
		//select from prTran
		
					$query = "select
					PRTran.empid, EarnDedId, TranAmt, Deduction.Descr
				
				from PRTran
					inner join Deduction on EarnDedId = DedId
				inner JOIN ".$this->employeeTable." ON ".$this->employeeTable.".EmpId = PRTran.EmpId
				inner join ADMARCPortalUsers a on a.empid = PRTran.EmpId
						
				where TranAmt != 0 and PRTran.S4Future06 = 0
					and PRTran.PerEnt = '".$perpost."'
					and a.division = '".$division."'
					and ".$this->employeeTable.".Status = 'A'
				
				order by
					PRTran.EmpId";

		$results = $this->databaseObject->PerformQuery( $query);
	
		if ($results) {
		return $earningsDeductions = $results;
		}
	}
	private function getPhoneNumbers($Empid) {
		
		//select from employeeDetails
		$query = " select EmpId, phonenumbers
		from ADMARCPortalUsers
		where  phoneNumbers != '' ";
		
		if ($Empid != "") {
			$query = $query . "and EmpId IN ".$Empid;
		}

		$results = $this->databaseObject->PerformQuery( $query);

		if ($results) {
			
		return $employeeInfo = $results;
		}
		return false;
	}
	public function sendPayslipSms($Empid, $perpost) {
		
		$MessageSentStatus = 'No message Sent!';
		$results = $this->getPhoneNumbers($Empid);
		if($results){
			//var_dump($results);
			while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				/*var_dump($row );
				echo "\n ";*/

				$empInfo = $this->getEmployeeInfo($row['EmpId']);
				$employee = 0;
				if ($empInfo)
					$employee = sqlsrv_fetch_array($empInfo, SQLSRV_FETCH_ASSOC);

				$EmployBankInfo = $this->getBankScheduleInfo($row['EmpId'],$perpost);
				$bankInfo = 0;
				if ($EmployBankInfo)
					$bankInfo = sqlsrv_fetch_array($EmployBankInfo, SQLSRV_FETCH_ASSOC);

				$employEarnings = $this->getEarnings($row['EmpId'],$perpost);
				$totalEarnings = 0;
				$earningMessage = "Earnings:\n";
				
				if($employEarnings)
					while ($earniRow = sqlsrv_fetch_array($employEarnings, SQLSRV_FETCH_ASSOC)) {
						if ($employee != 0){
							$earningMessage .= trim($earniRow['Descr']).": K".number_format($earniRow['TranAmt'], 2, '.',' ,')."\n";
							$totalEarnings = $totalEarnings + $earniRow['TranAmt'];
						}
					}

				$employDeductions = $this->getDeductions($row['EmpId'],$perpost);
				$totalDeductions = 0;
				$deductionMessage = "EE Deductions:\n";
				
				if($employDeductions)
					while ($DedRow = sqlsrv_fetch_array($employDeductions, SQLSRV_FETCH_ASSOC)) {
						if ($employee != 0){
							$deductionMessage .= trim($DedRow['Descr']).": K".number_format($DedRow['TranAmt'], 2, '.',' ,')."\n";
							$totalDeductions = $totalDeductions + $DedRow['TranAmt'];
						}
					}
				
				$employcontributions = $this->getEmployerContributions($row['EmpId'],$perpost);
				$totalemploycontributions = 0;
				$employcontributionsMessage = "ER Deductions:\n";

				if($employcontributions)
					while ($ERrow = sqlsrv_fetch_array($employcontributions, SQLSRV_FETCH_ASSOC)) {
						if ($employee != 0){
							$employcontributionsMessage .= trim($ERrow['Descr']).": K".number_format($ERrow['TranAmt'], 2, '.',' ,')."\n";
							$totalemploycontributions += $ERrow['TranAmt'];
						}
					}

				$phoneNumbers = explode(",",$row['phonenumbers']);
				
				foreach($phoneNumbers as $phoneNumber){
					//echo 'apatufika';
					if($employee != 0 && $bankInfo != 0){
						$message =	"ADMARC PAYSLIP\n".
						"Employee No.: ".trim($employee['EmpId'])."\n".
						"Employee Name.: ".trim($employee['Name'])."\n".
						"Bank account: ".trim($bankInfo['Bank_Account'])."\n".
						"Pay Date: ".$bankInfo['ChkDate']->format('l, j F,Y')."\n".
						"Job Title: ".trim($employee['JobTitle'])."\n".
						$earningMessage.
						$deductionMessage.
						$employcontributionsMessage.
						"Total ER Deductions: K".number_format($totalemploycontributions, 2, '.',' ,')."\n".
						"Total Earnings: K".number_format($totalEarnings, 2, '.',' ,')."\n".
						"Total EE Deductions: K".number_format($totalDeductions, 2, '.',' ,')."\n".	
						"NET PAY: K".number_format($bankInfo['NetAmt'], 2, '.',' ,');
						$message = addslashes($message);
						//var_dump($message);
						$MessageSentStatus = $this->sendSms($message, $phoneNumber);
					}
				}
				
				
			}
		}
				
		return $MessageSentStatus;
	}
	private function sendSms($message, $phoneNumber) {

		$data = array('message' => $message, 
                'phonenumbers' => $phoneNumber, 'api_key' => $this->smsApiKey);
        
        $query = http_build_query($data);

        $options = array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n", // Specify the correct Content-Type
                'content' => $query,
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($this->smsSendingUrlApi, false, $context);

		$jsonObject = json_decode($result);

		if ($jsonObject === null) {
			// Handle the error (e.g., log it, display a message, etc.)
			return "received a bad rensponse from sms server!";
			return "Error decoding JSON: " . json_last_error_msg();
		} else {
			// Successfully decoded JSON
			// Access properties as needed
			if(property_exists($jsonObject,'status')){
				return "message sent";
			}
			return json_encode($jsonObject);
		}
		
	}
	

}
?>
