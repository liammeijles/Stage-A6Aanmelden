<?php
$oPage = new DomDocument();
$oPage->loadHTMLFile("public/HTML/vwAanmelden.html"); 


if(!isset($_REQUEST['t'])){
  header("location: index.html");
  exit();
}

$sToken = rawurldecode($_REQUEST['t']);
$sPlek = isset($_REQUEST['plek']) ? rawurldecode($_REQUEST['plek']) : "VW";

function strip($sString){
  return (get_magic_quotes_runtime()
          ? stripslashes($sString)
          : $sString);
}

function aToUTF8($aRow){
  $aReturn = array();
  foreach($aRow as $sAttribute => $uValue){
    if(is_array($uValue)){
      $aReturn[$sAttribute] = aToUTF8($uValue);
    } elseif(!is_numeric($uValue)) {
      $aReturn[$sAttribute] = utf8_encode(strip($uValue));
    } else {
      $aReturn[$sAttribute] = $uValue;
    }
  }
  return $aReturn;
}

function textToLines($sText){
  $aLines = preg_split("/\r?\n/", utf8_encode($sText));
  return $aLines;  
}

$oSoap = new SoapClient("https://dev.infomaatje.org/admin/preview/bpmsSoap.wsdl");
try{
  /*** UPDATE ***/
  if(isset($_POST['t'])){
    $aAanmelding = array();
    $aAanmelding['t'] = $sToken;
    $aAanmelding['status'] = $_POST['status'];

    $aAanmelding['personen'] = array('vrijwilliger' => array(
                                        'naam' => $_POST['input0201'],
                                        'tussenvoegsel' => $_POST['input0501'],
                                        'achternaam' => $_POST['input0202'],
                                        'geslacht' => $_POST['input0203'],
                                        'postcode' => $_POST['input0208'],
                                        'huisnummer' => $_POST['input0209'],
                                        'straatnaam' => $_POST['input0208a'],
                                        'plaatsnaam' => $_POST['input0208b'],
                                        'mobiel' => $_POST['input0211'],
                                        'email' => $_POST['input0213'], 
                                        'beschikbaar' => json_encode($_POST['input020101']),
                                        'talenkennis' => $_POST['input020102']
                                    )
                                );
    /*$aAanmelding['gegevens']['referenties'][0] =  array(
                                    'naam' => $_POST['input0218'],
                                    'tussenvoegsel' => $_POST['input0503'],
                                    'achternaam' => $_POST['input0219'],
                                    'postcode' => $_POST['input0220'],
                                    'huisnummer' => $_POST['input0221'],
                                    'straatnaam' => $_POST['input0220a'],
                                    'plaatsnaam' => $_POST['input0220b'],
                                    'telefoon' => $_POST['input0222'],
                                    'mobiel' => $_POST['input0222a'],
                                    'email' => $_POST['input0223'],
                                    'relatie' => $_POST['input0224']
                                );
    $aAanmelding['gegevens']['referenties'][1] =  array(
                                    'naam' => $_POST['input0225'],
                                    'tussenvoegsel' => $_POST['input0504'],
                                    'achternaam' => $_POST['input0226'],
                                    'postcode' => $_POST['input0227'],
                                    'huisnummer' => $_POST['input0228'],
                                    'straatnaam' => $_POST['input0227a'],
                                    'plaatsnaam' => $_POST['input0227b'],
                                    'telefoon' => $_POST['input0229'],
                                    'mobiel' => $_POST['input0229a'],
                                    'email' => $_POST['input0230'],
                                    'relatie' => $_POST['input0231']
                                );*/
    for($i=1;$i<=$_POST['huisgenotenAantal']; $i++){
      if(!empty($_POST['input030'.$i.'0101'])){
        $aAanmelding['gegevens']['huisgenoten'][] = array(
                                    'voornaam' => $_POST['input030'.$i.'0101'],
                                    'tussenvoegsel' => $_POST['input030'.$i.'0102'],
                                    'achternaam' => $_POST['input030'.$i.'0103'],
                                    'geslacht' => $_POST['input030'.$i.'0104'],
                                    'geboortedatum' => $_POST['input030'.$i.'0105'],
                                    'relatie' => $_POST['input030'.$i.'0106']
                                ); 
      }
    }
    $aAanmelding['antwoorden'] = $_POST['antwoorden'];

    if(isset($_POST['input0500']) && $_POST['input0500']=='on'){
      $aAanmelding['afronden'] = array('status' => 'aangemeld');
    }
    $aMessage = $oSoap->SetAanmeldenVrijwilliger(aToUTF8($aAanmelding));
    
    if($aMessage['code'] != 0){
      throw new Exception($aMessage['message'], $aMessage['code']);
    }
    if(isset($_POST['input0500']) && $_POST['input0500']=='on') {
      $oPage = new DomDocument();
      $oPage->loadHTMLFile("public/HTML/aanmeldingMelding.html");
      $oMessage = $oPage->getElementById("message"); 

      if($aAanmelding['status'] == "nieuw") {
        $oP = $oPage->createElement('p', 'Fijn dat je bij ons maatje wilt worden. Een van de intakers neemt binnenkort telefonisch contact met je op.');
      } elseif($aAanmelding['status'] == "aanvullen"){
        $oP = $oPage->createElement('p', 'Bedankt voor het aanvullen van je dossier.');
      }else {
        $oP = $oPage->createElement('p', 'Fijn dat je je dossier hebt aangevuld. Een van de intakers neemt binnenkort contact met je op.');
      }
      $oMessage->appendChild($oP);
    } else {
      print(json_encode($aMessage));
      exit();
    }
  /*** AANMELDEN ***/
  } else {
    $aShow = $oSoap->getVrijwilligerAanmelding($sToken);
    if(isset($aShow['code']) && $aShow['code'] != 0){
      throw new Exception($aShow['message'], $aShow['code']);
    }
    if($sPlek == "stage") {
      $aVragenlijsten = $oSoap->GetVragenlijsten(207);
    } else {
      $aVragenlijsten = $aShow['vragenlijsten'];
    }
    $aRelaties = $aShow['relatietypes'];
    // HIDDEN FIELDS
    if($aShow['vrijwilliger']['dossier']['status'] == "aanvullen") $oPage = new webHTML('vrijwilligerAanvullenHTML');
    $oInput = $oPage->getElementById('email');
    $oInput = $oPage->getElementById('t');
    $oInput->setAttribute('value', $sToken);
    $oInput = $oPage->getElementById('status');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['dossier']['status']);
    $oInput = $oPage->getElementById('plek');
    $oInput->setAttribute('value', $sPlek);
    // VRIJWILLIGER
    $oInput = $oPage->getElementById('id0201');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['voornaam']);
    $oInput = $oPage->getElementById('id0501');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['tussenvoegsel']);
    $oInput = $oPage->getElementById('id0202');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['achternaam']);
    if(!empty($aShow['vrijwilliger']['geslacht'])){    
      $oInput = $oPage->getElementById('id0203_'.$aShow['vrijwilliger']['geslacht']);
      $oInput->setAttribute('checked', 'checked');
    }
    $oInput = $oPage->getElementById('id0208');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['postcode']);
    $oInput = $oPage->getElementById('id0209');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['huisnummer']);
    $oInput = $oPage->getElementById('id0208a');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['straatnaam']);
    $oInput = $oPage->getElementById('id0208b');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['plaatsnaam']);
    $oInput = $oPage->getElementById('id0211');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['mobiel']);
    $oInput = $oPage->getElementById('id0213');
    $oInput->setAttribute('value', $aShow['vrijwilliger']['email']);

    /*** AANVULLENDE INFORMATIE ***/
    $oDiv = $oPage->getElementById('para020101');
    $cInputs = $oDiv->getElementsByTagName('input');
    $aBeschikbaar = json_decode($aShow['vrijwilliger']['beschikbaar']);
    if(is_array($aBeschikbaar)) {
      foreach($cInputs as $oInput) {
        if(in_array($oInput->getAttribute("value"), $aBeschikbaar)) {
          $oInput->setAttribute("checked", "checked");
        }
      }
    }
    
    /*
    if (!isset($_POST['dossier'])) { 
      die(var_dump($_POST['dossier']));
} else{
      die('hij doet het!');
}*/
    $oText = $oPage->getElementById('id020102');
    $oText->nodeValue = $aShow['vrijwilliger']['dossier']['talenkennis'];
    
    // VRAGEN
    $iFieldSetNumber = $aShow['vrijwilliger']['dossier']['status'] == 'aanvullen' || $sPlek == "stage" ? 5 : 2;
    $oDivVragenlijst = $oPage->getElementById('vragenlijsten');
    foreach($aVragenlijsten as $aVragenlijst) {
      $oFieldset = $oPage->createElement('fieldset');
      $oFieldset->setAttribute('id', 'fieldset_'.$iFieldSetNumber);
      $oFieldset->setAttribute('class', 'hidden');
      $oLegend = $oPage->createElement('legend', $iFieldSetNumber.' '.$aVragenlijst['vragenlijst']['vragenlijst_omschrijving']);
      $oLegend->setAttribute('id', 'vragenlijst['.$aVragenlijst['vragenlijst']['vragenlijst'].']');
      $oFieldset->appendChild($oLegend);
      
      foreach($aVragenlijst['vragen'] as $aVraag){
        $oDiv = $oPage->createElement('div');
        $oDiv->setAttribute('id','vraag['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
        $oDiv->setAttribute('class', 'entry');
        $oLabel = $oPage->createElement('label', $aVraag['omschrijving']);
        $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
        $oLabel->setAttribute('class', 'entry');
        if(!empty($aVraag['toelichting'])){
          $oLabel->setAttribute('class', 'entry infotxtarea');
        }
        $oDiv->appendChild($oLabel);        
        switch($aVraag['type']){
          case 'alphanumeriek':
            $sValue = isset($aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']])?$aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]:"";
            $oInput = $oPage->createElement('textarea', $sValue);
            $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('placeholder', ($aVraag['waarde']));
            if($aVraag['verplicht']){
              $oInput->setAttribute('class', 'required');
              $oLabel->setAttribute('class', 'required');
            }
            $oDiv->appendChild($oInput);
            break;
          case 'datum':
          case 'numeriek':
            $oInput = $oPage->createElement('input');
            $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('value', $aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
            if($aVraag['type'] == "datum"){
              $oInput->setAttribute('class', 'formatDate');
              $oInput->setAttribute('data-value', ($aVraag['waarde']));
            } else {
              $oInput->setAttribute('placeholder', ($aVraag['waarde']));
            }
          
            $oDiv->appendChild($oInput);
            break;
          case 'select':
            $aOpties = explode(",", $aVraag['waarde']);
            $oInput = $oPage->createElement('select');
            $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            foreach($aOpties as $oOptie){
              $oOption = $oPage->createElement('option', $oOptie);
              $oOption->setAttribute('value', $oOptie);
              
              if($oOptie == $aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]){
                $oOption->setAttribute('selected', 'selected');
              }
              $oInput->appendChild($oOption);
            }
         
            $oDiv->appendChild($oInput);
            break;
          case 'checkbox':
            $aOpties = explode(",", $aVraag['waarde']);
            foreach($aOpties as $oOptie){
              $aAntwoordWaardes = explode(",", $aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput = $oPage->createElement('input');
              $oInput->setAttribute('type', 'checkbox');
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].'][]');
              $oInput->setAttribute('value', $oOptie);
              if(in_array($oOptie ,$aAntwoordWaardes)){
                $oInput->setAttribute('checked', 'checked'); 
              }
              $oLabel = $oPage->createElement('label');
              $oLabel->setAttribute('for', 'input_vraag_'.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
              $oLabel->appendChild($oInput);
              $oLabel->appendChild($oPage->createTextNode($oOptie));
              $oDiv->appendChild($oLabel);
            } 
            break;
          case 'radio':
            $aOpties = explode(",", $aVraag['waarde']);
            $i = 0;
            foreach($aOpties as $sOptie){
              $sAntwoord = isset($aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']])? $aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]: "";
              $sID = 'a' . $aVragenlijst['vragenlijst']['vragenlijst'] ."_". $aVraag['vraag'] ."_".$i++;
              $oInput = $oPage->createElement('input');
              $oInput->setAttribute('type', 'radio');
              $oInput->setAttribute('id', $sID);
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('value', $sOptie);
              if($sAntwoord == $sOptie) $oInput->setAttribute('checked', 'checked'); 
              $oLabel = $oPage->createElement('label');
              $oLabel->setAttribute('for', $sID);
              $oLabel->appendChild($oInput);
              $oLabel->appendChild($oPage->createTextNode(($sOptie)));
              $oDiv->appendChild($oLabel);
            } 
            break;
          default:
            $oInput = $oPage->createElement('textarea', $aShow['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
            $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
            $oInput->setAttribute('placeholder', ($aVraag['waarde']));
            if($aVraag['verplicht']){
              $oInput->setAttribute('class', 'required');
            }
            $oDiv->appendChild($oInput);
            break;
        }
        if($aVraag['verplicht']){
          $oInput->setAttribute('class', 'required');
          $oLabel->setAttribute('class', 'required');
        }
        if(!empty($aVraag['toelichting'])){
          $oDivInfo = $oPage->createElement('div');
          $oDivInfo->setAttribute('class', 'infobubbletxtarea hidden');
          $oDivInfoP = $oPage->createElement('p', $aVraag['toelichting']);
            
          $oDivInfo->appendChild($oDivInfoP);
          $oDiv->appendChild($oDivInfo); 
        }
        $oFieldset->appendChild($oDiv);
      } 
      $iFieldSetNumber++;
      $oDivVragenlijst->appendChild($oFieldset);
    }

    $oDivCheck = $oPage->createElement('div');
    $oDivCheck->setAttribute('id', 'para0500');
    $oDivCheck->setAttribute('class', 'entry required');
    $oLabelCheck = $oPage->createElement('label');
    $oLabelCheck->setAttribute('for', 'id0500');
    $oInputCheck = $oPage->createElement('input');
    $oInputCheck->setAttribute('id', 'id0500');
    $oInputCheck->setAttribute('type', 'checkbox');
    $oInputCheck->setAttribute('name', 'input0500');
    $oInputCheck->setAttribute('class', 'required');
    $oInputCheck->setAttribute('value', 'on');
    $oLabelCheck->appendChild($oInputCheck);
    $oLabelCheck->appendChild($oPage->createTextNode('Hiermee bevestig ik het formulier volledig naar waarheid te hebben ingevuld en dat deze aanmelding aan Budgetmaatjes 070 verzonden kan worden.'));
    $oDivCheck->appendChild($oLabelCheck);
    $oFieldset->appendChild($oDivCheck);
  }
} catch(Exception $oError){
  if(preg_match("/\.json$/i", $_SERVER['SCRIPT_NAME'])) {
    $aError = array("code" => $oError->getCode(), "message" => ($oError->getMessage()));
    die(json_encode($aError));
  }
 
  $oPage = new DomDocument();
  $oPage->loadHTMLFile("public/HTML/aanmeldingMelding.html");
  $oMessage = $oPage->getElementById("message");
  $oP = $oPage->createElement("p", ($oError->getMessage()));
  $oMessage->appendChild($oP);
  if(is_soap_fault($oError)){
    $oP = $oPage->createElement("pre", $oSoap->__getLastResponse());
    $oP->setAttribute('style', 'white-space: pre-line;');
    $oMessage->appendChild($oP);
  }
}
$cInputs = $oPage->getElementsByTagName('input');
foreach($cInputs as $oInput){
  $oInput->setAttribute('value', html_entity_decode($oInput->getAttribute('value')));
}
echo $oPage->saveHTML();
?> 
