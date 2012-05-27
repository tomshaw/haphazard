<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Default_Model_PollsIp extends Zend_Db_Table_Abstract
{
	protected $_name = 'polls_ip';
    
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
    
    public function checkIpAddress($pollId, $ip)
    {
		$select = $this->select()
			->from($this->_name, array('id','ip','created'))
			->where('poll_id = ?', $pollId)
			->where('ip = ?', $ip);
		$retval = $this->getAdapter()->fetchRow($select);
		return (sizeof($retval)) ? $retval : false;
	}
	
    public function updateEntry($time,$data)
    {		
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('poll_id = ?', $data['poll_id']); 
		$where[] = $this->getAdapter()->quoteInto('ip = ?', $data['ip']); 
		return $this->update(array('created' => $time), $where); 
    }
    
    public function insertEntry($data)
    {
    	return $this->insert($data);	
    }
    
    public function deleteEntry($id, $ip)
    {
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('poll_id = ?', $id); 
		$where[] = $this->getAdapter()->quoteInto('ip = ?', $ip); 
		$this->delete($where);
		return true;
    }
	
}