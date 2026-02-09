<?php

session_start();
if(!isset($_SESSION["accessLevel"]) || $_SESSION["accessLevel"] === 'user'){
    header("Location: index");
}

require_once 'classes\processes\BatchNumber.php';
require_once 'classes\processes\payeCalc.php';
require_once 'classes\processes\tevetCalc.php';
require_once 'classes\processes\AddCustomerFromEmployee.php';
require_once 'classes\processes\InsertMarketSubAcct.php';
require_once 'classes\addEarningOrDeduction.php';
require 'classes/payslip.php';
require_once 'classes\EventLog.php';
$message = '';
$resultMessage = '';
// Handle AJAX request for unreleased CA batches
if (isset($_GET['action'])){
     if($_GET['action'] === 'get_unreleased_ca_batches' && ($_SESSION["accessLevel"] === 'ADMIN' || $_SESSION["accessLevel"] === 'icttech')) {
        header('Content-Type: application/json');
        $batchNumberObj = new BatchNumber();
        $division = isset($_GET['division']) ? $_GET['division'] : $_SESSION["division"];
        $result = $batchNumberObj->unreleasedCABatches($division);
        
        $batches = [];
        if ($result) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $batches[] = [
                    'batnbr' => $row['batnbr'],
                    'Acct' => $row['Acct'],
                    'bankacct' => $row['bankacct'],
                    'sub' => $row['sub'],
                    'Crtd_User' => $row['Crtd_User'],
                    'Crtd_DateTime' => $row['Crtd_DateTime'] instanceof DateTime ? $row['Crtd_DateTime']->format('Y-m-d H:i:s') : $row['Crtd_DateTime'],
                    'TranAmt' => $row['TranAmt'],
                    'DrCr' => $row['DrCr'],
                    'Perent' => $row['Perent'],
                    'RefNbr' => $row['RefNbr'],
                    'TranDesc' => $row['TranDesc']
                ];
            }
        }
        
        echo json_encode($batches);
        exit;
    }elseif($_GET['action'] === 'get_deleted_ca_batches' && ($_SESSION["accessLevel"] === 'ADMIN' || $_SESSION["accessLevel"] === 'icttech')) {

        header('Content-Type: application/json');
        $batchNumberObj = new BatchNumber();
        $division = isset($_GET['division']) ? $_GET['division'] : $_SESSION["division"];
        $result = $batchNumberObj->getDeletedCABatches($division);
        
        $batches = [];
        if ($result) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $batches[] = [
                    'batnbr' => $row['batnbr'],
                    'Acct' => $row['Acct'],
                    'bankacct' => $row['bankacct'],
                    'sub' => $row['sub'],
                    'Crtd_User' => $row['Crtd_User'],
                    'Crtd_DateTime' => $row['Crtd_DateTime'] instanceof DateTime ? $row['Crtd_DateTime']->format('Y-m-d H:i:s') : $row['Crtd_DateTime'],
                    'TranAmt' => $row['TranAmt'],
                    'DrCr' => $row['DrCr'],
                    'Perent' => $row['Perent'],
                    'RefNbr' => $row['RefNbr'],
                    'TranDesc' => $row['TranDesc'],
                    'status' => $row['status']
                ];
            }
        }
        
        echo json_encode($batches);
        exit;
    }

}


  if(isset($_POST['report_type_dropdown'])){

    $eventLogger = new EventLog();

    if($_POST['period_input'] != "" || $_POST['report_type_dropdown'] == "release_ca_batch"){

        if($_POST['report_type_dropdown'] == "change_batch_period"){
        
            $batchNumberObj = new BatchNumber;
            if($batchNumberObj->changePeriod($_POST['batnbr_input'],$_POST['period_input'])){
                $message = 'batch period changed!';
                
                $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
                $_SESSION["accessLevel"], "batch period change ");
            }
                
            else $message = 'batch period not changed!'. implode(" * ",$batchNumberObj->error);
            
            //var_dump($batchNumberObj->error);
        }else if($_POST['report_type_dropdown'] == "release_ca_batch"){
            
            $startTime = microtime(true);
            $batchNumberObj = new BatchNumber();
            $input = $_POST['batnbr_input'];
            $input = preg_replace("/[()'\s]/", '', $input);
            $batches = array_filter(array_map('trim', explode(",", $input)));
            $failedBatches = [];
            
            foreach($batches as $batch) {
                if(!$batchNumberObj->releaseCABatch($batch)) {
                    $failedBatches[] = $batch;
                }
            }
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            if(empty($failedBatches)) {
                $message = 'all batches released successfully! (Execution time: ' . $executionTime . ' seconds)';
                $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
                $_SESSION["accessLevel"], "Release CA Batch: " . implode(", ", $batches) . " (Time: {$executionTime}s)");
            }
            else {
                $message = 'error releasing batches: ' . implode(", ", $failedBatches) . '. ' . implode(" * ", $batchNumberObj->error) . ' (Execution time: ' . $executionTime . ' seconds)';
            }
        }else if($_POST['report_type_dropdown'] == "send_payslip_text"){
            //var_dump($_POST);
            $payslipObject = new Payslip($_POST['payroll_dropdown']);
            
            $message = $payslipObject->sendPayslipSms($_POST['empid_input'], $_POST['period_input']);

            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "sending payslip text messages");

        }
        

    }
    elseif($_POST['report_type_dropdown'] == "restore_ca_batch" && $_POST['batnbr_input'] != ""){
        $batchNumberObj = new BatchNumber();
        $input = $_POST['batnbr_input'];
        $input = preg_replace("/[()'\s]/", '', $input);
        $batches = array_filter(array_map('trim', explode(",", $input)));
        $failedBatches = [];
        
        foreach($batches as $batch) {
            if(!$batchNumberObj->restoreCABatch($batch)) {
                $failedBatches[] = $batch;
            }
        }
        
        if(empty($failedBatches)) {
            $message = 'all batches restored successfully!';
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "Restoring CA Batch: " . implode(", ", $batches) );
        }
        else {
            $message = 'error restoring batches: ' . implode(", ", $failedBatches) . '. ' . implode(" * ", $batchNumberObj->error);
        }
        echo json_encode($message);
        exit;
    }
    else if($_POST['report_type_dropdown'] == "void_ca_batch" && $_POST['batnbr_input'] != ""){
        $batchNumberObj = new BatchNumber();
        $input = $_POST['batnbr_input'];
        $input = preg_replace("/[()'\s]/", '', $input);
        $batches = array_filter(array_map('trim', explode(",", $input)));
        $failedBatches = [];

        foreach($batches as $batch) {
            if(!$batchNumberObj->setBatchStatusToVoid($batch)) {
                $failedBatches[] = $batch;
            }
        }

        if(empty($failedBatches)) {
            $message = 'all batches voided successfully!';
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "Voiding CA Batch: " . implode(", ", $batches) );
        }
        else {
            $message = 'error voiding batches: ' . implode(", ", $failedBatches) . '. ' . implode(" * ", $batchNumberObj->error);
        }
        echo json_encode($message);
        exit;
    }
    else if($_POST['report_type_dropdown'] == "add_earning_deduction" && $_POST['payroll_code']  !="" && $_POST['earning_deduction_input']  !=""){
        $Employees = $_POST['earning_deduction_input'];
        $EarnDed = "";
        if(isset($_POST['EarnDed_checkbox'])){
            $EarnDed = $_POST['EarnDed_checkbox'];
        }
        //var_dump($_POST);
       if(new AddEarningOrDeduction($_POST['payroll_dropdown'],$Employees,$_POST['payroll_code'],$EarnDed))
            $message = "Adding Earning or deduction was successful";
        else
            $message = "error Adding Earning or deduction";

        
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "adding earning or deduction code :".$_POST['payroll_code'] );
    } 
    else if($_POST['report_type_dropdown'] == "recalculate_tevet_levy" && $_POST['payroll_dropdown']  !=""){
       
        $tevetCaluObj = new tevetCalculation($_POST['payroll_dropdown']);
        
        $resultMessage = $tevetCaluObj->checkingTevet();
        $message = $resultMessage;
        
        
        $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
        $_SESSION["accessLevel"], "Recalculating tevet levy :".$_POST['payroll_dropdown'] );
    } 
    else if($_POST['report_type_dropdown'] == "recalculate_paye" && $_POST['payroll_dropdown']  !=""){
       
        $payeCaluObj = new payeCalculation($_POST['payroll_dropdown']);
        
        $resultMessage = $payeCaluObj->checkingPaye();
        $message = $resultMessage;
        
        
        $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
        $_SESSION["accessLevel"], "Recalculating PAYE :".$_POST['payroll_dropdown'] );
    } 
    else if($_POST['report_type_dropdown'] == "add_missing_employee_in_customer"){
        $addEmpToCustObj = new AddCustomerFromEmployee();
        
        if($addEmpToCustObj->addCustomerFromEmployee($_POST['payroll_dropdown'])){
            $message = 'missing employees added!';
            
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "adding missing employee in customer");
        }
        else if(empty($addEmpToCustObj->error))
            $message = 'No missing employee to add!';
        else $message = 'error!'. implode(" * ",$addEmpToCustObj->error);
        
        
    } 
    else if($_POST['report_type_dropdown'] == "insert_market_subAcct"
            && $_POST['market_code'] != "" && $_POST['market_name'] != ""){
        $addSubacct = new InsertMarketSubAcct();
        //var_dump($_POST);
        
        if($addSubacct->AddMarket($_POST['market_code'],$_POST['market_name'],$_POST['Division_dropdown'])){
            $message = 'Market(s) added successfully!';
            
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "adding new markets");
        }
        else if(empty($addSubacct->error))
            $message = 'Nothing happened!';
        else $message = 'error! :'. implode(" * ",$addSubacct->error);
   
    } 
  }else{

    //echo "failed ";
  }
