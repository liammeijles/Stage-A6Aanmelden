<?
if(!(isset($_REQUEST['t']) && isset($_REQUEST['email']))){
  header("location: index.html");
  exit();
}

$sEmail = rawurldecode($_REQUEST['email']);
$sT = rawurldecode($_REQUEST['t']);

function strip($sString){
  return (magic_quotes_runtime 
          ? stripslashes($sString)
          : $sString);
}

function aToUTF8($aRow){
  $aReturn = array();
  foreach($aRow as $sAttribute => $uValue){
    if(is_array($uValue)){
      $aReturn[$sAttribute] = aToUTF8($uValue);
    } elseif(!is_numeric($uValue)) {
      $aReturn[$sAttribute] = toUTF8(strip($uValue));   
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

try{
  $oSoap = new a6SoapClient();
  switch($_SERVER['REQUEST_METHOD']) {
    case "POST":
      $aAanmelding = array();
      $aAanmelding['email'] = $sEmail;
      $aAanmelding['t'] = $sT;
      $aAanmelding['dossier'] = $_POST['dossier'];
      
      /*** AANMELDER IF NOT DEELNEMER ***/
      if(isset($_POST['input0236'])) {
        $aAanmelding['aanmelder']= array('naam_instantie' => $_POST['input0235'],
                                        'naam_contactpersoon' => $_POST['input0236'],
                                        'geslacht' => $_POST['input0237'],
                                        'werkdagen' => $_POST['input0238'],
                                        'postcode' => $_POST['input0239'],
                                        'huisnummer' => $_POST['input0240'],
                                        'straatnaam' => $_POST['input0239a'],
                                        'plaatsnaam' => $_POST['input0239b'],
                                        'telefoon' => $_POST['input0241'],
                                        'mobiel' => $_POST['input0241a'],
                                        'email' => $_POST['input0242'],
                                        'eerste_contact' => $_POST['input0243'],
                                        'frequentie_contact' => $_POST['input0244'],
                                        'toekomst_contact' => $_POST['input0245'],
                                        'andere_instanties' => $_POST['input0246']
                                      );
      }
      /*** DEELNEMER ***/
      $aAanmelding['deelnemer'] = array(
                                          'naam' => $_POST['input0201'],
                                          'tussenvoegsel' => $_POST['input0501'],
                                          'achternaam' => $_POST['input0202'],
                                          'geslacht' => $_POST['input0203'],
                                          'eerderAangemeld' => $_POST['input0504'],
                                          'geboortedatum' => $_POST['input0204'],
                                          'geboorteplaats' => $_POST['input0205'],
                                          'landvanherkomst' => $_POST['input0206'],
                                          'postcode' => $_POST['input0208'],
                                          'huisnummer' => $_POST['input0209'],
                                          'straatnaam' => $_POST['input0208a'],
                                          'plaatsnaam' => $_POST['input0208b'],
                                          'telefoon' => $_POST['input0210'],
                                          'mobiel' => $_POST['input0210a'],
                                          'email' => $_POST['input0211'],
                                          'beschikbaar' => json_encode($_POST['input020101'])
                                        );
      
      /*** HUISGENOTEN IF ANY ***/
     for($i=1;$i<=$_POST['huisgenotenAantal']; $i++){
        if(!empty($_POST['input030'.$i.'0101'])){
          $aAanmelding['huisgenoten'][] = array('voornaam' => $_POST['input030'.$i.'0101'],
                                      'tussenvoegsel' => $_POST['input030'.$i.'0102'],
                                      'achternaam' => $_POST['input030'.$i.'0103'],
                                      'geslacht' => $_POST['input030'.$i.'0104'],
                                      'geboortedatum' => $_POST['input030'.$i.'0105'],
                                      'relatie' => $_POST['input030'.$i.'0106']
                                    ); 
        }
      }
          
            
      $aAanmelding['antwoorden'] = $_POST['antwoorden'];
      
      if($_POST['input0500']=='on'){
        $aAanmelding['afronden'] = array('status' => 'aangemeld');
      }

      $aMessage = $oSoap->setAanmelding(aToUTF8($aAanmelding));
        
      if($_POST['input0500']=='on'){
        $aVragenlijsten = $oSoap->GetVragenlijsten(205);
        foreach($aVragenlijsten as $aVragenlijst){
          $aAanmelding['vragenlijst'][$aVragenlijst['vragenlijst']['vragenlijst']]['vragenlijst'] = array('vragenlijst' => $aVragenlijst['vragenlijst']['vragenlijst']);
          
          foreach($aVragenlijst['vragen'] as $aVraag){
            $aAanmelding['vragenlijst'][$aVragenlijst['vragenlijst']['vragenlijst']]['vragen'][] = array('vraag' => $aVraag['vraag'],
                                                    'antwoord' => $_POST['input_vraag_'.$aVragenlijst['vragenlijst']['vragenlijst'].'_'.$aVraag['vraag']]
                                                  );
          }
        }
        
        $oMailHTML = new webHTML('bevestigingsAanmeldingMailHTML');
        $hForm = new webHTML('dnAanmeldenHTML');
        $cFieldset = $hForm->documentElement->getElementsByTagName("fieldset");
        $oSpanTables = $oMailHTML->getElementById('tables');
        foreach ($cFieldset as $oFieldset) {
          if($oFieldset->getElementsByTagName('fieldset')->length < 1 && ($oFieldset->getElementsByTagName('label')->length > 0 || strtolower($oFieldset->getElementsByTagName('legend')->item(0)->nodeValue) == 'overige gezinsleden')){
            $oTable = $oMailHTML->createElement('table');
            $oTHead = $oMailHTML->createElement('thead');
            $oTr = $oMailHTML->createElement('tr');
            $oTh = $oMailHTML->createElement('th', $oFieldset->getElementsByTagName('legend')->item(0)->nodeValue);
            $oTh->setAttribute('colspan', '2');
            $oTr->appendChild($oTh);
            $oTHead->appendChild($oTr);
            $oTable->appendChild($oTHead);
            $oTBody = $oMailHTML->createElement('tbody');
            if(strtolower($oFieldset->getElementsByTagName('legend')->item(0)->nodeValue) == 'overige gezinsleden'){
              $oTh->setAttribute('colspan', '6');
              $oTr = $oMailHTML->createElement('tr');
              $oTh1 = $oMailHTML->createElement('th', 'Naam');
              $oTh2 = $oMailHTML->createElement('th', 'Tussenvoegsel');
              $oTh3 = $oMailHTML->createElement('th', 'Achternaam');
              $oTh4 = $oMailHTML->createElement('th', 'Geslacht');
              $oTh5 = $oMailHTML->createElement('th', 'Geboortedatum');
              $oTh6 = $oMailHTML->createElement('th', 'Relatie');
              $oTr->appendChild($oTh1);
              $oTr->appendChild($oTh2);
              $oTr->appendChild($oTh3);
              $oTr->appendChild($oTh4);
              $oTr->appendChild($oTh5);
              $oTr->appendChild($oTh6);
              $oTBody->appendChild($oTr);
              for($i=1; $i<=$_POST['huisgenotenAantal']; $i++) {
                if(!empty($_POST['input030'.$i.'0101'])){
                  $oTr = $oMailHTML->createElement('tr');
                  for($j = 1; $j<=5;$j++){
                    $oTd = $oMailHTML->createElement('td', utf8_encode($_POST['input030'.$i.'010'.$j]));
                    $oTr->appendChild($oTd);
                  }
                  $aRelaties = $oSoap->getRelaties();
                  foreach ($aRelaties as $aRelatie) {
                    if($_POST['input030'.$i.'0106']==$aRelatie['value']){
                      $oTd = $oMailHTML->createElement('td', $aRelatie['waarde']);
                      $oTr->appendChild($oTd);
                    }
                  }
                  $oTBody->appendChild($oTr);
                }
              }
            } else {
              $cLabel = $oFieldset->getElementsByTagName("label");
              foreach ($cLabel as $oLabel) {
                $sString = preg_replace("/id/","input",$oLabel->getAttribute("for"));
                if(!empty($_POST[$sString])){
                  $sValue = $_POST[$sString];
                  $oTr = $oMailHTML->createElement('tr');
                  $oTh = $oMailHTML->createElement('th', $oLabel->nodeValue);
                  $sString = preg_replace("/id/","input",$oLabel->getAttribute("for"));
                  switch(strToLower($oLabel->nodeValue)) {
                    case "geslacht":
                      $oTd = $oMailHTML->createElement('td', $_POST[$sString]);
                      $oTr->appendChild($oTh);
                      $oTr->appendChild($oTd);
                      $oTBody->appendChild($oTr);
                      break;
                    case "beschikbaar":  
                      $oTd = $oMailHTML->createElement('td');
                      $oTab = $oMailHTML->createElement('table');
                      $oTrTab = $oMailHTML->createElement('tr');
                      $oThTab = $oMailHTML->createElement('th', "&nbsp;");
                      $oTrTab->appendChild($oThTab);
                      $oThTab = $oMailHTML->createElement('th', "ochtend");
                      $oTrTab->appendChild($oThTab);
                      $oThTab = $oMailHTML->createElement('th', "middag");
                      $oTrTab->appendChild($oThTab);
                      $oThTab = $oMailHTML->createElement('th', "avond");
                      $oTrTab->appendChild($oThTab);
                      $oTab->appendChild($oTrTab);
                      $aDays = array("A" => "maa", "B" => "din", "C" => "woe", "D" => "don", "E" => "vrij", "F" => "zat", "G" => "zon");
                      
                      foreach($aDays as $kDay => $sDay) {
                        $aBeschikbaar = $_POST[$sString];
                        $oTrTab = $oMailHTML->createElement('tr');
                        $oThTab = $oMailHTML->createElement('th', $sDay);
                        $oTrTab->appendChild($oThTab);
                        for($i = 1; $i < 4; $i++) {
                          $oTdTab = $oMailHTML->createElement('td', in_array(($kDay . $i), $aBeschikbaar) ? "x" : "&nbsp;");
                          $oTrTab->appendChild($oTdTab);                    
                        } 
                        $oTab->appendChild($oTrTab);
                      }
                      $oTd->appendChild($oTab);
                    break;
                    default: 
                      $sValue = $_POST[$sString];
                      $oTd = $oMailHTML->createElement('td', utf8_encode($sValue));
                      break;
                  }
                  $oTr->appendChild($oTh);
                  $oTr->appendChild($oTd);
                  $oTBody->appendChild($oTr);
                } elseif(strtolower($oLabel->nodeValue) == 'geslacht') {
                  $oTr = $oMailHTML->createElement('tr');
                  $oTh = $oMailHTML->createElement('th', $oLabel->nodeValue);
                  $oTd = $oMailHTML->createElement('td', $_POST['input0203']);
                  $oTr->appendChild($oTh);
                  $oTr->appendChild($oTd);
                  $oTBody->appendChild($oTr);
                }
              }
            }
            $oTable->appendChild($oTBody);
            $oSpanTables->appendChild($oTable);
          }
        }
        foreach($aVragenlijsten as $aVragenlijst) {
          $oTable = $oMailHTML->createElement('table');
          $oTHead = $oMailHTML->createElement('thead');
          $oTr = $oMailHTML->createElement('tr');
          $oTh = $oMailHTML->createElement('th', $aVragenlijst['vragenlijst']['vragenlijst_omschrijving']);
          $oTh->setAttribute('colspan', '2');
          $oTr->appendChild($oTh);
          $oTHead->appendChild($oTr);
          $oTable->appendChild($oTHead);
          $oTBody = $oMailHTML->createElement('tbody'); 
          foreach($aVragenlijst['vragen'] as $aVraag) {
            $oTr = $oMailHTML->createElement('tr');
            $oTh = $oMailHTML->createElement('th', $aVraag['omschrijving']);
            $oTr->appendChild($oTh);
            $aAntwoorden = $_POST['antwoorden'];
            if(is_array($aAntwoorden[$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']])){
              $oTd = $oMailHTML->createElement('td', implode(',', utf8_encode($aAntwoorden[$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']])));
            } else {
              $aLines = textToLines($aAntwoorden[$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oTd = $oMailHTML->createElement('td');
              foreach($aLines as $sLine){
                $oP = $oMailHTML->createElement('p', $sLine);
                $oTd->appendChild($oP);
              }
            }
            $oTr->appendChild($oTd);
            $oTBody->appendChild($oTr);
          }
          $oTable->appendChild($oTBody);
          $oSpanTables->appendChild($oTable);
        }
        $sSubject = 'Aanmelding: Ik zoek een budgetmaatje!';
        $oMail = new webMail();
        $oMail->setHTML($oMailHTML->saveMailHTML());
        $oMail->setFrom("Automaatje <automaatje@anan6.com>");
        $oMail->setSubject($sSubject);
        $oPage = new webHTML('dnAanmeldingMeldingHTML'); 
        $oMessage = $oPage->getElementById("message");
        
        if($oMail->send(array($sEmail))){
          $oP = $oPage->createElement('p', 'Dank voor de aanmelding. Je ontvangt een mail ter bevestiging en met verdere informatie.');
        } else {
          $oP = $oPage->createElement('p', 'Er is een fout opgetreden met het versturen van de aanmelding. Probeert u het later nog eens.');
        }
        $oMessage->appendChild($oP);
      } else {
        print(json_encode($aMessage));
        exit();
      }
      break;
    case "GET":
      $oDeelnemer = (object) ['sEmail' => $sEmail, 'sToken' => $sT, 'bVragenLijst' => 'ja'];
      $aAanmelding = $oSoap->getAanmelding($oDeelnemer);
        //die(var_dump($aAanmelding));
      if($aAanmelding['code'] != 0){
        throw new Exception($aAanmelding['message'], $aAanmelding['code']);
      }

      $aVragenlijsten = $aAanmelding['vragenlijsten'];
      $aRelaties = $aAanmelding['relatietypes'];
      
      /*** INIT PAGE & FORM ***/
      $oPage = new webHTML('dnAanmeldenHTML');
      $oInput = $oPage->getElementById('email');
      $oInput->setAttribute('value', $sEmail);
      $oInput = $oPage->getElementById('t');
      $oInput->setAttribute('value', $sT);
      $oInput = $oPage->getElementById('dossier');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['dossier']['dossier']);
      
      /*** AANMELDER IF ANY ***/
      if(isset($aAanmelding['aanmelder'])) {
        $oInput = $oPage->getElementById('id0236');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['voornaam']);
        if(!empty($aAanmelding['aanmelder']['persoon']['geslacht'])){
          $oInput = $oPage->getElementById('id0237_'.$aAanmelding['aanmelder']['persoon']['geslacht']);
          $oInput->setAttribute('checked', 'checked');
        }
        $oInput = $oPage->getElementById('id0241a');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['mobiel']);
        $oInput = $oPage->getElementById('id0242');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['email']);
        $oInput = $oPage->getElementById('id0238');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['werkdagen']);
        
        $oInput = $oPage->getElementById('id0235');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['naam']);
        $oInput = $oPage->getElementById('id0239');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['postcode']);
        $oInput = $oPage->getElementById('id0239a');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['straatnaam']);
        $oInput = $oPage->getElementById('id0239b');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['plaatsnaam']);
        $oInput = $oPage->getElementById('id0240');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['huisnummer']);
        $oInput = $oPage->getElementById('id0241');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['telefoon']);
      } else {
        $cFieldsets = $oPage->getElementsByTagName('fieldset');
        $i = 0;
        $oDelField = null;
        foreach($cFieldsets as $oFieldset) {
          if(!$oFieldset->hasAttribute('id')) continue;
          if($oFieldset->getAttribute('id') == "fieldset_1") {
            $oDelField = $oFieldset;
          } else {
            $oFieldset->setAttribute('id', 'fieldset_' . $i++);
          }
        }
        if($oDelField) $oDelField->parentNode->removeChild($oDelField);
      }

      /*** DEELNEMER ***/
      $oInput = $oPage->getElementById('id0201');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['voornaam']);
      $oInput = $oPage->getElementById('id0501');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['tussenvoegsel']);
      $oInput = $oPage->getElementById('id0202');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['achternaam']);
      if(!empty($aAanmelding['deelnemer']['geslacht'])){    
        $oInput = $oPage->getElementById('id0203_'.$aAanmelding['deelnemer']['geslacht']);
        $oInput->setAttribute('checked', 'checked');
      }
      $oInput = $oPage->getElementById('id0204');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['geboortedatum']);
      $oInput = $oPage->getElementById('id0208');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['postcode']);
      $oInput = $oPage->getElementById('id0208a');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['straatnaam']);
      $oInput = $oPage->getElementById('id0208b');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['plaatsnaam']);
      $oInput = $oPage->getElementById('id0209');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['huisnummer']);
      $oInput = $oPage->getElementById('id0210');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['telefoon']);
      $oInput = $oPage->getElementById('id0210a');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['mobiel']);
      $oInput = $oPage->getElementById('id0211');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['email']);
        
      /*** AANVULLENDE INFOMATIE ***/
      $oDiv = $oPage->getElementById('para020101');
      $cInputs = $oDiv->getElementsByTagName('input');
      $aBeschikbaar = json_decode($aAanmelding['deelnemer']['beschikbaar']);
      if(is_array($aBeschikbaar)) {
        foreach($cInputs as $oInput) {
          if(in_array($oInput->getAttribute("value"), $aBeschikbaar)) {
            $oInput->setAttribute("checked", "checked");
          }
        }
      }
      /*** VRAGEN ***/
      $iFieldSetNumber = 3;
      $oDivVragenlijst = $oPage->getElementById('vragenlijsten');
      foreach($aVragenlijsten as $aVragenlijst) {
        $oFieldset = $oPage->createElement('fieldset');
        $oFieldset->setAttribute('id', 'fieldset_'.$iFieldSetNumber);
        $oFieldset->setAttribute('class', 'hidden');
        $oLegend = $oPage->createElement('legend', $iFieldsetNumber.' '.$aVragenlijst['vragenlijst']['vragenlijst_omschrijving']);
        $oLegend->setAttribute('id', 'vragenlijst['.$aVragenlijst['vragenlijst']['vragenlijst'].']');
        $oFieldset->appendChild($oLegend);
        
        foreach($aVragenlijst['vragen'] as $aVraag) {
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
              $oInput = $oPage->createElement('textarea', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
            case 'numeriek':
              $oInput = $oPage->createElement('input');
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('value', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
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
                if($oOptie == $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]){
                  $oOption->setAttribute('selected', 'selected');
                }
                $oInput->appendChild($oOption);
              }
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
            case 'checkbox':
              $aOpties = explode(",", $aVraag['waarde']);
              foreach($aOpties as $oOptie){
                $aAntwoordWaardes = explode(",", $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
                $oInput = $oPage->createElement('input');
                $oInput->setAttribute('type', 'checkbox');
                $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
                $oInput->setAttribute('value', $oOptie);
                if(in_array($oOptie ,$aAntwoordWaardes)){
                  $oInput->setAttribute('checked', 'checked'); 
                }
                $oLabel = $oPage->createElement('label');
                $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oLabel->appendChild($oInput);
                $oLabel->appendChild($oPage->createTextNode($oOptie));
                $oDiv->appendChild($oLabel);
              } 
              break;
            case 'radio':
              $aOpties = explode(",", $aVraag['waarde']);
              foreach($aOpties as $oOptie){
                $aAntwoordWaardes = explode(",", $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
                $oInput = $oPage->createElement('input');
                $oInput->setAttribute('type', 'radio');
                $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
                $oInput->setAttribute('value', $oOptie);
                if(in_array($oOptie ,$aAntwoordWaardes)){
                  $oInput->setAttribute('checked', 'checked'); 
                }
                $oLabel = $oPage->createElement('label');
                $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oLabel->appendChild($oInput);
                $oLabel->appendChild($oPage->createTextNode($oOptie));
                $oDiv->appendChild($oLabel);
              } 
              break;
            default:
              $oInput = $oPage->createElement('textarea', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
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
      $oLabel = $oPage->createElement('label');
      $oLabel->setAttribute('for', 'id0500');
      $oInput = $oPage->createElement('input');
      $oInput->setAttribute('id', 'id0500');
      $oInput->setAttribute('type', 'checkbox');
      $oInput->setAttribute('name', 'input0500');
      $oInput->setAttribute('class', 'required');
      $oInput->setAttribute('value', 'on');
      $oLabel->appendChild($oInput);
      $oLabel->appendChild($oPage->createTextNode('Hiermee bevestig ik het formulier volledig naar waarheid te hebben ingevuld en dat deze aanmelding aan Budgetmaatjes 070 verzonden kan worden.'));
      $oDivCheck->appendChild($oLabel);
      $oFieldset->appendChild($oDivCheck);
      
      /*** OPSCHONEN INVOERVELDEN ***/
      $cInputs = $oPage->getElementsByTagName('input');
      foreach($cInputs as $oInput){
        $oInput->setAttribute('value', html_entity_decode($oInput->getAttribute('value')));
      }
      break;
  }
} catch(Exception $oError){
  $oPage = new webHTML('dnAanmeldingMeldingHTML'); 
  $oMessage = $oPage->getElementById("message");
  $oP = $oPage->createElement("p", toUTF8($oError->getMessage()));
  $oMessage->appendChild($oP);
  if(is_soap_fault($oError)){
    $oP = $oPage->createElement("pre", $oSoap->__getLastResponse());
    $oMessage->appendChild($oP);
  }
}
return $oPage->saveHTML();
?>