
<?php 

    session_start();
    if(!isset($_SESSION["name"])){
        header("Location: index");
    }
    /*
    // remove all session variables
    session_unset();

    // destroy the session
    session_destroy();*/

    require 'classes/payslip.php';
    require_once 'classes\EventLog.php';
    require_once 'classes\authentication.php';
    require_once 'classes\datefunctions.php';
    require_once 'classes\EmployeeDetails.php';

    $EmployeementNumber = $_SESSION["empid"];
    $databaseNumber = $_SESSION["databaseNumber"];
    $empidInputValue = '';
    $OperationLog = '';

    
         // Handle AJAX call
    require_once 'ajaxcallhandler.php';

       //profile data
       if(!isset($_SESSION["profile"])){
          $_SESSION['profile'] = [
            "name" => $_SESSION['name'],
            "empid" => $_SESSION['empid'],
            "accesscode" => $_SESSION['accesscode'],
            "status" => $_SESSION['empid'],
            "accessLevel" => $_SESSION['accessLevel'],
            "division" => $_SESSION['division'],
            "LstLgin" => $_SESSION['LstLginDateTime'],
          ];
        }


    if(!isset($_SESSION["searchSourceID"])){
      $_SESSION["searchSourceID"] = 0;
    }


    if(!isset($_SESSION["perpost"])){
      $_SESSION['perpost'] = '202410';
      $payslipObject = new Payslip($databaseNumber);
    //getting employee info
    $_SESSION['perpost'] = trim($payslipObject->getLastPerPost($EmployeementNumber));
    }

    
   

    if (isset($_POST['period_checkbox']) && $_POST['period_checkbox'] == 'on' ) {
      $payslipObject = new Payslip($databaseNumber);

      
      if($payslipObject->culPerPost == $payslipObject->aprovedPerPost){
        $payslipObject->disaprovePerPost();
        $OperationLog = 'restricting view of period '.$payslipObject->culPerPost;
      }
        
      else{
        $payslipObject->aprovePerPost();
        $OperationLog = 'allowing view of period '.$payslipObject->culPerPost;
      } 
      
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
              $empidInputValue = '';
              
              if($_SESSION['division'] == $row['division'] || $_SESSION['division'] == 100 || $_SESSION["accessLevel"] == 'ADMIN'){
                $_SESSION['otherEmpid'] = $row['empid'];
                $_SESSION['databaseNumber'] = $dbNumber;
                $databaseNumber = $dbNumber;

                $print_name_title = trim($row['name']);
                //profile data

                $_SESSION['profile']["name"] = $row['name'];
                $_SESSION['profile']["empid"] = $row['empid'];
                $_SESSION['profile']["accesscode"] = $row['WCCode'];
                $_SESSION['profile']["status"] = $row['empid'];
                $_SESSION['profile']["accessLevel"] = $row['accessLevel'];
                $_SESSION['profile']["division"] = $row['division'];
                $_SESSION['profile']["LstLgin"] = $row['LstLginDateTime']->format("d/m/Y h:i A");

                $_SESSION["searchSourceID"] = $_POST['searchSourceID'];

                $payslipObject = new Payslip($databaseNumber);
                $_SESSION['perpost'] = $payslipObject->getLastPerPost($_POST['empid']);
                $_SESSION['fromMonth'] = $_SESSION['perpost'];
                $_SESSION['toMonth'] = $_SESSION['perpost'];
                    
                  
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
        //$date->modify('-1 month');
        $_SESSION['perpost'] = $date->format('Ym');
    }

    elseif (isset($_POST['monthRight']) ) {
        
        $dateFormat = 'Ym';
        $date = DateTime::createFromFormat($dateFormat,trim($_SESSION["perpost"]));
        $date->modify('+1 month');
        $_SESSION['perpost'] = $date->format('Ym');
    }

    if (isset($_POST['toMonth']) || isset($_POST['fromMonth'])) {

      if($_POST['fromMonth'] !='' ){
        $_SESSION['fromMonth'] = dateMonthToPreriod($_POST['fromMonth']);
      }
      if($_POST['toMonth'] !='' ){
        $_SESSION['toMonth'] = dateMonthToPreriod($_POST['toMonth']);
      }

    }
    
    elseif (isset($_POST['perpost']) && $_POST['perpost'] != "") {
        
        $_SESSION['perpost'] = dateMonthToPreriod($_POST['perpost']); 
    }

     
    //var_dump( $_SESSION);
    if(!isset($_SESSION['fromMonth']))
      $_SESSION['fromMonth'] = $_SESSION['perpost'];
    if(!isset($_SESSION['toMonth']))
      $_SESSION['toMonth'] = $_SESSION['perpost'];

    
    if(isset($_SESSION['otherEmpid']))
      $EmployeementNumber = trim($_SESSION['otherEmpid']);

    $payslipObject = new Payslip($databaseNumber, $_SESSION["searchSourceID"]);

    if($_SESSION["accessLevel"] != 'ADMIN'){
      if ($_SESSION['toMonth'] > $payslipObject->aprovedPerPost) {
          $_SESSION['toMonth'] = $payslipObject->aprovedPerPost;
      }
    }

    
    $print_name_title = $_SESSION['profile']['name'];


    //getting employee info
    $empInfo = $payslipObject->getEmployeeInfo($EmployeementNumber);
    $employee = 0;

    if ($empInfo) {
        $employee = sqlsrv_fetch_array($empInfo, SQLSRV_FETCH_ASSOC);
            //var_dump($employee );
    }
    //getting bank schedule info
    $EmployBankInfo = $payslipObject->getBankScheduleInfo($EmployeementNumber,$_SESSION['fromMonth'],$_SESSION['toMonth']);
    $bankInfo = [];
    $bankInfoCount =0;
    
    $eventLogger = new EventLog($databaseNumber);
    if ($OperationLog == '') {
      $OperationLog = $EmployeementNumber ." payslip view month : ".$_SESSION['fromMonth']." to ".$_SESSION['toMonth'];
    }
    $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
     $_SESSION["accessLevel"], $OperationLog);

    
    while ($row = sqlsrv_fetch_array($EmployBankInfo, SQLSRV_FETCH_ASSOC)) {
          $bankInfo[] = $row;
      }
    
    //getting earnings info
      $preiodRange = $payslipObject->getPreiodRange($EmployeementNumber,$_SESSION['fromMonth'],$_SESSION['toMonth']);

    //getting earnings info
      $employEarnings = $payslipObject->getEarnings($EmployeementNumber,$_SESSION['fromMonth'],$_SESSION['toMonth']);
      $earningsCount = 0;
      $earnings = [];
      while ($row = sqlsrv_fetch_array($employEarnings, SQLSRV_FETCH_ASSOC)) {
          $earnings[] = $row;
      }

    //getting deductions info
    $employDeductions = $payslipObject->getDeductions($EmployeementNumber,$_SESSION['fromMonth'],$_SESSION['toMonth']);
    $deductions = [];
    $deductionsCount = 0;
    while ($row = sqlsrv_fetch_array($employDeductions, SQLSRV_FETCH_ASSOC)) {
        $deductions[] = $row;
    }
    
    //getting employer contributions info
    $employcontributions = $payslipObject->getEmployerContributions($EmployeementNumber,$_SESSION['fromMonth'],$_SESSION['toMonth']);
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


  <nav class="navbar noprint screen-only">
    <div class="nav-left">
      <img src='\assets\img\admarc logo.png' alt='admarc-logo' class='nav-logo'>
      <div class="logo">Admarc portal</div>
    </div>
    <div class="nav-center">
      
      <?php
        if($_SESSION["accessLevel"] == 'ADMIN' )
            echo '<button class="btn logout" onclick="openchart()">
                <i class="fas fa-chart-line"></i>
                View Stats
              </button>';
      ?>

      <form method="post" id="period_form" class="form-container screen-only">
        
        <?php
        $action = 'Allow';
        if($payslipObject->culPerPost == $payslipObject->aprovedPerPost)
          $action = 'Restrict';
          
        $first ='<label class="label" for="period_checkbox">'.$action.' view of </label><div class="date-field">';
        if($_SESSION["accessLevel"] == 'ADMIN' )
          echo $first.'<div class="center-input btn "><label for="period_checkbox" > period '.$payslipObject->culPerPost.'</label><input  type="checkbox" id="period_checkbox"  name="period_checkbox" ></div></div>';
        ?>
        
      </form>

        
      <form id="date-form" method="post"  class="date-range" onsubmit="handleSubmit(event)">

        <!-- FROM -->
        <label class="label">From</label><div class="date-field ">
          
          <div class="date-box" onclick="document.getElementById('fromInput').showPicker()">
            <svg class="calendar-icon" viewBox="0 0 24 24">
              <path d="M7 2v2H5a2 2 0 0 0-2 2v2h18V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm13 6H4v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM6 12h5v5H6v-5z"/>
            </svg>
            <span class="date-text" id="fromText"><?php echo periodToDisplayDateMonth($_SESSION['fromMonth']); ?></span>
          </div>
          <input class='rangeMonth' type="month" id="fromInput" name='fromMonth' onchange="updateLabel(this, 'fromText'); document.getElementById('date-form').submit();">
        </div>

        <!-- TO -->
        
          <label class="label">To</label><div class="date-field">
          <div class="date-box" onclick="document.getElementById('toInput').showPicker()">
            <svg class="calendar-icon" viewBox="0 0 24 24">
              <path d="M7 2v2H5a2 2 0 0 0-2 2v2h18V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7zm13 6H4v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM6 12h5v5H6v-5z"/>
            </svg>
            <span class="date-text" id="toText"><?php echo periodToDisplayDateMonth($_SESSION['toMonth']); ?></span>
          </div>
          <input class='rangeMonth' type="month" id="toInput" name='toMonth' onchange="updateLabel(this, 'toText'); document.getElementById('date-form').submit();">
        </div>
      </form>

    </div>
    



    <div class="nav-right">

    
        <?php
        if($_SESSION["accessLevel"] == 'ADMIN' || $_SESSION["accessLevel"] == "HR" || $_SESSION["accessLevel"] == "icttech")
          echo '<button class="btn logout" onclick="openprofile();">
          <i class="fas fa fa-user"></i>
          Profile
        </button>';
        ?>
    
      <form method="post" id="searchForm" class="form-container screen-only">
          <div class="date-field">
          <?php
          if($_SESSION["accessLevel"] == 'ADMIN' || $_SESSION["accessLevel"] == "HR" || $_SESSION["accessLevel"] == "icttech")
            echo '<div class="search-container">
          <i class="fas fa-search"></i>
          <input placeholder="Search" type="text" id="searchBox" name="empid" autocomplete="off">
          </div>';
          ?>
          </div>
        </form>

        <a  href="index" >
          <button class="btn logout">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
        </a>
        <a  href="reports" >
          <button class="btn home">
            <i class="fas fa-home"></i>
          </button>
        </a>
        <button class="btn back" onclick="goBack()">
          <i class="fas fa-arrow-left"></i>
        </button>
    </div>
  </nav>
  <div class="space-div screen-only">
    <div id="suggestions" class="suggestions-box noprint screen-only"></div>
  </div>
   <div id="search-message-result" class="search-message-result noprint screen-only">
      <?php echo $empidInputValue; ?>
    </div>
  
    <div id="chartModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closechart()">
                <i class="fas fa-xmark" ></i>
            </span>

            <div class="modal-body">
                <!-- Navigation Controls -->
                <div class="chart-nav">
                    <button id="prevChart">⟨ Prev</button>
                    <span id="chartTitle">Usage Chart</span>
                    <button id="nextChart">Next ⟩</button>
                </div>

                <!-- Chart 1: Usage Chart -->
                <div class="chart-slide active" id="usageChartContainer">
                    <div class="controls">
                    <label for="chartMonthPicker">Select Month: </label>
                    <input type="month" id="chartMonthPicker" value="2025-09">
                    </div>
                    <canvas id="usageChart"></canvas>
                </div>

                <!-- Chart 2: Monthly Chart -->
                <div class="chart-slide" id="monthlyChartContainer">
                    <div class="controls">
                    <label for="yearPicker">Select Year: </label>
                    <input type="number" id="yearPicker" value="2025" min="2024" max="2100">
                    </div>
                    <canvas id="monthlyChart"></canvas>
                </div>
                <!-- Chart 3: Monthly Chart -->
                <div class="chart-slide" id="groupedMonthlyChartContainer">
                    
                    <canvas id="groupedMonthlyChart"></canvas>
                    
                </div>

                <!-- Chart Description -->
                <div class="chart-description">
                    <p id="chartDescription">
                    This chart shows the top users for a selected month.
                    </p>
                </div>
            </div>

        </div>
    </div>

  