?>

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- PAGE settings -->
        <link rel="icon" href="assets/img/admarc-png-logo.png">
        <title>ADMARC QUICK REPORT</title>
        <meta name="description" content="Admarc Quicky self generate reports">
        <meta name="keywords" content="Admarc Quicky self generate reports">
        <!-- CSS dependencies -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
            integrity="sha512-..."
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        /> 
        <link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="assets/css/aquamarine.css" type="text/css">
        <!-- Script: Make my navbar transparent when the document is scrolled to top -->
        <script src="assets/js/navbar-ontop.js"></script>
        <!-- Script: Animated entrance -->
        <script src="assets/js/animate-in.js"></script>
        <style>
        .uppercase-input{
          text-transform: uppercase; 
        }
        </style>
    </head>

    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
            <div class="container">
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar3SupportedContent" aria-controls="navbar3SupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
                <div class="collapse navbar-collapse text-center justify-content-center" id="navbar3SupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item mx-3">
                            <a class="nav-link text-light" href="reports"><b>Reports</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link text-light" href="payslipz"><b>Payslips</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="btn navbar-btn btn-secondary" href="processes"><b>Processes</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link text-light" href="payslip"><b>Payslip</b></a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Cover -->
        <div class="align-items-center d-flex cover section-aquamarine py-5" style="	background-image: url(assets/img/admarc-png-logo.png);	background-position: top left;	background-size: 100%;	background-repeat: repeat;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 align-self-center text-lg-left text-center">
                        <h1 class="mb-0 mt-5 display-4">ADMARC LTD</h1>
                        <p class="mb-5" contenteditable="true">Quick Reports</p>
                    </div>
                    <div id="process_form_div" class="col-lg-5 p-3">
                        <form class="p-4 bg-light" target="" method="post" action="" onsubmit="return validateForm()">
                            <div id="payroll_dropdown_div" class="form-group">
                                <label for="payroll_dropdown">Choose Payroll:</label>
                                <select id="payroll_dropdown" name="payroll_dropdown" class="form-control">
                                <option value="0" selected>Solomon</option>
                                <option value="1" >Executive Payroll</option>
                                <option value="2">Senior Payroll</option>
                                <option value="3">Junior HQ</option>
                                <option value="4">SOUTH</option>
                                <option value="5">CENTRE</option>
                                <option value="6">NORTH</option>
                                <option value="1,2,3,4,5,6">ALL PAYROLLS</option> 
                            </select>
                            </div>
                            <div class="form-group">
                                <label for="report_type_dropdown">Choose Process:</label>
                                <select id="report_type_dropdown" name="report_type_dropdown" class="form-control">
                                <?php
                                    if($_SESSION["accessLevel"] == 'finance' || $_SESSION["accessLevel"] == 'ADMIN'){
                                        echo '<option value="change_batch_period">Change Batch Period</option>';
                                    }
                                    if($_SESSION["accessLevel"] == 'finance' || $_SESSION["accessLevel"] == 'ADMIN'|| $_SESSION["accessLevel"] == 'icttech'){
                                        echo '<option value="release_ca_batch">Release CA Batch</option>';
                                    }
                                    if($_SESSION["accessLevel"] == 'ADMIN'){
                                        echo '<option value="add_earning_deduction">Add Earning Deduction</option>';
                                        echo '<option value="recalculate_paye">Recalculate P.A.Y.E</option>';
                                        echo '<option value="recalculate_tevet_levy">Recalculate Tevet Levy</option>';
                                        echo '<option value="send_payslip_text">Send Payslip Text</option>';
                                        echo '<option value="add_missing_employee_in_customer">Add Employees In Customer</option>';
                                        echo '<option value="insert_market_subAcct">Add Market</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div id="Division_dropdown_div" class="form-group">
                                <label for="Division_dropdown">Select Division:</label>
                                <?php
                                    $divisions = [
                                        '201' => 'NGABU',
                                        '202' => 'BLANTYRE',
                                        '203' => 'Luchenza',
                                        '205' => 'Liwonde',
                                        '207' => 'Balaka',
                                        '308' => 'Lilongwe',
                                        '310' => 'Mponera',
                                        '311' => 'Kasungu',
                                        '312' => 'SALIMA',
                                        '413' => 'Mzuzu',
                                        '415' => 'Karonga'
                                    ];

                                    // Only ADMIN can interact with the division dropdown
                                    $disabled = (isset($_SESSION['accessLevel']) && $_SESSION['accessLevel'] === 'ADMIN') ? '' : 'disabled';
                                    $currentDiv = isset($_SESSION['division']) ? (string)$_SESSION['division'] : '202';
                                ?>
                                <select id="Division_dropdown" name="Division_dropdown" class="form-control" <?php echo $disabled; ?>>
                                    <?php if (isset($_SESSION['accessLevel']) && $_SESSION['accessLevel'] === 'ADMIN'): ?>
                                        <?php foreach($divisions as $val => $label): ?>
                                            <option value="<?php echo $val; ?>" <?php echo ($val === $currentDiv) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php // non-admins only see their current division as a single option ?>
                                        <option value="<?php echo $currentDiv; ?>" selected><?php echo isset($divisions[$currentDiv]) ? $divisions[$currentDiv] : $currentDiv; ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div id="market_code_div" class="form-group">
                                <label for="market_code">Enter Market Code:</label>
                                <input type="text" placeholder="E.g. A72" id="market_code"
                                name="market_code"  class="form-control" oninput= "this.value = this.value.toUpperCase()">
                            </div>
                            <div id="market_name_div" class="form-group">
                                <label for="market_name">Enter Market Name:</label>
                                <input type="text" placeholder="E.g. SHIRE VALLEY" id="market_name" name="market_name"
                                class="form-control"  oninput= "this.value = this.value.toUpperCase()">
                            </div>
                            <div id="period_input_div" class="form-group">
                                <label for="period_input">Enter a Period:</label>
                                <input type="number" placeholder="E.g. 202404" id="period_input" name="period_input" min="201401" max="203012" class="form-control">
                            </div>
                            <div id="batnbr_input_div" class="form-group">
                                <label for="batnbr_input">Enter Batch number(s) each on its own line:</label>
                                <textarea value="hie" placeholder="E.g. 003370" id="batnbr_input" name="batnbr_input" class="form-control"></textarea>
                            </div>
                            <div id="empid_checkbox_div" class="form-group">
                                <input type="checkbox" id="empid_checkbox" name="empid_checkbox" >
                                <label for="empid_checkbox">Specify Employee(s)</label>
                            </div>
                            <div id="payroll_code_div" class="form-group">
                                <label for="payroll_code">Enter Payroll Code:</label>
                                <input type="text" placeholder="E.g. BASIC2" id="payroll_code" name="payroll_code" min="201401" max="203012" class="form-control">
                            </div>
                            <div id="EarnDed_checkbox_div" class="form-group">
                                <input type="checkbox" id="EarnDed_checkbox" name="EarnDed_checkbox" >
                                <label for="EarnDed_checkbox">Is a Deduction</label>
                            </div>
                            <div id="earning_deduction_input_div" class="form-group">
                                <label for="earning_deduction_input">Enter Employee earning(s) each on its own line:</label>
                                <textarea placeholder="E.g. 482217 6587.52 6587.52 202409 1" id="earning_deduction_input" name="earning_deduction_input" class="form-control"></textarea>
                            </div>
                            <div id="empid_input_div" class="form-group">
                                <label for="empid_input">Enter Employeement number(s) each on its own line:</label>
                                <textarea placeholder="E.g. 764115" id="empid_input" name="empid_input" class="form-control"></textarea>
                            </div>
                            <p class="text-warning"> <?php echo $message; ?></p>
                            <input id="submit_btn" type="submit" value="Change priod" class="btn btn-primary">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var userAccessLevel = '<?php echo $_SESSION["accessLevel"]; ?>';
            console.log("User Access Level: " + userAccessLevel);
        </script>
        <script src="assets/js/processes.js"></script>
        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <script src="assets/js/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="assets/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!-- Script: Smooth scrolling between anchors in the same page -->
        <script src="assets/js/smooth-scroll.js"></script>

        <a  href="index">
            <pingendo class="noprint"
                style="cursor:pointer;position: fixed;bottom: 
                20px;right:20px;padding:4px;background-color: 
                #DC4C64;border-radius: 8px; width:220px;display:flex;
                flex-direction:row;align-items:center;justify-content:center;
                font-size:14px;color:white">SIGN OUT&nbsp;&nbsp;
            </pingendo>
        </a>
    </body>

    </html>