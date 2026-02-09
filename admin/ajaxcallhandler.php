<?php

// unified POST handler: run only when a relevant POST key is present
if (
        isset($_POST["action"]) ||
        isset($_POST["newpassword"]) ||
        isset($_POST["name"]) ||
        isset($_POST["access-level"]) ||
        isset($_POST["division"]) ||
        isset($_POST["q"])
    ) {

    // 1) modal click (logging) - public POST but requires session values
    if (isset($_POST["action"]) && $_POST["action"] === "modal_click") {
        $eventLogger = new EventLog($databaseNumber);
        $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "ADMIN  chart view ");
        exit;
    }

    // 2) search query (returns JSON)
    if (isset($_POST["q"])) {
        $q = $_POST['q'];
        $searchSourceID = $_POST['searchSource'] ?? null;

        $databaseNumber = 2;
        $employeeObj = new Employee($databaseNumber);
        $result = $employeeObj->search($q, $searchSourceID);

        $data = [];
        if ($result) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // 3) Admin-only operations: newpassword, name, access-level, division
    if (!isset($_SESSION["accessLevel"]) || $_SESSION["accessLevel"] !== "ADMIN") {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }

    // change password (admin)
    if (isset($_POST["newpassword"])) {
        if ($_POST["newpassword"] !== "") {
            $authObj = new Authentication($databaseNumber);
            $result = $authObj->changePassword($_SESSION['profile']['empid'], $_POST["newpassword"]);
            $data = $result
                ? ["status" => true, "operation" => "password change"]
                : ["status" => false, "operation" => "password change"];

            header('Content-Type: application/json');
            echo json_encode($data);

            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "ADMIN " . $_SESSION['profile']['empid'] . " password change");
        }
        exit;
    }

    // change name (admin)
    if (isset($_POST["name"])) {
        if ($_POST["name"] !== "") {
            $employeeObj = new Employee($databaseNumber);
            $result = $employeeObj->changeName($_SESSION['profile']['empid'], $_POST["name"]);
            echo $result;
            if ($result == true) {
                $_SESSION['profile']["name"] = $_POST["name"];
                if ($_SESSION['profile']['empid'] === $_SESSION['empid']) {
                    $_SESSION["name"] = $_POST["name"];
                }
            }

            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "ADMIN " . $_SESSION['profile']['empid'] . " name change");
        }
        exit;
    }

    // change access level (admin)
    if (isset($_POST["access-level"])) {
        if ($_POST["access-level"] !== "") {
            $employeeObj = new Employee($databaseNumber);
            $result = $employeeObj->changeAccessLevel($_SESSION['profile']['empid'], $_POST["access-level"]);
            echo $result;
            if ($result == true) {
                $_SESSION['profile']["accessLevel"] = $_POST["access-level"];
                if ($_SESSION['profile']['empid'] === $_SESSION['empid']) {
                    $_SESSION["accessLevel"] = $_POST["access-level"];
                }
            }

            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "ADMIN " . $_SESSION['profile']['empid'] . " accessLevel change");
        }
        exit;
    }

    // change division (admin)
    if (isset($_POST["division"])) {
        if ($_POST["division"] !== "") {
            $employeeObj = new Employee($databaseNumber);
            $result = $employeeObj->changeDivision($_SESSION['profile']['empid'], $_POST["division"]);
            echo $result;
            if ($result == true) {
                $_SESSION['profile']["division"] = $_POST["division"];
                if ($_SESSION['profile']['empid'] === $_SESSION['empid']) {
                    $_SESSION["division"] = $_POST["division"];
                }
            }

            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "ADMIN " . $_SESSION['profile']['empid'] . " division change");
        }
        exit;
    }
}
