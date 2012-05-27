<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_Polls extends Zend_Db_Table_Abstract
{
	protected $_name = 'polls';
    
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
    
    public function queryRandom()
    {
		$select = $this->getAdapter()->select()
			->from($this->_name, array('id'))
			->where('enabled = ?', true)
			->order('RAND()')
			->limit('1');	
		return $this->getAdapter()->fetchAll($select); 
    }
    
    public function randomPoll()
    {
    	
    	$array = $this->queryRandom();
    	$id = $array[0]->id;
		$select = $this->getAdapter()->select()
			->from(array('poll' => 'polls'), array('id','title','description','starts','expires','interval','enabled','votefirst','confidential','ip','cookie','created'))
			->joinLeft(array('options' => 'polls_options'), 'poll.id = options.poll_id', array('id as option_id','poll_id','options','votes','order_id'))
			->where('poll.id = ?', $id)
			->where('poll.enabled = ?', true)
			->order('options.order_id ASC');	
		return $this->getAdapter()->fetchAll($select); 
    }
    
    public function updatePoll($data)
    {		
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('id = ?', $data['id']); 
		return $this->update($data, $where); 
    }
	
}