<?php
/**
 * Renders the layout named <module>.phtml in the /module/layout folder.
 *
 * postDispatch(Zend_Controller_Request_Abstract $request)
 *
 * @param Zend_Controller_Request_Abstract $request
 */
class Plugin_Layout_Module extends Zend_Controller_Plugin_Abstract
{   
	private $hasRun = null;
	
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (true === $this->hasRun) {
    		return $request;
		}
		
    	$module = $request->getModuleName();
    	
    	$layout = Zend_Layout::getMvcInstance();
        
    	$layout->setLayoutPath(dirname(dirname(dirname($layout->getLayoutPath()))).'/modules/'.$module.'/layouts');
        
        $layout->setLayout($module);
        
        $this->hasRun = true;
    }    
}