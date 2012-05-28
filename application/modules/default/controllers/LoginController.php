<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class LoginController extends Zend_Rest_Controller
{
    public function preDispatch()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            return $this->_helper->redirector('index', 'index');
        }
    }
    
    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/login.js');
        
        $this->view->token = $this->_helper->getHelper('Token');
    }
    
    public function loginAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        $token = $this->_helper->getHelper('Token');
        
        $errors = array();
        if ($request->isPost()) {
            $post = $this->_helper->Purify($request->getPost());
            
            if (empty($post['email'])) {
                $errors[] = 'You must enter an email address.';
            }
            
            if (empty($post['password'])) {
                $errors[] = 'You must enter a password.';
            }
            
            if (false === ($token->validate())) {
                $errors[] = 'There was a problem submitting your form.';
            }
            
            if (!Zend_Validate::is($post['email'], 'EmailAddress')) {
                $errors[] = 'The email address entered does not appear to be valid.';
            }
            
            if (!Zend_Validate::is($post['password'], 'StringLength', array('min' => 6,'max' => 32))) {
                $errors[] = 'Passwords must contain atleast 6 characters and not more than 32.';
            }
            
            if (sizeof($errors)) {
                $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => $errors
                )));
                return;
            }
            
            $adapter = new Plugin_Auth_Adapter_DbTable(Zend_Db_Table_Abstract::getDefaultAdapter(), 'users', 'email', 'password');
            
            $adapter->setIdentity($post['email']);
            
            $adapter->setCredential($post['password']);
            
            $auth = Zend_Auth::getInstance();
            
            $result = $auth->authenticate($adapter);
            
            if (!$result->isValid()) {
                $this->getResponse()->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => array(
                        'Your account credentials could not be validated.'
                    )
                )));
                return;
            }
            
            if (isset($post['remember_me'])) {
                Zend_Session::RememberMe(60 * 60 * 24 * 7); // 7 days in seconds.
            } else {
                Zend_Session::ForgetMe();
            }
            
            $row = $adapter->getResultRowObject(array('id','name','email','identity'));
            
            $auth->getStorage()->write((object) $row);
            
            $this->_helper->flash->addSuccess('You have successfully logged in!');
            
            $model = new Default_Model_User();
            $model->accessTime($row->id);
            
            $this->getResponse()->setHttpResponseCode(200)->appendBody(Zend_Json::encode(array(
                'success' => true
            )));
        }
    }
    
    protected function norender()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
    }
    
    /**
     * Satisfies Zend_Rest_Controller interface.
     */
    public function getAction()
    {
    }
    public function putAction()
    {
    }
    public function postAction()
    {
    }
    public function deleteAction()
    {
    }
    
}