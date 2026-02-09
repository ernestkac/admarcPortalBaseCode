<?php

require_once 'classes\EventLog.php';


$year = isset($_GET['year']) ? $_GET['year'] : date('Y');


// Array to hold total counts by month
$monthCounts = array_fill(1, 12, 0); // Jan–Dec



for ($i=0; $i < 7; $i++) { 
    $eventLogger = new EventLog($i);
    $result = $eventLogger->getUsageFrequncyInMonth($year);
    $data = [];

    //var_dump($result);

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        //var_dump($row);
        $month = (int)$row['month'];
        $count = (int)$row['usage_count'];
        $monthCounts[$month] += $count;

    }

    unset($eventLogger);
}


// Prepare data
$output = [];
foreach ($monthCounts as $month => $count) {
    $output[] = [
        'month' => $month,
        'usage_count' => $count
    ];
}
/*
//  Sort descending by usage_count
usort($output, function($a, $b) {
    return $b['usage_count'] - $a['usage_count'];
});
*/

echo json_encode($output);
?>
