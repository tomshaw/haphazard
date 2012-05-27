<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_Entries extends Zend_Db_Table_Abstract
{
	protected $_name = 'entries';

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
    
    public function del($id)
    {
        if (false === ($this->delete('id = ' . (int) $id))) {
            return false;
        } else {
            return true;
        }
    }
    
    public function fetchEntryById($id)
    {
    	$select = $this->getAdapter()->select()
    		->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
    		->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
    		->where('entry.id = ?', intval($id));
    	return $this->getAdapter()->fetchRow($select);
    }
    
    public function fetchEntryByTitle($title = null)
    {
    	$select = $this->getAdapter()->select()
    		->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
    		->where('entry.title = ?', $title)
    		->joinLeft(array('user' => 'users'), 'user.id = entry.user_id', array('name AS author','email'));
    	return $this->getAdapter()->fetchRow($select);
    }
    
    public function queryEntries($title = null, $parentId = false, $userId = false, $draft = false, $approved = false, $order_by = 'entry.id', $sort = 'DESC')
    {
    	$title_key = (null === $title) ? 'entry.title <> ?' : 'entry.title = ?';
    	$title_val = (null === $title) ? 'NULL' : $title;
    	
    	$parent_key = (false === $parentId) ? 'entry.parent_id <> ?' : 'entry.parent_id = ?';
    	$parent_val = (false === $parentId) ? '0' : $parentId;
    	
    	$user_key = (false === $userId) ? 'entry.user_id <> ?' : 'entry.user_id = ?';
    	$user_val = (false === $userId) ? '0' : $userId;

		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'));
		
		if ($draft === true) {
			$select->where('entry.draft = ?', true);
		}
		
		if ($approved === true) {
			$select->where('entry.approved = ' . $approved);
		}
		
		$select->where($title_key, $title_val)
			->where($parent_key, $parent_val)
			->where($user_key, $user_val)
			->order($order_by . ' ' . $sort);
			
		return $this->getAdapter()->fetchAll($select); 
    }
    
	function fetchArchives($day, $year, $month, $order = 'entry.id', $direction = 'DESC', $limit = 20) 
	{	
		$ts = mktime(0, 0, 0, $month, $day, $year);
		
		if (empty($day)) {
			$day = date('t', $ts);
		}

		$te = mktime(23, 59, 59, $month, $day, $year);
		
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where("entry.created >= ?", $ts)
			->where("entry.created <= ?", $te)
			->where("entry.parent_id <> ?", '0')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
			->order($order . ' ' . $direction)
			->limit($limit);
		return $this->getAdapter()->fetchAll($select); 
	}
	
	function fetchRssCategories() 
	{	
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','parent_id'))
			->where('entry.draft = 0')
			->where('entry.approved = 1')
			->where("entry.parent_id = ?", '0');
		return $this->getAdapter()->fetchAll($select); 	
	}
	
	function fetchCategories($order = 'entry.id', $direction = 'desc', $limit = 12) 
	{	
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where('entry.draft = 0')
			->where('entry.approved = 1')
			->where("entry.parent_id = ?", '0')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
			->order($order . ' ' . $direction)
			->limit($limit);
		return $this->getAdapter()->fetchAll($select); 	
	}
	
	function fetchByCategory($id, $order = 'entry.id', $direction = 'DESC') 
	{	
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where('entry.parent_id = ?', $id)
			->where('entry.draft = 0')
			->where('entry.approved = 1')
			->where("entry.parent_id != ?", '0')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
			->order($order . ' ' . $direction);
		return $this->getAdapter()->fetchAll($select); 	
	}

	function getCalArchives($day,$year,$month,$timestamp) 
	{	
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where("DATE_FORMAT(FROM_UNIXTIME(created), '%e') = ?", $day)
			->where("DATE_FORMAT(FROM_UNIXTIME(created), '%Y') = ?", $year)
			->where("DATE_FORMAT(FROM_UNIXTIME(created), '%c') = ?", $month)
			->where("entry.created <= ?", $timestamp)
			->where("entry.parent_id <> ?", '0')
			->where("entry.draft <> ?", '1')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'));
		return $this->getAdapter()->fetchAll($select); 
	}
  
    public function getLatestEntry($order = 'entry.id', $direction = 'desc', $limit = 1)
    {
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where('entry.draft = 0')
			->where('entry.approved = 1')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
			->order($order . ' ' . $direction)
			->limit($limit);
		return $this->getAdapter()->fetchRow($select); 	
    }
    
    public function queryTags($title, $order = 'entry.id')
    {	
    	$string = strtolower($title);
    	
		$select = $this->getAdapter()->select()
			->from(array('entry' => 'entries'), array('id','title','body','continued','user_id','parent_id','comments','draft','trackbacks','approved','created','modified'))
			->where('entry.draft = ?', '0')
			->where('entry.approved = ?', '1')
			->where('entry.parent_id <> ?', '0')
			->where('LOWER(entry.title) LIKE ?','%'.$string.'%')
			->orWhere('LOWER(entry.body) LIKE ?','%'.$string.'%')
			->where('LOWER(entry.continued) LIKE ?','%'.$string.'%')
			->joinLeft(array('users' => 'users'), 'users.id = entry.user_id', array('name AS author','email'))
			->order($order);
		return $this->getAdapter()->fetchAll($select);  
    }

	public function getResourceSelectData($id)
	{
		$select = $this->select()
			->from($this->_name, array('key' => 'id', 'value' => 'title'))
			->where('id != ?', $id);
		return $this->getAdapter()->fetchAll($select);
	}

	public function getRow($id)
	{
		return $this->getAdapter()->fetchRow($this->select()->from($this->_name)->where('id = ?', $id));
	}

	public function createEntry($id)
	{
		return ($id) ? $this->createSubEntry($id) : $this->createRootEntry();
	}

	public function deleteEntry($id)
	{
		$row = $this->getRow($id);
		
		if(!sizeof($row)) {
			throw new Exception('The entry you requested could not be found.');
		}

		$rows = $this->getChildren($row);

		if(sizeof($rows)) {
			throw new Exception('This entry has sub items and cannot be deleted.');
		}
		
		$this->delete($this->getAdapter()->quoteInto('id = ?', $id));
		
		$data = array( 
    		'right_id' => new Zend_Db_Expr("right_id - 2"),
		); 
		
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('left_id < ?', $row->right_id); 
		$where[] = $this->getAdapter()->quoteInto('right_id > ?', $row->right_id); 

		$this->update($data, $where); 
		
		$data = array( 
    		'left_id' => new Zend_Db_Expr("left_id - 2"),
			'right_id' => new Zend_Db_Expr("right_id - 2")
		); 
		
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('left_id > ?', $row->right_id); 

		$this->update($data, $where); 

		return true;
	}

	private function createSubEntry($parent_id)
	{
		$row = $this->getRow($parent_id);
		
		$data = array( 
    		'left_id' => new Zend_Db_Expr("left_id + 2"),
			'right_id' => new Zend_Db_Expr("right_id + 2")
		); 

		$where = array();
		$where[] = $this->getAdapter()->quoteInto('left_id > ?', $row->right_id); 

		$this->update($data, $where); 

		$data = array( 
    		'right_id' => new Zend_Db_Expr("right_id + 2")
		); 
		
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('left_id <= ?', $row->left_id); 
		$where[] = $this->getAdapter()->quoteInto('right_id >= ?', $row->left_id); 

		$this->update($data, $where); 
		
		return array('left_id' => $row->right_id, 'right_id' => $row->right_id + 1);
	}

	private function createRootEntry()
	{
		$select = $this->select()
			->from($this->_name, array('MAX(right_id) AS right_id'));
		$row = $this->getAdapter()->fetchRow($select);
		return array('left_id' => $row->right_id + 1, 'right_id' => $row->right_id + 2);
	}
	
	public function fetchMonthlyCount($ts,$te)
	{
		$select = $this->select()
			->from($this->_name, array('COUNT(id) AS orderkey'))
			->where("created >= ?", $ts)
			->where("created <= ?", $te)
			->where("approved <> ?", '0')
			->where("parent_id <> ?", '0')
			->where("draft <> ?", '1')
			->order('created DESC');
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function genNav($parent_id)
	{
		if(!$parent_id) {
			return array();
		}
		
		$data = $this->getRow($parent_id);
		
		if(!sizeof($data)) {
			return array();
		}

		$parents = $this->getParents($data, true);

		return (sizeof($parents)) ? $parents : array();
	}

	public function getParents($range, $target = false)
	{
		$switch = (true === $target) ? '=' : '';
		$select = $this->select()
			->from($this->_name)
			->where('right_id >'.$switch.' ?', (int) $range->right_id)
			->where('left_id <'.$switch.' ?', (int) $range->left_id);	
		return $this->getAdapter()->fetchAll($select);
	}

	public function getChildren($range, $target = false)
	{
		$switch = (true === $target) ? '=' : '';
		$select = $this->select()
			->from($this->_name)
			->where('right_id <'.$switch.' ?', (int) $range->right_id)
			->where('left_id >'.$switch.' ?', (int) $range->left_id);	
		return $this->getAdapter()->fetchAll($select);
	}
    
}