
<?php 


 
        // Handle AJAX call
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

        if ($_POST["action"] === "modal_click") {
                    
            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "chart view "); 

            exit; // Prevent HTML below from being sent in response to AJAX
        }
    }

      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["newpassword"]) && $_SESSION["accessLevel"]==="ADMIN") {
      
        if ($_POST["newpassword"] != "") {

          $authObj = new Authentication($databaseNumber);
          $result = $authObj->changePassword($_SESSION['profile']['empid'], $_POST["newpassword"]);
          $data = [];
          if($result){
            $data = array(
                  "status" => true,
                  "operation" => 'password change'
              );
          }else{
            $data = array(
                  "status" => false,
                  "operation" => 'password change'
              );
          }
          header('Content-type: application/json');
          echo json_encode($data);
              
            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "ADMIN ".$_SESSION['profile']['empid']." password change"); 

            
        }
        exit; // Prevent HTML below from being sent in response to AJAX
    }
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"]) && $_SESSION["accessLevel"]==="ADMIN") {
      
        if ($_POST["name"] != "") {

          $employeeObj = new Employee($databaseNumber);
          $result = $employeeObj->changeName($_SESSION['profile']['empid'],$_POST["name"]);
          echo $result;
          if($result == true){
            $_SESSION['profile']["name"] = $_POST["name"];
            if($_SESSION['profile']['empid']===$_SESSION['empid']){
                $_SESSION["name"] = $_POST["name"];
            }
          }
            
                    
            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "ADMIN ".$_SESSION['profile']['empid']." name change"); 

            exit; // Prevent HTML below from being sent in response to AJAX
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["access-level"]) && $_SESSION["accessLevel"]==="ADMIN") {
      
        if ($_POST["access-level"] != "") {

          $employeeObj = new Employee($databaseNumber);
          $result = $employeeObj->changeAccessLevel($_SESSION['profile']['empid'],$_POST["access-level"]);
          echo $result;
          if($result == true){
            $_SESSION['profile']["accessLevel"] = $_POST["access-level"];
            if($_SESSION['profile']['empid']===$_SESSION['empid']){
                $_SESSION["accessLevel"] = $_POST["access-level"];
            }
          }
             
                    
            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "ADMIN ".$_SESSION['profile']['empid']." accessLevel change"); 

            exit; // Prevent HTML below from being sent in response to AJAX
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["division"]) && $_SESSION["accessLevel"]==="ADMIN") {
      
        if ($_POST["division"] != "") {

          $employeeObj = new Employee($databaseNumber);
        
          $result = $employeeObj->changeDivision($_SESSION['profile']['empid'],$_POST["division"]);
          echo $result;
          if($result == true){
            $_SESSION['profile']["division"] = $_POST["division"];
            if($_SESSION['profile']['empid']===$_SESSION['empid']){
                $_SESSION["division"] = $_POST["division"];
            }
          }
             
                    
            $eventLogger = new EventLog($databaseNumber);
            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
            $_SESSION["accessLevel"], "ADMIN ".$_SESSION['profile']['empid']." division change"); 

            exit; // Prevent HTML below from being sent in response to AJAX
        }
    }


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["q"])) {
    $q = $_POST['q'];
    $searchSourceID = $_POST['searchSource'];
    

    $databaseNumber=2;

    $employeeObj = new Employee($databaseNumber);
        
    
    $result = $employeeObj->search($q, $searchSourceID);
          
    $data = [];
    if($result)
      while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
          $data[] = $row;
          //var_dump ($row);
      }
    //echo "me returned";
    echo json_encode($data);

    exit; // Prevent HTML below from being sent in response to AJAX
}

?>