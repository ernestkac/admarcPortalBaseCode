<?php

require_once "classes\database.php";
class BatchNumber {


	private $earningsDeductions;
	private $employeeInfo;
	private $bankScheduleInfo;
	
	private $databaseObject;


	private $databaseName = 0;

	public $error = array();

	public function __construct($databaseName = 0){
		$this->databaseName = $databaseName;
		$this->databaseObject = new Database($this->databaseName);

	}

	public function changePeriod($batchNumbers, $perpost) {

		$returnValue = false;
		$results = $this->getBatchModules($batchNumbers);			
		if ($results) {
			while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				//var_dump ($row['Module']);
				$this->updatePeriod($row['BatNbr'], $perpost, $row['Module'], $row['status']);
				$returnValue = true;
			}
		}else {
			if( ($errors = sqlsrv_errors() ) != null)  {  
					foreach( $errors as $error)  {  
						array_push($this->error,$error[ 'message']."\n");  
					}  
				}  
			 var_dump(sqlsrv_errors());
		}
		return $returnValue;
	}

	public function getBatchModules($batchNumbers){
		$query = "select BatNbr, user5, Module, status
		from Batch 
		where 
		 status in ('U','H','B')
		and  BatNbr in ".$batchNumbers;

		$results = $this->databaseObject->PerformQuery( $query);
		
		return $results;
	}

	public function updatePeriod($batchNumber, $perpost, $module, $batchStatus) {
		
		$results = $this->databaseObject->PerformQuery( $this->getUpdateQuery($batchNumber, $perpost, "Batch") );
						
		if ($results) {
			$results = $this->databaseObject->PerformQuery( $this->getUpdateQuery($batchNumber, $perpost, $module) );
			if ($results){
				if($batchStatus == 'U'){
					$results = $this->databaseObject->PerformQuery( $this->getUpdateQuery($batchNumber, $perpost, $module, $batchStatus) );
					if ($results)
						return true;
				}
				return true;
			}
		}
		return false;
	}

	public function getUpdateQuery($batchNumber, $perpost, $module, $batchStatus = 'H')  {

		if($batchStatus == 'U')
			return "update GLTran
			set PerEnt = '".$perpost."', PerPost = '".$perpost."',
			FiscYr = '".substr($perpost,0,4)."'
			where
			Module = '".$module."'
			and BatNbr in 
			('".$batchNumber."')";
		
		switch ($module) {
			case 'GL':
				return "update GLTran
				set PerEnt = '".$perpost."', PerPost = '".$perpost."',
				 FiscYr = '".substr($perpost,0,4)."'
				where
				Module = 'GL'
				and BatNbr in 
				('".$batchNumber."')";
			break;

			case 'IN':
				return "update INTran
				set PerEnt = '".$perpost."', PerPost = '".$perpost."',
				 FiscYr = '".substr($perpost,0,4)."'
				where
				BatNbr in 
				('".$batchNumber."')";
			break;

			case 'AR':
				return "update ARTran
				set PerEnt = '".$perpost."', PerPost = '".$perpost."',
				 FiscYr = '".substr($perpost,0,4)."'
				where
				BatNbr in 
				('".$batchNumber."')";
			break;

			case 'AP':
				return "update APTran
				set PerEnt = '".$perpost."', PerPost = '".$perpost."',
				 FiscYr = '".substr($perpost,0,4)."'
				where
				BatNbr in 
				('".$batchNumber."')";
			break;

			case 'CA':
				return "update CATran
				set PerEnt = '".$perpost."', PerPost = '".$perpost."'
				where
				BatNbr in 
				('".$batchNumber."')";
			break;
			
			default:
				return "update Batch set PerEnt = '".$perpost."', PerPost = '".$perpost."'
				from Batch 
				where (Status = 'U'
				or Status = 'H'
				or Status = 'B'
				)
				AND BatNbr in  
				('".$batchNumber."')";
			break;
		}
	}
	

    /* =============================
       PUBLIC ENTRY POINT
       ============================= */

    /**
     * Release a batch number following the CA -> GL rules provided
     */
    public function releaseCABatch($batNbr) {
        try {
            if (!$this->duplicateCATran($batNbr)) {
                return false;
            }

            if (!$this->updateTransferCATran($batNbr)) {
                return false;
            }

            if (!$this->markCATranReleased($batNbr)) {
                return false;
            }

            if (!$this->insertGLTranFromCATran($batNbr)) {
                return false;
            }

            if (!$this->setBatchStatusToUnposted($batNbr)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->rollback($batNbr);
            return false;
        }
    }

    /* =============================
       ROLLBACK FUNCTION
       ============================= */

    private function rollback($batNbr) {
        // Delete from GLTran
        $sql1 = "DELETE FROM GLTran WHERE BatNbr = ? AND Module = 'CA'";
        $this->execute($sql1, [$batNbr]);

        // Delete from CATran the duplicate with larger Linenbr (the transfer transaction)
        $sql2 = "DELETE FROM CATran WHERE BatNbr = ? AND Rlsed = 1 AND Linenbr = (SELECT MAX(Linenbr) FROM CATran WHERE BatNbr = ? AND Rlsed = 1)";
        $this->execute($sql2, [$batNbr, $batNbr]);

        // Update CATran Rlsed = 0 for remaining transactions
        $sql3 = "UPDATE CATran SET Rlsed = 0 WHERE BatNbr = ?";
        $this->execute($sql3, [$batNbr]);

        // Update Batch Rlsed = 0, Status = 'H'
        $sql4 = "UPDATE Batch SET Rlsed = 0, Status = 'H' WHERE BatNbr = ?";
        $this->execute($sql4, [$batNbr]);
    }

    /* =============================
       STEP 1: Batch status
       ============================= */

    private function setBatchStatusToUnposted($batNbr) {
        $sql = "UPDATE Batch SET Status = 'U', Rlsed = 1 WHERE module = 'CA' AND BatNbr = ?";
        return $this->execute($sql, [$batNbr]) !== false;
    }
    public function setBatchStatusToVoid($batNbr) {
        $sql = "UPDATE Batch SET Status = 'V' WHERE module = 'CA' AND BatNbr = ?";
        return $this->execute($sql, [$batNbr]) !== false;
    } 
    public function restoreCABatch($batNbr) {
        $sql = "UPDATE Batch SET Status = 'H' WHERE module = 'CA' AND BatNbr = ?";
        return $this->execute($sql, [$batNbr]) !== false;
    }
    /**
     * Get unreleased CA batches
     * Returns top 10 unreleased CA batches with their transaction details
     */
    public function unreleasedCABatches($division = '202') {
        $sql = "
                SELECT TOP 50 c.batnbr, c.Acct, c.bankacct, c.sub, c.Crtd_User, c.Crtd_DateTime, c.TranAmt, c.DrCr, c.Perent, c.RefNbr, c.TranDesc
                                                FROM CATran c
                                                INNER JOIN Batch b ON b.BatNbr = c.batnbr
                                                WHERE b.Status = 'H'
                                                AND b.Module = 'CA'
                                                AND LEFT(c.Perent, 4) = (
                select left(LastClosePerNbr, 4) from GLSetup)
                AND LEFT(c.sub, 3) = ?
                AND c.batnbr NOT IN (
                        SELECT batnbr FROM CATran
                        WHERE LEFT(Perent,4) = (select left(LastClosePerNbr, 4) from GLSetup)
                            AND LEFT(sub,3) = ?
                        GROUP BY batnbr
                        HAVING COUNT(*) > 1
                )";

        try {
            $result = $this->execute($sql, [$division, $division]);
            return $result;
        } catch (Exception $e) {
            array_push($this->error, $e->getMessage());
            return false;
        }
    }
     public function getDeletedCABatches($division = '202') {
        $sql = "
        SELECT TOP 50 c.batnbr, c.Acct, c.bankacct, c.sub, c.Crtd_User, c.Crtd_DateTime, c.TranAmt, c.DrCr, c.Perent, c.RefNbr, c.TranDesc, b.status
                        FROM CATran c
                        INNER JOIN Batch b ON b.BatNbr = c.batnbr
                        WHERE b.Status = 'V'
                        AND b.Module = 'CA'
                        and left(c.Perent, 4) = (
        select left(LastClosePerNbr, 4) from GLSetup)
        and LEFT(c.sub, 3) = ?";

        try {
            $result = $this->execute($sql, [$division]);
            return $result;
        } catch (Exception $e) {
            array_push($this->error, $e->getMessage());
            return false;
        }
    }
    /* =============================
       STEP 2: Duplicate CATran
       ============================= */

    private function duplicateCATran($batNbr) {
        $sql = "
            INSERT INTO CATran
			                ([Acct], [AcctDist], [bankacct], [BankCpnyID], [banksub], [batnbr],
                [ClearAmt], [ClearDate], [CpnyID], [Crtd_DateTime], [Crtd_Prog], [Crtd_User],
                [CuryID], [CuryMultDiv], [CuryRate], [curytranamt], [DrCr], [EmployeeID],
                [EntryId], [JrnlType], [Labor_Class_Cd], [LineID], [Linenbr], [LineRef],
                [LUpd_DateTime], [LUpd_Prog], [LUpd_User], [Module], [NoteID], [PayeeID],
                [PC_Flag], [PC_ID], [PC_Status], [PerClosed], [Perent], [PerPost], [ProjectID],
                [Qty], [RcnclStatus], [RecurId], [RefNbr], [Rlsed],
                [S4Future01], [S4Future02], [S4Future03], [S4Future04], [S4Future05],
                [S4Future06], [S4Future07], [S4Future08], [S4Future09], [S4Future10],
                [S4Future11], [S4Future12], [Sub], [TaskID], [TranAmt], [TranDate],
                [TranDesc], [trsftobankacct], [trsftobanksub], [TrsfToCpnyID],
                [User1], [User2], [User3], [User4], [User5], [User6], [User7], [User8])
            SELECT
                [Acct], [AcctDist], [bankacct], [BankCpnyID], [banksub], [batnbr],
                [ClearAmt], [ClearDate], [CpnyID], [Crtd_DateTime], [Crtd_Prog], [Crtd_User],
                [CuryID], [CuryMultDiv], [CuryRate], [curytranamt], [DrCr], [EmployeeID],
                [EntryId], [JrnlType], [Labor_Class_Cd], [LineID], Linenbr+1, [LineRef],
                [LUpd_DateTime], [LUpd_Prog], [LUpd_User], [Module], [NoteID], [PayeeID],
                [PC_Flag], [PC_ID], [PC_Status], [PerClosed], [Perent], [PerPost], [ProjectID],
                [Qty], [RcnclStatus], [RecurId], [RefNbr], 1 AS [Rlsed],
                [S4Future01], [S4Future02], [S4Future03], [S4Future04], [S4Future05],
                [S4Future06], [S4Future07], [S4Future08], [S4Future09], [S4Future10],
                [S4Future11], [S4Future12], [Sub], [TaskID], [TranAmt], [TranDate],
                [TranDesc], [trsftobankacct], [trsftobanksub], [TrsfToCpnyID],
                [User1], [User2], [User3], [User4], [User5], [User6], [User7], [User8]
            FROM CATran
            WHERE BatNbr = ? AND Rlsed = 0
        ";
        return $this->execute($sql, [$batNbr]) !== false;
    }

    /* =============================
       STEP 3–6: Update duplicated CATran
       ============================= */

    private function updateTransferCATran($batNbr) {
        $sql = "
            UPDATE CATran
            SET
                Sub = trsftobanksub,
                banksub = trsftobanksub,
                Linenbr = Linenbr + 1,
                DrCr = CASE WHEN DrCr = 'C' THEN 'D' ELSE 'C' END,
                Acct = trsftobankacct,
                bankacct = trsftobankacct
            WHERE BatNbr = ? AND Rlsed = 1
        ";
        return $this->execute($sql, [$batNbr]) !== false;
    }

    /* =============================
       STEP 7: Release CATran
       ============================= */

    private function markCATranReleased($batNbr) {
        $sql = "UPDATE CATran SET Rlsed = 1 WHERE BatNbr = ?";
        return $this->execute($sql, [$batNbr]) !== false;
    }

    /* =============================
       STEP 8: Insert GLTran
       ============================= */

    private function insertGLTranFromCATran($batNbr) {
        $sql = "INSERT INTO GLTran
        (Id, Acct, AppliedDate, BalanceType, BaseCuryID, BatNbr, CpnyID, CrAmt, Crtd_DateTime,
         Crtd_Prog, Crtd_User, CuryCrAmt, CuryDrAmt, CuryEffDate, CuryID, CuryMultDiv, CuryRate,
         CuryRateType, DrAmt, EmployeeID, ExtRefNbr, FiscYr, IC_Distribution, JrnlType,
         Labor_Class_Cd, LedgerID, LineId, LineNbr, LineRef, LUpd_DateTime, LUpd_Prog, LUpd_User,
         Module, NoteID, OrigAcct, OrigBatNbr, OrigCpnyID, OrigSub, PC_Flag, PC_ID, PC_Status,
         PerEnt, PerPost, Posted, ProjectID, Qty, RefNbr, RevEntryOption, Rlsed, S4Future01,
         S4Future02, S4Future03, S4Future04, S4Future05, S4Future06, S4Future07, S4Future08,
         S4Future09, S4Future10, S4Future11, S4Future12, ServiceDate, Sub, TaskID, TranDate,
         TranDesc, TranType, Units, User1, User2, User3, User4, User5, User6, User7, User8)
        SELECT '' AS Id, Acct, TranDate AS AppliedDate, 'A' AS BalanceType, 'MWK' AS BaseCuryID, BatNbr,
         CpnyID, CASE WHEN DrCr = 'C' THEN TranAmt ELSE 0 END AS CrAmt, '1900-01-01 00:00:00' AS
         Crtd_DateTime, '' AS Crtd_Prog, '' AS Crtd_User, CASE WHEN DrCr = 'C' THEN TranAmt ELSE 0
         END AS CuryCrAmt, CASE WHEN DrCr = 'D' THEN TranAmt ELSE 0 END AS CuryDrAmt,
         '1900-01-01 00:00:00' AS CuryEffDate, 'MWK' AS CuryID, 'M' AS CuryMultDiv, 1 AS CuryRate,
         '' AS CuryRateType, CASE WHEN DrCr = 'D' THEN TranAmt ELSE 0 END AS DrAmt, '' AS
         EmployeeID, '' AS ExtRefNbr, LEFT(PerEnt, 4) AS FiscYr, 0 AS IC_Distribution, 'CA' AS
         JrnlType, '' AS Labor_Class_Cd, 'ACTUAL' AS LedgerID, 0 AS LineId, Linenbr AS LineNbr,
         '' AS LineRef, GETDATE() AS LUpd_DateTime, '01520' AS LUpd_Prog, 'C.HONDE' AS LUpd_User,
         'CA' AS Module, 0 AS NoteID, '' AS OrigAcct, '' AS OrigBatNbr, CpnyID AS OrigCpnyID,
         '' AS OrigSub, '' AS PC_Flag, '' AS PC_ID, '' AS PC_Status, PerEnt, PerPost, 'P' AS
         Posted, 0 AS ProjectID, 0 AS Qty, RefNbr, '' AS RevEntryOption, 1 AS Rlsed, 0, 0, 0, 0,
         0, 0, 0, 0, 0, 0, 0, 0, '1900-01-01 00:00:00' AS ServiceDate, Sub, '' AS TaskID, TranDate,
         TranDesc, 'ZZ' AS TranType, 0 AS Units, User1, User2, '' AS User3, '' AS User4, '' AS
         User5, '' AS User6, '' AS User7, '' AS User8
        FROM CATran WHERE BatNbr = ?";
        return $this->execute($sql, [$batNbr]) !== false;
    }


    /* =============================
       HELPER
       ============================= */

    private function execute($sql, $params = []) {
        $result = $this->databaseObject->PerformQuery($sql, $params);
        
        // Check for SQL Server errors first
        if (function_exists('sqlsrv_errors')) {
            $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
            if ($errors) {
                $errorDetails = [];
                foreach ($errors as $error) {
                    $errorDetails[] = "SQLSTATE: {$error['SQLSTATE']}, Code: {$error['code']}, Message: {$error['message']}";
                }
                $errorMessage = 'Database operation failed | SQL Errors: ' . implode(' | ', $errorDetails);
                $errorMessage .= ' | Query: ' . substr($sql, 0, 200) . (strlen($sql) > 200 ? '...' : '');
                
                throw new Exception($errorMessage);
            }
        }
        
        // Check if result is false (true failure)
        if ($result === false) {
            $errorMessage = 'Database operation failed - Query returned false';
            
            if (function_exists('sqlsrv_errors')) {
                $errors = sqlsrv_errors();
                if ($errors) {
                    $errorDetails = [];
                    foreach ($errors as $error) {
                        $errorDetails[] = "SQLSTATE: {$error['SQLSTATE']}, Code: {$error['code']}, Message: {$error['message']}";
                    }
                    $errorMessage .= ' | SQL Errors: ' . implode(' | ', $errorDetails);
                }
            }
            
            $errorMessage .= ' | Query: ' . substr($sql, 0, 200) . (strlen($sql) > 200 ? '...' : '');
            throw new Exception($errorMessage);
        }
        
        return $result;
    }
}

?>