<div id="profileModal" class="profile-modal">
  <div class="profile-modal-content">

    <span class="close-btn" onclick="closeprofile()">
        <i class="fas fa-xmark" ></i>
    </span>

    <!-- Header -->
    <div class="header">
      <div class="avatar-wrapper">
        <?php 
          list($lastName, $firstName) = explode(' ',trim($_SESSION['profile']['name']), 2);
          echo "<img src='https://ui-avatars.com/api/?name=".$firstName."+".$lastName."&background=4a90e2&color=fff&rounded=true' class='avatar' alt='User' />";
        ?>
        <i class="fa fa-pencil avatar-pencil"></i>
      </div>
      <div class="user-info">
        <div class="role"><?php echo trim($employee['JobTitle']) ?></div>
        <div class="market"><?php echo trim($employee['Location']) ?></div>
        <div class="last-login">last login <?php echo $_SESSION['profile']['LstLgin'] ?></div>
      </div>
    </div>

    <!-- Name with edit -->

    <div>
      <span id="user-name"><?php echo $_SESSION['profile']['name'] ?></span>
      <i class="fa fa-pencil name-pencil"></i>

      <div class="name-edit" style="display:none;">
        <input type="text" id="name-input" class="name-input" oninput="this.value = this.value.toUpperCase();">
        <button id="name-ok" class="name-ok">OK</button>
      </div>
    </div>


    <!-- Other rows -->
    <div class="row"><span><?php echo $_SESSION['profile']['empid'] ?></span></div>
    <div class="bottom-profile-modal">
      <!-- Access Code -->
          <div class="row">
            <label>Access code</label>
            <span id='output'>• • • • • • • •</span><br>
              <?php echo ($_SESSION['accessLevel']=="ADMIN")? '<small class="toggle-btn" id="gen">Generate new password</small>': ''?>
            
          </div>

          <div class="row">
            <label>Status</label>
            
              <?php
               if($_SESSION["searchSourceID"] == 0){
                echo '<div class="switch">Active</div>';
               }else{
                echo '<div class="switch" style="background:#f70303">Inctive</div>';
               }
               ?>
            
          </div>

        <!-- Access Level -->
        <div class="row">
        <label>Access level</label>
        <div>
            <span name ="access-level" ><?php echo $_SESSION['profile']['accessLevel'] ?></span><br>
            <small class="toggle-btn" data-target="access-level">Change Access level</small>
            <select id="access-level" class="dropdown">
              <option>choose access level</option>
              <option>ADMIN</option>
              <option>icttech</option>
              <option>finance</option>
              <option>logistics</option>
              <option>HR</option>
              <option>User</option>
            </select>
        </div>
        </div>

        <!-- Division -->
        <div class="row">
        <label>Division</label>
        <div>
            <span name="division"><?php echo Payslip::divisions[$_SESSION['profile']['division']] ?></span><br>
            <small class="toggle-btn" data-target="division">Change Division</small>
            <select id="division" class="dropdown">
              <option value="201" >choose division</option>
              <option value="201" >NGABU</option>
              <option value="202" >BLANTYRE</option>
              <option value="203">Luchenza</option>
              <option value="205">Liwonde</option>
              <option value="207">Balaka</option>
              <option value="308">Lilongwe</option>
              <option value="310">Mponera</option>
              <option value="311">Kasungu</option>
              <option value="312">SALIMA</option>
              <option value="413">Mzuzu</option>
              <option value="415">Karonga</option>
            </select>
        </div>
        </div>
    </div>

  </div>
