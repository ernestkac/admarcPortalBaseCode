// Get the select element by its id
var select = document.getElementById("report_type_dropdown");


document.getElementById("empid_input").value = "";
var empid_input_div = document.getElementById("empid_input_div");
var empid_checkbox_div = document.getElementById("empid_checkbox_div");
var empid_checkbox = document.getElementById("empid_checkbox");

empid_input_div.style.display = "none";
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
    var empid_input_div = document.getElementById("empid_input_div");
    var empid_checkbox_div = document.getElementById("empid_checkbox_div");

    // Display a message based on the selected option
    if (selected_option == "latest_fixed_earnings") {
        period_input_div.style.display = "none";
        empid_checkbox_div.style.display = "";

        console.log(selected_option);
    } else if (selected_option == "employee_detail") {
        period_input_div.style.display = "none";

        console.log(selected_option);
    } else {
        console.log(selected_option);
        period_input_div.style.display = '';
        empid_input_div.style.display = "none";
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