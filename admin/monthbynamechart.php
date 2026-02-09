<?php

require_once 'classes\EventLog.php';


$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$data = [];

$grouped = [];

for ($i=0; $i < 7; $i++) { 
    $eventLogger = new EventLog($i);
    $result = $eventLogger->getUsageFrequncyInMonthByName($year);
    $data = [];

    //var_dump($result);

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        //var_dump($row);
        $name = trim($row['name']);
        $month = (int)$row['month'];
        $count = (int)$row['count'];

        
        if (!isset($grouped[$name])) {
            $grouped[$name] = array_fill(1, 12, 0);
        }

        $grouped[$name][$month] += $count;

    }

    unset($eventLogger);
}


// Prepare flat array for frontend
foreach ($grouped as $name => $months) {
    foreach ($months as $month => $count) {
        if ($count > 0) { //  only add non-zero values
            $data[] = [
                'name' => $name,
                'month' => $month,
                'count' => $count
            ];
        }
    }
}

/*
// Sort by count DESC
usort($data, function ($a, $b) {
    return $b['count'] <=> $a['count'];
    });
*/
//var_dump($data);
echo json_encode(array_slice($data,0, 100));
?>
