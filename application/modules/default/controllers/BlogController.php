<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class BlogController extends Zend_Controller_Action
{
    public function init()
    {
    }
    
    function indexAction()
    {
        $request = $this->getRequest();
        
        $title = $this->_request->getParam('title', null);
        
        $page = (int) $this->_getParam('page');
        
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
    
    function entryAction()
    {
        $request = $this->getRequest();
        
        $title = $this->_request->getParam('title');
        
        $entryId = $this->_request->getParam('entry_id');
        
        $title = (null === $title) ? null : urldecode($title);
        
        $token = $this->_helper->getHelper('Token');
        
        $model = new Default_Model_Entries();
        
        if ($request->isPost()) {
            $row = $model->fetchEntryById($entryId);
        } else {
            $row = $model->fetchEntryByTitle($title);
        }
        
        $this->view->data = $row;
        
        if ($request->isPost() && Zend_Auth::getInstance()->hasIdentity()) {
            if (false === ($token->validate())) {
                throw new Exception('There was a problem submitting your form.');
            }
            
            $post = $this->_helper->Purify($request->getPost());
            
            $post['user_id'] = Zend_Auth::getInstance()->getStorage()->read()->id;
            
            if (array_key_exists('token', $post) && isset($post['token'])) {
                unset($post['token']);
            }
            
            $model = new Default_Model_Comments();
            
            if (false === ($id = $model->addComment($post))) {
                throw new Exception('There was a problem saving your comment.');
            }
        }
        
        $model = new Default_Model_Comments();
        
        $comments = $model->getComments(intval($row->id));
        
        $this->view->comments = $comments;
        
        $this->view->token = $token;
    }
    
    function archivesAction()
    {
        $request = $this->getRequest();
        
        $year = $this->_request->getParam('year');
        
        $month = $this->_request->getParam('month');
        
        $day = $this->_request->getParam('day');
        
        $model = new Default_Model_Entries();
        
        $comments = new Default_Model_Comments();
        
        $entries = $model->fetchArchives($day, $year, $month);
        
        $rowset = $comments->appendCommentData($entries);
        
        $this->view->entries = $rowset;
    }
    
    function categoriesAction()
    {
        $id = (int) $this->_request->getParam('id');
        
        $page = (int) $this->_getParam('page');
        
        $this->view->page = $page;
        
        $model = new Default_Model_Entries();
        
        $comments = new Default_Model_Comments();
        
        $entries = $model->fetchByCategory($id);
        
        $rowset = $comments->appendCommentData($entries);
        
        $paginator = Zend_Paginator::factory($rowset);
        
        $paginator->setCurrentPageNumber($page);
        
        $paginator->setItemCountPerPage(10);
        
        $this->view->entries = $paginator;
    }
    
    function tagAction()
    {
        $tag = $this->_request->getParam('tag', null);
        
        $page = (int) $this->_getParam('page');
        
        $tag = (null === $tag) ? null : urldecode($tag);
        
        $this->view->title = $tag;
        
        $this->view->page = $page;
        
        $model = new Default_Model_Entries();
        
        $comments = new Default_Model_Comments();
        
        $entries = $model->queryTags($tag);
        
        $rowset = $comments->appendCommentData($entries);
        
        $paginator = Zend_Paginator::factory($rowset);
        
        $paginator->setCurrentPageNumber($page);
        
        $paginator->setItemCountPerPage(10);
        
        $this->view->entries = $paginator;
        
    }
    
    public function rssAction()
    {
        $service = (string) $this->_request->getParam('service', 'all');
        
        $id = (int) $this->_request->getParam('id', false);
        
        return $this->feeds('rss', $service, $id);
    }
    
    public function atomAction()
    {
        $service = (string) $this->_request->getParam('service', 'all');
        
        $id = (int) $this->_request->getParam('id', false);
        
        return $this->feeds('atom', $service, $id);
    }
    
    private function feeds($type, $service, $id)
    {
        $model = new Default_Model_Entries();
        
        switch (strtolower($service)) {
            case 'category':
                $entries = $model->fetchAll('parent_id = ' . $id);
                break;
            case 'entry':
                $entries = $model->fetchAll('id = ' . $id);
                break;
            case 'author':
                $entries = $model->queryEntries(null, false, $id);
                break;
            case 'all':
                $entries = $model->queryEntries(null);
                break;
            default:
                $entries = $model->queryEntries(null);
                break;
        }
        
        $catData = $model->fetchRssCategories();
        
        $cats = array();
        foreach ($catData as $data) {
            $cats[$data->id] = $data->title;
        }
        
        foreach ($entries as $entry) {
            $catName = '';
            if (isset($cats[$entry->parent_id])) {
                $catName = $cats[$entry->parent_id];
            }
            
            $out[] = array(
                'title' => $entry->title,
                'link' => APPLICATION_URL . '/blog/entry/' . urlencode($entry->title),
                'description' => 'RSS feeds provided by company name goes here.',
                'guid' => APPLICATION_URL . '/blog/entry/' . urlencode($entry->title),
                'content' => $entry->body . $entry->continued,
                'lastUpdate' => $entry->created,
                'comments' => APPLICATION_URL . '/entry/' . urlencode($entry->title),
                'commentRss' => APPLICATION_URL . '/rss/' . $type . '/comments/' . $entry->id,
                'source' => array(
                    'title' => 'This is the Title!',
                    'url' => APPLICATION_URL . '/blog'
                ),
                'category' => array(
                    array(
                        'term' => $catName
                    )
                )
            );
        }
        
        $array = array(
            'title' => APPLICATION_NAME,
            'link' => APPLICATION_URL,
            'lastUpdate' => $entries[0]->modified,
            'published' => $entries[0]->created,
            'charset' => 'utf-8',
            'description' => APPLICATION_NAME,
            'author' => 'Thomas Shaw',
            'email' => APPLICATION_EMAIL,
            'webmaster' => APPLICATION_EMAIL,
            'copyright' => APPLICATION_NAME,
            'image' => APPLICATION_URL . '/tomshaw.jpg',
            'generator' => Zend_Version::VERSION,
            'language' => 'en',
            'ttl' => '5',
            'rating' => 'NC',
            'entries' => $out
        );
        
        $feed = Zend_Feed::importArray($array, $type);
        
        assert($feed instanceof Zend_Feed_Abstract);
        
        $feed->send();
        
        exit;
    }
    
}