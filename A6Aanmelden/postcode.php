<?php 

include 'includes/a6Postcode.inc.php';
$oPostcode = new a6Postcode($_GET['postcode'],$_GET['huisnummer']);//Hier wordt een nieuwe object aangemaakt
header("content-type: application/json");
echo $oPostcode->toJSON($oPostcode);
?>