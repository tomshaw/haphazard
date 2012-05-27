<?php
/**
 * Sets up Zend Navigation in config files located in the configuration 
 * directory. Different container arrays are loaded dependant on module.
 * 
 * @internal $container = new Zend_Navigation(new Zend_Config_Xml('C:/web/htdocs/phpower/application/navigation.xml','navigation'));
 * @uses Zend_Controller_Plugin_Abstract
 */
class Plugin_Menus_Navigation extends Zend_Controller_Plugin_Abstract
{	
	protected $_cache = null;
	
    protected $_hops = null;

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (true === $this->_hops) {
            return;
        }
        
        $module = $request->getModuleName();
        
        if ($module == 'default' || $module == 'install') {
        	return;
        }
        
        $container = $this->getContainer($module);
        
        $page = new Zend_Navigation($container);
        
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        
        $view = $bootstrap->getResource('view');
        
        $view->navigation($page);
        
        $this->_hops = true;
    }
    
    protected function getContainer($module)
    {
    	$containerPath = APPLICATION_PATH . '/configs/navigation/';	 
    	if (file_exists($containerPath . $module . '.php')) {
    		return include $containerPath . $module . '.php';
    	} else {
    		throw new Zend_Navigation_Exception('Could not load the '. $module .' modules navigation container.');
    	}
    }
}