</div>

  <?php 
  $totalEarnings = 0;
  $qrcodeId = 0;
  while ($preiodRangeRow = sqlsrv_fetch_array($preiodRange, SQLSRV_FETCH_ASSOC)) {
  ?>

    <div class="page">

      
      <div class='payslipHeader'>
        <div class="logo-div">
          <img src='\assets\img\admarc logo.png' alt='admarc-logo' class='print-logo'>
        </div>
        
        <div id="qr-container" class="qrcode-div print-logo"  style=" width: 120px; height: 120px;">
            <div
              id="qrcode<?php echo $qrcodeId; $qrcodeId +=1;?>" 
              data-hidden-value='<?php $codeString = $_SESSION['profile']["empid"].",".$preiodRangeRow['PerEnt'].",".$_SESSION["empid"].",".date("y-m-d H:i");
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

  <script src="assets\js\chartmodal.js"></script>
  <script src="assets\js\profilemodal.js"></script>
  <script src="assets\js\searchv1.js"></script>
  <script src="assets\js\passGen.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

  <script type="text/javascript">
    window.onbeforeprint = () => {
      document.title = "<?php 
        if ($_SESSION['fromMonth'] == $_SESSION['toMonth']) {
          echo $print_name_title." ".periodToDisplayDateMonth($_SESSION['fromMonth'])." payllip"; 
        }else
          echo $print_name_title." ".periodToDisplayDateMonth($_SESSION['fromMonth'])." to ".periodToDisplayDateMonth($_SESSION['toMonth'])." payllip"; 
        ?>"; 
    };
    window.onafterprint = () => {
      document.title = "Admarc payslip portal";
    };

    function updateLabel(input, labelId) {
      const date = new Date(input.value);
      const month = date.toLocaleString('default', { month: 'short' });
      const year = date.getFullYear();
      document.getElementById(labelId).textContent = month +' ' + year;
    }

    function handleSubmit(event) {
      event.preventDefault();
      const from = document.getElementById('fromInput').value;
      const to = document.getElementById('toInput').value;
      // You can replace alert with actual form submission or fetch logic
    }

    function openDatepicker() {
        console.log('openDatepicker clicked');
        document.getElementById('id01').style.visibility = 'visible';
    }

    
    function goBack() {
      window.history.back();
    }

      function openchart() {
        document.getElementById("chartModal").style.display = "block";
        
        fetch("payslip.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=modal_click"
        })
        .then(response => response.text())
        .then(data => console.log("Log response:", data))
        .catch(error => console.error("Error logging click:", error));

      }

      function closechart() {
        document.getElementById("chartModal").style.display = "none";
      }

      
    
    document.addEventListener('change', function(event) {
      if (event.target.tagName === 'INPUT' && event.target.type === 'checkbox') {
          document.getElementById('period_form').submit();
      }
  });
  
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