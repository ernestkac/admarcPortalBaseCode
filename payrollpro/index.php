<?php
   require_once '..\classes\payrollEditScreen.php';

   if(!isset($_POST['submit'])){
    $PyrolEdtScrn = new PayrollEditScreen();
    $PyrolEdtScrn->getEmployeeDetails();
   }
   
   if(isset($_POST['submit'])){
    //var_dump($_POST);
    $PyrolEdtScrn = new PayrollEditScreen($_POST['payroll_input']);
    if($_POST['submit'] == 'load_employee'){
        $PyrolEdtScrn->getEmployeeDetails($_POST['empid_input']);
    }
    else if($_POST['submit'] == 'payrollChange'){
        $PyrolEdtScrn->getEmployeeDetails();
    }
        
   }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Pro</title>
    <!-- Include Bootstrap CSS (v4.4.1) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>

<body>
    <!-- New container for input fields -->
    <div class="container-xl p-2">
        <div class="row d-flex justify-content-between">
            <div class="">
                <span class="navbar-text ml-auto"><h1>PAYROLL PRO</h1></span><span class="navbar-text ml-auto"><h4><?php echo $PyrolEdtScrn->payrollPriod ?></h4></span>
            </div>
            <div class="">
                <a class="navbar-brand m-0 p-0" href="#">
                    <img src="https://th.bing.com/th/id/OIP.knV_gxFrceqy7e5cBgk3UQAAAA?rs=1&pid=ImgDetMain" alt="Payroll Pro Logo" height="72">
                </a>
            </div>

        </div>
        <form id="employeeEditForm" method="post" action="">
            <div class="row d-flex justify-content-between">
                <div class="">
                    <input type="number" name="empid_input" style="font-weight: bold;" value="<?php echo $PyrolEdtScrn->empid;?>" class="form-control border border-dark " id="empid" placeholder="Emp ID">
                    
                <button id="hidden_submit_btn" style="width:0; height:0; border:0px;" class="p-0 m-0" type="submit" name="submit" value="load_employee"></button>
                  
                </div>
                <div class="">
                    <select name="payroll_input" class="custom-select border border-dark" id="payroll_select_input">
                        <option >Payroll</option>
                        <option value = "1" <?php if($PyrolEdtScrn->payroll[1]) echo 'selected';?>>Executive</option>
                        <option value = "2" <?php if($PyrolEdtScrn->payroll[2]) echo 'selected';?>>Senior</option>
                        <option value = "3" <?php if($PyrolEdtScrn->payroll[3]) echo 'selected';?>>Junior HQ</option>
                        <option value = "4" <?php if($PyrolEdtScrn->payroll[4]) echo 'selected';?>>South</option>
                        <option value = "5" <?php if($PyrolEdtScrn->payroll[5]) echo 'selected';?>>Centre</option>
                        <option value = "6" <?php if($PyrolEdtScrn->payroll[6]) echo 'selected';?>>North</option>
                    </select>
                </div>
                <div class="">
                    <input type="text" name="fname_input" style="font-weight: bold;" value="<?php echo $PyrolEdtScrn->fname;?>" class="form-control border border-dark " id="fname" placeholder="First Name">
                </div>
                <div class="">
                    <input type="text" name="mname_input" style="font-weight: bold;" value="<?php echo $PyrolEdtScrn->mname;?>" class="form-control border border-dark " id="mname" placeholder="Middle Name">
                </div>
                <div class="">
                    <input type="text" name="sname_input" style="font-weight: bold;" value="<?php echo $PyrolEdtScrn->sname;?>" class="form-control border border-dark " id="sname" placeholder="Surname">
                </div>
                <div class="">
                    <select name="gender_input" class="custom-select border border-dark" id="GENDER">
                        <option >SEX</option>
                        <option <?php if($PyrolEdtScrn->genderM) echo 'selected';?>>M</option>
                        <option <?php if($PyrolEdtScrn->genderF) echo 'selected';?>>F</option>
                    </select>
                </div>
                <div class="">
                    <select name="status_input" class="custom-select border border-dark" id="STATUS">
                        <option >STATUS</option>
                        <option <?php if($PyrolEdtScrn->statusA) echo 'selected';?>>A</option>
                        <option <?php if($PyrolEdtScrn->statusI) echo 'selected';?>>I</option>
                    </select>
                </div>


            </div>
            <div class="row">
                <div class="border border-success rounded p-2 my-4 mr-2 col overflow-auto" style="height: 450px;">
                    <table id="earnings_table" class="table ">
                        <thead>
                            <tr>
                                <th scope="col" class="col-lg-1"><small><b>ID</b></small></th>
                                <th scope="col" class="col-lg-3"><small><b>Description</b></small></th>
                                <th scope="col" class="col-lg-2"><small><b>Amount</b></small></th>
                                <th scope="col" class="col-lg-1"><small><b>Priod</b></small></th>
                                <th scope="col" class="col-lg-1"><small><b>No of Prds</b></small></th>
                                <th scope="col" class="col-lg-1" class="col-lg-1"><small><b>Taxa ble</b></small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($PyrolEdtScrn->earnings as $earning){
                            ?>
                            <tr>
                                <th class="col-lg-1">
                                    <small>
                                        <b>
                                            <input style="width: 70px; border:0cap;" value="<?php echo $earning['benid']?>"
                                            type="text" id="earningID" list="earningIDs" oninput="validateInput()">
                                            <datalist class="" id="earningIDs">
                                                <option value="BASIC">MONTHLY basic pay</option>
                                                <option value="GOO1">gratuity refund</option>
                                                <option value="P004">pension refund</option>
                                                <option value="BASIC2">monthly basic arreas</option>
                                                <option value="OT001">over time security</option>
                                            </datalist>
                                        </b>
                                    </small>
                                </th>
                                <td class="col-lg-3 text-sm-left"><small id="row1desc"><?php echo $earning['Descr']?></small> </td>
                                <td class="col-lg-2" contenteditable="true"><small><p id="recalculation"><?php echo number_format($earning['amount'], 2, '.',',')?></p></small></td>
                                <td class="col-lg-1" contenteditable="true"><small id="row1perd"><?php echo $earning['perpost']?></small></td>
                                <td class="col-lg-1" contenteditable="true"><small id="row1periods"><?php echo $earning['NofPrds']?></small></td>
                                <td>
                                    <input type="checkbox" class="form-check-input" id="recalculation" disabled <?php echo ($earning['taxable'] == 0)? '': 'checked';?> style="position: relative; inset-inline-start: 50px;" id="row1checkbox">
                                </td>
                            </tr>
                            <?php }?>

                            <tr>
                                <th>...</th>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>
                                    <input type="checkbox" class="form-check-input" style="position: relative; inset-inline-start: 50px;" id="">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="border border-danger rounded p-2 my-4 ml-2 col table-responsive" style="height: 450px;">
                    <table id="deductionsTable" class="table table-striped ">
                        <thead>
                            <tr>
                                <th scope="col" class="col-lg-1"><small><b>ID</b></small></th>
                                <th scope="col" class="col-lg-3"><small><b>Description</b></small></th>
                                <th scope="col" class="col-lg-2"><small><b>Amount</b></small></th>
                                <th scope="col" class="col-lg-2"><small><b>principle</b></small></th>
                                <th scope="col" class="col-lg-2"><small><b>balance</b></small></th>
                                <th scope="col" class="col-lg-1"><small><b>Priod</b></small></th>
                                <th scope="col" class="col-lg-1"><small><b>No of Prds</b></small></th>
                                <th scope="col" class="col-lg-1" class="col-lg-1"><small><b>net pay</b></small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($PyrolEdtScrn->deductions as $deduction){
                            ?>
                            <tr>
                                <th class="col-lg-1"><small><b><?php echo $deduction['earndedid']?></b></small></th>
                                <td class="col-lg-3 text-sm-left"><small><?php echo $deduction['Descr']?></small> </td>
                                <td class="col-lg-2"><small><?php echo number_format($deduction['amount'], 2, '.',',')?></small></td>
                                <td class="col-lg-2"><small><?php echo number_format($deduction['principal'], 2, '.',',')?></small></td>
                                <td class="col-lg-2"><small><?php echo number_format($deduction['balance'], 2, '.',',')?></small></td>
                                <td class="col-lg-1"><small><?php echo $deduction['perpost']?></small></td>
                                <td class="col-lg-1"><small><?php echo $deduction['NofPrds']?></small></td>
                                <td class="col-lg-1">
                                    <input type="checkbox" disabled <?php echo ($deduction['netpay'] == 1)? 'checked': '';?> id="PAYE">
                                </td>
                            </tr>
                            <?php }?>

                            <tr>
                                <th>...</th>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                                <td>...</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row d-flex justify-content-between">  
                <div class="d-flex justify-content-between p-0 mr-2 col">
                    <button name="submit" value="load_defaults" class="btn btn-primary btn-lg">LOAD DEFAULTS</button>
                    <button type="button" class="btn btn-outline-dark w-6 btn-lg  border border-dark rounded-pill">GROSS : <?php echo number_format($PyrolEdtScrn->grossPay, 2, '.',', ')?></button>
                </div>
                <div class="d-flex justify-content-between p-0 ml-2 col">
                    <button type="button" class="btn btn-outline-dark btn-lg border border-dark rounded-pill">NET PAY : <?php echo number_format($PyrolEdtScrn->NetPay, 2, '.',', ');?></button>
                    <button  name="submit" value="save_changes" class="btn btn-primary btn-lg">SAVE CHANGES</button>
                </div>

            </div>
        </form>
    </div>

    <!-- Include Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script>
        const earningTableData = {
            BASIC: {
                DESC: 'MONTHLY BASIC PAY',
                TAXABLE: '1'
            },
            BASIC2: {
                DESC: 'MONTHLY BASIC PAY ARREAS',
                TAXABLE: '0'
            },
            P004: {
                DESC: 'PENSION REFUND',
                TAXABLE: '0'
            },
            G001: {
                DESC: 'GRATUITY REFUND',
                TAXABLE: '1'
            },
            OT001: {
                DESC: 'OVERTIME SECURITY',
                TAXABLE: '1'
            }
        };

        document.getElementById('payroll_select_input').addEventListener('change', function(){
            console.log(document.getElementById('employeeEditForm'));
            var submitBtn = document.getElementById('hidden_submit_btn');
            submitBtn.value = 'payrollChange';
            submitBtn.click();
        });

        function validateInput() {
            const input = document.getElementById('earningID');
            const validOptions = document.querySelectorAll('#earningIDs option');
            const inputValue = input.value.toLowerCase();
            var Description = document.getElementById('row1desc');


            // Check if the input value matches any valid option
            const isValid = Array.from(validOptions).some(option => option.value.toLowerCase() === inputValue);

            if (!isValid) {
                Description.innerHTML = "";
                console.log("here");
                input.setCustomValidity('Please select a valid fruit from the list.');
            } else {
                Description.innerHTML = earningTableData[input.value].DESC;
                if (earningTableData[input.value].TAXABLE == 1)
                    document.getElementById("row1checkbox").checked = true;
                else
                    document.getElementById("row1checkbox").checked = false;

                input.setCustomValidity('');
            }
        }

        function calculatePAYE(grossSalary) {
            // Define the tax brackets and rates
            const taxBrackets = [
                { limit: 100000, rate: 0 },
                { limit: 350000, rate: 0.25 },
                { limit: 2350000, rate: 0.3 },
                { limit: 4850000, rate: 0.35 }
            ];

            // Initialize the total tax
            let totalTax = 0;

            // Calculate tax for each bracket
            for (const bracket of taxBrackets) {
                if (grossSalary > bracket.limit) {
                    const taxableAmount = Math.min(grossSalary - bracket.limit, bracket.limit);
                    totalTax += taxableAmount * bracket.rate;
                }
            }

            // Calculate net pay
            const netPay = grossSalary - totalTax;

            // Return results
            return {
                grossSalary,
                payeDeduction: totalTax,
                netPay
            };
        }

        document.getElementById('recalculation').addEventListener('change', function (){
            console.log("recalculation called");
            
        });
        

        function getTableAmounts() {
            // Get the table element
            const earningsTable = document.getElementById("earnings_table");

            // Initialize sum variables
            let totalEarnings = 0;
            let totalTaxableEarnings = 0;

            // Iterate through rows (skip the first row with headers)
            for (let i = 1; i < earningsTable.rows.length; i++) {
                const row = earningsTable.rows[i];
                const checkbox = row.cells[6];
                console.log(checkbox);
                const totalEarnings = parseInt(row.cells[3].textContent, 10);
                const totalTaxableEarnings = parseInt(row.cells[3].textContent, 10);

                totalEarningsSum += totalEarnings;
                totalTaxableEarningsSum += totalTaxableEarnings;
            }

            // Display the sums
            console.log("Total Work Assigned Sum:", totalEarningsSum);
            console.log("Not Called Sum:", totalTaxableEarningsSum);
        }

    </script>
</body>

</html>