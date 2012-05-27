<?php

class Plugin_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
    protected function _authenticateValidateResult($resultIdentity)
    {
        $hash = new Plugin_PasswordHash();
        if (false === ($hash->validate($this->_credential, $resultIdentity['password']))) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
        } else {
        	$this->_resultRow = $resultIdentity;
        	$this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        	$this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        }
        return $this->_authenticateCreateAuthResult();
    }
}