<?php
  require_once 'classes/subscriptions.php';

  $subscriptionObject = new Subscriptions;

  var_dump( $subscriptionObject->createNewSubscription('1',50));
  //var_dump($subscriptionObject->get_Subscriptions());

?>