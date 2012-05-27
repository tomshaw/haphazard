<?php
/**
 * Loads stylesheets and javascript files based on the module name.
 * 
 */
class Plugin_Layout_Assets extends Zend_Controller_Plugin_Abstract
{
    private $_hops = null;

    public function postDispatch (Zend_Controller_Request_Abstract $request)
    {
        if (true === $this->_hops) {
            return $request;
        }
        
        $module = $request->getModuleName();
        
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        
        $view = $bootstrap->getResource('view');
        
        if ($this->fileExists('/css/'.$module.'.css')) {
        	$view->headLink()->appendStylesheet('/css/'.$module.'.css');
        }
        
        if ($this->fileExists('/js/'.$module.'.js')) {
            $view->headScript()->prependFile('/js/'.$module.'.js');
        }
        
        $this->_hops = true;
    }
    
    private function fileExists($file)
    {
        $path = APPLICATION_PATH . '/../public';
        if (file_exists($path . $file)) {
        	return true;
        }
        return false;
    }
}