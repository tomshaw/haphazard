<?php
/**
 * Random key generator.
 *
 * There are two methods to access the Zend_Controller_Action_Helper_Key Helper.
 * 1. Using the direct() method to output a hidden input and random token:
 *    $key = $this->_helper->Key($salt);
 *    Echoing the object inside your template will product a hidden csrf input.
 *
 * 2. By initialising the helper object by name and then accessing the method like any other object.
 *    $key = $this->_helper->getHelper('Key');
 *    $key->generate();
 */
class Zend_Controller_Action_Helper_Key extends Zend_Controller_Action_Helper_Abstract
{
	protected $_rand = null;
	
	public function direct($salt = null)
	{
		if (null === $this->_rand) {
			$this->_rand = (string) md5(uniqid(rand()));
		}
		return $this->_rand;
	}
}