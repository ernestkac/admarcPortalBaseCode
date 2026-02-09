// Get the select element by its id
var select = document.getElementById("report_type_dropdown");


document.getElementById("empid_input").value = "";
var empid_input_div = document.getElementById("empid_input_div");
var fiscalyr_input_div = document.getElementById("fiscalyr_input_div");
var empid_checkbox_div = document.getElementById("empid_checkbox_div");
var empid_checkbox = document.getElementById("empid_checkbox");
var payroll_dropdown_div = document.getElementById("payroll_dropdown_div");

empid_input_div.style.display = "none";
fiscalyr_input_div.style.display = "none";
empid_checkbox_div.style.display = "none";
//payroll_dropdown_div.style.display = "none";

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
    var empid_input = document.getElementById("empid_input");
    var empid_checkbox_div = document.getElementById("empid_checkbox_div");
    var payroll_dropdown = document.getElementById("payroll_dropdown");
    var payroll_dropdown_div = document.getElementById("payroll_dropdown_div");
    

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
        period_input.value = "none";
        fiscalyr_input.value = "";

        console.log(selected_option + " in employee detail");
    } else if (selected_option == "Sales_Report" || selected_option == "Purchases_Report") {
        period_input_div.style.display = "none";
        period_input.value = "";
        fiscalyr_input_div.style.display = "";
        payroll_dropdown.selectedIndex = 0;

        console.log(selected_option + " in sales and purchases");
    }  else if (selected_option == "5001" || selected_option == "5002") {
        period_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        period_input.value = "none";

        console.log(selected_option + " in logistics reports");
    } else {
        console.log(selected_option + " in else");
        period_input_div.style.display = '';
        empid_input_div.style.display = "none";
        fiscalyr_input_div.style.display = "none";
        empid_input.value = "";
        fiscalyr_input.value = "";
        empid_checkbox_div.style.display = "none";
    }
});

function validateForm() {
    if (document.getElementById("empid_input").value != "") { return prepareEmpids() }
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