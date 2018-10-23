 /*
 Error messages ... 
*/
var aMessage=new Object();
aMessage[20000]="Het `^` veld is verplicht.^";
aMessage[20001]="Gelieve de vraag `^` te beantwoorden.";
aMessage[20002]="De waarde van het veld `^` voldoet niet aan het verwachte patroon.\n^";
aMessage[20003]="De `maand` is incorrect. ";
aMessage[20004]="De `dag` is incorrect.";
aMessage[20005]="Het `jaar` is incorrect.";
aMessage[20006]="De waarde van het `^` veld is geen geldig email adres.";
aMessage[20007]="De waarde van het `^` veld dient een getal zonder decimalen te zijn.";
aMessage[20008]="De waarde van het `^` veld dient een getal met maximaal ^ decimalen te zijn.";
aMessage[20009]="De waarde van het `^` veld voldoet niet aan een geldig tijdsformaat (UU:MM).";
aMessage[20010]="De waarde van het wachtwoord `^` komt niet overeen met de waarde van het controle veld `^`.";
/*
 Function converts message to Error with descArguments, 2 possibilities : 
 - By order 1st ^ encountered  replaced with 1st argument and 2nd ^ replaced with 2nd argument &c.
 - By number all ^1 occurences encountered replaced with 1st argument and all ^3 with 3rd argument 
*/
String.prototype.toError=function() {
 var sErrorMessage=this;
 for(var __a=0;__a<arguments.length;__a++) {
  var rCaret=new RegExp("\\^"+(__a+1),"g");
  sErrorMessage=rCaret.test(sErrorMessage)?sErrorMessage.replace(rCaret,arguments[__a]):sErrorMessage.replace(/\^(?!\d)/,arguments[__a]);
 }
 return new Error(sErrorMessage);
}
//init form fields ... 
var oForm=null;
//select field on focus ... 
function selectElement() {
  this.select();
}
function setInitValues() {//Deze functie zet de Values vast
  var cForms=document.forms; 
  for(var f=0;f<cForms.length;f++) {
   var cElements=cForms[f].elements;
   for(var e=0;e<cElements.length;e++) {
    switch(cElements[e].type) {
    case "text":
    case "textarea":
     //cElements[e].onfocus=selectElement;
     //cElements[e].setAttribute("initValue",cElements[e].value);
     break;
    default: 
     continue;
     break;
   }
   }
  }
}
//call onload 
if(window.attachEvent) {
 window.attachEvent("onload",setInitValues);
} else if(window.addEventListener) {
 window.addEventListener("load",setInitValues,false);
} else {
 window.onload=setInitValues;
}
//function permettant de récuperer le contenu du tags <LABEL FOR=..> ..</LABEL> associé à un champ ..
function labelField(oField) {
 var cLabels=document.getElementsByTagName("LABEL")
 var sLabel=null;
 var sIdentifier=(/^(checkbox|radio)$/i.test(oField.type))?"id"+oField.name.replace(/input|\[|\]/gi,""):oField.id;
 for (var m=0;m<cLabels.length;m++) {
  var sFor=(cLabels[m].getAttribute("for"))?cLabels[m].getAttribute("for"):cLabels[m].getAttribute("htmlFor");
  if (sFor==sIdentifier) {
   //innerText doesn't exist with mozilla & Co....
   sLabel=cLabels[m].innerHTML.replace(/[\*:]|&[^;\s]+;|[\r\n]|<[^>]+>/gi,"");
   sLabel=sLabel.replace(/\s+$/,"");
   break;
  }
 }
 if (sLabel!=null) 
  return sLabel;
 else 
  return oField.name.replace(/[\-_]/g," ");
}
//permet de créer et initialiser ou modifier un champ du type hidden 
function setHidden(sField,sValue,sForm) {
 if (sForm) oForm=document.forms[sForm];
 var cFields=document.getElementsByName(sField);
 var oField=null;
 for (var k=0;k<cFields.length;k++) {
  if (cFields[k].type=="hidden") {
   oField=cFields[k];
   break;
  }
 }
 if (oField==null) {
  if (document.all) {
   //pour forcer que IE positionne le NAME correctement 
   oField=document.createElement("<INPUT NAME='" + sField + "'>");
  }
  else {
   oField=document.createElement("INPUT");
   oField.name=sField;
  }
  oField.type="hidden";
  oForm.appendChild(oField);
 }
 oField.value=sValue;
}
//function permetant de supprimer un champ hidden ... 
function delHidden(sField) {
 var cFields=document.getElementsByName(sField);
 for (var k=0;k<cFields.length;k++) {
   if (cFields[k].type=="hidden") {
   oField.removeNode();
   break;
  }
 }
}
//function pour afficher le message d'erreur ...
function showError(oField) {
 var sLabel=labelField(oField);
 var sHelp=(oField.getAttribute("help")?"\n"+oField.getAttribute("help").replace(/\\n/g,"\n"):"");
 var rQuestion=new RegExp("^.+\\?\\s*$");
 throw(aMessage[rQuestion.test(sLabel)?20001:20000].toError(sLabel,sHelp));
}
/*
 function permettant de controler la valeur d'un champ 
 avec une expression régulaire précisée par le rédacteur dans l'attribut PATTERN
*/
function checkPattern(oField) {
 var rPat=new RegExp(oField.getAttribute("pattern"),"gi");
 if (!rPat.test(oField.value)) {
  var sLabel=labelField(oField);
  var sHelp=(oField.getAttribute("help")?"\n"+oField.getAttribute("help").replace(/\\n/g,"\n"):"");
  throw(aMessage[20002].toError(sLabel,sHelp));
 }
 return true;
}
//function pour mettre à jour le champ sMailFrom ...
function setEmail(oField,soPrenom,soNom) {
 if (oForm==null) oForm=oField.form;
 var sMailFrom="";
 if (soPrenom) sMailFrom+=oForm.elements[soPrenom].value+" ";
 if (soNom) sMailFrom+=oForm.elements[soNom].value;
 setHidden("mailFromName",sMailFrom);
 setHidden("mailFrom",oField.value);
}
// vérifie la validité de la date
//##############################-
function verifDate(oField)
{
if (oField.value.length==0) 
 return true;
var aDate=oField.value.split(/[\/\-]/);
var iYear=parseInt(new Number(aDate[2]));
if (iYear<100) {
 iYear+=2000;
 }
var iMonth=parseInt(new Number(aDate[1]));
var iDay=parseInt(new Number(aDate[0]));
if (isNaN(iMonth)||1>iMonth || iMonth>12) {
  if(oField.onbeforeactivate) oField.onbeforeactivate();
  throw(aMessage[20003].toError());
 }
var iDayMax=30;
switch (iMonth) {
 case 1:
 case 3:
 case 5:
 case 7:
 case 8:
 case 10:
 case 12:
  iDayMax=31;
  break;
 case 2: 
  iDayMax=((iYear%4==0&&iYear%100!=0)||(iYear%1000==0))?29:28;
  break;
 default: 
  iDayMax=30;
  break;
 }
if (isNaN(iDay) || 1>iDay || iDay>iDayMax) {
  throw(aMessage[20004].toError());
 }
if (isNaN(iYear)) {
  throw(aMessage[20005].toError());
 }
if (iDay<10) aDate[0]="0" + iDay;
if (iMonth<10) aDate[1]="0" + iMonth;
return true;
}
//Controle format email
function verifEmail(oField) {
 var rEmail=new RegExp("^[-_\.a-z0-9]+@[-_\.a-z0-9]+[\.][a-z\.]+$","ig");
 if (!rEmail.test(oField.value)) {
  throw(aMessage[20006].toError(labelField(oField)));
 }
 //mise à jour adresse email ...
 var rSetEmail=new RegExp("setemail","ig")
 if (rSetEmail.test(oField.onchange)) oField.onchange();
 return true;
}
//controle format numerique
function verifNumeric(oField) {
 var rNum=new RegExp("^[0-9]+$","g");
 if (!rNum.test(oField.value)) {
  throw(aMessage[20007].toError(labelField(oField)));
 }
 return true;
}
//control format decimal
function verifDecimal(oField,iDecimal) {
 if(iDecimal==0) {
  throw(aMessage[20007].toError(labelField(oField)));
 }
 var rDec=new RegExp("^[0-9]+([.,][0-9]{1,"+iDecimal+"})?$");
 if (!rDec.test(oField.value)) {
  throw(aMessage[20008].toError(labelField(oField),iDecimal));
 }
 return true;
}
//control format money
function verifMoney(oField,iDecimal) {
 if(iDecimal==0) {
  throw(aMessage[20007].toError(labelField(oField)));
 }
 var rDec=new RegExp("^[0-9]+([.,][0-9]{1,"+iDecimal+"})?$");
 if (!rDec.test(oField.value)) {
  throw(aMessage[20008].toError(labelField(oField),iDecimal));
 }
 return true;
}
//controle format time
function verifTime(oField) {
 if (!/\d{2}\:\d{2}/g.test(oField.value)) {
  throw(aMessage[20009].toError(labelField(oField)));
 }
 
 var aTime=oField.value.split(/\:/);
 var iHour=parseInt(aTime[0]);
 var iMin=parseInt(aTime[1]);
 if (iHour > 24 || iMin>60 || (iHour==24 && iMin!=0)) {
  throw(aMessage[20009].toError(labelField(oField)));
 }
 return true;
}
//Fonctions to add extra field attributes ... 
function verifText(oField) {
 var aClass=oField.className.split(/\s+/);
 for (var c=0;c<aClass.length;c++) {
  var aValue=aClass[c].replace(/([A-Z0-9]+)/g," $1").split(/\s+/);
  switch(aValue[0].toLowerCase()) {
   case "decimal" : 
    oField.setAttribute("decimal",aValue[1]);
    break; 
   case "format" : 
    oField.setAttribute("format",aValue[1].toLowerCase());
    break; 
   case "required" :
    oField.setAttribute("required",1);
    break;
  }
 }
 if(oField.getAttribute("required")==1) {
  //if(oField.getAttribute("initValue")==oField.value)  return showError(oField);
  if(oField.value.replace(/\s/gi,"")=="")  return showError(oField);
 }else if(oField.getAttribute("initValue")==oField.value) {
  //oField.value="";
 }
 if(oField.value.length>0) { 
  switch (oField.getAttribute("format")) {
   case "date" : 
    if (!verifDate(oField)) return false;
    break;
   case "decimal" : 
    if (!verifDecimal(oField,oField.getAttribute("decimal"))) return false;
    break;
   case "email" : 
    if (!verifEmail(oField)) return false;
    break;
   case "money" : 
    if (!verifMoney(oField,oField.getAttribute("decimal"))) return false;
    break;
   case "numeric" : 
   case "numerique" : 
    if (!verifNumeric(oField)) return false;
    break;
   case "time" : 
    if (!verifTime(oField)) return false;
    break;
  }
 }
 if (oField.getAttribute("pattern")!=null && oField.value) {
  return checkPattern(oField);
 }
 return true;
}
function verifRadio(oField) {
 //établir la collection des radios en cours 
 var cRadio=document.getElementsByName(oField.name)
 var bRadioObl=false;
 var bRadioCkd=false;
 for (var j=0;j<cRadio.length;j++) {
  if (/required/i.test(cRadio[j].className)) bRadioObl=true;
  if (cRadio[j].checked) bRadioCkd=true;
 }
 if (bRadioObl && !bRadioCkd) {
  return showError(oField);
 }
 //mettre valeur defaut pour checkbox ...
 if (oField.type=="checkbox" && oField.getAttribute("default")) {
  if (oField.checked) {
   delHidden(oField.name);
  }
  else {
   setHidden(oField.name,oField.getAttribute("default"));
  }
 }
 return true;
}
function verifSelect(oField) {
 if (/required/i.test(oField.className) && oField.selectedIndex<1) {
  return showError(oField);
 }
 return true;
}
function verifPass(oField,i) {
 var oNext=(i < oForm.elements.length - 1)?oForm.elements[i+1]:null;
 if (oNext && oNext.type=="password" && oField.value!=oNext.value) {
  throw(aMessage[20010].toError(labelField(oField)));
 }
 return true;
}
function verifyForm(olForm, bCheckAll) {
 var oField=null;
 try {
  oForm=olForm; 
  var bDisabled = false;
  for (var i=0; i < oForm.elements.length; i++) {
   bDisabled = oForm.elements[i].tagName == 'FIELDSET'
     ? !$(oForm.elements[i]).is(":visible") 
     : bDisabled;
   if(bDisabled && bCheckAll !== true) continue;  
   oField=oForm.elements[i];
   switch (oField.type) {
    case "button" :
    case "hidden" :
    case "image" :
    case "reset" :
    case "submit" :
     break;
    case "password" :
     if (!verifPass(oField,i)) return false;
    case "file" :
    case "text" :
    case "textarea" :
     if (!verifText(oField)) return false;
     break;
    case "radio" :
    case "checkbox" :
     if (!verifRadio(oField)) return false;
     break;
    case "select-one" :
    case "select-multiple" :
     if (!verifSelect(oField)) return false;
     break;
    default : 
     //window.status=oForm.elements[i].tagName +" :: "+ ((oForm.elements[i].type)?oForm.elements[i].type:"notTyped");
     break;
   }
  }
  oForm.formpage.value=document.location.pathname.replace(/^.*\/([^\/]+)\.html?$/,"$1");
  if(oForm.formpage.value=="/") oForm.formpage.value=document.location.search.substr(1).replace(/\.html/i,"");
  return true;
 }
 catch(e) {
  //if($(oField).is(':visible')) oField.focus();
  oField=null;
  alert(e.message);
  return false;
 }
} 