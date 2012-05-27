<?php
/**
 * HeaderLinks helper
 *
 */
class App_View_Helper_HasIdentity
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function HasIdentity()
    {
    	$auth = Zend_Auth::getInstance();
        return $auth->hasIdentity();
    }

}
