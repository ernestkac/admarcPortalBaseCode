// Get the select element by its id
var select = document.getElementById("report_type_dropdown");


document.getElementById("empid_input").value = "";
document.getElementById("earning_deduction_input").value = "";
var empid_input_div = document.getElementById("empid_input_div");
var earning_deduction_input_div = document.getElementById("earning_deduction_input_div");
var fiscalyr_input_div = document.getElementById("fiscalyr_input_div");
var empid_checkbox_div = document.getElementById("empid_checkbox_div");
var empid_checkbox = document.getElementById("empid_checkbox");
var payroll_code_div = document.getElementById("payroll_code_div");
var EarnDed_checkbox_div = document.getElementById("EarnDed_checkbox_div");

empid_input_div.style.display = "none";
earning_deduction_input_div.style.display = "none";
payroll_code_div.style.display = "none";
EarnDed_checkbox_div.style.display = "none";
fiscalyr_input_div.style.display = "none";
empid_checkbox_div.style.display = "none";

// Add an onchange event listener to the select element
empid_checkbox.addEventListener("change", function() {
    if (this.checked) {
        empid_input_div.style.display = "";
    } else {
        empid_input_div.style.display = "none";
    }
});
select.addEventListener("change", function() {
    // Get the selected option value
    var selected_option = select.value;
    var period_input_div = document.getElementById("period_input_div");
    var period_input = document.getElementById("period_input");
    var fiscalyr_input_div = document.getElementById("fiscalyr_input_div");
    var fiscalyr_input = document.getElementById("fiscalyr_input");
    var empid_input_div = document.getElementById("empid_input_div");
    var earning_deduction_input_div = document.getElementById("earning_deduction_input_div");
    var empid_input = document.getElementById("empid_input");
    var earning_deduction_input = document.getElementById("earning_deduction_input");
    var empid_checkbox_div = document.getElementById("empid_checkbox_div");
    var payroll_code_div = document.getElementById("payroll_code_div");
    var EarnDed_checkbox_div = document.getElementById("EarnDed_checkbox_div");
    var payroll_dropdown = document.getElementById("payroll_dropdown");

    payroll_dropdown.selectedIndex = 1;

    // Display a message based on the selected option
    if (selected_option == "latest_fixed_earnings") {
        period_input_div.style.display = "none";
        period_input.value = "";
        empid_checkbox_div.style.display = "";

        console.log(selected_option);
    } else if (selected_option == "employee_detail") {
        period_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        period_input.value = "";
        fiscalyr_input.value = "";

        console.log(selected_option + " in employee detail");
    } else if (selected_option == "Insert_PayRoll_Codes") {
        period_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        EarnDed_checkbox_div.style.display = "";
        empid_input_div.style.display = "";
        payroll_code_div.style.display = "";
        period_input.value = "";
        fiscalyr_input.value = "";

        console.log(selected_option + " in insert payaroll code");
    } else if (selected_option == "add_earning_deduction") {
        period_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        EarnDed_checkbox_div.style.display = "";
        empid_input_div.style.display = "none";
        empid_input.value = "";
        earning_deduction_input_div.style.display = "";
        payroll_code_div.style.display = "";
        period_input.value = "";
        fiscalyr_input.value = "";

        console.log(selected_option + " in add earning or deduction");
    } 
    else if (selected_option == "Sales_Report" || selected_option == "Purchases_Report") {
        period_input_div.style.display = "none";
        period_input.value = "";
        fiscalyr_input_div.style.display = "";
        payroll_dropdown.selectedIndex = 0;

        console.log(selected_option + " in sales and purchases");
    } else {
        console.log(selected_option + " in else");
        period_input_div.style.display = '';
        empid_input_div.style.display = "none";
        earning_deduction_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        empid_input.value = "";
        earning_deduction_input.value = "";
        fiscalyr_input.value = "";
        empid_checkbox_div.style.display = "none";
    }
});

function validateForm() {
    if (document.getElementById("empid_input").value != "") {
        return prepareEmpids()
    } else if (document.getElementById("earning_deduction_input").value != "") {
        return prepareEarningDeduction()
    }
}

function prepareEarningDeduction() {
    var EarningDeductiontextArea = document.getElementById("earning_deduction_input");
    var text = EarningDeductiontextArea.value;
    text = text.replaceAll("		", "	");
    text = text.replaceAll("	", ",");
    text = text.replaceAll("\n", ";");
    text = text + ".";
    text = text.replace(";.", "");
    EarningDeductiontextArea.value = text;
}

function prepareEmpids() {

    var EmpidtextArea = document.getElementById("empid_input");
    var text = EmpidtextArea.value;
    if (text != "") {
        text = "('" + text;
        text = text.replaceAll("\n", "',.'");
        text = text.replaceAll("    ", "");
        text = text + "')";

        const array = text.split('.');
        text = "";
        var count = 0;
        for (let index = 0; index < array.length; index++) {
            text = text + array[index];
            if (index == count + 12) {
                text = text + "\n";
                count = count + 12;
            }

        }
        EmpidtextArea.value = text;

    }
    return true;
}