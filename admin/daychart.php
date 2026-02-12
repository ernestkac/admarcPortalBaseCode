<?php

require_once 'classes\EventLog.php';


// Get month from URL, default to current month
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$combinedData = [];

for ($i=0; $i < 7; $i++) { 
    $eventLogger = new EventLog($i);
    $result = $eventLogger->getUsageFrequncyByDay($month);
    $data = [];

    //var_dump($result);

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        //var_dump($row);
        $day = trim($row['day']);
        $count = (int)$row['usage_count'];

        // Combine counts by day
        if (isset($combinedData[$day])) {
            $combinedData[$day] += $count;
        } else {
            $combinedData[$day] = $count;
        }
    }

    unset($eventLogger);
}


// Convert to array of objects for JSON
$output = [];
foreach ($combinedData as $day => $count) {
    $output[] = ['day' => $day, 'usage_count' => $count];
}

//  Sort descending by usage_count
usort($output, function($a, $b) {
    return $b['usage_count'] - $a['usage_count'];
});

echo json_encode(array_slice($output,0, 20));
?>
