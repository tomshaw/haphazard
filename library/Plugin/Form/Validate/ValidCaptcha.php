<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Plugin_Form_Validate_ValidCaptcha extends Zend_Validate_Abstract
{

    const NOT_MATCH = 'notMatch';
    
    const REQUIRED = 'isRequired';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'The characters "%value%" does not match image.',
        self::REQUIRED => 'You must type in a captcha value.'
    );

    protected $_name;

    public function __construct($name)
    {
        return $this->_name = $name;
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value['input']);

    	$id = $value['id']; 
		
    	$input = $value['input'];
		
		$session = new Zend_Session_Namespace('Zend_Form_Captcha_' . $id);
		
		$iterator = $session->getIterator(); 
		
		if ($input == $iterator['word']) {
			return true;  
		}
		
        if(empty($value['input'])) {
        	return $this->_error(self::REQUIRED);
        }
		
        return $this->_error(self::NOT_MATCH);
    }
}