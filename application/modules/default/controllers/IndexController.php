<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
    	$request = $this->getRequest();
    		
    	$title = $request->getParam('title', null);
    
    	$page = (int) $request->getParam('page');
    
    	$title = (null === $title) ? null : urldecode($title);
    
    	$this->view->title = $title;
    
    	$this->view->page = $page;
    
    	$model = new Default_Model_Entries();
    
    	$comments = new Default_Model_Comments();
    
    	$entries = $model->queryEntries($title);
    
    	$rowset = $comments->appendCommentData($entries);
    
    	$paginator = Zend_Paginator::factory($rowset);
    
    	$paginator->setCurrentPageNumber($page);
    
    	$paginator->setItemCountPerPage(10);
    
    	$this->view->entries = $paginator;
    }
}