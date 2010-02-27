<?php
/**
 * @file
 * @ingroup SemanticRules
 * 
 * @defgroup SemanticRules Semantic Rules extension
 * 
 * Semantic rules extension entry point
 * 
 * @author: Kai K�hn / ontoprise / 2009
 */

if ( !defined( 'MEDIAWIKI' ) ) die;

define('SEMANTIC_RULES_VERSION', '{{$VERSION}}');
if (!defined("SMW_HALO_VERSION")) {
	trigger_error("SMWHalo is required but not installed.");
	die();
}

global $smwgDefaultStore;
if($smwgDefaultStore != 'SMWTripleStore') {
    trigger_error("Triplestore not active. See manual how to activate.");
    die();
}

$wgExtensionFunctions[] = 'ruleSetupExtension';
$srgSRIP = $IP . '/extensions/SemanticRules';

/**
 * Setups rule extension
 *
 * @return boolean (MW Hook)
 */
function ruleSetupExtension() {
	global $srgSRIP, $smwgDefaultRuleStore, $wgHooks, $wgAutoloadClasses, $wgSpecialPages, $wgSpecialPageGroups, $wgExtensionCredits;
	$wgHooks['BeforePageDisplay'][]='srfAddHTMLHeader';

	$smwgDefaultRuleStore = "SRRuleStore";

	srfSRInitUserMessages();

	
	$wgHooks['InternalParseBeforeLinks'][] = 'srfTripleStoreParserHook';
	
	
	$wgAutoloadClasses['SRRuleStore'] = $srgSRIP . '/includes/SR_RuleStore.php';
	
	$wgAutoloadClasses['SMWAbstractRuleObject'] = $srgSRIP . '/includes/SR_AbstractRuleObject.php';
	$wgAutoloadClasses['SMWConstant'] = $srgSRIP . '/includes/SR_Constant.php';
	$wgAutoloadClasses['SMWExplanationLiteral'] = $srgSRIP . '/includes/SR_ExplanationLiteral.php';
	$wgAutoloadClasses['SMWFlogicParser'] = $srgSRIP . '/includes/SR_FlogicParser.php';
	$wgAutoloadClasses['SMWFormulaParser'] = $srgSRIP . '/includes/SR_FormulaParser.php';
	$wgAutoloadClasses['SMWLiteral'] = $srgSRIP . '/includes/SR_Literal.php';
	$wgAutoloadClasses['SMWPredicate'] = $srgSRIP . '/includes/SR_Predicate.php';
	$wgAutoloadClasses['SMWPredicateSymbol'] = $srgSRIP . '/includes/SR_PredicateSymbol.php';
	$wgAutoloadClasses['SMWRuleObject'] = $srgSRIP . '/includes/SR_RuleObject.php';
	$wgAutoloadClasses['SMWTerm'] = $srgSRIP . '/includes/SR_Term.php';
	$wgAutoloadClasses['SMWVariable'] = $srgSRIP . '/includes/SR_Variable.php';
	
	global $wgRequest;
	$action = $wgRequest->getVal('action');
    if ($action == 'ajax') {
    	
        require_once($srgSRIP . '/includes/SR_RulesAjax.php');
        require_once($srgSRIP . '/includes/SR_WebInterfaces.php');
    }
    
	/*$wgAutoloadClasses['SRExplanations'] = $srgSRIP . '/specials/Explanations/SR_Explanations.php';
	$wgSpecialPages['Explanations'] = array('SRExplanations');
	$wgSpecialPageGroups['Explanations'] = 'smwplus_group';*/
	
	$wgExtensionCredits['parserhook'][]= array('name'=>'SemanticRules&nbsp;Extension', 'version'=>SEMANTIC_RULES_VERSION,
            'author'=>"Thomas&nbsp;Schweitzer, Kai&nbsp;K&uuml;hn. Maintained by [http://www.ontoprise.de Ontoprise].", 
            'url'=>'https://sourceforge.net/projects/halo-extension', 
            'description' => 'Enables the power of rules to SMWHalo');
	

	return true;
}

/**
 * Registers SR user/content messages.
 */
function srfSRInitUserMessages() {
	global $wgMessageCache, $wgLang, $srgSRIP;

	$srLangClass = 'SR_Language' . str_replace( '-', '_', ucfirst( $wgLang->getCode() ) );

	if (file_exists($srgSRIP.'/languages/'. $srLangClass . '.php')) {
		include_once($srgSRIP.'/languages/'. $srLangClass . '.php' );
	}
	// fallback if language not supported
	if ( !class_exists($srLangClass)) {
		include_once($srgSRIP.'/languages/SR_LanguageEn.php' );
		$srgLang = new SR_LanguageEn();
	} else {

		$srgLang = new $srLangClass();
	}

	$wgMessageCache->addMessages($srgLang->getUserMessages(), $wgLang->getCode());
	$wgMessageCache->addMessages($srgLang->getContentMessages(), $wgLang->getCode());

}

