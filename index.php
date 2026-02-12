<?php
    // Start the session
    session_start();
    session_unset();

    require_once 'classes\authentication.php';
    require_once 'classes\EventLog.php';
        $messageToAlert = "Enter Your Creditials";
        $hideChangePassword = 1;
    if(isset($_POST['empid_input'])){

        //$payrolls = explode(",", $_POST['payroll_dropdown']);
        //var_dump($_POST);
 
        $databaseNumber = 1;
        //setting $row varible to anything to $row = "1";
        for ($databaseNumber=1; $databaseNumber < 7; $databaseNumber++) {
            //echo $databaseNumber;
        
            $AuthenticationObject = new Authentication($databaseNumber);

            $results = $AuthenticationObject->login($_POST['empid_input'],
            $_POST['password_input']);

            if ($_POST['new_password_1_input'] != '') {
                $results = $AuthenticationObject->changePassword($_POST['empid_input'], $_POST['new_password_1_input'],
            $_POST['password_input']);
            
            }
            if($results){
                $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC);
                //var_dump($row);
                if ($row != null) {
                
                    $_SESSION["division"]  = $row['division'];
                    $_SESSION["name"]  = trim($row['name']);
                    $_SESSION["empid"]  = trim($row['empid']);
                    $_SESSION["accessLevel"]  = trim($row['accessLevel']);
                    $_SESSION["accesscode"] = $_POST['password_input'];
                    $_SESSION['LstLginDateTime'] = $row['LstLginDateTime']->format("d/m/Y h:i A");
                    $_SESSION["division"] = $row['division'];
                    $_SESSION["databaseNumber"]  = $databaseNumber;
                    if($row['email'])
                        $_SESSION["email"]  = trim($row['email']);

                    if ($_POST['new_password_1_input'] != '') {
                        $_POST['password_input'] = $_POST['new_password_1_input'];
                    }
                
                    /*
                        var_dump($_SESSION["name"] );
                    */
                    if ($row['LstLginDateTime']) { 
                        if($AuthenticationObject->setLastLoginDatetime($_POST['empid_input'],
                            $_POST['password_input'])){

                            $eventLogger = new EventLog($databaseNumber);
                            $eventLogger->addLog($_SESSION["empid"], $_SESSION["name"],
                            $_SESSION["accessLevel"], "successful login");
        
                            if($_SESSION["accessLevel"] != 'user'){
                                var_dump("calling finance group in index");
                                $_SESSION["group"]  = 'finance';
                                header("Location: reports");
                            }
                        
                            else header("Location: payslip");
                        }
                    }
                    //echo "password is default alert to change password";
                    $messageToAlert = 'You must change password';
                    $hideChangePassword = 0;
                }
                else{$messageToAlert = "wrong username or password!";}
                    
            }else{$messageToAlert = "wrong username or password!";}
            
        }     
    }else{

        //echo "failed ";
    }
    if($hideChangePassword == 0)
        $messageToAlert = 'You must change password';
    
