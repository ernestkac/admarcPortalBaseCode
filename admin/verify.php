<?php 

    require 'classes/payslip.php';
    require_once 'classes\EventLog.php';
    require_once 'classes\authentication.php';
    require_once 'classes\datefunctions.php';
    require_once 'classes\EmployeeDetails.php';
    
require_once 'ajaxcallhandler.php';

//zero meaning verify code not ok
  $verify = 0;

  if(!isset($_GET["code"])){
    $codeString = array(
      'empId'=> 'empid',
      'empName'=> 'emp name',
      'month'=> 'payslipmonth',
      'division'=> 'division',
      'printByEmpid'=> 'print empid',
      'printByName'=> 'print name',
      'printedAt'=> date('Y-m-d H:i')
    );
/*
    echo json_encode($codeString);

    echo '******';
    echo '******';
    echo '******';
    echo '******';

    echo Authentication::encrypt(json_encode($codeString));*/
  }

  //variables for getting payslip info
   $bankInfo = [];
   $bankInfoCount =0;
   $earnings = [];
   $deductions = [];
   $empContri = [];
   $preiodRange = 0;
            

  if(isset($_GET["code"])){
    
    //var_dump($_GET["code"]);
    $decryotedString = Authentication::decrypt($_GET["code"]);
    $array = explode(',', str_replace('"','',$decryotedString));
    //var_dump($decryotedString);
    //var_dump($array);
    
    if(count($array) === 4){

    $verify = 1;
    $EmployeementNumber = $array[0];
    $perEnt = $array[1];
    $crtUser = $array[2];
    $crtUserName = '';
    $crtDate = $array[3];


      //echo "empid entered";

      //fetching data for the creating user
      for ($dbNumber=1; $dbNumber <= 6; $dbNumber++) {
        //var_dump($dbNumber);
        $AuthenticationObject = new Authentication($dbNumber);
        $results = $AuthenticationObject->loginOther($crtUser);

        if($results){
          
          //var_dump($results);
          $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
          
          if ($row != null) {
            //var_dump($row);
            $empidInputValue = '';
          
            $databaseNumber = $dbNumber;

            $crtUserName = trim($row['name']);
          }
        }
      }

        //fetching data for the payslip
      for ($dbNumber=1; $dbNumber <= 6; $dbNumber++) {
        //var_dump($dbNumber);
        $AuthenticationObject = new Authentication($dbNumber);
        $results = $AuthenticationObject->loginOther($EmployeementNumber);

        if($results){
          
          //var_dump($results);
          $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
          
          if ($row != null) {
            //var_dump($row);
            $empidInputValue = '';
          
            $databaseNumber = $dbNumber;

            $print_name_title = trim($row['name']);
            //profile data
/*
            $row['profile']["name"] = $row['name'];
            $row['profile']["empid"] = $row['empid'];
            $row['profile']["status"] = $row['empid'];
            $row['profile']["accessLevel"] = $row['accessLevel'];
            $row['profile']["division"] = $row['division'];
            $row['profile']["LstLgin"] = $row['LstLginDateTime']->format("d/m/Y h:i A");

            */
            $payslipObject = new Payslip($databaseNumber);
            
            //getting employee info
            $empInfo = $payslipObject->getEmployeeInfo($EmployeementNumber);
            $employee = 0;

            if ($empInfo) {
                $employee = sqlsrv_fetch_array($empInfo, SQLSRV_FETCH_ASSOC);
                    //var_dump($employee );
            }
            //getting bank schedule info
            $EmployBankInfo = $payslipObject->getBankScheduleInfo($EmployeementNumber,$perEnt,$perEnt);
           
            $eventLogger = new EventLog($databaseNumber);

            $OperationLog = $EmployeementNumber ." payslip verify month : ".$perEnt;
            $eventLogger->addLog(000000, 'verify',
            $row["accessLevel"], $OperationLog);

            
            while ($BankInfoRow = sqlsrv_fetch_array($EmployBankInfo, SQLSRV_FETCH_ASSOC)) {
                  $bankInfo[] = $BankInfoRow;
              }
            
            //getting earnings info
              $preiodRange = $payslipObject->getPreiodRange($EmployeementNumber,$perEnt,$perEnt);

            //getting earnings info
              $employEarnings = $payslipObject->getEarnings($EmployeementNumber,$perEnt,$perEnt);
              $earningsCount = 0;
             
              while ($employEarningsRow = sqlsrv_fetch_array($employEarnings, SQLSRV_FETCH_ASSOC)) {
                  $earnings[] = $employEarningsRow;
              }

            //getting deductions info
            $employDeductions = $payslipObject->getDeductions($EmployeementNumber,$perEnt,$perEnt);
            
            $deductionsCount = 0;
            while ($employDeductionsRow = sqlsrv_fetch_array($employDeductions, SQLSRV_FETCH_ASSOC)) {
                $deductions[] = $employDeductionsRow;
            }
            
            //getting employer contributions info
            $employcontributions = $payslipObject->getEmployerContributions($EmployeementNumber,$perEnt,$perEnt);
            
            $empContriCount = 0;
            while ($employcontributionsRow = sqlsrv_fetch_array($employcontributions, SQLSRV_FETCH_ASSOC)) {
                $empContri[] = $employcontributionsRow;
            }
            break;
          }
        }
      }
    }else{
            $empidInputValue = 'Failed to verify your scan!';
          }
    } 

