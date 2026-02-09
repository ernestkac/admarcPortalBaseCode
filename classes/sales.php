<?php

require_once 'database.php';
require_once 'downloadReport.php';
class SalesReport {


	private $FiscYr;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseName;

	public $error = array();

	public function __construct($databaseName, $FiscYr){
		$this->FiscYr = $FiscYr;
		$this->databaseName = $databaseName;
		$this->databaseObject = new Database($databaseName);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){

			$query = "IF OBJECT_ID('dbo.batch_invetory', 'V') IS NOT NULL
					DROP VIEW dbo.batch_invetory
					GO
					
					create view batch_invetory
					as
					
					select BatNbr,Inventory.Descr  from INTran
					inner join Inventory on Inventory.InvtID = INTran.InvtID
					where  FiscYr = '".$this->FiscYr."'
					and Acct = '460023'";

			$querysGenerated = explode("GO", $query);
		
				$outputResult;
				foreach ($querysGenerated as $queryGenerated){
					//var_dump($queryGenerated);
					//echo "***********\n";
					$outputResult = $this->databaseObject->PerformQuery( $queryGenerated);
				}


			$query = "select INTran.ACCT,INTran.BatNbr,FiscYr,INTran.PerPost,
			Batch.User5 as Week_No,INTRAN.InvtID,Inventory.Descr,
			INTran.SiteID,Site.Name,
			Qty,INTran.User3 AS price,INTran.User4 as Amount,
			INTran.InvtSub,INTran.TranDate, Batch.Status AS BatchStatus
			from INTran
			inner join Site
			on INTran.SiteID = Site.SiteId
			inner join Inventory
			on INTran.InvtID = Inventory.InvtID
			inner join Batch
			on INTran.BatNbr = Batch.BatNbr
			where intran.FiscYr = '".$this->FiscYr."'
			and (TranDesc like 'C7%' or TranDesc like 'S1%') AND INTran.Acct in ('460023')
			and INTran.User3 <> 0 
			and Batch.User5 like '%".$this->FiscYr."'";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {

				$this->fileName = "Sales for ".$this->FiscYr.".csv";
				
				$this->reportFileManagerObject->generateCSVFile($results, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
			}else{
				var_dump(sqlsrv_errors());
			}
		
		}
	}

}
?>