?>

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- PAGE settings -->
        <link rel="icon" href="assets/img/admarc-png-logo.png">
        <title>ADMARC LOGIN</title>
        <!-- CSS dependencies -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="assets/css/aquamarine.css" type="text/css">
        <!-- Script: Make my navbar transparent when the document is scrolled to top -->
        <script src="assets/js/navbar-ontop.js"></script>
        <!-- Script: Animated entrance -->
        <script src="assets/js/animate-in.js"></script>

       
        <style type="text/css">
            .valid {
            color: green;
            }

            .valid:before {
            position: relative;
            left: -35px;
            content: "✔";
            }

            .invalid {
            color: red;
            }

            .invalid:before {
            position: relative;
            left: -35px;
            content: "✖";
            }
            /* Password toggle */
            .password-wrapper { position: relative; }
            .password-wrapper .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; font-size: 18px; }
        </style>
    </head>

    <body>
       
        <!-- Cover -->
        <div class="align-items-center d-flex cover section-aquamarine py-5" style="	background-image: url(assets/img/admarc-png-logo.png);	background-position: top left;	background-size: 100%;	background-repeat: repeat;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7 align-self-center text-lg-left text-center">
                        <h1 class="mb-0 mt-5 display-4">ADMARC LTD</h1>
                        <p class="mb-5" contenteditable="true">Quick Reports</p>
                    </div>
                    <div class="col-lg-5 p-3">
                        <form class="p-4 bg-light" method="post" action="" onsubmit="return validatelogin();">
                           
                            <div id="empid_input_div" class="form-group">
                                <label id='lable_for_message' for="empid_input" style=" font-weight:bold ;" ><?php echo $messageToAlert?></label>
                                <input type='number' placeholder="employement Number" id="empid_input" name="empid_input" class="form-control"></input>
                            </div>
                            <div id="earning_deduction_input_div" class="form-group">
                                <div class="password-wrapper">
                                    <input type='password' placeholder="Password" id="password_input" name="password_input" maxlength="30" class="form-control"></input>
                                    <i class="fa fa-eye toggle-password" id="toggle_password" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div id="new_password_1_input_div" class="form-group change-password">
                                <div class="password-wrapper">
                                    <input type='password' placeholder="New Password" id="new_password_1_input" maxlength="30" oninput='validatePassword()' name="new_password_1_input" class="form-control"></input>
                                    <i class="fa fa-eye toggle-password" id="toggle_new_password_1" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div id="new_password_2_input_div" class="form-group change-password">
                                <div class="password-wrapper">
                                    <input type='password' placeholder="Retype New Password" id="new_password_2_input" maxlength="30" oninput='validatePassword2()' name="new_password_2_input" class="form-control"></input>
                                    <i class="fa fa-eye toggle-password" id="toggle_new_password_2" aria-hidden="true"></i>
                                </div>
                            </div>
                            <input id="submit_button" type="submit" value="login" class="btn btn-primary"></input>
                            <div class="float-right form-group">
                                <label id="change_password_label" onclick="showHideChangePassword();"style=" font-weight:bold ;" >change password</label>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <script src="assets/js/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="assets/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!-- Script: Smooth scrolling between anchors in the same page -->
        <script src="assets/js/smooth-scroll.js"></script>
        <pingendo onclick="window.open('localhost/', '_blank')" style="cursor:pointer;position: fixed;bottom: 20px;right:20px;padding:4px;background-color: #00b0eb;border-radius: 8px; width:220px;display:flex;flex-direction:row;align-items:center;justify-content:center;font-size:14px;color:white">Made By ICT Department&nbsp;&nbsp;</pingendo>
    

        
   <!--         // HTML
<input type="password" id="password" oninput="validatePassword()">

<div id="message">
  <h3>Password must contain the following:</h3>
  <p id="length" class="invalid">Minimum 8 characters</p>
  <p id="capital" class="invalid">A capital (uppercase) letter</p>
  <p id="lowercase" class="invalid">A lowercase letter</p>
  <p id="number" class="invalid">A number</p>
