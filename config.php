<?php

require_once('vendor/autoload.php');

$stripe = array(
  "secret_key"      => "", // Replace by your secret key	
  "publishable_key" => "" // Replace by your publishable key
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);


?>