?>

<!DOCTYPE html>
<html>
<head>
   <link rel="icon" href="assets/img/admarc-png-logo.png">
   <!-- Font Awesome CDN -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-..."
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />  
  <link
    rel="stylesheet"
    href="assets\css\chartmodal.css"
  />  
  <link
    rel="stylesheet"
    href="assets\css\profilemodal.css"
  />   
  <link
    rel="stylesheet"
    href="assets\css\search.css"
  />  

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
        margin: 0;
        padding: 0;
        background-color: #FAFAFA;
        font: 12pt "Tahoma";
    }
      #copy{
        margin-left:15px;
      }
    #id01 {
      visibility: hidden;
    }
    * {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
    }
      .payslipHeader{
        display: flex;
        justify-content: space-between;
      }
      .qrcode-div{
        display: grid;
        place-items: center;
      }
      #qrcode-logo{
        grid-area: 1 / 1;
      }
      .qrcode{
        grid-area: 1 / 1;
      }
    .page {
        width: 297mm;
        
        padding: 10mm;
        margin: 10mm auto;
        border: 1px #D3D3D3 solid;
        border-radius: 5px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }
    .form-div {
            width: 297mm;
            padding: 10mm;
            margin: auto;
            
    }
    .print-logo{
      /*display: none;*/
       height: 120px;
    }
    .form-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .center-input {
        flex-grow: 1;
        text-align: center;
    }
    input[type=number], select {
      padding: 12px 20px;
      margin: 8px 20px;
      border: 2px solid #ccc;
      border-radius: 4px;
    }

    .date-range {
      
      display: flex;
      justify-content: center;
      align-items: center;
      display: flex;
      gap: 30px;
    }

    .date-field {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }

    .label {
      color: #888;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .date-box {
      display: flex;
      align-items: center;
      background-color: white;
      border-radius: 8px;
      padding: 10px 16px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      min-width: 120px;
      justify-content: center;
    }

    .calendar-icon {
      width: 16px;
      height: 16px;
      margin-right: 8px;
      fill: #333;
    }

    .date-text {
      font-weight: 500;
      color: #222;
    }

    .rangeMonth {
      position: absolute;
      width: 0;
      height: 0;
      border: none;
      padding: 0;
      visibility: hidden;
    }

              
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      background-color: #fff;
      border-bottom: 1px solid #ddd;
    }

    .logo {
      font-weight: bold;
      font-size: 1.2rem;
    }

    .nav-left {
      display: flex;
      align-items: center;
    }
    .nav-center {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .date-range {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .date-range input[type="date"] {
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
          
    .nav-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .search-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-container i {
      position: absolute;
      left: 12px;
      color: #999;
    }

    .search-container input {
      padding: 6px 6px 6px 30px;
      border: 1px solid #ccc;
      border-radius: 8px; 
      width: 140px;
    }

    .btn {
      background-color: #f5f5f5;
      border: none;
      padding: 8px 10px;
      border-radius: 8px;
      margin: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .btn i {
      font-size: 16px;
    }

    .btn.logout {
      font-weight: bold;
    }
    .space-div{
      display: flex;
      flex-direction: row-reverse;
      margin: 60px auto;
    }
    .nav-logo{
      height: 40px; /* Adjust to preferred height */
      object-fit: contain;
    }
    a{
      text-decoration: none;
    }
    @media print {
      .noprint {
          visibility: hidden;
      }
      body{
          background-color: white;
      }
      .page {
          width: 297mm;
          break-before: page;
          padding: 5mm;
          margin: auto;
          border: 0px;
          box-shadow: 0 0 0px rgba(0, 0, 0, 0.1);
      }
                  
      .screen-only{
        display: none;
      }       
      .print-logo{
        /*display: block;
        position: inline;
        top:10px;
        left:20px;
        width: 130px;*/
      }
    }

   
  </style>
</head>
<body>


  <div class="space-div screen-only">
    <div id="suggestions" class="suggestions-box noprint screen-only"></div>
  </div>
   <div id="search-message-result" class="search-message-result noprint screen-only">
      <?php echo $empidInputValue; ?>
    </div>
  


  <?php 
  $totalEarnings = 0;
  $qrcodeId = 0;

  if($verify === 1)
  while ($preiodRangeRow = sqlsrv_fetch_array($preiodRange, SQLSRV_FETCH_ASSOC)) {
  ?>

    <div class="page">

      
      <div class='payslipHeader'>
        <div class="logo-div">
          <img src='\assets\img\admarc logo.png' alt='admarc-logo' class='print-logo'>
        </div>

        <div id='created-user-profile'>
          <p  style="margin: 0px;">Generated by:</p>
          <h3 style="margin-bottom: 0px;">
            <?php
              echo $crtUserName;
            ?>
          </h3>
          <h4 style="margin-top: 0px;">
            <?php
              echo $crtUser;
            ?>
          </h4>
          <small>
            <?php
              echo $crtDate;
            ?>
          </small>
        </div>
        
        <div id="qr-container" class="qrcode-div print-logo"  style=" width: 120px; height: 120px;">
            <div
              id="qrcode<?php echo $qrcodeId; $qrcodeId +=1;?>" 
              data-hidden-value='<?php $codeString = $EmployeementNumber.",".$perEnt.",".$crtUser.",".$crtDate;
              echo "https://admarcportal.pages.dev?code=".Authentication::encrypt(json_encode($codeString)); ?>'
              class="qrcode" ></div>
            <img id='qrcode-logo' src="\assets\img\admarc logo.png" 
                alt="Logo"
                style="top: 35px; left: 35px; width: 50px; height: 50px; border-radius: 10px;" />
        </div>
      </div>

        <div style="background-color:#999999;">
            <h2 style="text-align: center; margin:0;">PAY SLIP</h2>
            <h2 class="form-container noprint" style="text-align: center; margin:0;"></h2>
        </div>
        <hr style="height:6px;background-color:black; margin:1;">

      <table style="width:100%">
      
      <tr>
        <td style="width:14%">Employee No.:</td>
        <td style="width:25%">
          <?php if ($employee != 0)echo trim($employee['EmpId']); ?>
        </td>
        <td style="width:10%"></td>
        <td style="width:11%">Bank Code:</td>
        <td style="width:10%; padding:0">
        <?php if ($bankInfo != 0)echo trim($bankInfo[0]['SortCode']); ?> - 
        </td>
        <td style="width:30%">
          <?php if ($bankInfo != 0)echo trim($bankInfo[0]['Bank_Name']); ?>
        </td>
      </tr>
      <tr>
        <td >Employee Name.:</td>
        <td ><?php if ($employee != 0)echo trim($employee['Name']); ?></td>
        <td ></td>
        <td >Bank account:</td>
        <td ><?php if ($bankInfo != 0)echo trim($bankInfo[0]['Bank_Account']); ?></td>
      </tr>
      <tr>
        <td >Pay GroupID:</td>
        <td ><?php if ($employee != 0)echo trim($employee['PayGroup']); ?></td>
      </tr>
      <tr>
        <td >Pay Date:</td>
        <td >
            <?php 
                for ($i = $bankInfoCount; $i < count($bankInfo); $i++) {
                    $bankInfoRow = $bankInfo[$i];
                if ($bankInfo != 0 && $preiodRangeRow['PerEnt'] == $bankInfoRow['PerEnt']) echo $bankInfoRow['ChkDate']->format('l, j F,Y'); 
                }?>
        </td>
      </tr>
      <tr>
        <td >Job Title:</td>
        <td ><?php if ($employee != 0)echo trim($employee['JobTitle']); ?></td>
      </tr>
      <tr>
        <td >Location:</td>
        <td ><?php if ($employee != 0)echo trim($employee['Location']); ?></td>
      </tr>
      </table>

      <hr style="height:6px;background-color:black; margin:1;">

      <table style="width:100%">
        
        <tr>
          <th style="width:30% ">Description</th>
          <th style=" text-align: right;width:25%;">Employer Contribution</th>
          <th style=" text-align: right;width:15%">Earning</th>
          <th style=" text-align: right;width:15%">Deduction</th>
          <th style=" text-align: right;width:15%;">Balance</th>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <tr>
        </tr>
        <!--earning-->


        <?php 
          $totalEarnings = 0;
          for ($i = $earningsCount; $i < count($earnings); $i++) {
              $row = $earnings[$i];
        ?>

        
                <tr>
                  <td ><?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo trim($row['Descr']); else{ $earningsCount = $i; break;} ?></td>
                  <td ></td>
                  <td style=" text-align: right;">
                    <?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
                  </td>
                  <td ></td>
                  <td ></td>
                </tr>

      <?php 

      $totalEarnings = $totalEarnings + $row['TranAmt'];
      } ?>
        <!--end of earnings-->

        <!--deduction-->
        <?php
          $totalDeductions = 0;
          $totalLoanBalance = 0;
          for ($i = $deductionsCount; $i < count($deductions); $i++) {
              $row = $deductions[$i];
        ?>
          <tr>
            <td ><?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo trim($row['Descr']); else{ $deductionsCount = $i; break;} ?></td>
            <td ></td>
            <td ></td>
            <td style=" text-align: right;">
              <?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
            </td>
            <td style=" text-align: right;">
              <?php if ($employee != 0 && $row['LoanBalance'] != 0  && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo number_format($row['LoanBalance'], 2, '.',' ,'); ?>
            </td>
          </tr>

        
      <?php 
      $totalDeductions = $totalDeductions + $row['TranAmt'];
      $totalLoanBalance = $totalLoanBalance + $row['LoanBalance'];
      }?>
      <!--end of deduction-->

        <!--employer contribution-->

        <?php
              $totalemploycontributions = 0;
              for ($i = $empContriCount; $i < count($empContri); $i++) {
              $row = $empContri[$i];
          ?>
            <tr>
              <td ><?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo trim($row['Descr']); else{ $empContriCount = $i; break;} ?></td>
              <td style=" text-align: right;">
                <?php if ($employee != 0 && $preiodRangeRow['PerEnt'] == $row['PerEnt'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
              </td>
              <td ></td>
              <td ></td>
              <td ></td>
            </tr>
          <?php 
          $totalemploycontributions = $totalemploycontributions + $row['TranAmt'];
          }?>

      <!-- end of employer contribution-->
        
        <!--totals -->
          <tr>
              <td ></td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php if ($bankInfo != 0)echo number_format($totalemploycontributions, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php if ($bankInfo != 0)echo number_format($totalEarnings, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php if ($bankInfo != 0)echo number_format($totalDeductions, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php if ($bankInfo != 0)echo number_format($totalLoanBalance, 2, '.',' ,'); ?>
              </td>
          </tr>

          
        <td ></td>
          <td ></td>
        <td ></td>
          <td ></td>
        <td ></td>
          <td ></td>
        <td ></td>
          <td ></td>

      </table>
      <table>
          <tr>
              <td style="width:50%">
                <small>
                  <?php 
                for ($i = $bankInfoCount; $i < count($bankInfo); $i++) {
                      $bankInfoRow = $bankInfo[$i];
                      if ($bankInfo != 0 && $preiodRangeRow['PerEnt'] == $bankInfoRow['PerEnt'])echo number_format($bankInfoRow['NetAmt'], 2, '.',' ,'); }?>
                      Credited to your A/C No. : 
                      <?php if ($bankInfo != 0)echo trim($bankInfo[0]['Bank_Account']); ?> 
                      - <?php if ($bankInfo != 0)echo trim($bankInfo[0]['Bank_Name']); ?>
                    </small>
                  </td>
                  <td style="width:10%"></td>
                  <td><h2>NET PAY</h2></td>
                  <td>
                    <h2 STYLE="text-decoration:overline; border-bottom: 6px double; text-align: right;">
                      <?php
                      for ($i = $bankInfoCount; $i < count($bankInfo); $i++) {
                      $bankInfoRow = $bankInfo[$i];
                      if ($bankInfo != 0 && $preiodRangeRow['PerEnt'] == $bankInfoRow['PerEnt'])echo number_format($bankInfoRow['NetAmt'], 2, '.',' ,'); 
                      }?>
                </h2>
              </td>   
          </tr>
      </table>

    </div>

  <?php } ?>


  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

  <script type="text/javascript">

  
    //QR code script
    
      const maxqrcodeId = <?php echo $qrcodeId; ?>;
      for (let index = 0; index < maxqrcodeId; index++) {

        qrcodeId = document.getElementById("qrcode"+index);
        link = qrcodeId.dataset.hiddenValue;
        console.log(link)
        new QRCode(qrcodeId, {
          text: link,
          width: 120,
          height: 120,
          correctLevel: QRCode.CorrectLevel.H
        });
      }
      

        //end of QR code script


  </script>

</body>
</html>