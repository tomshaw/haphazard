<?php
/**
 * HeaderLinks helper
 *
 */
class App_View_Helper_AclRole
{
    public $view;
    
	private $_roles = array(
		'anonymous',
		'member',
		'author',
		'moderator',
		'administrator'
	);

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function AclRole($role_id)
    {
    	if(isset($this->_roles[$role_id])) {
    		return $this->_roles[$role_id];
    	}
        return 'Unknown';
    }

}