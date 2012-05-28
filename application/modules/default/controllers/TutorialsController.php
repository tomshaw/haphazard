<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class TutorialsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $page = (int) $this->_getParam('page');
        
        $model = new Default_Model_Tutorials();
        
        $comments = new Default_Model_TutorialsComments();
        
        $entries = $model->fetchTutorials();
        
        $rowset = $comments->appendCommentData($entries);
        
        $paginator = Zend_Paginator::factory($rowset);
        
        $paginator->setCurrentPageNumber($page);
        
        $paginator->setItemCountPerPage(10);
        
        $this->view->rows = $paginator;
        
    }
    
    public function viewAction()
    {
        $request = $this->getRequest();
        
        $article = $request->getParam('article', null);
        
        $article = (null === $article) ? null : urldecode($article);
        
        $model = new Default_Model_Tutorials();
        
        $this->view->row = $model->fetchTutorial($article);
        
        // @TODO Check if file exists.
        $this->render($article);
    }
}