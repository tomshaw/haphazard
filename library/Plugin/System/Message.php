<?php
/**
 * Creates a place holder using the key messages. Assigns FlashMessenger messages to the 
 * view layer using the partial template.
 */
class Plugin_System_Message extends Zend_Controller_Plugin_Abstract
{	
    /**
     * @param $request
     * @return unknown_type
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('flash');
    	
    	$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        $view = $bootstrap->getResource('view');
    	
    	$view->placeholder('messages');
    	
    	if(sizeof($messenger->getMessages())) {
	    	
	    	$view->placeholder('messages')->append($view->partial('messages.phtml', array(
	    		'moduleName' => $request->getModuleName(),
	    	    'controllerName' => $request->getControllerName(),
	    		'messages' => $messenger->getMessages(),
	    		'messageType' => $messenger->getMessageType())
	    	));
    	
    	}
    }
}