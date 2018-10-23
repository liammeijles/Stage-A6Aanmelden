$(document).ready(function(){
    /*** ZETTEN VAN VERPLICHTE DIVS ***/
    $('div[id^=para]').each(function() {
      if($(this).has(':input.required').length > 0){
        $(this).addClass('required');
      } else {
        $(this).removeClass('required');
      }
    })
    var countFieldsets = $("fieldset[id^='fieldset_']").length;
    var countWidth = 100 / countFieldsets;
    var currentPage = 'fieldset_0';
    
    for(i=0; i<countFieldsets; i++)
    {
      var fieldSet = $('#fieldset_' + i);
      var fieldSetName = $('#fieldset_' + i + ' legend:first').text();
      
      active = '';
      if(i == 0){
        active = 'class="active"';
      }
      
      $('#stepsback').append('<div '+ active +'><a href="#" name="fieldset_' + i + '" title="' + fieldSetName + '"></a></div>');
      $('.stepsback div').width(countWidth+'%');
    }
    
    function changeFieldset(newFieldset)
    {
      currentPage = newFieldset;
      
      $('#id0500').prop('checked', false);
      
      $("fieldset[id^='fieldset_']").hide();
      $(".stepsback div").removeClass('active');
      
      $("a[name="+currentPage+"]").parent().addClass('active');
      $('#'+$("a[name="+currentPage+"]").attr('name')).show();
    
      if($("a[name="+currentPage+"]").attr('name') === 'fieldset_0'){
        $('#vorigeButton').addClass('disabled');
      } else {
        $('#vorigeButton').removeClass('disabled');
      }
      
      if($("a[name="+currentPage+"]").attr('name') === 'fieldset_'+(countFieldsets-1)){
        $('#volgendeButton').hide();
        $('#inputSubmit').show();
      } else {
        $('#volgendeButton').show();
        $('#inputSubmit').hide();
      }
    }
    
    function updateForm()
    {
      var formFields = $('#form02').serialize();
      
      $.post(document.location.pathname.replace(/html?$/i, "json"), formFields, function(data) {
        var d = new Date();
        var string = d.getDate()+'-'+parseInt(d.getMonth()+1)+'-'+d.getFullYear()+' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds()+':'+d.getMilliseconds();
        console.log('Update uitgevoerd: ' + string);
      });
    }
    
    $('.stepsback div a').click(function(){
      changeFieldset($(this).attr('name'));
      updateForm();
      $('div.entry:has(:input.required)').find('label:first:visible').addClass('required');
      if( $('div.entry:has(:input.required)').find('label:first:visible').length > 0){
        $('div#RequiredInfo').css('display','block');
      } else {
        $('div#RequiredInfo').css('display','none');
      }
     
    });
    
    $('#volgendeButton').click(function(){
      var splitVolgende = currentPage.split('_');
      var volgende = parseInt(splitVolgende[1]) + 1;
  
      if(verifyForm(this.form)) {
        changeFieldset('fieldset_'+volgende);
        updateForm();
        $('div.entry:has(:input.required)').find('label:first:visible').addClass('required');
        if( $('div.entry:has(:input.required)').find('label:first:visible').length > 0){
             $('div#RequiredInfo').css('display','block');
        } else {
         $('div#RequiredInfo').css('display','none');
        }
      }
    });
    
    $('#vorigeButton').click(function(){
      var splitVorige = currentPage.split('_');
      var vorige = parseInt(splitVorige[1]) - 1;
      if(vorige > 0){
        changeFieldset('fieldset_'+vorige);
      }
      updateForm();
      $('div.entry:has(:input.required)').find('label:first:visible').addClass('required');
      if( $('div.entry:has(:input.required)').find('label:first:visible').length > 0){
          $('div#RequiredInfo').css('display','block');
        } else {
          $('div#RequiredInfo').css('display','none');
        }
    });
    
    $('.infotxtarea').mouseover(function(){
     $(this).parent().children('.infobubbletxtarea').show();
    });
    $('.infotxtarea').mouseout(function() {
     $(this).parent().children('.infobubbletxtarea').hide();
    });
    $('.infotxtarea').click(function() {
      $(this).parent().children('.infobubbletxtarea').toggleClass('hidden');
    });
  
    
    $('#form02').submit(function(e){
      if(verifyForm(this, true)) {
        document.getElementById("inputSubmit").disabled = true;
        $('#inputSubmit').val('Bezig met versturen');      
      } else {
        e.preventDefault();
      }
    });  
  }); 
