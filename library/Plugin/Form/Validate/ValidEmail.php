<?php

class Plugin_Form_Validate_ValidEmail extends Zend_Validate_Abstract
{
    const USER_EXISTS = 'userExists';

    protected $_messageTemplates = array(
        self::USER_EXISTS => 'The email "%value%" already exists.',
    );

    protected $_model;

    public function __construct(Default_Model_Users $model)
    {
        $this->_model = $model;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        
        $user = $this->_model->checkEmail($value);
        
        if($user) {
        	$this->_error(self::USER_EXISTS);
        	return false;	
        }
        
		return true;
    }
}
