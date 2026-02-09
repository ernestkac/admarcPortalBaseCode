<?php
// Start session
session_start();
    if(isset($_SESSION["name"])){
        session_unset();
        session_destroy();
    }

require_once 'classes\authentication.php';
require_once 'classes\EventLog.php';

// Simple HTML-escape helper
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Default UI state
$messageToAlert = "Enter Your Credentials";
$hideChangePassword = 1;

// Sanitize/collect POST safely
$empid_input = trim((string)filter_input(INPUT_POST, 'empid_input', FILTER_SANITIZE_NUMBER_INT));
$password_input = isset($_POST['password_input']) ? trim((string)$_POST['password_input']) : '';
$new_password_1_input = isset($_POST['new_password_1_input']) ? trim((string)$_POST['new_password_1_input']) : '';
$new_password_2_input = isset($_POST['new_password_2_input']) ? trim((string)$_POST['new_password_2_input']) : '';
$csrf_post = $_POST['csrf_token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Basic server-side validation
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_post)) {
        $messageToAlert = 'Invalid form submission';
    } elseif ($empid_input === '') {
        $messageToAlert = 'Employee ID required';
    } else {
        // ensure empid is numeric
        if (!ctype_digit($empid_input)) {
            $messageToAlert = 'Invalid Employee ID';
        } else {
            // Try each database as original logic
            $authenticated = false;
            for ($databaseNumber = 1; $databaseNumber < 7 && !$authenticated; $databaseNumber++) {
                $AuthenticationObject = new Authentication($databaseNumber);

                // Use raw password string to authenticate but DO NOT echo or store it in session
                $results = $AuthenticationObject->login($empid_input, $password_input);

                // If change-password requested, validate match and reasonable length
                if ($new_password_1_input !== '') {
                    if ($new_password_1_input !== $new_password_2_input) {
                        $messageToAlert = 'New passwords do not match';
                        break;
                    }
                    if (strlen($new_password_1_input) < 6) {
                        $messageToAlert = 'New password too short';
                        break;
                    }
                    // Delegate actual password change to Authentication class
                    $results = $AuthenticationObject->changePassword($empid_input, $new_password_1_input, $password_input);
                }

                if ($results) {
                    $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
                    if ($row !== null) {
                        // Successful authentication: regenerate session id and store minimal data
                        session_regenerate_id(true);
                        $_SESSION["name"] = $row['name'];
                        $_SESSION["empid"] = $row['empid'];
                        $_SESSION["email"] = $row['email'];
                        $_SESSION["accessLevel"] = $row['accessLevel'];
                        $_SESSION['LstLginDateTime'] = isset($row['LstLginDateTime']) && is_object($row['LstLginDateTime'])
                            ? $row['LstLginDateTime']->format("d/m/Y h:i A")
                            : null;
                        $_SESSION["division"] = $row['division'];
                        $_SESSION["databaseNumber"] = $databaseNumber;

                        // log and update last login datetime via Authentication/EventLog
                        if ($AuthenticationObject->setLastLoginDatetime($empid_input, $new_password_1_input !== '' ? $new_password_1_input : $password_input)) {
                            $eventLogger = new EventLog($databaseNumber);
                            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"], $_SESSION["accessLevel"], "successful login");
                        }

                        // Redirect based on access level
                        if ($_SESSION["accessLevel"] !== 'user') {
                            header("Location: reports");
                            exit;
                        } else {
                            header("Location: payslip");
                            exit;
                        }
                    } else {
                        $messageToAlert = 'Wrong user or password';
                    }
                } else {
                    // results false -> wrong credentials or other error
                    $messageToAlert = 'Wrong user or password';
                }
            } // end for
        } // end numeric check
    } // end CSRF/empid check
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="assets/img/admarc-png-logo.png">
    <title>ADMARC LOGIN</title>
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="assets/css/aquamarine.css" type="text/css">
    <script src="assets/js/navbar-ontop.js"></script>
    <script src="assets/js/animate-in.js"></script>
    <style type="text/css">
        .valid { color: green; }
        .valid:before { position: relative; left: -35px; content: "✔"; }
        .invalid { color: red; }
        .invalid:before { position: relative; left: -35px; content: "✖"; }
    </style>
