<?php
/**
 * flashMessages view helper. Called as flashMessages() in the controller template.
 *
 */
class Zend_View_Helper_FlashMessages
{

    /**
     * flashMessages.
     *
     * Returns the array from the action helper formatted to display the required notices to the user.
     * 
     * @return array
     */
	public function flashMessages()
	{
		$messages = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages();
	
		if (!sizeof($messages)) {
			$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			return $redirector->goto('index', 'index');
		}

		$data = array();
		foreach ($messages as $message) {
			if(isset($message['url'])) {
				$data['url'] = $message['url'];
				continue;
			}
			if(isset($message['status'])) {
				$data['status'] = $message['status'];
				continue;
			}
			if(isset($message['return'])) {
				$data['return'] = $message['return'];
				continue;
			}
			if(isset($message['message'])) {
				$data['messages'][] = $message['message'];
			}
		}
		return $data;
	}
}
?>