$(document).ready(function() {

    /*** VERWIJDEREN AANWEZIGEN ***/
    function bindDel(){
      $('div#interform table:not(.beschikbaar) tr td:last-child').on('click', function(){
        var temp = $(this).parent('tr').children('td');
        temp.filter(':not(:nth-child(4))').children().val('');
        temp.filter(':nth-child(4)').children().prop('checked', false);
        temp.filter(':nth-child(5)').children().val('01-01-2000');
      });
    }
    bindDel();
  
    /*** REGELS TOEVOEGEN BIJ INVOEREN VAN EEN NIEUWE HUISGENOOT ***/
    $('table#id0301 tbody tr.new input:eq(1)').on('change',function() {
      
      /*** COPIER NIEUWE REGEL NAAR LAATSTE POSITIE ***/
      $(this).parents('tr').clone()
        .removeClass('new')
        .insertBefore($(this).parents('tr'));
      /*** WERK ID's BIJ VAN NIEUWE REGEL ***/
      $(this).parents('tr').find(':input').each(function(){
        var iId = parseInt(this.getAttribute('id').substr(2),10);
        iId += 10000;
        this.setAttribute('id','id' + ('0' + iId).slice(-8));
        this.setAttribute('name','input' + ('0' + iId).slice(-8));
        /*** RESET EERSTE VELD WAARDE ***/
        if(this.getAttribute('id').substr(8)=='05'){
          this.value = '01-01-2000';
        } else {
          if(this.selectedIndex) {
            this.selectedIndex = 0;
          } else if(this.getAttribute('type')=='radio'){
            $(this).prop('checked', false);
          } else {
            this.value = '';
          }
        }
        $('table input.formatDate').removeClass('hasDatepicker').datepicker({
          dateFormat: "dd-mm-yy", 
          showAnim: "slideDown", 
          dayNamesMin: [ "Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za" ], 
          dayNames: [ "Zondag", "Maandag", "Dinsdag", "Woensdag", "Donderdag", "Vrijdag", "Zaterdag" ], 
          monthNamesShort: [ "Jan", "Feb", "Mrt", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec" ] , 
          yearRange: "-100:+0",
          monthNames: [ "Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December" ], 
          changeMonth: true,
          changeYear: true,
          onChangeMonthYear: function(iYear, iMonth){
            var aDate = this.value.split(/\D+/);
            if(aDate == "") aDate[0] = "01";
            aDate[2] = iYear;
            aDate[1] = ("0" + (parseInt(iMonth))).slice(-2);
            this.value = aDate.join("-");
          }
        });
      });
      $(this).parents('tr').find('td').each(function(){
        var sTdHeader = this.getAttribute('headers').substr(-1);
        var iTdHeader = parseInt(this.getAttribute('headers').substr(3),10)+1;
        $(this).attr('headers', 'id0'+iTdHeader+sTdHeader);
      });
      $(this).parents('tr').each(function(){
        var trId = parseInt(this.getAttribute('id').substr(3),10)+1;
        $(this).attr('id', 'tr0'+trId);
      });
  
      /*** FOCUS OP JUISTE VELD ***/
      $(this).parents('tr').prev().find(':input:eq(2)').focus();
  
      /*** DEL OPNIEUW BINDEN ***/
      bindDel();
      $('#form02 > fieldset:nth-child(1) > input[name="huisgenotenAantal"]')
        .val(parseInt($('#form02 > fieldset:nth-child(1) > input[name="huisgenotenAantal"]').val())+1);
    });
  
  });