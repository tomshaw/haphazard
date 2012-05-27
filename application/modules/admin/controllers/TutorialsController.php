<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Admin_TutorialsController extends Zend_Controller_Action
{

    public function indexAction() 
    {
		$page = (int) $this->_getParam('page',1);

		$this->view->page = $page;
    	
    	$path = APPLICATION_PATH . '/modules/default/views/scripts/tutorials/';
    	
    	$files = array();
    	if (false !== ($handle = @opendir($path))) {
    		while (false !== ($file = @readdir($handle))) {
    			if ($file != '.' && $file != '..' && $file != 'index.phtml') {
 					$files[] = $file;
				}
    		}
    	}
    	
    	$model = new Default_Model_Tutorials();
    	
    	$rowset = $model->fetchAll();
    	
    	// Produces an array of tutorials/files that are present in the tutorials directory which are not present
    	// in the database and assigns that listing for the administrator to add them.
    	$new = $this->checkNew($files,$rowset);
    	
    	// Assigns the new tutorials to the multi select in the format of title equals both the key and the var.
    	$this->view->new = $this->formatSelectArray($new);
    	
		$paginator = Zend_Paginator::factory($rowset);

		$paginator->setCurrentPageNumber($page);
		
		$paginator->setItemCountPerPage(10);

    	$this->view->rows = $paginator;
    	
    }
    
    public function editAction()
    {
    	$request = $this->getRequest();
		
		$id = (int) $this->_getParam('id');
		
		$listing = (bool) $this->_getParam('listing');
		
		$model = new Default_Model_Tutorials();
			
		$row = $model->fetchRow('id = ' . $id);

		if ($id && $request->isPost() && !$listing) {
			
			$formValues = $this->_request->getPost();
			
			$row->setFromArray($formValues);
                
			$row->created = time();
                
			$row->save(); 
			
			return $this->_helper->redirector->gotoUrl('/admin/tutorials/edit/' . $id);
			
		} else {
			
			$model = new Default_Model_TutorialsCategories();
			
			$users = new Default_Model_User();
			
			$user = $users->fetchRow('id = ' . $row->user_id);
			
			$this->view->user = $user;
			
			$this->view->categories = $model->fetchCategoriesSelect();
			
			$this->view->row = $row;
			
		}
    }
    
    public function addAction()
    {
		$rows = $this->_getParam('rows');
		
		if(!sizeof($rows)) {
			throw new Exception('Before hitting the submit button you must select tutorials to initialize.');	
		}
		
		$model = new Default_Model_Tutorials();
		
    	$auth = Zend_Auth::getInstance();
    	
    	$user_data = $auth->getStorage()->read();
    	
    	$user_id = $user_data->id;
		
    	foreach($rows as $key => $var) {
				
			$data = array(
				'user_id' => $user_id,
				'file' => trim(htmlspecialchars(str_replace('.phtml','',$var))),
				'description' => 'This tutorial has not been assigned a description.',
				'approved' => '0',
				'created' => time()
			);
				
			if (false === ($id = $model->insert($data))) {
		    	throw new Exception('There was a problem creating the tutorial row.');
		    }
				
		}
		
    	return $this->_helper->redirector->gotoUrl('/admin/tutorials');
    }

	public function deleteAction()
	{			
		if(false === ($id = (int) $this->_getParam('id'))) {
			throw new Exception('This method requires a tutorial identification number.');
		}
		
		$model = new Default_Model_Tutorials();
		
		$delete = $model->delete('id = ' . $id);
		
		return $this->_helper->redirector->gotoUrl('/admin/tutorials');
	}
    
    /**
     * Returns an array of tutorial file names not present in the database.
     * 
     * @param $files
     * @param $rowset
     * @return array
     */
    private function checkNew($files,$rowset)
    {
    	static $prefix = '.phtml';
    	
    	$data = array();
    	foreach($rowset->toArray() as $row) {
    		$data[] = $row['file'].$prefix;
    	}
    	
    	return array_diff($files,$data);
    }
    
    private function formatSelectArray($files)
    {    	
    	$data = array();
    	foreach($files as $file) {
    		$data[$file] = $file;
    	}
    	return $data;
    }

}
