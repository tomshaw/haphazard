<?php

class Install_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $token = $this->_helper->getHelper('Token');
		
		$errors = array();
		if($request->isPost()) {
		    
		    $post = $request->getPost();
		    
		    $obj = $this->_helper->getHelper('Token');
		    if(!$obj->validate()) {
		    	$errors[] = 'There was a problem submitting your form...';
		    }
		    
		    $model = new Install_Model_Setup();
		    
		    try {
		        $model->initSetup();
		    } catch(Exception $e) {
		        $errors[] = $e;
		    }
		    
		    if(!sizeof($errors)) {
		        
		        $this->_helper->flash->addSuccess('Installation has completed successfully.');
		        
		        $this->_helper->redirector->gotoUrl('/install');
		        
		    }
		} 
		
		$this->view->errors = $errors;
		    
		$this->view->token = $token;
		
    }
}