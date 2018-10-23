<?php
class a6Postcode {
    const API_URL_POSTCODE_NL = 'https://api.postcode.nl/rest/';
    const API_URL_POSTCODE_NU = 'https://postcode-api.apiwise.nl/v2/addresses/';
    const API_FREE = 'https://geodata.nationaalgeoregister.nl/locatieserver/v3/free';
    const API_FREE_PFX_ADR = '&fq=type:adres';
    const API_FREE_PFX_PC = '&fq=type:postcode';
  
    private $street = "";
    private $firstHouseNumber = null;
    private $houseNumber = null;
    private $houseNumberAdditions = "";
    private $postcode = "";
    private $city = null;
    private $municipality = "";
    private $province = "";
    private $latitude = 0;
    private $longitude = 0;
    private $rdX = 0;
    private $rdY = 0;
    private $addressType = "";
    private $API = "";
  
    private static function getHouseNumber($sHouseNumber) {
      $iHouseNumber = NULL;
      $aHouseAdd = array();
      if(preg_match("/^\s*([0-9]+).*/", $sHouseNumber, $aHouseAdd)) $iHouseNumber = (int) $aHouseAdd[1];
      return $iHouseNumber;
    }
    static function postCodeFree($sPostalCode, $sHouseNumber = NULL){
      $sPostalCode = strToUpper(preg_replace("/[^a-z0-9]/i", "", $sPostalCode));
      if(isset($sHouseNumber)) {
        $sUrl = self::API_FREE . '?q=' . urlencode('"' . $sPostalCode . " " . $sHouseNumber . '"'). self::API_FREE_PFX_ADR;
      } else {
        $sUrl = self::API_FREE . '?q=' . urlencode('"' . $sPostalCode . '"'). self::API_FREE_PFX_PC;
      }
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $sUrl);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      $__sResponse = curl_exec($ch);
      /*** NAAR COMMON FORMAT ***/
      $__oResult = new a6Postcode();
      $__aResult = json_decode($__sResponse, true);
      if(isset($__aResult['response']['numFound']) && $__aResult['response']['numFound'] > 0) {
        $__oResult->setAttribute('postcode', $__aResult['response']['docs'][0]['postcode']);
        $__oResult->setAttribute('street', $__aResult['response']['docs'][0]['straatnaam']);
        if(isset($__aResult['response']['docs'][0]['huisnummer'])) $__oResult->setAttribute('firstHouseNumber',$__aResult['response']['docs'][0]['huisnummer'] );
        if(isset($__aResult['response']['docs'][0]['huisnummer'])) {
          $__oResult->setAttribute('houseNumber', $__aResult['response']['docs'][0]['huisnummer']);
          if(isset($__aResult['response']['docs'][0]['huisletter'])) {
            $__oResult->setAttribute('houseNumberAdditions', $__aResult['response']['docs'][0]['huisletter']);
          }
        }
        $__oResult->setAttribute('city', $__aResult['response']['docs'][0]['woonplaatsnaam']);
        $__oResult->setAttribute('municipality', $__aResult['response']['docs'][0]['gemeentenaam']);
        $__oResult->setAttribute('province', $__aResult['response']['docs'][0]['provincienaam']);
        $aCoor = preg_split("/[\(\)\s]/", $__aResult['response']['docs'][0]['centroide_ll']);
        if(count($aCoor) > 2) {
          $__oResult->setAttribute('latitude', $aCoor[2]);
          $__oResult->setAttribute('longitude', $aCoor[1]);
        }
        $__oResult->setAttribute('addressType', $__aResult['response']['docs'][0]['type']);
      }
      $__oResult->setAttribute('API', "nationaalgeoregister.nl");
      return $__oResult;
    }
  
    static function postCodeNl($sPostalCode, $sHouseNumber) {
      $__sResponse = "{exception: 'unknown'}";
  
      $iHouseNumber = self::getHouseNumber($sHouseNumber);
      $sPostalCode = preg_replace("/[^0-9a-z]/i", "", $sPostalCode);
      if(preg_match("/^\d{4}[a-z]{2}$/i", $sPostalCode)) {
        $ch = curl_init();
        $url = self::API_URL_POSTCODE_NL.'/addresses/'.$sPostalCode.'/'.$iHouseNumber.'/';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, self::API_NL_KEY .':'.self::API_NL_SECRET);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PostcodeNl_Api_RestClient/1.1.1.0 PHP/'. phpversion());
        $__sResponse = curl_exec($ch);
        curl_close( $ch );
      }
      /*** NAAR COMMON FORMAT ***/
      $__oResult = new a6Postcode();
      $__aResult = json_decode($__sResponse, true);
      foreach($__oResult as $sAttribute => $uValue) {
        $__oResult->$sAttribute = $__aResult[$sAttribute];
      }
      $__oResult->setAttribute('API', "postcode.nl");
      return $__oResult;
    }
    
    static function postCodeNu($sPostalCode, $sHouseNumber = NULL) {
      $__sResponse = "{status: 'unknown'}";
      $iHouseNumber = $sHouseNumber ? self::getHouseNumber($sHouseNumber) : NULL;
      $sPostalCode = preg_replace("/[^0-9a-z]/i", "", $sPostalCode);
      if(preg_match("/^\d{4}[a-z]{2}$/i", $sPostalCode)) {
        $sUrl = self::API_URL_POSTCODE_NU . "?postcode=" . $sPostalCode . ($iHouseNumber ? "&number=" . $iHouseNumber : "");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "X-Api-Key: " . self::API_NU_KEY));
        $__sResponse = curl_exec( $ch );
        curl_close( $ch );
      }
      $__aResult = json_decode($__sResponse, true);
      $__oResult = new a6Postcode();
      if(is_array($__aResult["_embedded"]["addresses"][0])) {  
        $__aAddress = $__aResult["_embedded"]["addresses"][0];
        foreach($__oResult as $sAttribute => $uValue) {
          switch($sAttribute) {
            case "houseNumber";
              if($iHouseNumber == (int) $__aAddress["number"])  {
                $__oResult->houseNumber = $__aAddress["number"];
              } else {
                $__oResult->firstHouseNumber = $__aAddress["number"];
              }
              break;
            case "latitude";
              $__oResult->latitude = $__aAddress["geo"]["center"]["wgs84"]["coordinates"][1];
              break;
            case "longitude";
              $__oResult->longitude = $__aAddress["geo"]["center"]["wgs84"]["coordinates"][0];
              break;
            case "rdX";
              $__oResult->rdX = $__aAddress["geo"]["center"]["rd"]["coordinates"][1];
              break;
            case "rdY";
              $__oResult->rdY = $__aAddress["geo"]["center"]["rd"]["coordinates"][0];
              break;
            case "addressType";
              $__oResult->addressType = $__aAddress["purpose"];
              break;
            default:
              $__oResult->$sAttribute = isset($__aAddress[$sAttribute]['label']) ? $__aAddress[$sAttribute]['label'] : $__aAddress[$sAttribute];
              break;
          }
        }
      }
      $__oResult->setAttribute('API', "postcodeapi.nu");
      return $__oResult;
    }
  
    public function __construct($sPostalCode = NULL, $sHouseNumber = NULL) {
      /*** POSTALCODE AND HOUSENUMBER ***/
      if($sPostalCode && $sHouseNumber) {
        foreach(self::postCodeFree($sPostalCode, $sHouseNumber) as $sAttribute => $uValue){
          $this->$sAttribute = $uValue;
        }
  
        /*** IF NOT FOUND USE POSTCODE FREE ON POSTALCODE ONLY ***/
        if(strLen($this->street) < 2) {
          foreach(self::postCodeFree($sPostalCode) as $sAttribute => $uValue){
            $this->$sAttribute = $uValue;
          }
        }
        /*** IF NOT FOUND USE POSTCODE NU ***/
        if(strLen($this->street) < 2) {
          foreach(self::postCodeNu($sPostalCode, $sHouseNumber) as $sAttribute => $uValue){
            $this->$sAttribute = $uValue;
          }
        }
  
        /*** IF NOT FOUND USE POSTCODE NL ***/
        if(strLen($this->street) < 2) {
          foreach(self::postCodeNl($sPostalCode, $sHouseNumber) as $sAttribute => $uValue){
            $this->$sAttribute = $uValue;
          }
        }
      } 
      
      /*** POSTALCODE ONLY ***/
      if(strLen($this->street) < 2 && $sPostalCode)  {
        foreach(self::postCodeFree($sPostalCode) as $sAttribute => $uValue){
          $this->$sAttribute = $uValue;
        }
  
        if(strLen($this->street) < 2) {
          foreach(self::postCodeNu($sPostalCode) as $sAttribute => $uValue){
            $this->$sAttribute = $uValue;
          }
        }
      }
    }
      
    public function getAttribute($sAttribute) {
      return $this->$sAttribute;
    }
  
    public function setAttribute($sAttribute, $uValue) {
      return  $this->$sAttribute = $uValue;
    }
    
    public function toJSON() {
      $aData = array();
      foreach($this as $sAttribute => $uValue) {
        //$aData[$sAttribute] = is_numeric($uValue) ? $uValue : toUTF8($uValue);
        $aData[$sAttribute] = $uValue;
      }
      return json_encode($aData);
    }
  }
  ?>