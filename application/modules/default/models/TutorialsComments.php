<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_TutorialsComments extends Zend_Db_Table_Abstract
{

	protected $_name = 'tutorials_comments';
    
    public function _getAdapter()
    {
    	return $this->getAdapter();
    }
    
    public function getTableName()
    {
    	return $this->_name;
    }

    public function getColumns()
    {
        return $this->info(self::COLS);
    }

    public function getPrimary()
    {
        return $this->info(self::PRIMARY);
    }
    
    public function appendCommentData($entries = array())
    {
		$comments = $this->fetchAll();
		
		$comments_array = array();
		foreach($comments as $comment) {
			$comments_array[$comment->tutorial_id][] = $comment->id;
		}
		
		$rowset = array();
		if(sizeof($entries)) {
			foreach($entries as $entry) {
				$entry->comment_count = 0;
				if (isset($comments_array[$entry->id])) {
					if (count($comments_array[$entry->id]) > 0) {
						$entry->comment_count = count($comments_array[$entry->id]); 
					}
				}
				$rowset[] = $entry;
			}
		}
		return $rowset;
    }
	
}