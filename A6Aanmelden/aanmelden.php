<?php
/*
Plugin Name:  Aanmelden
Plugin URI:   http://localhost
Description:  Stuur een mail!
Version:      2018.01
Author:       Anan6.com
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/
//Shortcut
add_shortcode('dnAanmelden','fDNAanmelden');
//perform the shortcode output
function fDNAanmelden($atts, $content = '', $tag){
    $html  = '<link href="/wp-content/plugins/aanmelden/public/CSS/style.css" rel="stylesheet" type="text/css" />';
    $html .= '<script src="/wp-content/plugins/aanmelden/public/js/homeAanmelden.js" type="text/javascript"></script>';
    $html .= '<form id="dnAanmelden" action="#">';
    $html .= '<legend>Aanmeldingsformulier deelnemer:</legend>';
    $html .= '<div class="entry">';
    $html .= '<label for="dnAkkoord">Voordat je de aanmelding start, is het van belang dat je kennis neemt van en akkoord gaat met onze privacyverklaring. Deze kun je <a href="#">hier</a> lezen en hier accorderen<input type="checkbox" id="dnAkkoord" name="dnAkkoord" value="OK"></label></div>';
    $html .= '<div class="entry">';
    $html .= '<label for="wie">De aanvraag betreft:</label>';
    $html .= '<div>';
    $html .= '<label><input type="radio" name="wie" value="M">Mijzelf</label>';
    $html .= '<label><input type="radio" name="wie" value="A">Iemand die ik begeleid</label></div></div>';
    $html .= '<div class="entry">';
    $html .= '<label class="red" for="aanEmail">E-mailadres deelnemer:</label><input type="text" name="email" id="dnEmail" placeholder="Email" class="required formatEmail"><button id="dnSubmit" type="submit" disabled="disabled">Aanmelden</button></div>';
    $html .= '<p id="dnMessage"></p></form>';
    return $html;
}
//Shortcut
add_shortcode('vwAanmelden', 'fVWAanmelden');
 function fVWAanmelden() {
    $html  = '<link href="/wp-content/plugins/aanmelden/public/CSS/style.css" rel="stylesheet" type="text/css" />';
    $html .= '<script src="/wp-content/plugins/aanmelden/public/js/homeAanmelden.js" type="text/javascript"></script>';
    $html .= '<form id="vwAanmelden" action="#">';
    $html .= '<legend>Aanmeldingsformulier vrijwilliger:</legend>';
    $html .= '<div class="entry">';
    $html .= '<label for="vwAkkoord">Voordat je de aanmelding start, is het van belang dat je kennis neemt van en akkoord gaat met onze privacyverklaring. Die kun je <a href="#">hier</a> lezen en hier accorderen<input type="checkbox" id="vwAkkoord" name="vwAkkoord" value="OK"></label></div>';
    $html .= '<div class="entry">';
    $html .= '<div>';
    $html .= '<div class="entry">';
    $html .= '<label class="red" for="aanEmail">E-mailadres vrijwilliger:</label><input type="text" name="email" id="vwEmail" placeholder="Email" class="required formatEmail"><button id="vwSubmit" type="submit" disabled="disabled">Aanmelden</button></div>';
    $html .= '<p id="vwMessage"></p></form>';
    return $html;
}

?> 