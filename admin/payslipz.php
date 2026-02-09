
<?php 
    require_once 'session_handler.php';
    
    if(!isset($_SESSION["name"])){
        header("Location: index");
    }
    if($_SESSION["accessLevel"] != 'ADMIN' & $_SESSION["accessLevel"] != 'HR' & $_SESSION["accessLevel"] != 'icttech' ){
        header("Location: index");
        exit;
    }
    /*
    // remove all session variables
    session_unset();

    // destroy the session
    session_destroy();*/

    require 'classes/payslip.php';
    require_once 'classes\EventLog.php';
    require_once 'classes\authentication.php';

    $EmployeementNumber = $_SESSION["empid"];
    $databaseNumber = $_SESSION["databaseNumber"];
    $empidInputValue = '';
    $division = $_SESSION['division'];


    if(!isset($_SESSION["perpost"])){
      $_SESSION['perpost'] = '202410';
      $payslipObject = new Payslip($databaseNumber);
      //getting employee info
      $_SESSION['perpost'] = $payslipObject->getLastPerPost($EmployeementNumber);
    }
     
    if (isset($_POST['Division_dropdown']) ) {
      
      $division = $_POST['Division_dropdown'];
    }
    if (isset($_POST['payroll_dropdown']) ) {
      
        $dbNumber = 2;

        if($_POST['payroll_dropdown'] == 1)
          $dbNumber = $_POST['payroll_dropdown'];

         elseif($_POST['payroll_dropdown'] == 2)
          $dbNumber = $_POST['payroll_dropdown'];

         else{
          if($division[0] == 1)
            $dbNumber = 3;
          if($division[0] == 2)
            $dbNumber = 4;
          if($division[0] == 3)
            $dbNumber = 5;
          if($division[0] == 4)
            $dbNumber = 6;
         }
        //echo $dbNumber;
        $_SESSION['databaseNumber'] = $dbNumber;
        $databaseNumber = $_SESSION["databaseNumber"];
      
    }

    if (isset($_POST['empid']) && $_POST['empid'] != '' ) {
      
        //echo "empid entered";
        for ($dbNumber=1; $dbNumber <= 6; $dbNumber++) {
          //var_dump($dbNumber);
          $AuthenticationObject = new Authentication($dbNumber);
          $results = $AuthenticationObject->loginOther($_POST['empid']);
          if($results){
            
            //var_dump($results);
            $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
            
            if ($row != null) {
              //var_dump($row);
              if($_SESSION['division'] == $row['division'] || $_SESSION['division'] == 100 || $_SESSION["accessLevel"] == 'ADMIN'){
                $_SESSION['otherEmpid'] = $row['empid'];
                $_SESSION['databaseNumber'] = $dbNumber;
                if($_SESSION["accessLevel"] == 'ADMIN')
                  $empidInputValue = $row['empid'] .' - '. $row['WCCode'];
              }else{
                $empidInputValue = 'You do not have rights to view!';
              }
                
              break;
            }else{
              $empidInputValue = 'The employement number not found!';
            }
          }
        }
      
      
    } 
    
    elseif (isset($_POST['monthLeft']) ) {
          
      $dateFormat = 'Ym';
      $date = DateTime::createFromFormat($dateFormat,trim($_SESSION["perpost"]));
      $date->modify('-1 month');
      $date->modify('-1 month');
      $_SESSION['perpost'] = $date->format('Ym');
    }

    elseif (isset($_POST['monthRight']) ) {
        
        $dateFormat = 'Ym';
        $date = DateTime::createFromFormat($dateFormat,trim($_SESSION["perpost"]));
        $date->modify('+1 month');
        $_SESSION['perpost'] = $date->format('Ym');
    }
    
    if (isset($_POST['perpost']) && $_POST['perpost'] != "") {
        $date = new DateTime($_POST['perpost']); 
        $date->modify('+9 month');
        $_SESSION['perpost'] = $date->format('Ym');
    }
    
    //var_dump( $_SESSION);

    if(isset($_SESSION['otherEmpid']))
      $EmployeementNumber = trim($_SESSION['otherEmpid']);

    $payslipObject = new Payslip($databaseNumber);

    
    if($_SESSION["accessLevel"] != 'ADMIN'){
      if ($_SESSION['perpost'] > $payslipObject->aprovedPerPost) {
          $_SESSION['perpost'] = $payslipObject->aprovedPerPost;
      }
    }

    //getting employee info
    $empsInfo = false;
    
      $empsInfo = $payslipObject->getAllEmployeeInfo($_SESSION['perpost'], $division);
    $employees = 0;
    /*
    if ($empsInfo) {
        $employees = sqlsrv_fetch_array($empsInfo, SQLSRV_FETCH_ASSOC);
            //var_dump($employees );
    }*/

    $eventLogger = new EventLog($databaseNumber);
    $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
     $_SESSION["accessLevel"], "ADMIN payslips view month : ".$_SESSION['perpost']); 

    
    //getting earnings info
    $employEarnings = false;
    
      $employEarnings = $payslipObject->getAllEarnings($_SESSION['perpost'], $division);
    $earnings = [];
    $earningsCount = 0;
    while ($row = sqlsrv_fetch_array($employEarnings, SQLSRV_FETCH_ASSOC)) {
        $earnings[] = $row;
    }
    
    //getting deductions info
    $employDeductions = false;
    
      $employDeductions = $payslipObject->getAllDeductions($_SESSION['perpost'], $division);
    $deductions = [];
    $deductionsCount = 0;
    while ($row = sqlsrv_fetch_array($employDeductions, SQLSRV_FETCH_ASSOC)) {
        $deductions[] = $row;
    }
    
    //getting employer contributions info
    $employcontributions = false;
    
      $employcontributions = $payslipObject->getAllEmployerContributions($_SESSION['perpost'], $division);
    $empContri = [];
    $empContriCount = 0;
    while ($row = sqlsrv_fetch_array($employcontributions, SQLSRV_FETCH_ASSOC)) {
        $empContri[] = $row;
    }

