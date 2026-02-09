<?php

require_once 'database.php';
require_once 'EmployeeDetails.php';
require_once 'EarningsAndDeductions.php';
require_once 'Payslip.php';
class PayrollEditScreen {

	public $empid = '';
	public $fname = '';
	public $mname = '';
	public $sname = '';
	public $genderM = false;
	public $genderF = false;
	public $statusA = false;
	public $statusI = false;

	public $grossPay = 0;
	public $NetPay = 0;
	public $payrollPriod;

	private $PerPost;
	public $earnings = array();
	public $deductions = array();	
	public $payroll;
	
	private $databaseObject;
	private $databaseNumber;

	public $error = array();

	public function __construct($databaseName = 3){
		$this->databaseNumber = $databaseName;
		$this->payroll = array(false, false, false, false, false, false, false);
		$this->payroll[$databaseName] = true;
		$this->databaseObject = new Database($this->databaseNumber);

		
	}

	function getEmployeeDetails($empid = false){
		
		$employObject = new Employee($this->databaseNumber);
		$currentEmployee = $employObject->getEmployeeDetails($empid);
		//var_dump($empid." : ".$this->databaseNumber);
		if($currentEmployee){
   
			$currentEmployee = sqlsrv_fetch_array($currentEmployee, SQLSRV_FETCH_ASSOC);
			if ($currentEmployee != null) {
				
				$this->empid = trim($currentEmployee['EmpId']);
				$this->fname = trim($currentEmployee['NameFirst']);
				$this->mname = trim($currentEmployee['NameMiddle']);
				$this->sname = trim($currentEmployee['NameLast']);
				$this->genderM = (trim($currentEmployee['Gender']) == 'M') ? true : false ;
				$this->genderF = (trim($currentEmployee['Gender']) == 'F') ? true : false ;
				$this->statusA = (trim($currentEmployee['Status']) == 'A') ? true : false ;
				$this->statusI = (trim($currentEmployee['Status']) == 'I') ? true : false ;

				$earningNdeductionObject = new EarningsAndDeductions($this->databaseNumber);
				
				$earnings = $earningNdeductionObject->getEarnings($this->empid);
				
				if($earnings){
					//var_dump($this->empid ." : ".$this->databaseNumber);
					
					while($earning = sqlsrv_fetch_array($earnings, SQLSRV_FETCH_ASSOC)){
						
						$this->earnings[] = array_map('trim',$earning);
						$this->grossPay += $earning['amount'];
						
						//var_dump($earning);
						
					}
					//echo $this->grossPay;
					//var_dump($this->earnings);
				}

				$deductions = $earningNdeductionObject->getDeductions($this->empid);
				if($deductions){
					while($deduction = sqlsrv_fetch_array($deductions, SQLSRV_FETCH_ASSOC)){
						
						$this->deductions[] = array_map('trim',$deduction);
						
						
					}
					//var_dump($this->deductions);
				}

				$this->payrollPriod = $earningNdeductionObject->getCurrentPeriods();
				
				$payslipObj = new Payslip($this->databaseNumber);
				$bankScheduleInfo = $payslipObj->getBankScheduleInfo($this->empid, $this->payrollPriod);
				if($bankScheduleInfo){
		
					$EmpbankScheduleInfo = sqlsrv_fetch_array($bankScheduleInfo, SQLSRV_FETCH_ASSOC);
					if($EmpbankScheduleInfo != null)
						$this->NetPay = $EmpbankScheduleInfo['NetAmt'];
						
					//var_dump($this->NetPay);
				}
				//var_dump($this->payrollPriod);
				
				return true;
			}
		}
		return false;
	}

}
?>
