<?php

require_once "classes\database.php";
class InsertMarketSubAcct {
	
	private $liveDatabaseObject;
	
	private $DivisionName = array(
		'201' => 'NGABU',
		'202' => 'BLANTYRE',
		'203' => 'LUCHENZA',
		'205' => 'LIWONDE',
		'207' => 'BALAKA',
		'308' => 'LILONGWE',
		'310' => 'MPONERA',
		'311' => 'KASUNGU',
		'312' => 'SALIMA',
		'413' => 'MZUZU',
		'415' => 'KARONGA'
	);
	private $DivisionShortName = array(
		'201' => 'NGAB',
		'202' => 'BT',
		'203' => 'LUCH',
		'205' => 'LIWO',
		'207' => 'BLK',
		'308' => 'LL',
		'310' => 'MPON',
		'311' => 'KASU',
		'312' => 'SALM',
		'413' => 'MZ',
		'415' => 'KARO'
	);

	private $returnValue;

	public $error = array();

	public function __construct(){
		$this->liveDatabaseObject = new Database(0);
	}

	public function AddMarket($marketCodes, $marketNames, $DivisionCode){

		$marketCodes = explode(',', $marketCodes);
		$marketNames = explode(',', $marketNames);

		if(count($marketCodes) === count($marketNames))
			foreach($marketCodes as $index => $marketCode){
				
				$marketName = $marketNames[$index];
				
				if(strlen($marketCode) === 3){
					/* var_dump($marketCode);
					var_dump($marketName); */
					$this->returnValue = $this->insertSubAcct($marketCode, $marketName, $DivisionCode);
					if($this->returnValue){
						$this->returnValue = $this->AddMarketinSite($marketCode, $marketName, $DivisionCode);

						if($this->returnValue){
							$this->returnValue = $this->AddMarketSegDef($marketCode, $marketName, $DivisionCode);

							if($this->returnValue)
								$this->returnValue = $this->AddMarketCashAcct($marketCode, $marketName, $DivisionCode);
						}
					}
				}else{
					$this->returnValue = false;
			array_push($this->error, $marketCode. " Market code(s) error\n"); 
				}
				
			}
		else{
			$this->returnValue = false;
			array_push($this->error, "number of market codes does not match number of market Names\n"); 
		}
		return $this->returnValue;
	}

	public function insertSubAcct($marketCode, $marketName, $DivisionCode){

		//getting the last subAcct s for the division to copy data from
		if($this->liveDatabaseObject){
			$query = ["create  view createMarket as
				select LEFT(sub,3) as region_district_code,RIGHT( LEFT(sub,6),3) market_code from SubAcct
				where LEFT(sub,3) = ".$DivisionCode,
				
				"select RIGHT( LEFT(sub,6),3) market_code, sub, Descr
				from SubAcct
				where RIGHT( LEFT(sub,6),3) in
				(
				select MAX(market_code) from createMarket
				)",

				"drop view createMarket"];
			
			$results = $this->liveDatabaseObject->PerformQuery( $query[0]);
			$results = $this->liveDatabaseObject->PerformQuery( $query[1]);

			//REPLACING AND INSERTING THE SUBACCT
			while ($subAcct = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				$newSubAcct = str_replace($subAcct['market_code'], $marketCode, $subAcct['sub']);
				$DescrArray = explode('-',$subAcct['Descr']);
				$newDescr = $marketName. " MARKET - ". $this->DivisionName[$DivisionCode]. " - " . end($DescrArray);

				if(strlen($newDescr) > 30)
					$newDescr = $marketName. " MKT - ". $this->DivisionShortName[$DivisionCode]. " - " . end($DescrArray);
				
				if(strlen($newDescr) > 30)
					$newDescr = $marketName. " MKT-". $this->DivisionShortName[$DivisionCode]. " -" . end($DescrArray);
					
				if(strlen($newDescr) > 30)
					$newDescr = $marketName. " MKT-". end($DescrArray);
				
				$insetQuery = "INSERT INTO [SubAcct]
					([Active] ,[ConsolSub] ,[Crtd_DateTime] ,[Crtd_Prog] ,[Crtd_User] ,[Descr]
					,[LUpd_DateTime] ,[LUpd_Prog] ,[LUpd_User] ,[NoteID] ,[S4Future01] ,[S4Future02]
					,[S4Future03] ,[S4Future04] ,[S4Future05] ,[S4Future06] ,[S4Future07] ,[S4Future08]
					,[S4Future09] ,[S4Future10] ,[S4Future11] ,[S4Future12] ,[Sub] ,[User1]
					,[User2] ,[User3] ,[User4] ,[User5] ,[User6] ,[User7]
					,[User8])
				VALUES "."('1','".$newSubAcct."',getdate(),'99999','E.KACHINGW',
					'".$newDescr."',getdate(),'99999','E.KACHINGW',
					'0',' ',' ','0','0','0','0','1900-01-01 00:00:00','1900-01-01 00:00:00','0',
					'0',' ',' ','".$newSubAcct."',' ',' ','0','0',' ',' ',
					'1900-01-01 00:00:00','1900-01-01 00:00:00')";
			
				//run the insertion query
				$InsertResults = $this->liveDatabaseObject->PerformQuery( $insetQuery);
			
				if ($InsertResults) {
	
					$this->returnValue = true;
					
					
				}else{
					if( ($errors = sqlsrv_errors() ) != null)  {  
						foreach( $errors as $error)  {  
							//var_dump( $error['message']);
							array_push($this->error, $error['message']."\n".$insetQuery."\n"); 
							$this->returnValue = false;
						}  
					} 
				}
			}

			//deleting the views used
			$results = $this->liveDatabaseObject->PerformQuery( $query[2]);

		}

		return $this->returnValue;
	}