?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="icon" href="assets/img/admarc-png-logo.png">
    <style>
      body {
          margin: 0;
          padding: 0;
          background-color: #FAFAFA;
          font: 12pt "Tahoma";
      }
      #id01 {
        visibility: hidden;
      }
      * {
          box-sizing: border-box;
          -moz-box-sizing: border-box;
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
                
      .print-logo{
        display: none;
      }
      @media print {
        .noprint {
            visibility: hidden;

        }
                  
      .print-logo{
        display: block;
        position: inline;
        top:0px;
        left:20px;
        width: 100px;
      }
        .page {
            page-break-after: always;
            padding: 0mm;
          margin: 10mm auto;
          border: 0px #D3D3D3 solid;
          box-shadow: 0 0 0px rgba(0, 0, 0, 0.1);
        }
                
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
      input, select {
      padding: 12px 20px;
      margin: 8px 20px;
      border: 2px solid #ccc;
      border-radius: 4px;
    }

    .sticky-form {
      position: fixed; /* Makes the form stay in place */
      top: 0; /* Positions it at the top of the viewport */
      left: 0; /* Aligns it to the left edge */
      width: 100%; /* Makes it span the full width of the viewport */
      background-color: white; /* Optional: Add a background color */
      padding: 10px; /* Optional: Add some padding */
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Optional: Add a shadow for better visibility */
      z-index: 1000; /* Ensures the form stays above other content */
    }

    </style>
  </head>
  <body>

    <form method="post" id="payslips_form" class="sticky-form form-container noprint">
        <button type="submit" style="background:transparent; border:none;" name="monthLeft">
          <img src="assets\img\leftbtn1.ico" alt="Previous Month">
        </button>
        <?php 
        if($_SESSION["accessLevel"] == 'ADMIN'){
          $divisionSelectTag = "
        <div id='Division_dropdown_div' class='form-group center-input'>
          <select id='Division_dropdown' name='Division_dropdown' class='form-control'>
          <option value='100'  ";
          if($division == 100)
            $divisionSelectTag .= "selected";
          $divisionSelectTag .=" >Head Office</option>
          <option value='201'  ";
              if($division == 201)
                $divisionSelectTag .= "selected";
              $divisionSelectTag .=" >NGABU</option>
              <option value='202'  ";
              if($division == 202) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="   >BLANTYRE</option>
              <option value='203'  ";
              if($division == 203) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Luchenza</option>
              <option value='205'  ";
              if($division == 205) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Liwonde</option>
              <option value='207'  ";
              if($division == 207) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Balaka</option>
              <option value='308'  ";
              if($division == 308) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Lilongwe</option>
              <option value='310'  ";
              if($division == 310) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Mponera</option>
              <option value='311'  ";
              if($division == 311) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Kasungu</option>
              <option value='312'  ";
              if($division == 312) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >SALIMA</option>
              <option value='413'  ";
              if($division == 413) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Mzuzu</option>
              <option value='415'  ";
              if($division == 415) 
                $divisionSelectTag .="selected"; 
              $divisionSelectTag .="  >Karonga</option>
          </select>
        </div>";
        echo $divisionSelectTag;
        }
        ?>
        <div class="form-group center-input">
            <select id="payroll_dropdown" name="payroll_dropdown" class="form-control">
                <option value="1" <?php if($_SESSION['databaseNumber'] == 1) echo 'selected'?>>Executive Staff</option>
                <option value="2" <?php if($_SESSION['databaseNumber'] == 2) echo 'selected'?>>Senior Staff</option>
                <option value="3" <?php if($_SESSION['databaseNumber'] > 2) echo 'selected'?>>Junior Staff</option>
            </select>
        </div>
        <button type="submit" style="float:right; background:transparent; border:none;" name="monthRight" >
          <img src="assets\img\rightbtn1.ico" alt="Next Month">
        </button>
      </form>
    <?php
      while ($employee = sqlsrv_fetch_array($empsInfo, SQLSRV_FETCH_ASSOC)) {
        //var_dump($employee);
    ?>
    <div class="page">
      
      <img src='\assets\img\admarc logo.png' alt='admarc-logo' class='print-logo'>
      <div style="background-color:#999999;">
          <h2 style="text-align: center; margin:0;">PAY SLIP</h2>
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
          <?php if ($employee != 0)echo trim($employee['SortCode']); ?> - 
          </td>
          <td style="width:30%">
            <?php if ($employee != 0)echo trim($employee['Bank_Name']); ?>
          </td>
        </tr>
        <tr>
          <td >Employee Name.:</td>
          <td ><?php if ($employee != 0)echo trim($employee['Name']); ?></td>
          <td ></td>
          <td >Bank account:</td>
          <td ><?php if ($employee != 0)echo trim($employee['Bank_Account']); ?></td>
        </tr>
        <tr>
          <td >Pay GroupID:</td>
          <td ><?php if ($employee != 0)echo trim($employee['PayGroup']); ?></td>
        </tr>
        <tr>
          <td >Pay Date:</td>
          <td >
            <?php if ($employee != 0) echo $employee['ChkDate']->format('l, j F,Y'); ?>
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
          //echo  "start i value ->".$i;
        ?>

            <tr>
              <td ><?php if ($employee['EmpId'] == $row['empid'])echo trim($row['Descr']); else{ $earningsCount = $i; break;}?></td>
              <td ></td>
              <td style=" text-align: right;">
                <?php if ($employee['EmpId'] == $row['empid'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
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
          <td ><?php if ($employee['EmpId'] == $row['empid'])echo trim($row['Descr']); else{ $deductionsCount = $i; break;}?></td>
          <td ></td>
          <td ></td>
          <td style=" text-align: right;">
            <?php if ($employee['EmpId'] == $row['empid'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
          </td>
          <td style=" text-align: right;">
            <?php if ($employee['EmpId'] == $row['empid'] && $row['LoanBalance'] != 0 )echo number_format($row['LoanBalance'], 2, '.',' ,'); ?>
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
          <td ><?php if ($employee['EmpId'] == $row['empid'])echo trim($row['Descr']); else{ $empContriCount = $i; break;} ?></td>
          <td style=" text-align: right;">
            <?php if ($employee['EmpId'] == $row['empid'])echo number_format($row['TranAmt'], 2, '.',' ,'); ?>
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
                <?php echo number_format($totalemploycontributions, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php echo number_format($totalEarnings, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php echo number_format($totalDeductions, 2, '.',' ,'); ?>
              </td>
              <td style="text-decoration:overline; text-align: right; font-weight:bold ;">
                <?php echo number_format($totalLoanBalance, 2, '.',' ,'); ?>
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
                  <?php if ($employee != 0)echo number_format($employee['NetAmt'], 2, '.',' ,'); ?>
                  Credited to your A/C No. : 
                  <?php if ($employee != 0)echo trim($employee['Bank_Account']); ?> 
                  - <?php if ($employee != 0)echo trim($employee['Bank_Name']); ?>
                </small>
              </td>
              <td style="width:10%"></td>
              <td><h2>NET PAY</h2></td>
              <td>
                <h2 STYLE="text-decoration:overline; border-bottom: 6px double; text-align: right;">
                  <?php if ($employee != 0)echo number_format($employee['NetAmt'], 2, '.',' ,'); ?>
                </h2>
              </td>   
          </tr>
      </table>

    </div>
    <?php
        }
    ?>

    <pingendo onclick='openDatepicker();' class="noprint"
    style="cursor:pointer;position: fixed;bottom: 
    50px;right:20px;padding:4px;background-color: 
    #00b0eb;border-radius: 8px; width:220px;display:flex;
    flex-direction:row;align-items:center;justify-content:center;
    font-size:14px;color:white">Choose Month&nbsp;&nbsp;</pingendo>
    <a  href="index">
    <pingendo class="noprint"
    style="cursor:pointer;position: fixed;bottom: 
    20px;right:20px;padding:4px;background-color: 
    #DC4C64;border-radius: 8px; width:220px;display:flex;
    flex-direction:row;align-items:center;justify-content:center;
    font-size:14px;color:white">SIGN OUT&nbsp;&nbsp;</pingendo>
    </a>
    <div id="id01" class="noprint" style="cursor:pointer;position: fixed;bottom: 20px;right:20px;padding:4px;background-color: #00b0eb;border-radius: 8px; width:220px;display:flex;flex-direction:row;align-items:center;justify-content:center;font-size:14px;color:white">
      <div class="w3-modal-content">
        <div class="w3-container">
          <span onclick="document.getElementById('id01').style.visibility = 'hidden';" class="w3-closebtn">&times;</span>
            <form method="post" action="">
                <p>pick Month</p>
                <input type="month" style="display: block;
                                    height: calc(1.5em + .75rem + 2px);
                                    padding: .375rem .75rem;
                                    font-size: 1rem;
                                    font-weight: 400;
                                    line-height: 1.5;
                                    color: #495057;
                                    background-color: #fff;
                                    background-clip: padding-box;
                                    border: 1px solid #ced4da;
                                    border-radius: .25rem;
                                    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;" id="perpost" name="perpost"/>
                <input type="submit" style="color: #fff;
                        background-color: #007bff;width: 100%;
                        border-color: #007bff;
                        display: inline-block;
                        font-weight: 400;
                        text-align: center;
                        vertical-align: middle;
                        cursor: pointer;
                        padding: .375rem .75rem;
                        font-size: 1rem;
                        line-height: 1.5;
                        border-radius: .25rem;
                        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;" value="view payslip"/>
            </form>
        </div>
      </div>
    </div>
    <script type="text/javascript">

    
        function openDatepicker() {
            console.log('openDatepicker clicked');
            document.getElementById('id01').style.visibility = 'visible';
        }

        document.addEventListener('change', function(event) {
            if(event.target.tagName === 'SELECT'){
                document.getElementById('payslips_form').submit();
            }
        });

    </script>
  </body>
</html>