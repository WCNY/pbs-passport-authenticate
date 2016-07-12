<?php
 
$membership_id = 'FK_100313365923';
 
$station = 'WCNY';
$MVAULT_USERNAME = CLUAfOV9SmRqzext;
$MVAULT_SECRET = cAYwXjr0eOv2g582;
 
$MVAULT_URL = 'https://mvault.services.pbs.org/api/';
 
require_once('class-PBS-MVault-client.php');
 
$client = new PBS_MVault_Client($MVAULT_USERNAME, $MVAULT_SECRET, $MVAULT_URL, $station);
 
$response = $client->get_membership($membership_id);
 
 
if ($response) {
  print_r($response);
} else {
  echo "cant connect\n";
}
 
 
?>