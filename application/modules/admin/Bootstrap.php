<?php

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap 
{
	protected function _initCustomerModuleLoader()
	{
		$loader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '',
			'basePath'  => APPLICATION_PATH . '/modules/admin')
		);
		return $loader;
	}
}