<?php
/**
 * There are two methods to access the csrf Zend_Controller_Action_Helper_Token Helper.
 * 1. Using the direct() method to output a hidden input and random token:
 *    $token = $this->_helper->getHelper('Token');
 *    Echoing the object inside your template will product a hidden csrf input.
 *
 * 2. By initialising the helper object by name and then accessing the method like any other object.
 *    $token = $this->_helper->getHelper('Token');
 *    $token->validate(); == return bool;
 */
class Zend_Controller_Action_Helper_Token extends Zend_Controller_Action_Helper_Abstract
{
	protected $_hash;
	
	protected $_salt = 'salt';
	
	protected $_session;
	
	protected $_timeout = 300;
	
	protected $_token;
	
	public function __construct()
	{
		if (null === $this->_token) {
			$request = Zend_Controller_Front::getInstance()->getRequest();
			if ($request->isPut()) {
				parse_str($request->getRawBody(), $params);
				foreach($params as $key => $value) {
					if ($key == 'token') {
						$request->setParam($key, $value);
					}
				}
			}
			$this->_token = $request->getParam('token');
		}
	}
	
	public function getToken()
	{
		return $this->_token;
	}
	
	public function getSession()
	{
		if ($this->_session === null) {
			$this->_session = new Zend_Session_Namespace('store');
		}
		return $this->_session;
	}
	
	public function setSalt($salt)
	{
		$this->_salt = $salt;
		return $this;
	}
	
	public function getSalt()
	{
		return $this->_salt;
	}
	
	public function setTimeout($timeout)
	{
		$this->_timeout = $timeout;
		return $this;
	}
	
	public function getTimeout()
	{
		return $this->_timeout;
	}
	
	public function getHash()
	{
		if (null === $this->_hash) {
			$this->_hash = md5(mt_rand(1, 1000000) . $this->getSalt() . mt_rand(1, 1000000) . $this->getTimeout());
		}
		return $this->_hash;
	}
	
	public function sessionToken()
	{
		$session = $this->getSession();
		$session->setExpirationHops(1, null, true);
		$session->setExpirationSeconds($this->getTimeout());
		$session->hash = $this->getHash();
		return $this;
	}
	
	public function validate()
	{
		$session = $this->getSession();
		if (isset($session->hash)) {
			if ($session->hash == $this->getToken()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function __toString()
	{
		return '<input type="hidden" name="token" id="token" value="' . $this->sessionToken()->getHash() . '">';
	}
}