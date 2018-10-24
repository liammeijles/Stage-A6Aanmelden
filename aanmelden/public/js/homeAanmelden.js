if(!$){
  $ = jQuery;
}
function IsEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }

  $(document).ready(function() {
    $('#dnSubmit').css({opacity: 0.5});
    $("input#dnAkkoord, input#vwAkkoord").on('click', function() {
      if($(this).prop("checked")) {
        $("input#dnEmail").attr("placeholder", "Voer hier uw e-mailadres in, u ontvangt per omgaande verdere instructies");
        $("button#dnSubmit, button#vwSubmit").prop("disabled", false).css({opacity: 1});
      } else {
        $("input#dnEmail").attr("placeholder", "Vergeet niet onze privacyverklaring te accoderen");
        $("button#dnSubmit, button#vwSubmit").prop("disabled", true).css({opacity: 0.5});
      }
    });
    
    $("#aanmelden").on('click', '#dnSubmit', function() {  
      try {
        if($('input[name="wie"]:checked').length == 0) throw new Error('Kun je aangeven voor wie de aanvraag gedaan wordt?');
        if(!IsEmail($('#dnEmail').val())) throw new Error('Het e-mailadres is niet ingevuld of onjuist.');
          
        $('#dnSubmit').css({'background':'url(isend.png) no-repeat 142px center' , 'background-color':'#666666' , '-webkit-transition':'1s' , 'transition':'1s'});
          
        $.post("dnReqAanmelden.json", {
          wie: $('input[name="wie"]:checked').val(),
          email: $('#dnEmail').val()
        }, function(oResult) {
          if(oResult.code == 1) {
            //$('#dnSubmit').css({'background':'url(isent.png) no-repeat 102px center','background-color':'#8cc63e'});
            $('#dnMessage').html(oResult.message);
          } else {
            $('#dnMessage').html('<span class="error">' + oResult.message + '</span>');
          }
        }); 
        //vanaf hier (regel 39 t/m regel 67);
      } catch(e) {
        alert(e.message);
      }
      return false;
    });

    $("#vwAanmelden", "#dnAanmelden").on('click', '#vwSubmit', function() {  
      console.log("hoi");
      if(IsEmail($('#vwEmail').val())){
        
        $('#vwSubmit').css({'background':'url(isend.png) no-repeat 142px center' , 'background-color':'#666666' , '-webkit-transition':'1s' , 'transition':'1s'});
          
        $.post("/wp-content/plugins/aanmelden/vrijwilligerAanmeldenHome.php", {//LINK is VERANDERD VAN INCLUDE NAAR PUBLIC
          email: $('#vwEmail').val(),
        }, function(oResult) {
          if(oResult.code == 1){
            $('#vwMessage').html(oResult.message);
          }
          else{
            $('#vwMessage').html(oResult.message).addClass("error"); 

          }
        }); 
      } else {
        alert('Er moet wél een email adres worden ingevoerd.');
      }
      return false;
    });
  }); 