/**
 * Register SR javascript user/content messages
 *
 * @param $out
 */
function srfAddJSLanguageScripts(& $out) {
	global $srgSRIP, $wgLanguageCode, $wgUser, $wgScriptPath;

	// content language file
	$lng = '/scripts/languages/SR_Language';
	if (!empty($wgLanguageCode)) {
		$lng .= ucfirst($wgLanguageCode).'.js';
		if (file_exists($srgSRIP . $lng)) {
			// add content language script file
		} else {
			// add english default content language script file
		}
	} else {
		// add english default content language script file
	}

	// user language file
	$lng = '/scripts/languages/SR_Language';
	if (isset($wgUser)) {
		$lng .= "User".ucfirst($wgUser->getOption('language')).'.js';
		if (file_exists($srgSRIP . $lng)) {
			$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. $lng .'"></script>');
		} else {
			$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_LanguageUserEn.js"></script>');

		}
	} else {
		$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_LanguageUserEn.js"></script>');
	}

	// base language script
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath .'/extensions/SemanticRules'. '/scripts/languages/SR_Language.js"></script>');
}

/**
 * Includes javascript/css files.
 *
 * @param $out
 * @return boolean (MW hook)
 */
function srfAddHTMLHeader(& $out) {
	global $srgSRIP, $wgScriptPath, $smwgEnableFlogicRules, $wgRequest, $wgTitle;

    $SF = ($wgTitle->getNamespace() == -1 &&
           in_array($wgTitle->getBasetext(), array("AddData", "EditData")));
	$action = $wgRequest->getVal('action');
	if ($action != "edit" && $action != "annotate" && $action != "formedit" && !$SF) return true;

    srfAddJSLanguageScripts($out);

	$rulesEnabled = isset($smwgEnableFlogicRules)
	? (($smwgEnableFlogicRules) ? 'true' : 'false')
	: 'false';
	$out->addScript('<script type= "text/javascript">var smwgEnableFlogicRules='.$rulesEnabled.';</script>'."\n");

	$out->addLink(array('rel'   => 'stylesheet','type'  => 'text/css',
	                    'media' => 'screen, projection','href'  => $wgScriptPath . '/extensions/SemanticRules/skins/rules.css'));

	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Rule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CategoryRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_CalculationRule.js"></script>');
	$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_PropertyChain.js"></script>');
    
	$localname = SpecialPage::getLocalNameFor("Explanations");
	global $wgTitle;
	if ($wgTitle->getNamespace() == NS_SPECIAL && $wgTitle->getText() == $localname) {
		$out->addScript('<script type="text/javascript" src="'.$wgScriptPath . '/extensions/SemanticRules/scripts/SR_Explanations.js"></script>');
	}
	return true;

}

/**
 * Parses rules from page text
 *
 * @return boolean (SMWHalo hook)
 */
function srfTripleStoreParserHook(&$parser, &$text, &$strip_state = null) {
	global $smwgEnableFlogicRules, $smwgTripleStoreGraph;
	// rules
	// meant to be a hash map $ruleID => $ruleText,
	// where $ruleID has to be a URI (i.e. containing at least one colon)

	$rules = array();
	if (isset($smwgEnableFlogicRules)) {

		// search rule tags
		$ruleTagPattern = '/&lt;rule(.*?&gt;)(.*?.)&lt;\/rule&gt;/ixus';
		preg_match_all($ruleTagPattern, trim($text), $matches);

		// at least one parameter and content?
		for($i = 0; $i < count($matches[0]); $i++) {
			$header = trim($matches[1][$i]);
			$ruletext = trim($matches[2][$i]);

			// parse header parameters
			$ruleparamterPattern = "/([^=]+)=\"([^\"]*)\"/ixus";
			preg_match_all($ruleparamterPattern, $header, $matchesheader);

			$native = false;
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'native') {
					$native = true;
				}
				
			}
			// fetch name of rule (ruleid) and put into rulearray
			for ($j = 0; $j < count($matchesheader[0]); $j++) {
				if (trim($matchesheader[1][$j]) == 'name') {
					$name = $matchesheader[2][$j];
					$is_url = strpos($name, ":");
					if ($is_url === false) {
						// no valid URL given, so build one
						$url = $smwgTripleStoreGraph . "#" . urlencode(str_replace(' ', '_', $name));
					} else {
						$url = $name;
					}
					
					$ruletext = str_replace("&lt;","<", $ruletext);
					$ruletext = str_replace("&gt;",">", $ruletext);
					$rules[] = array($url, $ruletext, $native);
				}
			}
		}

		// remove rule tags from text
		$text = preg_replace($ruleTagPattern, "", $text);
	}

	SMWTripleStore::$fullSemanticData->setRules($rules);
	return true;
}
