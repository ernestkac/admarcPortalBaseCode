<?php

require_once 'database.php';
require_once 'downloadReport.php';
class Logistics {


	private $PerPost;
	private $fileName;
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseName = 0){
		$this->databaseNumber = $databaseName;
		$this->databaseObject = new Database($this->databaseNumber);

	}

	function getRepatriationReport($reportCode = '5001'){
		$query = "	select
			APTran.VendId as 'Transporter Number', Vendor.Name as 'Transpoter Name',
			APTran.User1 as vehicleReg,
			APTran.S4Future11 as MarketFrom, Site.Name as MarketName, APTran.S4Future01 as MarketTo,
			sites_view.name as MarkertTo, APTran.TranDesc,
			APTRAN.BatNbr as BatchNumber, APTran.FiscYr, APTran.PerPost as Period,
			APTran.Qty/1000 as Tonage, APTran.user4 as TarDistance, APTran.TranAmt,
			APTran.TranDate
		from APTran 
			join Vendor on Vendor.VendId = APTran.VendId
			left join Site on Site.SiteId = APTran.S4Future11
			left join sites_view on sites_view.SiteId = APTran.S4Future01
		where aptran.User6 = '".$reportCode."' 
		
		order by aptran.VendId, FiscYr, PerPost
		";
		
			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results) {

				if($reportCode == '5002')
					$this->fileName = "Repatriation report .csv";
				
				if($reportCode == '5001')
					$this->fileName = "Staff Transfer report .csv";
				
				$this->reportFileManagerObject = new ReportFileManager();
				$this->reportFileManagerObject->generateCSVFile($results, $this->fileName);
				
				$this->reportFileManagerObject->downloadFile($this->fileName);
				
			}else{
				var_dump(sqlsrv_errors());
			}
	}

}
?>
