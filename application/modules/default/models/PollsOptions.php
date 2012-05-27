<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_PollsOptions extends Zend_Db_Table_Abstract
{

	protected $_name = 'polls_options';
    
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
    
    public function updateVotes($id,$votes)
    {
    	$data = array('votes' => $votes); 
    	return $this->update($data, array('id = ?' => $id));
    }
    
	public function getVoteCount($pollId)
	{
		$select = $this->select()
			->from($this->_name, array('SUM(votes) AS total'))
			->where('poll_id = ?', $pollId);
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getResultsData($pollId, $order = 'ASC')
	{
		$select = $this->select()
			->from($this->_name, array('id', 'options', 'votes', 'order_id'))
			->where('poll_id = ?', $pollId)
			->order('order_id ' . $order);
		return $this->getAdapter()->fetchAll($select);
	}
	
	public function getOptions($pollId, $order = 'ASC')
	{
		$select = $this->select()
			->from($this->_name, array('id', 'options', 'votes', 'order_id'))
			->where('poll_id = ?', $pollId)
			->order('order_id ' . $order);
		return $this->getAdapter()->fetchAll($select);
	}
	
    public function reorderOptions($pollId)
    {	
    	$data = self::getResultsData($pollId, 'ASC');
    	
		$i = 10;
		foreach($data as $count) {
			$this->update(array('order_id' => $i), 'id = ' . $count->id); 
			$i += 10;
		}
    }	
}
