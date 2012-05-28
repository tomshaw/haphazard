<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Admin_UsersController extends Zend_Rest_Controller
{
    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/users.js');
    }
    
    public function getitemsAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        $page = $request->getParam('page', '1');
        
        $model = new Default_Model_User();
        
        $select = $model->fetchUsers();
        
        $paginator = Zend_Paginator::factory($select);
        
        $paginator->setCurrentPageNumber($page);
        
        $paginator->setItemCountPerPage(10);
        
        $out               = array();
        $out['paginator']  = $paginator->getIterator()->toArray();
        $out['properties'] = get_object_vars($paginator->getPages('Jumping'));
        
        $this->getResponse()->setHttpResponseCode(200)->appendBody(Zend_Json::encode($out));
    }
    
    public function getAction()
    {
        $this->norender();
        
        $id = $this->getRequest()->getParam('id');
        
        if (!$id) {
            $this->getResponse()->setHttpResponseCode(404)->appendBody(Zend_Json::encode(array(
                'errors' => 'No id provided.'
            )));
            return;
        }
        
        $model = new Default_Model_User();
        
        $row = $model->fetchRow('id = ' . $id);
        
        if (!$row) {
            $this->getResponse()->setHttpResponseCode(404)->appendBody(Zend_Json::encode(array(
                'errors' => 'Could not locate specified user using id: ' . $id
            )));
            return;
        }
        
        $data = $row->toArray();
        
        $token = $this->_helper->getHelper('Token');
        
        $data['token'] = $token->sessionToken()->getHash();
        
        $this->getResponse()->setHttpResponseCode(200)->appendBody(Zend_Json::encode($data));
    }
    
    public function putAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        if ($request->isPut()) {
            parse_str($request->getRawBody(), $params);
            foreach ($params as $key => $value) {
                $request->setParam($key, $value);
            }
        }
        
        $id = $this->getRequest()->getParam('id');
        
        if (!$id) {
            $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                'errors' => 'No id provided.'
            )));
            return;
        }
        
        $token = $this->_helper->getHelper('Token');
        
        $model = new Default_Model_User();
        if (null === ($row = $model->fetchRow('id = ' . intval($id)))) {
            $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                'errors' => 'Could not lookup user.'
            )));
            return;
        }
        
        $errors = array();
        if ($request->isPut()) {
            $put = $this->_helper->Purify($request->getParams());
            
            if (false === ($token->validate())) {
                $errors[] = 'There was a problem submitting your form...';
            }
            
            if (!Zend_Validate::is($put['email'], 'EmailAddress')) {
                $errors[] = 'The email address ' . $put['email'] . ' entered does not appear to be valid.';
            }
            
            if (!Zend_Validate::is($put['username'], 'StringLength', array('min' => 2,'max' => 16))) {
                $errors[] = 'Usernames must be atleast 2 characters and not more than 16.';
            }
            
            if (isset($put['password']) && !empty($put['password'])) {
                if (!Zend_Validate::is($put['password'], 'StringLength', array('min' => 6,'max' => 32))) {
                    $errors[] = 'Passwords must contain atleast 6 characters and not more than 32.';
                }
                $passwordHash    = new Plugin_PasswordHash();
                $put['password'] = $passwordHash->generate($put['password']);
            } else {
                unset($put['password']);
            }
            
            if ($row->email != $put['email']) {
                if ($model->emailExists((string) $put['email'])) {
                    $errors[] = 'Oops that email address already exists in our system.';
                }
            }
            
            if ($row->username != $put['username']) {
                if ($model->usernameExists((string) $put['username'])) {
                    $errors[] = 'Oops that username has already been taken.';
                }
            }
            
            $put['newsletter'] = (array_key_exists('newsletter', $put)) ? '1' : '0';
            
            if (sizeof($errors)) {
                $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => $errors
                )));
                return;
            }
            
            if ($row->email != $put['email']) {
                $this->emailVerification($put['email'], $row);
                unset($put['email']);
                $message = 'An email has been sent to verify your new email address.';
            } else {
                $message = 'Account has been updated successfully.';
            }
            
            $row->setFromArray($put);
            
            $row->save();
            
            $this->getResponse()->setHttpResponseCode(200);
            $this->getResponse()->setBody(Zend_Json::encode(array(
                'success' => array(
                    $message
                )
            )));
            
            return;
        }
        
    }
    
    public function createAction()
    {
        $this->view->headScript()->appendFile('/js/create.js');
        
        $this->view->token = $this->_helper->getHelper('Token');
    }
    
    public function postAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        $token = $this->_helper->getHelper('Token');
        
        $errors = array();
        if ($request->isPost()) {
            $post = $this->_helper->Purify($request->getPost());
            
            if (false === ($token->validate())) {
                $errors[] = 'There was a problem submitting your form...';
            }
            
            if (!Zend_Validate::is($post['email'], 'EmailAddress')) {
                $errors[] = 'The email address entered does not appear to be valid.';
            }
            
            if (!Zend_Validate::is($post['username'], 'StringLength', array('min' => 2,'max' => 16))) {
                $errors[] = 'Usernames must be atleast 2 characters and not more than 16.';
            }
            
            if (isset($post['password']) && !empty($post['password'])) {
                if (!Zend_Validate::is($post['password'], 'StringLength', array('min' => 6,'max' => 32))) {
                    $errors[] = 'Passwords must contain atleast 6 characters and not more than 32.';
                }
                
                $passwordHash = new Plugin_PasswordHash();
                
                $post['password'] = $passwordHash->generate($post['password']);
            }
            
            $model = new Default_Model_User();
            
            if ($model->emailExists((string) $post['email'])) {
                $errors[] = 'Oops that email address already exists in our system.';
            }
            
            if ($model->usernameExists((string) $post['username'])) {
                $errors[] = 'Oops that username has already been taken.';
            }
            
            $post['newsletter'] = (array_key_exists('newsletter', $post)) ? '1' : '0';
            
            if (sizeof($errors)) {
                $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => $errors
                )));
                return;
            }
            
            $data               = array();
            $data['name']       = (string) trim(htmlspecialchars($post['name']));
            $data['username']   = (string) trim(htmlspecialchars($post['username']));
            $data['email']      = (string) trim(htmlspecialchars($post['email']));
            $data['password']   = $post['password'];
            $data['identity']   = '1';
            $data['newsletter'] = '1';
            $data['code']       = $this->_helper->Key();
            $data['created']    = new Zend_Db_Expr("NOW()");
            $data['modified']   = new Zend_Db_Expr("NOW()");
            
            try {
                $id = $model->insert($data);
            }
            catch (Exception $e) {
                $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => $e->getMessage()
                )));
                return;
            }
            
            $this->getResponse()->setHttpResponseCode(200)->setBody(Zend_Json::encode(array(
                'success' => array(
                    $data['name'] . ' account has been created successfully.'
                )
            )));
            
            return;
        }
        
        $this->getResponse()->setHttpResponseCode(200)->appendBody(Zend_Json::encode(array(
            'token' => $token->sessionToken()->getHash()
        )));
    }
    
    public function deleteAction()
    {
        $this->norender();
        
        $id = $this->getRequest()->getParam('id');
        
        if (!$id) {
            $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                'errors' => 'No id provided.'
            )));
            return;
        }
        
        $model = new Default_Model_User();
        
        $model->delete('id = ' . intval($id));
        
        $this->getResponse()->appendBody(__METHOD__ . "::Deleting the requested article!");
        //$this->getResponse()->setHttpResponseCode(204); // When deleting no content is required in user body.
        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setBody(Zend_Json::encode(array(
            'success' => array(
                'Customer has been deleted successfully.'
            )
        )));
    }
    
    public function secretAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        $apiKey = $request->getHeader('apikey');
        
        if ($apiKey != 'd4SdfXsAd6H9Z') {
            $this->getResponse()->setHttpResponseCode(403)->appendBody('API key was not validated. Please try again.');
            return;
        }
        
        $this->getResponse()->setHttpResponseCode(200);
        $this->getResponse()->setBody(Zend_Json::encode(array(
            'success' => array(
                'API key was successfully authenticated.'
            )
        )));
    }
    
    private function norender()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->getHelper('layout')->disableLayout();
    }
}