</head>
<body>
    <div class="align-items-center d-flex cover section-aquamarine py-5" style="background-image: url(assets/img/admarc-png-logo.png); background-position: top left; background-size: 100%; background-repeat: repeat;">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 align-self-center text-lg-left text-center">
                    <h1 class="mb-0 mt-5 display-4">ADMARC LTD</h1>
                    <p class="mb-5" contenteditable="true">Quick Reports</p>
                </div>
                <div class="col-lg-5 p-3">
                    <form class="p-4 bg-light" method="post" action="" onsubmit="return validatelogin();">
                        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                        <div id="empid_input_div" class="form-group">
                            <label id='lable_for_message' for="empid_input" style="font-weight:bold;"><?php echo h($messageToAlert); ?></label>
                            <input type="number" placeholder="Employment Number" id="empid_input" name="empid_input" class="form-control" value="<?php echo h($empid_input); ?>"></input>
                        </div>
                        <div id="earning_deduction_input_div" class="form-group">
                            <input type="password" placeholder="Password" id="password_input" name="password_input" maxlength="72" class="form-control"></input>
                        </div>
                        <div id="new_password_1_input_div" class="form-group change-password">
                            <input type="password" placeholder="New Password" id="new_password_1_input" maxlength="72" oninput="validatePassword()" name="new_password_1_input" class="form-control"></input>
                        </div>
                        <div id="new_password_2_input_div" class="form-group change-password">
                            <input type="password" placeholder="Retype New Password" id="new_password_2_input" maxlength="72" oninput="validatePassword2()" name="new_password_2_input" class="form-control"></input>
                        </div>
                        <input id="submit_button" type="submit" value="login" class="btn btn-primary"></input>
                        <div class="float-right form-group">
                            <label id="change_password_label" onclick="showHideChangePassword();" style="font-weight:bold;">change password</label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.3.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/smooth-scroll.js"></script>

    <script>
    // keep client-side validation for UX only (server-side enforced above)
    function validatePassword() {
        var password = document.getElementById("new_password_1_input").value;
        var message = 'password Accepted!';
        document.getElementById("lable_for_message").textContent = message;
        return message;
    }

    function validatePassword2() {
        var password1 = document.getElementById("new_password_1_input").value;
        var password2 = document.getElementById("new_password_2_input").value;
        var message = document.getElementById("lable_for_message").textContent;
        if (password1 === password2) {
            message = validatePassword();
        } else {
            message = 'password mismatch!';
        }
        document.getElementById("lable_for_message").textContent = message;
        return message;
    }

    // initialize UI
     showHideChangePassword();
    if (<?php echo (int)$hideChangePassword; ?> === 1) showHideChangePassword();

    function showHideChangePassword() {
        var label = document.getElementById('change_password_label');
        var new1 = document.getElementById('new_password_1_input');
        var new2 = document.getElementById('new_password_2_input');
        var submit = document.getElementById('submit_button');

        if (label.innerHTML.trim() === 'Log In') {
            submit.value = 'Log In';
            label.innerHTML = 'Change Password';
            new1.style.display = 'none';
            new2.style.display = 'none';
            new1.value = '';
            new2.value = '';
        } else {
            submit.value = 'Change Password';
            label.innerHTML = 'Log In';
            new1.style.display = 'block';
            new2.style.display = 'block';
        }
    }

    function validatelogin(){
        var username = document.getElementById("empid_input").value.trim();
        var password = document.getElementById("password_input").value;
        var password1 = document.getElementById("new_password_1_input").value;
        if (username !== "" && password1 !== "") {
            if (validatePassword2() === 'password Accepted!' ){
                return true;
            }
            return false;
        }
        if (username !== "" && password !== ""){
            return true;
        }
        return false;
    }
    </script>
</body>
</html>