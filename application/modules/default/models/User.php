<?php

class Default_Model_User extends Zend_Db_Table_Abstract
{
	protected $_name = 'users';
	
    public function getColumns()
    {
        return $this->info(self::COLS);
    }
    
    public function fetchUsers()
    {
    	return $this->select()
    		->from($this->_name, array('id','username','email','password','name','identity','newsletter','created','modified','code'));
    }
    
    public function emailExists($email)
    {
    	$select = $this->select()
    		->from($this->_name, array('email'))
    		->where('email = ?', $email);
    	$row = $this->getAdapter()->fetchRow($select);
    	return ($row) ? $row->email : false;
    }
    
    public function usernameExists($username)
    {
    	$select = $this->select()
    		->from($this->_name, array('username'))
    		->where('username = ?', $username);
    	$row = $this->getAdapter()->fetchRow($select);
    	return ($row) ? $row->username : false;
    }
    
    public function accessTime($id)
    {
    	$where = $this->getAdapter()->quoteInto('id = ?', $id);
    	$this->update(array('modified' => new Zend_Db_Expr("NOW()")), $where);
    }
    
}