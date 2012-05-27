<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Zend_Controller_Action_Helper_Flash extends Zend_Controller_Action_Helper_Abstract 
{
    static protected $_session = null;

	protected $_messenger;

	protected $_request;
	
	protected $_namespace = 'default';

    function __construct()
    {
    	if(null === $this->_messenger) {
    		$this->_messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    	}
        if(null === $this->_request) {
    		$this->_request = Zend_Controller_Front::getInstance()->getRequest();
        }
        if (!self::$_session instanceof Zend_Session_Namespace) {
            self::$_session = new Zend_Session_Namespace($this->_namespace);
        }
    }
    
    public function getMessages()
    {
    	return $this->_messenger->getMessages();
    }
    
    public function getMessageType()
    {
    	return self::$_session->messageType;
    }
    
    public function setMessageType($type)
    {
    	self::$_session->messageType = $type;
    }
    
    public function addMessage($message)
    {
    	$this->setMessageType('success');
    	return $this->_messenger->addMessage($message);
    }
    
    public function addSuccess($message)
    {
    	$this->setMessageType('success');
    	if(is_array($message)) {
    		foreach($message as $_index => $value) {
    			$this->_messenger->addMessage($value);
    		}
    	} else {
    		$this->_messenger->addMessage($message);
    	}
    }
    
    public function addError($message)
    {
    	$this->setMessageType('error');
    	if(is_array($message)) {
    	    foreach($message as $_index => $value) {
    	        $this->_messenger->addMessage($value);
    	    }
    	} else {
    		$this->_messenger->addMessage($message);
    	}
    }
    
    public function addNotice($message)
    {
    	$this->setMessageType('notice');
    	if(is_array($message)) {
    		foreach($message as $_index => $value) {
    			$this->_messenger->addMessage($value);
    		}
    	} else {
    		$this->_messenger->addMessage($message);
    	}
    }
}