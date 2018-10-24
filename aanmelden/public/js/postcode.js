$(document).ready(function(){
    function postcodeCheck(oPostcode,oHuisnummer){
      var sPostcode = oPostcode.val();
      var sHuisnummer = oHuisnummer.val();
      var oStraat = oPostcode.parent().parent().find("label:contains('Straat') + input");
      var oWoonplaats = oPostcode.parent().parent().find("label:contains('Woonplaats') + input");
        $.get('postcode.php'
          , {"postcode": sPostcode, "huisnummer": sHuisnummer}
          , function(oAdres){
              oWoonplaats.val(oAdres.city ? oAdres.city : ""); 
              oStraat.val(oAdres.street ? oAdres.street : ""); 
              if(oAdres.city==null && sPostcode.length > 0){
                oPostcode.css('border-color', 'red');
                oHuisnummer.css('border-color', 'red');
              } else if(oAdres.houseNumber == null && sPostcode.length > 0){
                oPostcode.removeAttr('style');
                oHuisnummer.css('border-color', 'red');
              } else {
                oPostcode.removeAttr('style');
                oHuisnummer.removeAttr('style');
              }
            });      
      
    }
    
   function prettyPostcode(oPostcode){
     if(/^\d{4}\s*[a-z]{2}$/i.test(oPostcode.val())) {
       var sPostcode = oPostcode.val().replace(/\s/g, "").toUpperCase();
       oPostcode.val(sPostcode.substr(0,4) + " " + sPostcode.substr(-2));
     }
   }
   
   $('label:contains(Postcode) + input').each(function(){
     var oPostcode = $(this);
     var oHuisnummer = $(this).parent().parent().find("label:contains('Huisnummer') + input");
     var oStraat = oPostcode.parent().parent().find("label:contains('Straat') + input");
     oHuisnummer.bind('change', function(){
       postcodeCheck(oPostcode, $(this));
     });
     oPostcode.bind('change', function(){
       prettyPostcode($(this));
       postcodeCheck($(this), oHuisnummer);
     });
     /*** NA INLEZEN ***/
     
     if(this.value.length > 0 && oStraat.val().length == 0) {
       postcodeCheck(oPostcode, oHuisnummer);
       prettyPostcode(oPostcode);
     }
   });
 });