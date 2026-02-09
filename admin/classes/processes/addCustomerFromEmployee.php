<?php

require_once "classes\database.php";
class AddCustomerFromEmployee {
	
	private $liveDatabaseObject;
	private $payrollDatabaseObject;

	private $employeeList;
	private $customerList;
	private $valuesToInsert = "";
	private $returnValue;

	public $error = array();

	public function __construct(){
		$this->liveDatabaseObject = new Database(0);
	}

	public function addCustomerFromEmployee($i){
		$this->payrollDatabaseObject = new Database($i);

		//getting the customer IDs to exclude
		if($this->liveDatabaseObject){
			$query = "select CustId from Customer
					where
					CustId not like ' %'
					and CustId not like '2%'
					and CustId not like '1%'
					and CustId not like '3%'
					and CustId not like '5%'
					and CustId not like '6%'
					and CustId not like '40%'
					and CustId not like '41%'
					and CustId not like '42%'
					and CustId != ''";
			$results = $this->liveDatabaseObject->PerformQuery( $query);

			//employee numbers to exclude from inserting
			$this->customerList = "(''";
			while ($customerId = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				$this->customerList .= ",'".trim($customerId['CustId'])."'";
			}
			$this->customerList .= ")";

			//var_dump($this->customerList);

		}

		//getting the employees to insert into customers
		
			
		if($this->payrollDatabaseObject){
			$query = "select Addr1, Addr2, City, Country, CpnyID, Department,
			DfltEarnType, DfltExpAcct, DfltExpSub, DfltWrkloc, EmpId, Name,
			PayGrpId, Phone, Salut, State, Status 
			from employee 
			where status = 'A' and empid not in ".$this->customerList;
			$results = $this->payrollDatabaseObject->PerformQuery( $query);

			//employee numbers to exclude from inserting
			while ($employeeValues = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {

				if($this->liveDatabaseObject){
			
					$query = "INSERT INTO [AdmarcSLAppLive].[dbo].[Customer]
					([AccrRevAcct] ,[AccrRevSub] ,[AcctNbr] ,[Addr1] ,[Addr2] ,[AgentID] ,
					[ApplFinChrg] ,[ArAcct] ,[ArSub] ,[Attn] ,[AutoApply] ,[BankID] ,
					[BillAddr1] ,[BillAddr2] ,[BillAttn] ,[BillCity] ,[BillCountry] ,
					[BillFax] ,[BillName] ,[BillPhone] ,[BillSalut] ,[BillState] ,
					[BillThruProject] ,[BillZip] ,[CardExpDate] ,[CardHldrName] ,[CardNbr] ,
					[CardType] ,[City] ,[ClassId] ,[ConsolInv] ,[Country] ,[CrLmt] ,
					[Crtd_DateTime] ,[Crtd_Prog] ,[Crtd_User] ,[CuryId] ,[CuryPrcLvlRtTp] ,
					[CuryRateType] ,[CustFillPriority] ,[CustId] ,[DfltShipToId] ,[DunMsg] ,
					[EMailAddr] ,[Fax] ,[InvtSubst] ,[LanguageID] ,[LUpd_DateTime] ,
					[LUpd_Prog] ,[LUpd_User] ,[Name] ,[NoteId] ,[OneDraft] ,[PerNbr] ,
					[Phone] ,[PmtMethod] ,[PrcLvlId] ,[PrePayAcct] ,[PrePaySub] ,
					[PriceClassID] ,[PrtMCStmt] ,[PrtStmt] ,[S4Future01] ,[S4Future02] ,
					[S4Future03] ,[S4Future04] ,[S4Future05] ,[S4Future06] ,[S4Future07] ,
					[S4Future08] ,[S4Future09] ,[S4Future10] ,[S4Future11] ,[S4Future12] ,
					[Salut] ,[SetupDate] ,[ShipCmplt] ,[ShipPctAct] ,[ShipPctMax] ,
					[SICCode1] ,[SICCode2] ,[SingleInvoice] ,[SlsAcct] ,[SlsperId] ,
					[SlsSub] ,[State] ,[Status] ,[StmtCycleId] ,[StmtType] ,[TaxDflt] ,
					[TaxExemptNbr] ,[TaxID00] ,[TaxID01] ,[TaxID02] ,[TaxID03] ,[TaxLocId] ,
					[TaxRegNbr] ,[Terms] ,[Territory] ,[TradeDisc] ,[User1] ,[User2] ,
					[User3] ,[User4] ,[User5] ,[User6] ,[User7] ,[User8] ,[Zip])
					VALUES "."('','','','".str_replace("'", "",trim($employeeValues['Addr1']))."','".
					str_replace("'", "",trim($employeeValues['Addr2']))."','','1','140001',
					'100000000000000000000000','".trim($employeeValues['CpnyID'])."','0','','".
					str_replace("'", "",trim($employeeValues['Addr1']))."',' P.O. BOX 5052','".trim($employeeValues['CpnyID'])
					."','".trim($employeeValues['City'])."','".trim($employeeValues['Country'])."','',
					'".str_replace("'", "",trim($employeeValues['Name']))."','".trim($employeeValues['Phone'])."','"
					.trim($employeeValues['Salut'])."','".trim($employeeValues['State'])."','0','',
					'1900-01-01 00:00:00','','',' ','".trim($employeeValues['City'])."','LOC ',
					'0','MW ','0','2024-04-17 14:00:00','08260 ','E.KACHINGW','','','','5',
					'".trim($employeeValues['EmpId'])."','DEFAULT ','0','','','0','',
					'2024-04-17 14:00:00','08260 ','E.KACHINGW','".str_replace("'", "",trim($employeeValues['Name'])).
					"','0','0','202412','".trim($employeeValues['Phone'])."',' ','','240002',
					'".trim($employeeValues['DfltExpSub'])."','','0','1','','','0','0','0','0',
					'1900-01-01 00:00:00','1900-01-01 00:00:00','0','0','','','"
					.trim($employeeValues['Salut'])."','1900-01-01 00:00:00','1',' ','0','','',
					'0','310001','','".trim($employeeValues['DfltExpSub'])."','".trim($employeeValues['State'])
					."','".trim($employeeValues['Status'])."','ST','O','C',' ','','','','',' ',' ',
					'30','','0','','','0','0','','','1900-01-01 00:00:00','1900-01-01 00:00:00','')";
			
					//run the insertion query
					$InsertResults = $this->liveDatabaseObject->PerformQuery( $query);
				
					if ($InsertResults) {
		
						$this->returnValue = true;
						
						
					}else{
						if( ($errors = sqlsrv_errors() ) != null)  {  
							foreach( $errors as $error)  {  
								array_push($this->error,$error[ 'message']."\n".$query."\n"); 
								$this->returnValue = false;
							}  
						} 
					}
				
				}
			//var_dump($employeeValues['Name']);
			}
			

		}
		
		//var_dump($this->valuesToInsert);
		return $this->returnValue;
	}

}
?>
