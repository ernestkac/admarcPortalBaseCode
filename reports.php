<?php

session_start();
if(!isset($_SESSION["group"])){
    header("Location: index");
}

require_once 'classes\EarningsAndDeductions.php';
require_once 'classes\TaxData.php';
require_once 'classes\fixedEarnings.php';
require_once 'classes\latestFixedEarnings.php';
require_once 'classes\EmployeeDetails.php';
require_once 'classes\BankSchedule.php';
require_once 'classes\Sales.php';
require_once 'classes\logistics.php';
require_once 'classes\purchases.php';
require_once 'classes\EventLog.php';

  if(isset($_POST['period_input'])){

    $payrolls = explode(",", $_POST['payroll_dropdown']);
            // var_dump($payrolls);
    foreach ($payrolls as $payroll){
        
        $eventLogger = new EventLog($payroll);

        if($_POST['period_input'] != ""){


            if($_POST['report_type_dropdown'] == "earnings_deductions"){
                $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
                 $_SESSION["accessLevel"], "earning and deductuion rprt extract period :".$_POST['period_input']);
                $earningsDeductionsObj = new EarningsAndDeductions($payroll);
                $earningsDeductionsObj->getEarningsAndDeductionsReport($_POST['period_input']); 
                
            } 
            else if($_POST['report_type_dropdown'] == "tax_data"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "tax data rprt extract period :".$_POST['period_input']);
            $earningsDeductionsObj = new TaxData($payroll,$_POST['period_input']);
            } 
            else if($_POST['report_type_dropdown'] == "fixed_earnings"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "fixed earnings rprt extract period :".$_POST['period_input']);
            $earningsDeductionsObj = new fixedEarnings($payroll,$_POST['period_input']);
            }
            else if($_POST['report_type_dropdown'] == "bank_schedule"){ 
                $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
                 $_SESSION["accessLevel"], "bank schedule rprt extract period :".$_POST['period_input']);  
                $earningsDeductionsObj = new BankSchedule($payroll,$_POST['period_input']);
                
            } 
            

        }else if($_POST['report_type_dropdown'] == "latest_fixed_earnings"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "latest fixed earnings rprt extract");
            
            $earningsDeductionsObj = new latestFixedEarnings($payroll,$_POST['empid_input']);
            
        } 
        else if($_POST['report_type_dropdown'] == "Sales_Report"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "sales rprt extract");
            
            $earningsDeductionsObj = new SalesReport($payroll,$_POST['fiscalyr_input']);
            
        } 
        else if($_POST['report_type_dropdown'] == "Purchases_Report"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "purchases rprt extract");
            
            $earningsDeductionsObj = new PurchasesReport($payroll,$_POST['fiscalyr_input']);
            
        }
        else if($_POST['report_type_dropdown'] == "employee_detail"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "active employee details rprt extract");
            
            $employDetailsObj = new Employee($payroll);
            $employDetailsObj->getEmployeeDetails();
            
        } 
        else if($_POST['report_type_dropdown'] == "5001" || $_POST['report_type_dropdown'] == "5002"){
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
             $_SESSION["accessLevel"], "staff transer repartr rprt extract");
            
            $LogisticsObj = new Logistics();
            $LogisticsObj->getRepatriationReport($_POST['report_type_dropdown']);
            
        } 
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
        <link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="assets/css/aquamarine.css" type="text/css">
        <!-- Script: Make my navbar transparent when the document is scrolled to top -->
        <script src="assets/js/navbar-ontop.js"></script>
        <!-- Script: Animated entrance -->
        <script src="assets/js/animate-in.js"></script>
    </head>

    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
            <div class="container">
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar3SupportedContent" aria-controls="navbar3SupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
                <div class="collapse navbar-collapse text-center justify-content-center" id="navbar3SupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item mx-3">
                            <a class="btn navbar-btn btn-secondary" href="#reports"><b>Reports</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link text-light" href="payslipz"><b>Payslips</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link text-light" href="processes"><b>Processes</b></a>
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
                    <div class="col-lg-5 p-3">
                        <form class="p-4 bg-light" method="post" action="" onsubmit="return validateForm()">
                            <div class="form-group">
                                <label for="payroll_dropdown">Choose Payroll:</label>
                                <select id="payroll_dropdown" name="payroll_dropdown" class="form-control">
                                <option value="0">Solomon</option>
                                <option value="1" selected>Executive Payroll</option>
                                <option value="2">Senior Payroll</option>
                                <option value="3">Junior HQ</option>
                                <option value="4">SOUTH</option>
                                <option value="5">CENTRE</option>
                                <option value="6">NORTH</option>
                                <!-- <option value="1,2,3,4,5,6">ALL PAYROLLS</option> -->
                            </select>
                            </div>
                            <div class="form-group">
                                <label for="report_type_dropdown">Choose Report:</label>
                                <select id="report_type_dropdown" name="report_type_dropdown" class="form-control">
                                    <?php
                                        if($_SESSION["accessLevel"] == 'finance' || $_SESSION["accessLevel"] == 'ADMIN'){
                                            echo '<option value="earnings_deductions">EARNINGS AND DEDUCTIONS</option>';
                                            echo '<option value="bank_schedule">BANK SCHEDULE</option>';
                                            echo '<option value="tax_data">TAX RETURNS</option>';
                                            echo '<option value="fixed_earnings">FIXED EARNINGS AS PER PERIOD</option>';
                                            echo '<option value="latest_fixed_earnings">LATEST FIXED EARNINGS</option>';
                                            echo '<option value="employee_detail">ACTIVE EMPLOYEE DETAILS</option>';
                                            echo '<option value="Sales_Report">SALES REPORT</option>';
                                            echo '<option value="Purchases_Report">PURCHASES REPORT</option>';
                                        }
                                        if($_SESSION["accessLevel"] == 'logistics' || $_SESSION["accessLevel"] == 'ADMIN'){
                                            echo '<option value="5002">REPATRIATION</option>';
                                            echo '<option value="5001">STAFF TRANSFER</option>';
                                        }else{
                                            echo '<option value="defaults">No process available</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div id="fiscalyr_input_div" class="form-group">
                                <label for="fiscalyr_input">Enter FiscalYr:</label>
                                <input type="number" placeholder="E.g. 2024" id="fiscalyr_input" name="fiscalyr_input" min="2014" max="2030" class="form-control">
                            </div>
                            <div id="period_input_div" class="form-group">
                                <label for="period_input">Enter a Period:</label>
                                <input type="number" placeholder="E.g. 202404" id="period_input" name="period_input" min="201401" max="203012" class="form-control">
                            </div>
                            <div id="empid_checkbox_div" class="form-group">
                                <input type="checkbox" id="empid_checkbox" name="empid_checkbox" >
                                <label for="empid_checkbox">Specify Employee(s)</label>
                            </div>
                            <div id="empid_input_div" class="form-group">
                                <label for="empid_input">Enter Employeement number(s) each on its own line:</label>
                                <textarea placeholder="E.g. 482217" id="empid_input" name="empid_input" class="form-control"></textarea>
                            </div>
                            <input type="submit" value="Download Report" class="btn btn-primary">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/myjs.js"></script>
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