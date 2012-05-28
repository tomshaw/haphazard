<?php

class AccountController extends Zend_Rest_Controller
{
    protected $_auth = null;
    
    public function init()
    {
        if (null === $this->_auth) {
            $this->_auth = Zend_Auth::getInstance()->getStorage()->read();
        }
    }
    
    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/account.js');
        
        $model = new Default_Model_User();
        if (null === ($row = $model->fetchRow('id = ' . intval($this->_auth->id)))) {
            $this->_helper->redirector->gotoUrl('/logout');
        }
        
        $this->view->newsletter = ($row->newsletter == 1) ? true : false;
        
        $this->view->data = $row;
        
        $this->view->token = $this->_helper->getHelper('Token');
    }
    
    public function putAction()
    {
        $this->norender();
        
        $request = $this->getRequest();
        
        $response = $this->getResponse();
        
        $token = $this->_helper->getHelper('Token');
        
        $model = new Default_Model_User();
        if (null === ($row = $model->fetchRow('id = ' . intval($this->_auth->id)))) {
            $response->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array('errors' => array('There was a server side problem.'))));
            return;
        }
        
        $errors = array();
        if ($request->isPut()) {
            $put = $this->_helper->Purify($request->getParams());
            
            if (false === ($token->validate())) {
                $errors[] = 'There was a problem verifying your form token.';
            }
            
            if (!Zend_Validate::is($put['email'], 'EmailAddress')) {
                $errors[] = 'The email address entered does not appear to be valid.';
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
                $response->setHttpResponseCode(503)->appendBody(Zend_Json::encode(array(
                    'errors' => $errors
                )));
                return;
            }
            
            if ($row->email != $put['email']) {
                $this->emailVerification($put['email'], $row);
                unset($put['email']);
                $message = 'An email has been sent to verify your new email address.';
            } else {
                $message = 'Your account has been updated successfully.';
            }
            
            $row->setFromArray($put);
            
            $row->save();
            
            $response->setHttpResponseCode(200)->setBody(Zend_Json::encode(array(
                'success' => array(
                    $message
                )
            )));
        }
    }
    
    public function verifyAction()
    {
        $request = $this->getRequest();
        
        $params = $this->_helper->Purify($request->getParams());
        
        $errors = array();
        
        if (array_key_exists('id', $params)) {
            if ($params['id'] != $this->_auth->id) {
                $errors[] = 'You must be logged in as the approporiate customer.';
            }
        } else {
            $errors[] = 'No customer identification number found.';
        }
        
        if (!array_key_exists('email', $params)) {
            $errors[] = 'The verification process needs a customer email address.';
        }
        
        if (!Zend_Validate::is($params['email'], 'EmailAddress')) {
            $errors[] = 'The email address supplied does not appear to be valid.';
        }
        
        $model = new Default_Model_User();
        if (null === ($row = $model->fetchRow('id = ' . intval($this->_auth->id)))) {
            $errors[] = 'There is a problem loading your account.';
        }
        
        if (sizeof($errors)) {
            $this->_helper->flash->addError($errors);
            $this->_helper->redirector->gotoUrl('/account');
        }
        
        $row->setFromArray(array(
            'email' => (string) $params['email']
        ));
        
        $row->save();
        
        $this->_helper->flash->addSuccess('Your email address has been successfully verified.');
        
        $this->_helper->redirector->gotoUrl('/account');
    }
    
    private function displayAvatarsAction()
    {
        $request = $this->getRequest();
        
        $category = (string) $this->_getParam('category');
        
        $category = ($category) ? $category : 'renderings';
        
        $catselect = (bool) $this->_getParam('catselect', false);
        
        $avatar = (string) $this->_getParam('avatar', null);
        
        $this->view->category = $category;
        
        if ($request->isPost()) {
            if (null !== $avatar && $catselect === false) {
                $model = new Default_Model_User();
                $model->updateAvatar($this->id, (string) $avatar);
            }
        }
        
        $dir = opendir('./img/avatars/');
        
        $available_categories = array();
        while (($directory_name = @readdir($dir)) != false) {
            if ($directory_name != '.' && $directory_name != '..' && $directory_name != 'uploads' && $directory_name != 'default.jpg') {
                $available_categories[$directory_name] = $dir;
            }
        }
        
        $categories = '<select name="category" onchange="this.form.submit();">';
        while ((list($key) = each($available_categories)) != false) {
            $selected = ($key == $category) ? ' selected="selected"' : '';
            if (count($available_categories[$key])) {
                $categories .= '<option value="' . $key . '"' . $selected . '>' . ucfirst($key) . '</option>';
            }
        }
        $categories .= '</select>';
        
        $this->view->category_select = $categories;
        
        @closedir($dir);
        
        $dir = opendir('./img/avatars/' . $category);
        
        $row = $col = 0;
        
        $available_avatars = array();
        while (($avatar_name = @readdir($dir)) != false) {
            if ($avatar_name != '.' && $avatar_name != '..') {
                if (preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $avatar_name)) {
                    $available_avatars[$row][$col] = array(
                        'file' => $avatar_name,
                        'name' => ucfirst(str_replace("_", " ", preg_replace('/^(.*)\..*$/', '\1', $avatar_name))),
                        'path' => './img/avatars/' . $category . '/' . $avatar_name,
                        'dbname' => $category . '/' . $avatar_name
                    );
                    $col++;
                    if ($col == 6) {
                        $row++;
                        $col = 0;
                    }
                }
            }
        }
        
        @closedir($dir);
        @ksort($available_avatars);
        @reset($available_avatars);
        
        $this->view->avatars = $available_avatars;
    }
    
    public function getAction()
    {
    }
    public function postAction()
    {
    }
    public function deleteAction()
    {
    }
    
    protected function emailVerification($email, $row)
    {
        $server = Zend_Controller_Front::getInstance()->getRequest()->getServer('SERVER_NAME');
        
        $this->view->server = $server;
        $this->view->id     = $row->id;
        $this->view->name   = $row->name;
        $this->view->email  = $email;
        
        $template = $this->view->render('emails/email_verification.phtml');
        
        try {
            $mail = new Zend_Mail();
            $mail->setBodyHtml($template);
            $mail->setFrom(APPLICATION_EMAIL, APPLICATION_NAME);
            $mail->addTo($email, $row->name);
            $mail->setSubject('Email Verification - Do not reply.');
            $mail->send();
        }
        catch (Exception $e) {
            $this->_helper->flash->addError('There was a problem emailing your message.');
            $this->_helper->redirector->gotoUrl('/account');
        }
        
        return;
    }
    
    protected function norender()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
    }
    
}