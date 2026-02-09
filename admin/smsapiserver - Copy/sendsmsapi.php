<?php
  require_once 'classes/subscriptions.php';
  require_once 'classes/sms_messages.php';
  // Load the ws library
  require_once __DIR__ . '/vendor/autoload.php';

  $subscriptionObject = new Subscriptions;
  $smsMessagesObject = new smsMessages;

  $socketLink = "ws://localhost:8080";

  $send_status_success = 1;
  $send_status_fail = 0;

  //data from user must be like this
  //token, message, numbers

  

  //var_dump( $_POST);
  if(isset($_POST['token'])){

    $postToken = $_POST['token'];
    $postMessage = $_POST['message'];
    $postNumbers = $_POST['numbers'];

    $phoneNumbersCount = count(explode(",", $postNumbers));
    $verifySender = $subscriptionObject->verifySender($postToken, $phoneNumbersCount);

    if ($verifySender){
      //send requsted messages here
      try{
        $client = new WebSocket\Client($socketLink);

        //set time out
        $client->setTimeout(30);

        // Send a message to the server
        $client->send($postNumbers.":".$postMessage);
        
        // Keep the connection open until a response is received
        while ($client->isConnected()) {
            $response = $client->receive();
            if ($response !== null) {
                // Print the response and exit the loop
                $responseObject = json_decode($response);
                if($responseObject === null){
                  echo '{"status":"error", "message":'.
                    '"unknown reponse from server!"}';
                  //record to database for sending later
                  if(!$smsMessagesObject->add_sms_messages($postToken, $postNumbers, $postMessage,
                    $phoneNumbersCount, $send_status_fail)){
                    var_dump( $smsMessagesObject->error);
                  }
 
                }else{
                  // access the values
                  if($responseObject->status == "error"){
                    echo '{"status":"error", "message":'.
                      '"error sending the sms!"}';
                    //record to database for sending later
                    if(!$smsMessagesObject->add_sms_messages($postToken, $postNumbers, $postMessage,
                      $phoneNumbersCount, $send_status_fail)){
                      var_dump( $smsMessagesObject->error);
                    }
                  }else{
                    //record to database as a successful message sent
                    if(!$smsMessagesObject->add_sms_messages($postToken, $postNumbers, $postMessage,
                      $phoneNumbersCount, $send_status_success)){
                      var_dump( $smsMessagesObject->error);
                    }
                    //increment used sms to subscription
                    if(!$subscriptionObject->incrementSmsUsage($postToken, $phoneNumbersCount)){
                      var_dump( $subscriptionObject->error);
                    }else{
                      echo $response;
                    }
                  }
                }
                break;
            }
        }
      } catch (WebSocket\ConnectionException $e) {
          // handle the timeout error here
          
            echo '{"status":"error", "message":'.
              '"time out! server no response! try again! code-'.$e->getCode().'"}';

            //record to database for sending later
            if(!$smsMessagesObject->add_sms_messages($postToken, $postNumbers, $postMessage,
              $phoneNumbersCount, $send_status_fail)){
              var_dump( $smsMessagesObject->error);
            }
        }

    }else{
      //send reject message to the sender
      echo '{"status":"error", "message":'.
        '"check your subscription! subscription limit reached or bad token"}';
      var_dump($subscriptionObject->error);
    }
  }

?>