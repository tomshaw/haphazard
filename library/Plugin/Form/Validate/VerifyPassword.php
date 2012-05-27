<?php

class Plugin_Form_Validate_VerifyPassword extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'nomatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => "The passwords you entered do not match."
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        
        $this->_setValue($value);

        if(is_array($context)) {
        	if($value === $context['password']) {
        		return true;
        	}
        }
        return $this->_error(self::NOT_MATCH);
    }
}