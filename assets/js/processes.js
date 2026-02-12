// Get DOM elements
var select = document.getElementById("report_type_dropdown");
var batnbr_input = document.getElementById("batnbr_input");
var batnbr_input_div = document.getElementById("batnbr_input_div");
var empid_checkbox_div = document.getElementById("empid_checkbox_div");
var empid_checkbox = document.getElementById("empid_checkbox");
var payroll_dropdown_div = document.getElementById("payroll_dropdown_div");
var empid_input_div = document.getElementById("empid_input_div");
var Division_dropdown_div = document.getElementById("Division_dropdown_div");
var market_code_div = document.getElementById("market_code_div");
var market_name_div = document.getElementById("market_name_div");
var earning_deduction_input_div = document.getElementById("earning_deduction_input_div");
var EarnDed_checkbox_div = document.getElementById("EarnDed_checkbox_div");
var payroll_code_div = document.getElementById("payroll_code_div");
var process_form_div = document.getElementById("process_form_div");

// Function to apply special styles for release_ca_batch
function applyReleaseCABatchStyles() {
    var navHeight = document.querySelector('.navbar').offsetHeight;
    process_form_div.style.position = "fixed";
    process_form_div.style.left = "0";
    process_form_div.style.top = navHeight + "px";
    process_form_div.style.height = "calc(100vh - " + navHeight + "px)";
    process_form_div.style.width = "20%"; // Occupy 25% of screen width in desktop mode
    process_form_div.style.marginLeft = "20px"; 
    process_form_div.style.marginRight = "20px"; 
    process_form_div.style.zIndex = "1000";
    process_form_div.style.transition = "all 0.5s ease";
    process_form_div.style.backgroundColor = "#f3f3f3"; // Match form's bg-light
    
    // Create right panel div
    var rightPanel = document.createElement('div');
    rightPanel.id = 'right_panel';
    rightPanel.style.position = "fixed";
    rightPanel.style.left = "20%";
    rightPanel.style.top = navHeight + "px";
    rightPanel.style.height = "calc(100vh - " + navHeight + "px)";
    rightPanel.style.width = "80%";
    rightPanel.style.backgroundColor = "#f3f3f3";
    rightPanel.style.zIndex = "1000";
    rightPanel.style.transition = "all 0.5s ease";
    rightPanel.style.padding = "20px";
    rightPanel.style.overflow = "hidden";
    
    // Create table container
    var tableContainer = document.createElement('div');
    tableContainer.style.height = "100%";
    tableContainer.style.display = "flex";
    tableContainer.style.flexDirection = "column";
    
    // Create header with left controls and Release All button
    var headerDiv = document.createElement('div');
    headerDiv.style.display = "flex";
    headerDiv.style.justifyContent = "space-between";
    headerDiv.style.alignItems = "center";
    headerDiv.style.marginBottom = "10px";

    // Left controls container (division dropdown + deleted batches button)
    var leftControls = document.createElement('div');
    leftControls.style.display = 'flex';
    leftControls.style.alignItems = 'center';
    leftControls.style.gap = '8px';

    // Division dropdown (will try to clone existing options if present)
    var divisionSelect = document.createElement('select');
    divisionSelect.id = 'division_dropdown';
    divisionSelect.className = 'form-control';
    divisionSelect.style.minWidth = '140px';
    divisionSelect.style.padding = '6px';

    // Try to reuse options from existing Division_dropdown element on the page
    var existingDivSelect = document.getElementById('Division_dropdown');
    if (existingDivSelect) {
        // clone options
        for (var i = 0; i < existingDivSelect.options.length; i++) {
            var opt = existingDivSelect.options[i].cloneNode(true);
            divisionSelect.appendChild(opt);
        }
        divisionSelect.selectedIndex = existingDivSelect.selectedIndex;
    } else {
        // Fallback options
        var opt = document.createElement('option'); opt.value = '202'; opt.text = '202'; divisionSelect.appendChild(opt);
        var opt2 = document.createElement('option'); opt2.value = '201'; opt2.text = '201'; divisionSelect.appendChild(opt2);
    }

    // Deleted Batches button (initial shows unreleased list)
    var deletedBtn = document.createElement('button');
    deletedBtn.id = 'deleted_batches_btn';
    deletedBtn.textContent = 'Deleted Batches';
    // Default style when showing unreleased data (swapped: use danger)
    deletedBtn.className = 'btn btn-danger';
    deletedBtn.style.padding = '6px 10px';

    leftControls.appendChild(divisionSelect);
    leftControls.appendChild(deletedBtn);

    headerDiv.appendChild(leftControls);

    var releaseAllBtn = document.createElement('button');
    releaseAllBtn.textContent = "Release All";
    releaseAllBtn.className = "btn btn-primary";
    releaseAllBtn.style.padding = "8px 16px";
    headerDiv.appendChild(releaseAllBtn);
    // Attach handler for Release All button (will act on current table rows)
    releaseAllBtn.addEventListener('click', async function() {
        var buttons = Array.from(document.querySelectorAll('#batch_table_body button[data-batch]'))
            .filter(b => b.textContent && b.textContent.trim() === 'Release' && !b.disabled);
        if (!buttons || buttons.length === 0) return;
        releaseAllBtn.disabled = true;
        releaseAllBtn.textContent = 'Releasing...';

        for (const btn of buttons) {
            var batch = btn.getAttribute('data-batch');
            await releaseSingleBatch(batch, btn);
        }

        releaseAllBtn.textContent = 'Released All';
    });
    
    // Create table
    var table = document.createElement('table');
    table.className = "table table-striped table-bordered";
    table.style.width = "100%";
    table.style.marginBottom = "0";
    
    // Create table header
    var thead = document.createElement('thead');
    thead.style.position = "sticky";
    thead.style.top = "0";
    thead.style.backgroundColor = "#f8f9fa";
    thead.style.zIndex = "1";
    
    var headerRow = document.createElement('tr');
    var headers = ['Batch', 'Src Acct', 'Dst Acct', 'Bank Sub', 'Crtd Date', 'Crtd User', 'Amount', 'DrCr', 'PerEnt', 'RefNbr', 'Tran Desc', 'Action'];
    
    headers.forEach(function(headerText) {
        var th = document.createElement('th');
        th.textContent = headerText;
        th.style.padding = "8px";
        th.style.textAlign = "center";
        th.style.fontWeight = "bold";
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    table.appendChild(thead);
    
    // Create table body with scrollable container
    var tbodyContainer = document.createElement('div');
    tbodyContainer.style.flex = "1";
    tbodyContainer.style.overflowY = "auto";
    tbodyContainer.style.maxHeight = "calc(100vh - " + (navHeight + 120) + "px)";
    
    var tbody = document.createElement('tbody');
    tbody.id = 'batch_table_body';
    
    tbodyContainer.appendChild(tbody);
    
    // Assemble the table structure
    tableContainer.appendChild(headerDiv);
    tableContainer.appendChild(table);
    tableContainer.appendChild(tbodyContainer);
    
    rightPanel.appendChild(tableContainer);
    
    // Add to document body
    document.body.appendChild(rightPanel);

    // Wire division change and Deleted Batches button to reload table by division
    function getSelectedDivision() {
        if (divisionSelect && divisionSelect.value) return divisionSelect.value;
        if (existingDivSelect && existingDivSelect.value) return existingDivSelect.value;
        return '202';
    }

    divisionSelect.addEventListener('change', function() {
        fetchUnreleasedCABatches(getSelectedDivision());
    });

    // Toggle button state: default shows deleted batches; clicking again shows unreleased
    var deletedMode = false; // false => showing unreleased, button shows 'Deleted Batches'
    deletedBtn.addEventListener('click', function() {
        if (!deletedMode) {
            // switch to deleted view
            fetchDeletedCABatches(getSelectedDivision());
            deletedBtn.textContent = 'Unreleased Batches';
            // swapped: use primary color when viewing deleted batches
            deletedBtn.className = 'btn btn-primary';
            deletedMode = true;
        } else {
            // switch back to unreleased view
            fetchUnreleasedCABatches(getSelectedDivision());
            deletedBtn.textContent = 'Deleted Batches';
            // swapped: use danger color when viewing unreleased
            deletedBtn.className = 'btn btn-danger';
            deletedMode = false;
        }
    });

    // Initial load using selected division (falls back sensibly)
    fetchUnreleasedCABatches(getSelectedDivision());
}

// Function to reset form styles
function resetFormStyles() {
    process_form_div.style.position = "";
    process_form_div.style.left = "";
    process_form_div.style.top = "";
    process_form_div.style.height = "";
    process_form_div.style.width = "";
    process_form_div.style.zIndex = "";
    process_form_div.style.transition = "";
    process_form_div.style.backgroundColor = ""; // Reset background
    process_form_div.style.paddingLeft = ""; // Reset padding
    
    // Remove right panel if it exists
    var rightPanel = document.getElementById('right_panel');
    if (rightPanel) {
        rightPanel.remove();
    }
}

// Function to fetch unreleased CA batches from server
function fetchUnreleasedCABatches(division = '202') {
    fetch('processes.php?action=get_unreleased_ca_batches&division=' + encodeURIComponent(division))
        .then(response => response.json())
        .then(data => {
            populateBatchTable(data, 'unreleased');
        })
        .catch(error => {
            console.error('Error fetching CA batches:', error);
            populateBatchTable([], 'unreleased');
        });
}

function fetchDeletedCABatches(division = '202') {
    fetch('processes.php?action=get_deleted_ca_batches&division=' + encodeURIComponent(division))
        .then(response => response.json())
        .then(data => {
            populateBatchTable(data, 'deleted');
        })
        .catch(error => {
            console.error('Error fetching deleted CA batches:', error);
            populateBatchTable([], 'deleted');
        });
}

// Function to populate the batch table with data
function populateBatchTable(batchData, mode = 'unreleased') {
    var tbody = document.getElementById('batch_table_body');
    tbody.innerHTML = ''; // Clear existing rows

    // Build counts of batch numbers to detect duplicates so we can disable releases
    var batchCounts = {};
    if (Array.isArray(batchData)) {
        batchData.forEach(function(r) {
            var key = r && r.batnbr ? r.batnbr : '';
            if (key !== '') batchCounts[key] = (batchCounts[key] || 0) + 1;
        });
    }
    
    if (batchData.length === 0) {
        // Show message if no data
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = "12";
        td.textContent = (mode === 'deleted') ? "No deleted CA batches found." : "No unreleased CA batches found.";
        td.style.textAlign = "center";
        td.style.padding = "20px";
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }
    
    batchData.forEach(function(row) {
        var tr = document.createElement('tr');
        
        // Add data cells in the correct order to match table headers
        var cellData = [
            row.batnbr,
            row.Acct,
            row.bankacct,
            row.sub,
            row.Crtd_DateTime ? new Date(row.Crtd_DateTime).toLocaleDateString() : '',
            row.Crtd_User,
            row.TranAmt,
            row.DrCr,
            row.Perent,
            row.RefNbr,
            row.TranDesc
        ];
        
        // Add data cells
        cellData.forEach(function(cellValue) {
            var td = document.createElement('td');
            td.textContent = cellValue;
            td.style.padding = "8px";
            td.style.textAlign = "center";
            tr.appendChild(td);
        });
        
        // Add action button cell
        var actionTd = document.createElement('td');
        actionTd.style.padding = "8px";
        actionTd.style.textAlign = "center";
        
        if (mode === 'deleted') {
            var restoreBtn = document.createElement('button');
            restoreBtn.textContent = 'Restore';
            restoreBtn.className = 'btn btn-sm btn-info';
            restoreBtn.style.padding = '4px 8px';
            restoreBtn.style.fontSize = '12px';
            restoreBtn.setAttribute('data-batch', row.batnbr);
            restoreBtn.addEventListener('click', function() {
                restoreCABatch(row.batnbr, this);
            });
            actionTd.appendChild(restoreBtn);
        } else {
            var releaseBtn = document.createElement('button');
            releaseBtn.textContent = "Release";
            releaseBtn.className = "btn btn-sm btn-success";
            releaseBtn.style.padding = "4px 8px";
            releaseBtn.style.fontSize = "12px";
            releaseBtn.setAttribute('data-batch', row.batnbr); // Store batch number for later use
            // Attach per-row release handler
            releaseBtn.addEventListener('click', function() {
                releaseSingleBatch(row.batnbr, this);
            });

            // If this batch appears more than once in the dataset, disable release
            if (batchCounts[row.batnbr] && batchCounts[row.batnbr] > 1) {
                releaseBtn.disabled = true;
                releaseBtn.title = 'Duplicate batch - appears multiple times; release disabled';
            }
            
            actionTd.appendChild(releaseBtn);

            // Add bin (void) button next to Release
            var voidBtn = document.createElement('button');
            voidBtn.title = 'Void batch';
            voidBtn.className = 'btn btn-sm btn-danger';
            voidBtn.style.marginLeft = '6px';
            voidBtn.style.padding = '4px 8px';
            voidBtn.style.fontSize = '12px';
            voidBtn.innerHTML = '<i class="fa fa-trash"></i>';
            
            voidBtn.setAttribute('data-batch', row.batnbr);
            voidBtn.addEventListener('click', function() {
                voidBatch(row.batnbr, this);
            });
            actionTd.appendChild(voidBtn);
        }
        tr.appendChild(actionTd);
        
        tbody.appendChild(tr);
    });
}

// Function to release a single CA batch via AJAX POST
async function releaseSingleBatch(batchNbr, button) {
    if (!batchNbr) return;
    if (button) {
        button.disabled = true;
        button.textContent = 'Releasing...';
    }

    var form = new FormData();
    form.append('report_type_dropdown', 'release_ca_batch');
    form.append('batnbr_input', batchNbr);

    try {
        var resp = await fetch('processes.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        });

        if (resp.ok) {
            if (button) {
                button.textContent = 'Released';
                button.className = 'btn btn-sm btn-secondary';
            }
        } else {
            if (button) {
                button.disabled = false;
                button.textContent = 'Release';
            }
            console.error('Failed to release batch', batchNbr, resp.status);
        }
    } catch (err) {
        console.error('Error releasing batch', batchNbr, err);
        if (button) {
            button.disabled = false;
            button.textContent = 'Release';
        }
    }
}

// Configuration for each process type
var processConfigs = {
    "change_batch_period": {
        show: ["batnbr_input_div", "period_input_div"],
        hide: ["payroll_dropdown_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Change Priod",
        payrollIndex: 0,
        clearPeriod: true
    },
    "release_ca_batch": {
        show: ["batnbr_input_div"],
        hide: ["payroll_dropdown_div", "period_input_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Release Batch",
        payrollIndex: 0,
        clearPeriod: true
    },
    "send_payslip_text": {
        show: ["payroll_dropdown_div", "empid_checkbox_div"],
        hide: ["batnbr_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Send Text",
        payrollIndex: 2,
        clearPeriod: true
    },
    "add_earning_deduction": {
        show: ["payroll_dropdown_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        hide: ["batnbr_input_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div"],
        submitText: "Add earning or Deduction",
        payrollIndex: 1
    },
    "recalculate_paye": {
        show: ["payroll_dropdown_div"],
        hide: ["batnbr_input_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Correct P.A.Y.E",
        payrollIndex: 1
    },
    "recalculate_tevet_levy": {
        show: ["payroll_dropdown_div"],
        hide: ["batnbr_input_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Correct tevet levy",
        payrollIndex: 1
    },
    "add_missing_employee_in_customer": {
        show: ["payroll_dropdown_div"],
        hide: ["batnbr_input_div", "empid_checkbox_div", "empid_input_div", "Division_dropdown_div", "market_code_div", "market_name_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Add Emplo",
        payrollIndex: 1
    },
    "insert_market_subAcct": {
        show: ["Division_dropdown_div", "market_code_div", "market_name_div"],
        hide: ["batnbr_input_div", "empid_checkbox_div", "empid_input_div", "payroll_dropdown_div", "payroll_code_div", "EarnDed_checkbox_div", "earning_deduction_input_div"],
        submitText: "Add SubAcct",
        payrollIndex: 0
    }
};

// Initialize - hide all optional elements first
Object.values(processConfigs).forEach(config => {
    config.hide.forEach(id => {
        document.getElementById(id).style.display = "none";
    });
});

// Get the currently selected option and show elements for it
var selectedOption = select.value;
var config = processConfigs[selectedOption];
if (config) {
    config.show.forEach(id => {
        document.getElementById(id).style.display = "";
    });
    // Set submit button text for initial load
    document.getElementById("submit_btn").value = config.submitText;
}

// Apply special styles if release_ca_batch is selected initially
if (selectedOption === "release_ca_batch") {
    if(userAccessLevel === 'ADMIN' || userAccessLevel === 'icttech'){
        applyReleaseCABatchStyles();
    }
} else {
    if(userAccessLevel === 'ADMIN' || userAccessLevel === 'icttech'){
        resetFormStyles();
    }
}

// Clear initial values
batnbr_input.value = "";

// Event listeners
empid_checkbox.addEventListener("change", function() {
    empid_input_div.style.display = this.checked ? "" : "none";
});

select.addEventListener("change", function() {
    var selected_option = select.value;
    var config = processConfigs[selected_option];

    if (config) {
        // Hide all elements first
        Object.values(processConfigs).forEach(cfg => {
            cfg.hide.forEach(id => {
                document.getElementById(id).style.display = "none";
            });
        });

        // Show required elements
        config.show.forEach(id => {
            document.getElementById(id).style.display = "";
        });

        // Set submit button text
        document.getElementById("submit_btn").value = config.submitText;

        // Set payroll dropdown if specified
        if (config.payrollIndex !== undefined) {
            document.getElementById("payroll_dropdown").selectedIndex = config.payrollIndex;
        }

        // Clear period input if specified
        if (config.clearPeriod) {
            document.getElementById("period_input").value = "";
        }

        // Apply special styles for release_ca_batch
        if (selected_option === "release_ca_batch") {
            if(userAccessLevel === 'ADMIN' || userAccessLevel === 'icttech'){
                applyReleaseCABatchStyles();
            }
        } else {
            if(userAccessLevel === 'ADMIN' || userAccessLevel === 'icttech'){
                resetFormStyles();
            }
        }

        console.log(selected_option);
    }
});

function validateForm() {
    var batnbrInput = document.getElementById("batnbr_input");
    var earningDeductionInput = document.getElementById("earning_deduction_input");

    if (batnbrInput.value !== "") {
        return prepareBatchNumbers();
    } else if (earningDeductionInput.value !== "") {
        return prepareEarningDeduction();
    }
    return true;
}

function prepareEarningDeduction() {
    var textArea = document.getElementById("earning_deduction_input");
    var text = textArea.value
        .replace(/\t\t/g, "\t")
        .replace(/\t/g, ",")
        .replace(/\n/g, ";");

    textArea.value = text.replace(/;?\.$/, "");
    return true;
}

function prepareBatchNumbers() {
    var textArea = document.getElementById("batnbr_input");
    var text = textArea.value.trim();

    if (text !== "") {
        text = "('" + text.replace(/\n/g, "',.'").replace(/ {4}/g, "") + "')";

        var array = text.split('.');
        text = "";
        var count = 0;

        for (let i = 0; i < array.length; i++) {
            text += array[i];
            if (i === count + 12) {
                text += "\n";
                count += 12;
            }
        }

        textArea.value = text;
    }

    return true;
}

// Function to restore a deleted CA batch via AJAX POST
async function restoreCABatch(batchNbr, button) {
    if (!batchNbr) return;
    if (button) {
        button.disabled = true;
        button.textContent = 'Restoring...';
    }

    var form = new FormData();
    form.append('report_type_dropdown', 'restore_ca_batch');
    form.append('batnbr_input', batchNbr);

    try {
        var resp = await fetch('processes.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        });

        if (resp.ok) {
            if (button) {
                button.textContent = 'Restored';
                button.className = 'btn btn-sm btn-secondary';
                var tr = button.closest('tr');
                if (tr) tr.remove();
            }
        } else {
            if (button) {
                button.disabled = false;
                button.textContent = 'Restore';
            }
            console.error('Failed to restore batch', batchNbr, resp.status);
        }
    } catch (err) {
        console.error('Error restoring batch', batchNbr, err);
        if (button) {
            button.disabled = false;
            button.textContent = 'Restore';
        }
    }
}

// Function to void a CA batch via AJAX POST (sets Status = 'V')
async function voidBatch(batchNbr, button) {
    if (!batchNbr) return;
    if (button) {
        button.disabled = true;
        // show spinner/icon if desired
        button.innerHTML = 'Voiding...';
    }

    var form = new FormData();
    form.append('report_type_dropdown', 'void_ca_batch');
    form.append('batnbr_input', batchNbr);

    try {
        var resp = await fetch('processes.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        });

        if (resp.ok) {
            if (button) {
                // remove the row from the table to reflect change
                var tr = button.closest('tr');
                if (tr) tr.remove();
            }
        } else {
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-trash"></i>';
            }
            console.error('Failed to void batch', batchNbr, resp.status);
        }
    } catch (err) {
        console.error('Error voiding batch', batchNbr, err);
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-trash"></i>';
        }
    }
}