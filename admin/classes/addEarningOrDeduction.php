<?php

require_once 'database.php';
class AddEarningOrDeduction {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseName, $EmployeesEarnings, $earningCode,$EarnDed){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			$calyr = '2020';
			if($this->databaseNumber == 1)
				$calyr = '2018';
			
			$EmployeeEarnings = explode(";", $EmployeesEarnings);
			foreach ($EmployeeEarnings as $employeeEarningRow) {
				$EmployeeEarning = explode(",", $employeeEarningRow);
				if (count($EmployeeEarning) >= 5) {
					$EmpId = $EmployeeEarning[0];
					$amount = $EmployeeEarning[1];
					$taxableAmount = $EmployeeEarning[2];
					$PerPost = $EmployeeEarning[3];
					$numberOfPeriods = ($EmployeeEarning[4] == '0') ? "" : $EmployeeEarning[4] ;
					/*echo "number of periods = : " . $numberOfPeriods;
					echo "empid  = : " . $EmpId;
					echo "amount  = : " . $amount;
					echo "taxable  = : " . $taxableAmount;
					echo "perpost  = : " . $PerPost;
					var_dump($EmployeeEarning);*/
					$query = "INSERT INTO [BenEmp]
						([BenId], [BTotWorked], [BYBegBal], [BYTDAccr], [BYTDAvail], [BYTDUsed]
						, [BYTDWorked], [CpnyID], [Crtd_DateTime], [Crtd_Prog]
						, [Crtd_User], [CurrAvail], [CurrBYBegBal], [CurrBYTDAccr]
						, [CurrBYTDAvail], [CurrBYTDUsed], [CurrBYTDWorked], [CurrLastAvailDate]
						, [CurrLastCloseDate], [CurrLastPayPEndDate], [CurrUsed], [CurrWorked]
						, [EmpId], [LastAccrRate], [LastAvailDate], [LastCloseDate]
						, [LastPayPerEndDate], [LUpd_DateTime], [LUpd_Prog]
						, [LUpd_User], [MaxCarryOver], [NoteId], [S4Future01]
						, [S4Future02], [S4Future03], [S4Future04], [S4Future05]
						, [S4Future06], [S4Future07], [S4Future08], [S4Future09]
						, [S4Future10], [S4Future11], [S4Future12]
						, [Status], [TrnsBenId], [TrnsCarryFwdHist]
						, [TrnsDate], [User1], [User2], [User3]
						, [User4], [User5], [User6], [User7], [User8])
					VALUES
						('".$earningCode."',0,0,0,0,0,0,'".$this->databaseObject->CpnyID[$this->databaseNumber]."','2021-09-16 08:49:00','02250','E.KACHINGW',0,0,0,0,0,0,'1900-01-01 00:00:00',
						'1900-01-01 00:00:00','1900-01-01 00:00:00',0,0,'".$EmpId."',
						0,'1900-01-01 00:00:00','1900-01-01 00:00:00',
						'1900-01-01 00:00:00','2023-07-29 20:48:00','02250','E.KACHINGW',
						0,0,'','',0,	0,	0,	0,	'1900-01-01 00:00:00',
						'1900-01-01 00:00:00',	0,	0,'','','A',
						'',0,'1900-01-01 00:00:00','".$PerPost."','',".$amount.",	".$taxableAmount.",'','".$numberOfPeriods."','1900-01-01 00:00:00',
						'1900-01-01 00:00:00')";

					$deleteQuery = "delete from benemp where empid = "
						.$EmpId." and benid = '".$earningCode."'";

					if ($EarnDed == "on") {
						$query = "INSERT INTO [EarnDed]
							([AddlCrAmt], [AddlExmptAmt], [ArrgCurr], [ArrgEmpAllow], [ArrgYTD], [CalMaxYtdDed]
							, [CalYr], [CalYtdEarnDed], [CpnyID], [Crtd_DateTime], [Crtd_Prog], [Crtd_User], [CurrEarnDedAmt],
							[CurrRptEarnSubjDed], [CurrUnits], [DedSequence], [EarnDedId], [EarnDedType], [EDType], [EmpId],
							[Exmpt], [FxdPctRate], [LUpd_DateTime], [LUpd_Prog], [LUpd_User], [MtdEarnDed00], [MtdEarnDed01], 
							[MtdEarnDed02], [MtdEarnDed03], [MtdEarnDed04]
							, [MtdEarnDed05], [MtdEarnDed06], [MtdEarnDed07], [MtdEarnDed08], [MtdEarnDed09]
							, [MtdEarnDed10], [MtdEarnDed11], [MtdUnits00], [MtdUnits01], [MtdUnits02], [MtdUnits03], [MtdUnits04], [MtdUnits05]
							, [MtdUnits06], [MtdUnits07], [MtdUnits08], [MtdUnits09], [MtdUnits10], [MtdUnits11], [MtdRptEarnSubjDed00], [MtdRptEarnSubjDed01]
							, [MtdRptEarnSubjDed02], [MtdRptEarnSubjDed03], [MtdRptEarnSubjDed04], [MtdRptEarnSubjDed05]
							, [MtdRptEarnSubjDed06], [MtdRptEarnSubjDed07], [MtdRptEarnSubjDed08], [MtdRptEarnSubjDed09]
							, [MtdRptEarnSubjDed10], [MtdRptEarnSubjDed11], [NbrOthrExmpt], [NbrPersExmpt]
							, [NoteId], [QtdEarnDed00], [QtdEarnDed01], [QtdEarnDed02]
							, [QtdEarnDed03], [QtdRptEarnSubjDed00], [QtdRptEarnSubjDed01], [QtdRptEarnSubjDed02]
							, [QtdRptEarnSubjDed03], [S4Future01], [S4Future02], [S4Future03]
							, [S4Future04], [S4Future05], [S4Future06], [S4Future07], [S4Future08], [S4Future09], [S4Future10], [S4Future11]
							, [S4Future12], [User1], [User2], [User3]
							, [User4], [User5], [User6], [User7], [User8], [WrkLocId], [YtdPerTkn], [YtdRptEarnSubjDed], [YtdUnits])
						VALUES
							(".$amount.",".$taxableAmount.",	0,	0,	0,	0,	".$calyr.",	0,	'".$this->databaseObject->CpnyID[$this->databaseNumber]."','2023-03-15 16:45:00',	'2250','SYSADMIN',
							0,	0,	0,	0,	'".$earningCode."','V',	'D',	'".$EmpId."',0,0,	'2023-08-14 14:02:00',	'2250',    	'SYSADMIN',
							0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,
							0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,	0,'','',0,	0,	0,	0,
							'1900-01-01 00:00:00',	'1900-01-01 00:00:00',	0,	0,'','', '".$PerPost."','".$numberOfPeriods."',".$amount.",	".$taxableAmount.",'','202404',
							'1900-01-01 00:00:00','1900-01-01 00:00:00','',0,	0,	0)
							";
							$deleteQuery = "delete from earnded where empid = "
							.$EmpId." and earndedid = '".$earningCode."'";
					}
					//delete all if they already exsit
					$this->databaseObject->PerformQuery( $deleteQuery);
					//run the insertion query
					$results = $this->databaseObject->PerformQuery( $query);
				
					if ($results) {

						echo "Success insertion";
						$returnValue = true;
						
						
					}else{
						//var_dump(sqlsrv_errors());
						$returnValue = false;

						//echo $query;
					}
				}
			}
			
		
		}

		return $returnValue;
	}

}
?>
