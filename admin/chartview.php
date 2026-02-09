<?php 

//require_once 'namechart.php';
//require_once 'monthchart.php';
//require_once 'monthbynamechart.php';

/*
$path = session_save_path();
if ($path == "") {
    $path = sys_get_temp_dir();
}

$files = glob($path . "/sess_*");
foreach ($files as $file) {
    @unlink($file);
}
echo "✅ All sessions destroyed, all users logged out.";
*/
?>

<!DOCTYPE html>
<html>
<head>
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
    <title>Animated Employee Usage Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }
        #controls {
            margin-bottom: 20px;
        }

     

    </style>
</head>
<body>


    <div id="chartModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closechart()">
                <i class="fas fa-xmark"></i>
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
                    <input type="month" id="chartMonthPicker" value="2025-08">
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
                    <div class="controls">
                    <label for="yearPicker2">Select Year: </label>
                    <input type="number" id="yearPicker2" value="2025" min="2024" max="2100">
                    </div>
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

  
        <button class="btn logout" onclick="openchart()">
          <i class="fas fa-chart-line"></i>
          View Stats
        </button>

    <script>

        //script for modal
      function openchart() {
        document.getElementById("chartModal").style.display = "block";
        
        fetch("vvvvpayslip.php", {
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

    </script>
    
    <script src="assets\js\chartmodal.js"></script>
</body>
</html>
