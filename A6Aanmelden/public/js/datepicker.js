$(document).ready(function() {
    var aWeekDays = ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'];
    $('input.formatDate').datepicker({
      dateFormat: "dd-mm-yy", 
      showAnim: "slideDown", 
      dayNamesMin: [ "Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za" ], 
      dayNames: [ "Zondag", "Maandag", "Dinsdag", "Woensdag", "Donderdag", "Vrijdag", "Zaterdag" ], 
      monthNamesShort: [ "Jan", "Feb", "Mrt", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec" ] , 
      yearRange: "-100:+0",
      monthNames: [ "Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December" ], 
      changeMonth: true,
      changeYear: true,
      beforeShowDay : function(date){
        if(this.getAttribute('data-value')) {
          var dToday = new Date();
          dToday.setHours(0);
          if(date < dToday) return [false, ''];
          var aDays = this.getAttribute('data-value').toLowerCase().split(/[\s\,\;]+/);
          var sDay = aWeekDays[date.getDay()];
          for(var d in aDays) {
            if(aDays[d].substr(0,3).toLowerCase() == sDay) return [true, ''];
          }
          return [false, ''];
        }
        return [true,''];
      }, 
      onChangeMonthYear: function(iYear, iMonth){
        var aDate = this.value.split(/\D+/);
        if(aDate == "") aDate[0] = "01";
        aDate[2] = iYear;
        aDate[1] = ("0" + (parseInt(iMonth))).slice(-2);
        this.value = aDate.join("-");
      }
    }); 
  });
