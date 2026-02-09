<?php 
     require 'classes/payslip.php';
     $payslipObject = new Payslip(3);
     $empInfo = $payslipObject->getDeductions('482217','202306');
 
     
        while ($row = sqlsrv_fetch_array($empInfo, SQLSRV_FETCH_ASSOC)) {
            var_dump($row);
        }
    
?> 