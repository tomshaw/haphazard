<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_Tutorials extends Zend_Db_Table_Abstract
{
	protected $_name = 'tutorials';
    
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
    
	function fetchTutorials($order = 'tutorial.id', $direction = 'DESC', $limit = 20)
	{	
		$select = $this->getAdapter()->select()
			->from(array('tutorial' => 'tutorials'))
			->where("tutorial.approved = ?", '1')
			->where("tutorial.visible = ?", '1')
			->joinLeft(array('category' => 'tutorials_categories'), 'tutorial.category_id = category.id', array('title AS category'))
			->joinLeft(array('user' => 'users'), 'user.id = tutorial.user_id', array('name AS author','email','comment as bio'))
			->order($order . ' ' . $direction)
			->limit($limit);
		return $this->getAdapter()->fetchAll($select); 
	}
	
    public function fetchTutorial($title)
    {
		$select = $this->getAdapter()->select()
			->from(array('tutorial' => 'tutorials'))
			->where("tutorial.file = ?", $title)
			->where("tutorial.approved = ?", '1')
			->where("tutorial.visible = ?", '1')
			->joinLeft(array('category' => 'tutorials_categories'), 'tutorial.category_id = category.id', array('title AS category'))
			->joinLeft(array('user' => 'users'), 'user.id = tutorial.user_id', array('name AS author','email','comment as bio'))
			->joinLeft(array('comments' => 'tutorials_comments'), 'comments.tutorial_id = tutorial.id', array('COUNT(tutorial_id) AS count'));
		return $this->getAdapter()->fetchRow($select); 
    }
	
}