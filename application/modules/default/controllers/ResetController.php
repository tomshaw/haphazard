<?php

class ResetController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
        	return $this->_helper->redirector('index','index');	
        }
    }

    public function indexAction()
    {
    	$request = $this->getRequest();
    	 
    	$token = $this->_helper->getHelper('Token');
    
    	$errors = array();
    	if ($request->isPost()) {
    
    		$post = $this->_helper->Purify($request->getPost());
    		
    		$model = new Default_Model_User();
    
    		if (false === ($token->validate())) {
    			$errors[] = 'There was a problem with your form token.';
    		}
    
    		if (empty($post['email'])) {
    			$errors[] = 'You must submit an email address to reset.';
    		}
    		 
    		if (!Zend_Validate::is($post['email'], 'EmailAddress')) {
    			$errors[] = 'The email address entered does not appear to be valid.';
    		}
    		
    		$statement = $model->getAdapter()->quoteInto('email = ?',(string)$post['email']);    	
    		if (null === ($row = $model->fetchRow($statement))) {
    			$errors[] = 'We could not find a user matching the email address your provided.';
    		}
    		
    		if (sizeof($errors)) {
    			$this->_helper->flash->addError($errors);
    			$this->_helper->redirector->gotoUrl('/reset');
    		}
    		 
    		$key = md5(uniqid(rand()));
    		 
    		$row->setFromArray(array('code'=>$key));
    		 
    		$row->save();
    		 
    		$this->emailResetPassword($row, $key);
    		
    		$this->_helper->flash->addSuccess('A link containing instructions how to reset your password has been emailed to you.');
    
    		return $this->_helper->redirector('index','login');
    	}
    
    	$this->view->token = $token;
    }
    
    public function passwordAction()
    {
    	$request = $this->getRequest();
    	 
    	$token = $this->_helper->getHelper('Token');
    
    	$key = (string) $request->getParam('key');
    
    	if (!$key) {
    		$this->_helper->flash->addError('The action requires a valid key.');
    		$this->_helper->redirector->gotoUrl('/reset');
    	}
    
    	$model = new Default_Model_User();
    
    	$row = $model->fetchRow($model->getAdapter()->quoteInto('code = ?', $key));
    
    	if (!$row) {
    		$this->_helper->flash->addError('The certification key you provided could not be validated.');
    		$this->_helper->redirector->gotoUrl('/reset');
    	}
    
    	$errors = array();
    	if ($request->isPost()) {
    
    		$post = $this->_helper->Purify($request->getParams());
    
    		if (false === ($token->validate())) {
    			$errors[] = 'There was a problem with your form token.';
    		}
    		 
    		if (empty($post['password'])) {
    			$errors[] = 'This form requires a password.';
    		}
    		 
    		if (!Zend_Validate::is($post['password'], 'StringLength', array('min' => 6, 'max' => 32))) {
    			$errors[] = 'Passwords must contain atleast 6 characters and not more than 32.';
    		}
    		 
    		if ($row->email != $post['email']) {
    			$errors[] = 'The email address you entered is not correct.';
    		}
    		 
    		if (sizeof($errors)) {
    			$this->_helper->flash->addError($errors);
    			$this->_helper->redirector->gotoUrl('/reset');
    		}
    		
    		$passwordHash = new Plugin_PasswordHash();
    
    		$row->setFromArray(array('password'=>$passwordHash->generate($post['password']),'code'=>null));
    
    		try {
    			$row->save();
    		} catch (Exception $e) {
    			$errors[] = $e->getMessage();
    		}
    		
    		if(sizeof($errors)) {
    			$this->_helper->flash->addError($errors);
    			$this->_helper->redirector->gotoUrl('/reset');
    		}
    		
    		$this->loginUser($row->email, (string)$post['password']);
    		
    		$this->_helper->flash->addSuccess('Your password has been reset successfully!');
    		
    		return $this->_helper->redirector('index','index');
    	}
    
    	$this->view->token = $token;
    
    	$this->view->key = $key;
    }
    
    private function loginUser($email, $password)
    {
    	$adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table_Abstract::getDefaultAdapter(), 'users', 'email', 'password');
    
    	$adapter->setIdentity($email);
    
    	$adapter->setCredential(md5($password));
    
    	$auth = Zend_Auth::getInstance();
    
    	$result = $auth->authenticate($adapter);
    	 
    	if (!$result->isValid()) {
    		$errors[] = 'Your account could not be validated.';
    	}
    
    	if(sizeof($errors)) {
    		$this->_helper->flash->addError($errors);
    		return $this->_helper->redirector('index','index');
    	}
    	 
    	$row = $adapter->getResultRowObject(array('id', 'name', 'email', 'identity'));
    	 
    	$auth->getStorage()->write((object) $row);
    	 
    	return $this;
    }
    
    private function emailResetPassword($row, $key)
    {
    	$server = Zend_Controller_Front::getInstance()->getRequest()->getServer('SERVER_NAME');
    
    	$name = $row->name;
    	$email = $row->email;
    	 
    	$this->view->server = $server;
    	$this->view->customer_name = $name;
    	$this->view->customer_key = $key;
    
    	$template = $this->view->render('emails/reset_password.phtml');
    
    	try {
    		$mail = new Zend_Mail();
    		$mail->setBodyHtml($template);
    		$mail->setFrom(APPLICATION_EMAIL, APPLICATION_NAME);
    		$mail->addTo($email, $name);
    		$mail->setSubject('Reset Password - Do not reply.');
    		$mail->send();
    	} catch (Exception $e) {
    		throw new Zend_Mail_Exception($e);
    	}
    }
}