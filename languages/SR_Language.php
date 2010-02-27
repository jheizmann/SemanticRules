<?php
/**
 * @file
 * @ingroup SRLanguage
 * 
 * @defgroup SRLanguage Semantic rules language files
 * @ingroup SemanticRules
 * 
 * Abstract Language class for SemanticRules extension
 * 
 * @author: Kai K�hn / ontoprise / 2009
 *
 */
abstract class SR_Language {

    // the message arrays ...
    protected $srContentMessages;
    protected $srUserMessages;
    
    public function getUserMessages() {
    	return $this->srUserMessages;
    }
    
    public function getContentMessages() {
    	return $this->srContentMessages;
    }
}
