<?php
/**
 * Language file De
 * 
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
require_once("SR_Language.php");

class SR_LanguageDe extends SR_Language {

    protected $srContentMessages = array(
    
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Erwarte eine Funktion, Variable, Konstante oder Klammer bei $1',
    'smw_srf_expected_comma' => 'Erwarte ein Komma bei $1',
    'smw_srf_expected_(' => 'Erwarte eine �ffnende Klammer bei $1',
    'smw_srf_expected_)' => 'Erwarte eine schlie�ende Klammer bei $1',
    'smw_srf_expected_parameter' => 'Erwarte einen Parameter bei $1',
    'smw_srf_missing_operator' => 'Erwarte eine Operator bei $1',
    
    // Explanations
    'smw_explanations' => 'Explanations',
    'explanations' => 'Explanations',
    'smw_expl_not_all_inputs' => 'Bitte f�llen Sie alle obenstehenden Felder aus.',
    'smw_expl_and' => 'UND',
    'smw_expl_because' => 'WEIL',
    'smw_expl_value' => 'Wert',
    'smw_expl_img' => 'Erkl�rung anfordern',
    'smw_expl_explain_category' => 'Erkl�rung f�r Kategoriezuordnung:',
    'smw_expl_explain_property' => 'Erkl�rung f�r Propertyzuordnung:',
    'smw_expl_error' => 'Leider gab es einen Fehler w�hrend der Auswertung der Erkl�rung:'
    
    );
    
    protected $srUserMessages = array();
}
?>