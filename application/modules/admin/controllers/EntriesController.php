<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Admin_EntriesController extends Zend_Controller_Action
{
	protected $_redirector;
	
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }
    
    /**
     * First level are categories anything deeper is a normal blog entry.
     */
    public function indexAction()
    {
        $parentId = (int) $this->_request->getParam('parent_id', 0);
        
        $page = (int) $this->_getParam('page', 1);
        
        $this->view->page = $page;
        
        $this->view->parent_id = $parentId;
        
        $model = new Default_Model_Entries();
        
        $select = $model->queryEntries(null, $parentId);
        
        $paginator = Zend_Paginator::factory($select);
        
        $paginator->setCurrentPageNumber($page);
        
        $paginator->setItemCountPerPage(10);
        
        $this->view->data = $paginator;
        
        $this->view->navdata = $model->genNav($parentId);
    }
    
    function createAction()
    {
        $parentId = (int) $this->_request->getParam('parent_id', 0);
        
        $this->view->parent_id = $parentId;
        
        $auth = Zend_Auth::getInstance();
        
        $userData = $auth->getStorage()->read();
        
        $form = new Admin_Form_Entry();
        
        $form->setDefault('parent_id', $parentId);
        
        $form->removeElement('id');
        
        if (0 === $parentId) {
            $form->removeElement('body');
            $form->removeElement('continued');
            $form->removeElement('draft');
            $form->removeElement('comments');
            $form->removeElement('trackbacks');
            $form->removeElement('approved');
        }
        
        $this->view->form = $form;
        
        if ($this->_request->isPost()) {
            $post = $this->_request->getPost();
            
            if ($form->isValid($post)) {
                $model = new Default_Model_Entries();
                
                $row = $model->createRow();
                
                $row->setFromArray($form->getValues());
                
                $row->user_id = $userData->id;
                
                $row->created = $row->modified = time();
                
                $ary = $model->createEntry($parentId);
                
                $row->left_id = $ary['left_id'];
                
                $row->right_id = $ary['right_id'];
                
                $id = $row->save();
                
                $this->_redirector->gotoUrl('/admin/entries/edit/parent_id/' . $parentId . '/id/' . $id);
                
            } else {
                $form->populate($post);
            }
        }
    }
    
    public function editAction()
    {
        $id = (int) $this->_request->getParam('id', 0);
        
        $parentId = (int) $this->_request->getParam('parent_id', 0);
        
        $this->view->parent_id = $parentId;
        
        $listing = $this->_request->getParam('listing', false);
        
        $form = new Admin_Form_Entry();
        
        $form->submit->setLabel('Update Entry');
        
        if (0 === $parentId) {
            $form->removeElement('body');
            $form->removeElement('continued');
            $form->removeElement('draft');
            $form->removeElement('comments');
            $form->removeElement('trackbacks');
            $form->removeElement('approved');
        }
        
        $this->view->form = $form;
        
        if ($this->_request->isPost() && (false === ($listing))) {
            $formValues = $this->_request->getPost();
            
            if ($form->isValid($formValues)) {
                $model = new Default_Model_Entries();
                
                if (false === ($row = $model->fetchRow('id = ' . $id))) {
                    throw new Exception('The blog entry could not be found.');
                }
                
                $row->setFromArray($form->getValues());
                
                $row->modified = time();
                
                $row->save();
                
                $this->_redirector->gotoUrl('/admin/entries/edit/parent_id/' . $parentId . '/id/' . $id);
                
            } else {
                $form->populate($formValues);
            }
            
        } elseif (false !== ($id)) {
            $form->setDefault('id', $id);
            
            $model = new Default_Model_Entries();
            
            if (false === ($data = $model->fetchRow('id = ' . $id))) {
                throw new Exception('The blog entry could not be found.');
            }
            
            $this->view->title = $data->title;
            
            $form->populate($data->toArray());
            
        } else {
            $this->_helper->redirector('index');
        }
    }
    
    function deleteAction()
    {
        $id = (int) $this->_request->getParam('id', false);
        
        $model = new Default_Model_Entries();
        
        if ($this->_request->isPost()) {
            if (false !== ($id)) {
                if (false === ($model->deleteEntry($id))) {
                    throw new Exception('Could not delete blog entry.');
                }
                
                $model = new Default_Model_Comments();
                
                if (false === ($model->deleteComments($id))) {
                    throw new Exception('Could not delete blog entry.');
                }
                
            }
            
            $this->_redirector->gotoUrl('/admin/entries/page/1');
        }
        
        $form = new Admin_Form_EntryDelete();
        
        if (false !== ($id)) {
            if (false === ($data = $model->fetchRow('id = ' . $id))) {
                throw new Exception('Could not delete blog entry.');
            }
            
            $form->populate($data->toArray());
            
            $this->view->title = 'Delete: ' . $data->title;
            
            $this->view->form = $form;
        }
    }
    
}