	public function AddMarketinSite($marketCode, $marketName, $DivisionCode){
		
			$name = $marketName. " MARKET - ". $this->DivisionName[$DivisionCode];
			$regionalCode = substr($DivisionCode, 0, 1);
			$districtCode = substr($DivisionCode, 1);

			$insetQuery = "INSERT INTO [Site]
				([Addr1] ,[Addr2] ,[AlwaysShip] ,[Attn] ,[City] ,[COGSAcct] ,[COGSSub]
				,[Country] ,[CpnyID] ,[Crtd_DateTime] ,[Crtd_Prog] ,[Crtd_User] ,[DfltInvtAcct] ,[DfltInvtSub]
				,[DfltRepairBin] ,[DfltVendorBin] ,[DicsAcct] ,[DiscSub] ,[Fax] ,[FrtAcct] ,[FrtSub]
				,[GeoCode] ,[IRCalcPolicy] ,[IRDaysSupply] ,[IRDemandID] ,[IRFutureDate] ,[IRFuturePolicy]
				,[IRLeadTimeID] ,[IRPrimaryVendID] ,[IRSeasonEndDay] ,[IRSeasonEndMon] ,[IRSeasonStrtDay] 
				,[IRSeasonStrtMon] ,[IRServiceLevel] ,[IRSftyStkDays]
				,[IRSftyStkPct] ,[IRSftyStkPolicy] ,[IRSourceCode] ,[IRTargetOrdMethod] ,[IRTargetOrdReq]
				,[IRTransferSiteID] ,[LUpd_DateTime]
				,[LUpd_Prog] ,[LUpd_User] ,[MiscAcct] ,[MiscSub] ,[Name] ,[NoteID] ,[Phone]
				,[ReplMthd] ,[REPWhseLoc] ,[RTVWhseLoc] ,[S4Future01] ,[S4Future02] ,[S4Future03]
				,[S4Future04] ,[S4Future05] ,[S4Future06] ,[S4Future07] ,[S4Future08] ,[S4Future09]
				,[S4Future10] ,[S4Future11] ,[S4Future12] ,[Salut] ,[SiteId] ,[SlsAcct]
				,[SlsSub] ,[State] ,[User1] ,[User2] ,[User3] ,[User4] ,[User5]
				,[User6] ,[User7] ,[User8] ,[VisibleForWC] ,[Zip])

			VALUES (' ',' ','0',' ',' ','460023','".$regionalCode."-".$districtCode."-".$marketCode.
				"-00-00-00000000000000 ',' ','A003 ',getdate(),'99999','E.KACHINGW',' ',' ',' ',' ','460017',
				'".$regionalCode."-".$districtCode."-".$marketCode."-00-00-00000000000000 ',' ','460018','".
				$regionalCode."-".$districtCode."-".$marketCode."-00-00-00000000000000 ',
				' ','1','0',' ','1900-01-01 00:00:00',' ',' ',' ','0','0','0','0','0','0','0',
				' ',' ',' ','0',' ','1900-01-01 00:00:00',' ',' ',' ',' ','".$name."','0',' ',' ',' ',' ','".
				$regionalCode."-".$districtCode."-".$marketCode."-00-00-00000000000000 ',
				' ','0','0','0','0','1900-01-01 00:00:00','1900-01-01 00:00:00','0','1','320000',' ',' ',
				'".$marketCode."','310001','".$regionalCode."-".$districtCode."-".$marketCode.
				"-00-00-00000000000000 ',' ',' ',' ','0','0','".$regionalCode."','".$districtCode."',
				'1900-01-01 00:00:00','1900-01-01 00:00:00','0',' ');";
		
				//run the insertion query
				$InsertResults = $this->liveDatabaseObject->PerformQuery( $insetQuery);
			
			if ($InsertResults) {

				$this->returnValue = true;
				
				
			}else{
				if( ($errors = sqlsrv_errors() ) != null)  {  
					foreach( $errors as $error)  {  
						var_dump( $error['message']);
						array_push($this->error, $error['message']."\n".$insetQuery."\n"); 
						$this->returnValue = false;
					}  
				} 
			}
		return $this->returnValue;
	}
	
