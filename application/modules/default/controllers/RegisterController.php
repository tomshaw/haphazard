<?php

class RegisterController extends Zend_Controller_Action
{
	
    public function preDispatch()
    {
    	$request = $this->getRequest();
        if (Zend_Auth::getInstance()->hasIdentity()) {
        	if ($request->getActionName() != 'email') {
        		return $this->_helper->redirector('index','index');	
        	}
        }
    }

    public function indexAction()
    {	   
    	$request = $this->getRequest();
    	
    	$token = $this->_helper->getHelper('Token');
    
    	$errors = array();
    	if ($request->isPost()) {
    
    		$model = new Default_Model_User();
    
    		$post = $this->_helper->Purify($request->getPost());
    		
    		if (empty($post['username'])) {
    			$errors[] = 'You must enter a password.';
    		}
    		
    		if (empty($post['name'])) {
    			$errors[] = 'You must enter a password.';
    		}
    		
    		if (empty($post['email'])) {
    			$errors[] = 'You must enter an email address.';
    		}
    		 
    		if (empty($post['password'])) {
    			$errors[] = 'You must enter a password.';
    		}
    		 
    		if (false === ($token->validate())) {
    			$errors[] = 'There was a problem with your form token.';
    		}
    		 
    		if (!Zend_Validate::is($post['email'], 'EmailAddress')) {
    			$errors[] = 'The email address entered does not appear to be valid.';
    		}
    		
    		if (!Zend_Validate::is($post['username'], 'StringLength', array('min' => 2, 'max' => 16))) {
    			$errors[] = 'Usernames must be atleast 2 characters and not more than 16.';
    		}
    		 
    		if (!Zend_Validate::is($post['password'], 'StringLength', array('min' => 6, 'max' => 32))) {
    			$errors[] = 'Passwords must contain atleast 6 characters and not more than 32.';
    		}
    		 
    		if ($model->emailExists((string)$post['email'])) {
    			$errors[] = 'Your email address already exists in the system.';
    		}
    		
    		if ($model->usernameExists((string)$post['username'])) {
    			$errors[] = 'Username already exists.';
    		}
    		 
    		if (sizeof($errors)) {
    			$this->_helper->flash->addError($errors);
    			$this->_helper->redirector->gotoUrl('/register'); 
    		}
    		
    		$passwordHash = new Plugin_PasswordHash();
    			 
    		$data = array();
    		$data['name'] = (string) trim(htmlspecialchars($post['name']));
    		$data['username'] = (string) trim(htmlspecialchars($post['username']));
    		$data['email'] = (string) trim(htmlspecialchars($post['email']));
    		$data['password'] = $passwordHash->generate($post['password']);
    		$data['identity'] = '1';
    		$data['newsletter'] = '1';
    		$data['code'] = $this->_helper->Key();
    		$data['created'] = new Zend_Db_Expr("NOW()");
    		$data['modified'] = new Zend_Db_Expr("NOW()");
    
    		try {
    			$id = $model->insert($data);
    		} catch (Exception $e) {
    			$errors[] = $e->getMessage();
    		}
    			 
    		if(sizeof($errors)) {
    			$this->_helper->flash->addError($errors);
    			$this->_helper->redirector->gotoUrl('/register');
    		}
    		
    		$this->loginUser((string)$post['email'], (string)$data['password']);
    		
    		$this->_helper->flash->addSuccess('Account created successfully!');
    		
    		return $this->_helper->redirector('index','index');
    	}
    
    	$this->view->token = $token;
    }
    
    public function emailAction()
    {
    	$request = $this->getRequest();
    
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

    	$row->setFromArray(array('code'=>null));
    
    	try {
    		$row->save();
    	} catch (Exception $e) {
    		
    	}
    
    	$this->_helper->flash->addSuccess('Thank you your email address has been confirmed!');
    
    	return $this->_helper->redirector('index','index');
    }
    
    private function loginUser($email, $password)
    {
    	$adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table_Abstract::getDefaultAdapter(), 'users', 'email', 'password');
    	 
    	$adapter->setIdentity($email);
    	 
    	$adapter->setCredential($password);
    	 
    	$auth = Zend_Auth::getInstance();
    	
    	$errors = array();
    	if (false === ($auth->authenticate($adapter)->isValid())) {
    		$errors[] = 'Your account could not be validated.';
    	}
    	 
    	if(sizeof($errors)) {
    		$this->_helper->flash->addError($errors);
    		return $this->_helper->redirector('index','index');
    	}
    	
    	$row = $adapter->getResultRowObject(array('id', 'name', 'email', 'identity'));
    	
    	$auth->getStorage()->write((object) $row);
    	 
    	$this->sendRegistrationEmail($row);
    	
    	return $this;
    }
    
    private function sendRegistrationEmail($row)
    {
    	$server = Zend_Controller_Front::getInstance()->getRequest()->getServer('SERVER_NAME');
    	
    	$this->view->server = $server;
    	$this->view->name = $row->name;
    	$this->view->email = $row->email;
    	$this->view->key = $this->_helper->Key();
    
    	$template = $this->view->render('emails/registration.phtml');
    
    	try {
    		$mail = new Zend_Mail();
    		$mail->setBodyHtml($template);
    		$mail->setFrom(APPLICATION_EMAIL, APPLICATION_NAME);
    		$mail->addTo($row->email, $row->name);
    		$mail->setSubject('Thank you for registering!');
    		$mail->send();
    	} catch (Exception $e) {
    		$this->_helper->flash->addError('There was a problem emailing your message.');
    		$this->_helper->redirector->gotoUrl('/account');
    	}
    }
    
}