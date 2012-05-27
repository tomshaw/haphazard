<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_TutorialsCategories extends Zend_Db_Table_Abstract
{
	protected $_name = 'tutorials_categories';
    
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
    
	public function fetchCategoriesSelect()
	{
		$select = $this->select()
			->from($this->_name);
		$rows = $this->getAdapter()->fetchAll($select);
		
		$data = array();
		foreach($rows as $row) {
			$data[$row->id] = $row->title;
		}
		return $data;
	}
	
}
