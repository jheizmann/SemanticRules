<?php
/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @ingroup SRRuleObject
 *
 * @defgroup SRRuleObject Abstract semantic rule object
 * @ingroup SemanticRules
 *
 * @author Kai K�hn
 */

if (!defined('MEDIAWIKI')) die();

abstract class SMWAbstractRuleObject {

	private $_sessionId;
	private $_body;
	private $_head;
	private $_freeVars;
	private $_boundVars;
	private $_axiomId;
	private $_ontologyId;

	// constructor
	function __construct($axiomId = "") {
		$this->_axiomId = $axiomId;
	}

	// setter functions to fill rule object

	public function parseRuleObject($ruleObject) {
		// set flogic string
		$this->_flogicID = $ruleObject;

		// fetch sessionId
		$_ruleobject = $ruleObject->rule;
		// fetch rule
		if ($_ruleobject !== NULL) {
			if (isset($_ruleobject->rule->_error)) {
				echo $_ruleobject->rule->_error;
				return;
			} else {
				$this->setRule($_ruleobject);
				return $this;
			}
		}
	}

	public function setRule($rule) {

		// set axiomId
		if (isset($rule->_axiomId)) {
			$this->_axiomId = $rule->_axiomId;
		}

		// set ontologyId
		if (isset($rule->_ontologyId)) {
			$this->_ontologyId = $rule->_ontologyId;
		}

		// fetch head of rule
		if (isset($rule->head)) {
			$this->_head = array();
			foreach ($rule->head->children() as $belement) {

				array_push($this->_head, $this->setLiteral($belement));

			}
		}

		// fetch body of rule
		if (isset($rule->body)) {
			$bodyargs = array();
			if (!is_array($rule->body)) {
				//$rule->body = array($rule->body);
			}
			foreach ($rule->body->children() as $belement) {

				array_push($bodyargs, $this->setLiteral($belement));

			}
			$this->_body = $bodyargs;
		}

		// fetch bound variables
		if (isset($rule->boundVariables)) {
			$boundvars = array();
			//if (is_array($rule->boundVariables)) {
			foreach ($rule->boundVariables->children() as $boundval) {
				array_push($boundvars, $this->setVariable($boundval));
			}
			/*} else {
				array_push($boundvars, $this->setVariable($rule->boundVariables));
				}*/
			$this->_boundVars = $boundvars;
		}

		// fetch free variables
		/*if (isset($rule->freeVariables)) {
			$this->_freeVars = $this->setVariable($rule->freevariables);
			}*/
	}

	public function setVariable($var) {
		return new SMWVariable((string) $var->_variableName);
	}

	public function setLiteral($lit) {

		$templit = new SMWLiteral($this->setPredicatesymbol($lit->_predicatesymbolws), $this->setArguments($lit->_arguments));
		$templit->setArity((string) $lit->_arity);
		return $templit;
	}

	public function setPredicatesymbol($ps) {
		return new SMWPredicateSymbol((string) $ps->_name, (string)$ps->arity);
	}

	public function setArguments($arg) {
		$termargs = array();

		foreach ($arg->children() as $termval) {
			$arity = (string) $termval->_arity;
			if ($arity == 0) {
				$isGround = (string) $termval->_isGround;
				if ($isGround == "true") {
					// 1st char. '"' denotes property/category... FIXME: provide method to distinguish Variables/Constants/Categories/Properties

					if (is_numeric((string) $termval->_argument) || substr((string)$termval->_argument,0,1) == "\"") {
						$tempterm = new SMWConstant( $termval->_argument);
							
					} else {
						$terms = self::convertToArray($termval->_argument);
						$tempterm = new SMWTerm($terms, count($terms), true);
					}
				} else {

					$tempterm = new SMWVariable((string) $termval->_argument);
				}
			} else {

		$terms = self::convertToArray($termval->_argument);
				$tempterm = new SMWTerm($terms , count($terms), $termval->_isGround);
			}
			array_push($termargs, $tempterm);
		}

		return $termargs;
	}

	private static function convertToArray($simple_xml_set) {
		$result = array();
		foreach( $simple_xml_set as $a) {
			$result[] = (string) $a;
		}
		return $result;
	}

	public function setBody($body) {
		$this->_body = $body;
	}

	// exactly one SR_Literal object
	public function setHead($head) {
		$this->_head = $head;
	}

	public function setSessionId($sid) {
		$this->_sessionId = $sid;
	}

	public function setAxiomId($id) {
		$this->_axiomId = $id;
	}

	public function setBoundVariables($boundvars) {
		$this->_boundVars = $boundvars;
	}

	// getter functions to access parsed rule object

	// sessionId when parsing a flogic rule via webservice
	public function getSessionId() {
		return $this->_sessionId;
	}

	// body consisting of array of SR_Literal objects (implicitly concatenated by "AND")
	public function getBody() {
		return $this->_body;
	}

	// exactly one SR_Literal object
	public function getHead() {
		return $this->_head;
	}

	// #of free variables in rule
	public function getFreeVariables() {
		return $this->_freeVars;
	}

	// #of bound variables in rule
	public function getBoundVariables() {
		return $this->_boundVars;
	}

	public function getAxiomId() {
		return $this->_axiomId;
	}

}

