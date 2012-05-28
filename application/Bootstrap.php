<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{    
    protected function _initViewSettings()
    {
        $this->bootstrap('view');
        
        $view = $this->getResource('view');
        
        $view->doctype('HTML5');
        
        $view->headTitle(APPLICATION_NAME);
        
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        
        $view->jQuery()->enable()->setVersion('1.7.1')->setUiVersion('1.8.17')->addStylesheet('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/ui-lightness/jquery-ui.css')->uiEnable();
        
        $view->headScript()->prependFile('/js/bootstrap.js');
        
        $view->headScript()->appendFile('/js/main.js');
        
        $view->headScript()->appendFile('/js/underscore.js');
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        
        $viewRenderer->setView($view);
        
        return $view;
    }
    
    protected function _initCache()
    {
        $frontendOptions = array(
            'lifetime' => 7200,
            'automatic_serialization' => true
        );
        $backendOptions  = array(
            'cache_dir' => APPLICATION_PATH . '/../data/cache/'
        );
        Zend_Registry::set('cache', Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions));
    }
    
    protected function _initFilter()
    {
        HTMLPurifier_Bootstrap::registerAutoload();
        $config = HTMLPurifier_Config::createDefault();
        Zend_Registry::set('purifier', new HTMLPurifier($config));
    }
    
    protected function _initLogger()
    {
        $this->bootstrap('log');
        $logger = $this->getResource('log');
        Zend_Registry::set('logger', $logger);
    }
    
    protected function _initConfigureDatabase()
    {
        $this->bootstrap('db');
        $db = $this->getResource('db');
        $db->getConnection();
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $db->query("SET NAMES 'utf8'");
        $db->query("SET CHARACTER SET 'utf8'");
        return $db;
    }
    
}