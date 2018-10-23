<?
$aResult = array("code" => 0, "message" => "An unknown error has occured.");
try {
  if(isset($_POST['email'])){
    
    $oSoap = new a6SoapClient();
    $sWie = $_POST['wie'];
    $sEmail = $_POST['email'];
    $sCode = $oSoap->makeAanmelding($sEmail, $sWie);
    
    $oMail = new webMail();
    $oMailHtml = new webHTML('aanmeldingMailHTML');
    
    $oLink = $oMailHtml->getElementById('mail_link');
    $oLink->setAttribute('href', 'dnAanmelden.html?email='.$sEmail.'&t='.$sCode);
    $sMailFromName = "Automaatje";
    $sMailFromEmail = "automaatje@automaatje.org";
    $sMailSubject = "Aanmelding ik zoek een maatje!";
    
    $oMail->setHTML($oMailHtml->saveMailHTML());
    $oMail->setFrom($sMailFromName." <".$sMailFromEmail.">");
    $oMail->setReplyTo("automaatje DEV <info@automaatje.org>");
    $oMail->setSubject($sMailSubject);
    if($oMail->send(preg_split("/;/", $sEmail))){
      $aResult["code"] = 1;
      $aResult["message"] = 'Er is een email verzonden met verdere instructies!';
    } else {
      $aResult["message"] = 'Er is helaas iets fout gegaan bij het versturen van de instructies.';
    }
  } 
} catch(Exception $oException) {
  $aResult["message"] = toUTF8($oException->getMessage());
}
print json_encode($aResult);
exit();
?>