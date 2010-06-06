<?php
/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the halo-Extension.
 *
 *   The halo-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The halo-Extension is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file
 * @ingroup SRRuleObject
 * 
 * @author Kai K�hn
 */

if (!defined('MEDIAWIKI')) die();

class SMWRuleObject extends SMWAbstractRuleObject {
	
	// internally used to parse UPN stack
	private $numarray = array();
	// holds bound variables for UPN stack parser.
	private $bound = array();
	private $tokentypes = array('const', 'var', 'op', 'func1', 'func2');
	
	function __construct($value = "") {
		parent::__construct($value);
    }
    
    // create f-logic rule from formula
    public static function newFromFormula($resultprop, $function, $bndvars) {
    	
    	$formula = array();
    	// parse formula to get UPN from infix notation
     	$parsedformula = new SMWFormulaParser($function);
 		if ($parsedformula->isFormulaValid()) {
     		$formula = $parsedformula->getUPNStack();
     		
	 	} else {
		    return $parsedformula->getErrorMsg();		    
 		}
 		$variables = $parsedformula->getVariables();

 		$donotaddtoheader = array();
 		// replace constants in parsed formula
 		for ($i = 0; $i < sizeof($formula); $i++) {
 			if ($formula[$i] == "var") {
 				$i++;
				for ($j = 0; $j < sizeof($bndvars); $j++) {
					if (sizeof($bndvars[$j] == 3)) {
						if ($bndvars[$j][1] == "const" && $bndvars[$j][0] == $formula[$i]) {
							$formula[$i] = $bndvars[$j][2];
							$element = array_search($bndvars[$j][0], $variables);
							if ($element !== FALSE) {
								unset($variables[$element]);						
							}
						}
					}
				}			
			}
 		}
 		
 		
    	$ruleobject = new SMWRuleObject();
       	// parse formula to get all bound variables
       	$evalflogic = $ruleobject->parseMathRuleArray($formula);
    		    	
    	// create rule head and always include result variable.
		global $smwgTripleStoreGraph;
		$flogicstring = "FORALL _XRES, _RESULT";
 	
		// fetch bound variables
		$boundvariables = $ruleobject->bound;
		$boundvars = "";
		for ($i = 0; $i < sizeof($boundvariables); $i++) {
			$boundvars .= ", " . $boundvariables[$i];
		}		
		$flogicstring .= $boundvars;
    	foreach ($variables as $var) {
			$flogicstring .= ", " . $var;
		}
		$resultvar = end($boundvariables);
		
		// build rule body assignments of bound variables
		$variableassignments = "";
		$count = 0;
		for ($j = 0; $j < sizeof($bndvars); $j++) {
			if (sizeof($bndvars[$j] == 3)) {
				if ($bndvars[$j][1] == "prop") {
					if ($count > 0) {
						$variableassignments .= " AND ";
					}
					$count++;
					// do not add further instance variable
//					$internalvar = "_X" . $count;
//					$flogicstring .= ", " . $internalvar;
					$variableassignments .= $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $bndvars[$j][2], $bndvars[$j][0]));
				}
				
			}
		}
		
		// add result
		if (($variableassignments . $evalflogic) !== '') {
		      $resultMapping = " AND evaluable_(_RESULT, " . $resultvar . ") ";
		} else {
			  $resultMapping = " evaluable_(_RESULT, " . $resultvar . ") ";
		}
		
		if ($variableassignments !== '') {
			$evalflogic  = " AND " . $evalflogic;
		}
		
		// fetch rule head
		$head = $ruleobject->argtostring(new SMWPredicateSymbol(P_ATTRIBUTE,3), $ruleobject->createPropertyAssignment("_XRES", $resultprop, "_RESULT"));
		$flogicstring .= " ". $head . " <- ";
		
		// don't foget the "." :)
    	return $flogicstring . $variableassignments . $evalflogic . $resultMapping . ".";
    }

	/*
	 * Method to get Flogic string from a Rule Object
	 */
    
	public function getFlogicString() {
		global $smwgTripleStoreGraph;
		$flogicstring = 'RULE "' . $smwgTripleStoreGraph . '#"#' . $this->getAxiomId() . ": ";
		return $flogicstring . $this->getPureFlogic(); 		
	}
	
	public function getWikiFlogicString() {
		return $this->getPureFlogic();
	}
        
	private function getPureFlogic() {
		global $smwgTripleStoreGraph;
		$flogicstring = "";

		
		// fetch rule head
		$head = $this->argtostring($this->getHead()->getPreditcatesymbol(), $this->getHead()->getArguments());
		$flogicstring .= " " . $head . " :- ";

		// fetch array of rule body and concatenate arguments
		$body = "";
		$bodyarray = $this->getBody();
		for ($i = 0; $i < sizeof($bodyarray); $i++) {
			if ($i > 0) {
				$body .= " AND ";
			}
			$body .= $this->argtostring($bodyarray[$i]->getPreditcatesymbol(), $bodyarray[$i]->getArguments());
		}

		// don't forget the "." @ end of flogic string ;-)

		$flogicstring .= $body . ".";

		return $flogicstring;
	}

	

	// f-logic helper functions
	
	private function argtostring($pred, $args) {
		switch ($pred->getPredicateName()) {
		case P_ATTRIBUTE:
			// attribute statement
			return $this->getFloPropertyPart($args);
		    break;
		case P_RELATION:
			// relation statement
			return $this->getFloPropertyPart($args);
		    break;
		case P_ISA:
			// isa statement
			return $this->getFloIsaPart($args);
			break;
		default:
			// custom statement
			return $this->getFloOperatorPart($pred->getPredicateName(), $args);
			break;
		}
	}

	private function getFloPropertyPart($args) {
		// statement with 3 terms (att/rel)
		// attribute/relation
		$tmp = "";		 	
		for ($i = 0; $i < sizeof($args); $i++) {
			
			$tmp .= $args[$i] instanceof SMWVariable ? $args[$i]->getName() : ucfirst($args[$i]->getName());		
			if ($i == 0) {				
				$tmp .= "[prop#";
			} else if ($i == 1) {
				$tmp .="->";
			}
		}
		return $tmp .= "]";
	}

	private function getFloOperatorPart($op, $args) {
		$tmp = $op . "(";
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ",";
			}
			$tmp .= $args[$i]->getName();
		}
		return $tmp .= ")";
	}

	private function getFloIsaPart($args) {
		$tmp = '';
		for ($i = 0; $i < sizeof($args); $i++) {
			if ($i > 0) {
				$tmp .= ":cat#";
			}
			$tmp .= ucfirst($args[$i]->getName());
		}
		return $tmp;
	}

	
	
	// flogic-mathematic functions helpers	
	private function parseMathRuleArray($stack) {
		global $smwgTripleStoreGraph; 
		$flogic = "";
		$count = 0;
		for ($x = 0; $x < sizeof($stack); $x++)
		{
			// fetch type token
		    $typetoken = $stack[$x];
		    $x++;
		    // fetch value token
		    $valuetoken = $stack[$x];
		    
			switch ($typetoken) {
			case $this->tokentypes[0]:
		    	array_push($this->numarray, $valuetoken);
			    break;
			case $this->tokentypes[1]:
		    	array_push($this->numarray, $valuetoken);
				break;
			case $this->tokentypes[2]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
			    }
			    $count++;
		    	$var1 = array_pop($this->numarray);
		    	$var2 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);				    	
		    	  		
		    	$flogic .= $this->evalBinary($var1, $var2, $valuetoken, $boundvar);
			    break;
			case $this->tokentypes[3]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
		    	}
				$count++;
				$var1 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);		
				
		    	$flogic .= $this->evalUnary($var1, $valuetoken, $boundvar);		
			    break;
			case $this->tokentypes[4]:
			    if ($count > 0) {
			    	$flogic .= " AND ";
		    	}
		    	$count++;				
		    	$var1 = array_pop($this->numarray);
		    	$var2 = array_pop($this->numarray);
		    	array_push($this->bound,$boundvar = "t".$x);		
		    	$flogic .= $this->evalBinary($var1, $var2, $valuetoken, $boundvar);	
			    break;
			}
		}
		return $flogic;
	}
	
	// creates binary eval expression in f-logic
	
	private function evalBinary($var, $var2, $op, $x) {
		// put result onto stack
		array_push($this->numarray, $x);
		return "evaluable_(".$x.", ".$op."(".$var2.",".$var."))";
	}

	// creates unary eval expression in f-logic
	private function evalUnary($var, $op, $x) {		
		// put result onto stack
	   	array_push($this->numarray, $x);	
		return "evaluable_(".$x.", ".$op."(".$var."))";
	}
	
	private function createPropertyAssignment($intvariable, $prop, $variable) {
		global $smwgTripleStoreGraph;
		$f = array();
		array_push($f, new SMWVariable($intvariable));
		array_push($f, new SMWTerm(array($smwgTripleStoreGraph.'/property', $prop), 2, false));
		array_push($f, new SMWVariable($variable));
		return $f;				
	}

	// String helper functions
	private function strStartsWith($source, $prefix)
	{  
   		return strncmp($source, $prefix, strlen($prefix)) == 0;
	}

	private function strEndsWith($haystack, $needle) {
  		$needle = preg_quote( $needle);
  		return preg_match( '/(?:$needle)\$/i', $haystack);
	}

}

