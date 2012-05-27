<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_Comments extends Zend_Db_Table_Abstract
{
	protected $_name = 'comments';
    
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
    
    public function getComments($entry_id, $approved = 0, $order_by = 'comment.created', $sort = 'DESC', $limit = '20')
    {
    	$adapter = $this->getAdapter();
    	
		$select = $adapter->select()
			->from(array('comment' => 'comments'), array('id','user_id','entry_id','parent_id','title','body','approved','created'))
			->where('comment.entry_id = ?', $entry_id)
			->joinLeft(array('users' => 'users'), 'users.id = comment.user_id', array('name AS author','email'))
			->order($order_by . ' ' . $sort)
			->limit($limit);
		return $adapter->fetchAll($select);  
    }
    
    public function appendCommentData($entries = array())
    {
		$comments = $this->fetchAll();
		
		$data = array();
		foreach($comments as $comment) {
			$data[$comment->entry_id][] = $comment->id;
		}
		
		$rowset = array();
		if(sizeof($entries)) {
			foreach($entries as $entry) {
				$entry->comment_count = 0;
				if (isset($data[$entry->id])) {
					if (count($data[$entry->id]) > 0) {
						$entry->comment_count = count($data[$entry->id]); 
					}
				}
				$rowset[] = $entry;
			}
		}
		return $rowset;
    }
    
    public function addComment($data)
    {
		$merge = array(
			'approved' => true,
			'created' => time(),
		);
		$this->insert(array_merge($data,$merge));
	}
	
    public function deleteComments($id)
    {
        if (false === ($this->delete('entry_id = ' . (int) $id))) {
            return false;
        } else {
            return true;
        }
    }	
}