	public function AddMarketSegDef($marketCode, $marketName, $DivisionCode){
		
			$name = $marketName. " MARKET - ". $this->DivisionName[$DivisionCode];
			$regionalCode = substr($DivisionCode, 0, 1);
			$districtCode = substr($DivisionCode, 1);

			$insetQuery = "INSERT INTO [SegDef]
				([Active] ,[Crtd_DateTime] ,[Crtd_Prog] ,[Crtd_User] ,[Description]
				,[FieldClass] ,[FieldClassName] ,[ID] ,[LUpd_DateTime] ,[LUpd_Prog]
				,[LUpd_User] ,[SegNumber] ,[User1] ,[User2] ,[User3]
				,[User4])

			VALUES ('0',getdate(),'99999','E.KACHINGW','".$name."','001','SUBACCOUNT ','".
				$marketCode."',getdate(),'99999','E.KACHINGW','3','".$regionalCode."','".
				$districtCode."','0','0');";
		
			//run the insertion query
			$InsertResults = $this->liveDatabaseObject->PerformQuery( $insetQuery);
		
			if ($InsertResults) {

				$this->returnValue = true;
				
				
			}else{
				if( ($errors = sqlsrv_errors() ) != null)  {  
					foreach( $errors as $error)  {  
						var_dump( $error['message']);
						array_push($this->error, $error['message']."\n".$insetQuery."\n"); 
						$this->returnValue = false;
					}  
				} 
			}
		return $this->returnValue;
	}

	public function AddMarketCashAcct($marketCode, $marketName, $DivisionCode){
		
			$name = $marketName. " MARKET - ". $this->DivisionName[$DivisionCode] . " -IMPREST";

			if(strlen($name) > 30)
				$name = substr($name, 0, 30);

			$insetQuery = "INSERT INTO [CashAcct]
				([AcceptGLUpdates] ,[AcctNbr] ,[AcctType] ,[Active] ,[Addr1] ,[Addr2]
				,[AddrID] ,[Attn] ,[BankAcct] ,[BankID] ,[BankSub] ,[CashAcctName]
				,[City] ,[Country] ,[CpnyID] ,[Crtd_DateTime] ,[Crtd_Prog] ,[Crtd_User]
				,[CurrentBal] ,[curycurrentbal] ,[CuryID] ,[CustID] ,[Fax] ,[LastAutoCheckNbr]
				,[LastManualCheckNbr] ,[LUpd_DateTime] ,[LUpd_Prog] ,[LUpd_User] ,[Name] ,[NoteID]
				,[Phone] ,[S4Future01] ,[S4Future02] ,[S4Future03] ,[S4Future04] ,[S4Future05]
				,[S4Future06] ,[S4Future07] ,[S4Future08] ,[S4Future09] ,[S4Future10] ,[S4Future11]
				,[S4Future12] ,[Salut] ,[State] ,[transitnbr] ,[User1] ,[User2]
				,[User3] ,[User4] ,[User5] ,[User6] ,[User7] ,[User8] ,[Zip])

			VALUES ('-1',' ',' ','1',' ',' ',' ',' ','130032',' ','".$DivisionCode .$marketCode.
				"000000000000000000','".$name."',' ',' ','A003 ',getdate(),'99999',
				'E.KACHINGW','0','0','MWK ',' ',' ',' ',' ','1900-01-01 00:00:00','20250','E.KACHINGW',
				' ','0',' ',' ',' ','0','0','0','0','1900-01-01 00:00:00','1900-01-01 00:00:00','0','0',
				' ',' ',' ',' ',' ',' ',' ','0','0','Main',' ','1900-01-01 00:00:00','1900-01-01 00:00:00',' '),
				('-1',' ',' ','1',' ',' ',' ',' ','130042',' ','".$DivisionCode .$marketCode.
				"000000000000000000','".$name."',' ',' ','A003 ',getdate(),'99999',
				'E.KACHINGW','0','0','MWK ',' ',' ',' ',' ','1900-01-01 00:00:00','20250','E.KACHINGW',
				' ','0',' ',' ',' ','0','0','0','0','1900-01-01 00:00:00','1900-01-01 00:00:00','0','0',
				' ',' ',' ',' ',' ',' ',' ','0','0','Main',' ','1900-01-01 00:00:00','1900-01-01 00:00:00',' ');";
		
			//run the insertion query
			$InsertResults = $this->liveDatabaseObject->PerformQuery( $insetQuery);
		
			if ($InsertResults) {

				$this->returnValue = true;
				
				
			}else{
				if( ($errors = sqlsrv_errors() ) != null)  {  
					foreach( $errors as $error)  {  
						var_dump( $error['message']);
						array_push($this->error, $error['message']."\n".$insetQuery."\n"); 
						$this->returnValue = false;
					}  
				} 
			}
		return $this->returnValue;
	}

}
?>
