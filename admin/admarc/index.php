<?php
require_once 'classes\EarningsAndDeductions.php';
require_once 'classes\TaxData.php';
require_once 'classes\fixedEarnings.php';
require_once 'classes\latestFixedEarnings.php';
require_once 'classes\EmployeeDetails.php';
  if(isset($_POST['period_input'])){

    if($_POST['period_input'] != ""){
        if($_POST['report_type_dropdown'] == "earnings_deductions"){


            $earningsDeductionsObj = new EarningsAndDeductions($_POST['payroll_dropdown'],$_POST['period_input']);
            
            
        } 
        else if($_POST['report_type_dropdown'] == "tax_data"){
           $earningsDeductionsObj = new TaxData($_POST['payroll_dropdown'],$_POST['period_input']);
        } 
        else if($_POST['report_type_dropdown'] == "fixed_earnings"){
           $earningsDeductionsObj = new fixedEarnings($_POST['payroll_dropdown'],$_POST['period_input']);
        } 
        

    }else if($_POST['report_type_dropdown'] == "latest_fixed_earnings"){
        
        $earningsDeductionsObj = new latestFixedEarnings($_POST['payroll_dropdown'],$_POST['empid_input']);
        
    } 
    else if($_POST['report_type_dropdown'] == "employee_detail"){
        
        $earningsDeductionsObj = new EmployeeDetail($_POST['payroll_dropdown'],$_POST['empid_input']);
        
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
                            <a class="nav-link text-light" href="#"><b>Simple as</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="#menu"><b>ONE</b></a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="#venue"><b>TWO</b></a>
                        </li>
                    </ul>
                    <a class="btn navbar-btn btn-secondary mx-2" href="#book"><b>THREE</b></a>
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
                                <option value="AdmarcSLAppPayRolHQ">Executive Payroll</option>
                                <option value="AdmarcSLAppPayRolSN">Senior Payroll</option>
                                <option value="AdmarcSLAppPayRollHW">Junior HQ</option>
                                <option value="AdmarcSLAppPayRollSS">SOUTH</option>
                                <option value="ADMARCSLAPPPAYROLLC">CENTRE</option>
                                <option value="AdmarcSLAppPayRollN">NORTH</option>
                            </select>
                            </div>
                            <div class="form-group">
                                <label for="report_type_dropdown">Choose Report:</label>
                                <select id="report_type_dropdown" name="report_type_dropdown" class="form-control">
                                <option value="earnings_deductions">EARNINGS AND DEDUCTIONS</option>
                                <option value="tax_data">TAX RETURNS</option>
                                <option value="fixed_earnings">FIXED EARNINGS AS PER PERIOD</option>
                                <option value="latest_fixed_earnings">LATEST FIXED EARNINGS</option>
                                <option value="employee_detail">ACTIVE EMPLOYEE DETAILS</option>
                            </select>
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
        <pingendo onclick="window.open('localhost/', '_blank')" style="cursor:pointer;position: fixed;bottom: 20px;right:20px;padding:4px;background-color: #00b0eb;border-radius: 8px; width:220px;display:flex;flex-direction:row;align-items:center;justify-content:center;font-size:14px;color:white">Made By Ernest Kac&nbsp;&nbsp;<img src="https://pingendo.com/site-assets/Pingendo_logo_big.png" class="d-block" alt="Pingendo logo" height="16"></pingendo>
    </body>

    </html>