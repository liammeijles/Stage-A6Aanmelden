<?php
header("content-type: application/json");
if(isset($_POST['email'])){
  $oSoap = new SoapClient("https://dev.infomaatje.org/admin/preview/bpmsSoap.wsdl");
  
  
  $sEmail = $_POST['email'];
  $sCode = $oSoap->makeVrijwilligerAanmelding($sEmail);
  
  /*$oMail = new webMail();
  $oMailHtml = new webHTML('aanmeldingVrijwilligerMailHTML');
 
  $oLink = $oMailHtml->getElementById('mail_link');
  $oLink->setAttribute('href', 'vrijwilligerAanmelden.html?email='.$sEmail.'&t='.$sCode);

  $oLink = $oMailHtml->getElementById('mail_stagiaire');
  if($oLink) $oLink->setAttribute('href', 'vrijwilligerAanmelden.html?email='.$sEmail.'&t='.$sCode."&plek=stage");

  $sMailFrom = '"' . $GLOBALS['sServerFromName'] . '"<' .$GLOBALS['sServerFrom']. '>'; 
  $sMailSubject = "Welkom bij Budgetmaatjes 070";
  
  $oMail->setHTML($oMailHtml->saveMailHTML());
  $oMail->setFrom($sMailFrom);
  $oMail->setReplyTo($sMailFrom);
  $oMail->setSubject($sMailSubject);
  if($oMail->send(preg_split("/;/",$sEmail))){
    echo 'Er is een email verzonden met verdere instructies!';
  } else {
    echo '<span color="red">Er is helaas iets fout gegaan bij het versturen van de email.</span>';
  }*/
  echo '{"code": 1, "message":"email is verzonden", "id" :"'.$sCode.'"}';
}

?>  
