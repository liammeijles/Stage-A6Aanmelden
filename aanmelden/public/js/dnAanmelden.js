$(document).ready(function(){
    $('input[name="input0100"]').on('click', function(){
      if(this.value.toUpperCase() != "A") {
        $('fieldset#fieldset_1').addClass("ignore");
      } else {
        $('fieldset#fieldset_1').removeClass("ignore");
      }
    });
  });