</div>

        -->

        <script >
        // JavaScript

        //calling the function onsetup to hide other password fields
        showHideChangePassword() ;

        function validatePassword() {
            console.log("validatePassword called");
            var password = document.getElementById("new_password_1_input").value;
            var message = document.getElementById("lable_for_message").textContent;
            console.log(message);
            message = 'password Accepted!';
            /*
            // Validate length
            if(password.match(/[a-z]/)) {
                // Validate capital letter
                if(password.match(/[A-Z]/)) {
                    // Validate lowercase letter
                    if(password.length == 6) {
                        // Validate number
                        if(password.match(/[0-9]/)) {
                            console.log('password Accepted!');
                            message = 'password Accepted!';
                        }  else {
                            console.log('password must have A number');
                            message = 'password must have A number';
                        }
                    }  else {
                        console.log("Must be 6 characters");
                        message = 'Must be 6 characters';
                    }
                }  else {
                    console.log("password must have A capital /uppercase letter");
                    message = 'password must have A capital /uppercase letter';
                }
            } else {
                
                console.log('password must have A small /lowercase letter');
                message = 'password must have A small /lowercase letter';
            }*/
            document.getElementById("lable_for_message").textContent = message;
            return message;
        }

        function validatePassword2() {
            console.log("validatePassword called");
            var password1 = document.getElementById("new_password_1_input").value;
            var password2 = document.getElementById("new_password_2_input").value;
            var message = document.getElementById("lable_for_message").textContent;
            console.log(message);

            
            if(password1 == password2) {
                message = validatePassword();
            } else {
                
                console.log('password missmatch!');
                message = 'password missmatch!';
            }
            document.getElementById("lable_for_message").textContent = message;
            return message;
        }

        //calling the function onsetup to hide other password fields
        if(<?php echo $hideChangePassword;?> == 1)
        showHideChangePassword() ;
function showHideChangePassword() {
    if (document.getElementById('change_password_label').innerHTML == 'Log In') {
            
        document.getElementById('submit_button').value = 'Log In';
        document.getElementById('change_password_label').innerHTML = 'Change Password';
        
        document.getElementById('new_password_1_input_div').style.display='none';
        document.getElementById('new_password_2_input_div').style.display='none';
        document.getElementById('new_password_1_input').value='';
        document.getElementById('new_password_2_input').value='';
    }else{
        document.getElementById('submit_button').value = 'Change Password';
        document.getElementById('change_password_label').innerHTML = 'Log In';
        
        document.getElementById('new_password_1_input_div').style.display='block';
        document.getElementById('new_password_2_input_div').style.display='block';
    }
    
}

function validatelogin(){
    console.log("validateLogin called");
    
    var username = document.getElementById("empid_input").value;
    var password = document.getElementById("password_input").value;
    var password1 = document.getElementById("new_password_1_input").value;
    console.log(username);
    console.log(password);
    if (username != "" && password1 != "") {
        console.log('password1 not empty!');
        
        if (validatePassword2() == 'password Accepted!' ){
            console.log("phrase accepted");
            return true;
        }
         return false;   
    }
    if (username != "" && password != ""){
        console.log("username and password not empyt");
        return true;
    }


    return false;
}
/*
// Import the crypto module
const crypto = require('crypto');
// Function to generate a random salt
function generateSalt(length) {
    return crypto.randomBytes(Math.ceil(length/2))
        .toString('hex') // Convert to hexadecimal format
        .slice(0,length); // Return required number of characters
}

// Function to hash password with salt
function hashPassword(password, salt) {
    const hash = crypto.createHash('sha256');
    hash.update(password + salt);
    return salt + hash.digest('hex');
}

// Function to check password
function checkPassword(inputPassword, storedPasswordHash) {
    const salt = storedPasswordHash.substr(0, 16); // Assuming salt is 16 characters long
    const inputPasswordHash = hashPassword(inputPassword, salt);
    return inputPasswordHash === storedPasswordHash;
}

// Usage
const password = 'your_password';
const salt = generateSalt(16);
const hashedPassword = hashPassword(password, salt);

console.log(hashedPassword); // Store this hashed password in the database

// When user logs in again
const userPassword = 'your_password'; // This should be taken from user input
console.log(checkPassword(userPassword, hashedPassword)); // Returns true if passwords match
 */       
        // Password visibility toggle for all icons
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.toggle-password').forEach(function(toggle){
                toggle.addEventListener('click', function(){
                    var wrapper = toggle.closest('.password-wrapper');
                    if(!wrapper) return;
                    var input = wrapper.querySelector('input');
                    if(!input) return;
                    if(input.type === 'password'){
                        input.type = 'text';
                        toggle.classList.remove('fa-eye');
                        toggle.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        toggle.classList.remove('fa-eye-slash');
                        toggle.classList.add('fa-eye');
                    }
                });
            });
        });

        </script>
    </body>

    </html>