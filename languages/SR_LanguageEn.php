<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
 * Language file En
 * 
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
require_once("SR_Language.php");

class SR_LanguageEn extends SR_Language {

    protected $srContentMessages = array(
    // Simple Rules formula parser
    'smw_srf_expected_factor' => 'Expected a function, variable, constant or braces near $1',
    'smw_srf_expected_comma' => 'Expected a comma near $1',
    'smw_srf_expected_(' => 'Expected an opening brace near $1',
    'smw_srf_expected_)' => 'Expected a closing brace near $1',
    'smw_srf_expected_parameter' => 'Expected a parameter near $1',
    'smw_srf_missing_operator' => 'Expected an operator near $1',
    
    'sr_ob_rulelist' => 'Rule metadata',
    
    # These constants map internal TSC rule types to the wiki representation.
    'sr_definition_rule' => 'Definition',
    'sr_property_chaining' => 'Property chaining',
    'sr_calculation' => 'Calculation',
    
    #rule widget
    'sr_ruleselector' => 'Rule format: ',
    'sr_easyreadible' => 'Pretty print',
    'sr_stylizedenglish' => 'Stylized english',
    'sr_rulesdefinedfor' => 'Rules defined for',
    'sr_rulestatus' => 'Status',
    'sr_rule_isactive_state' => 'active',
    'sr_rule_isinactive_state' => 'inactive',
    
    #Unified search extension
    'sr_rulesfound' => 'The following rules were found:'
    
    );
    
    protected $srUserMessages = array('smw_ob_ruleTree' => 'Rules');
}
