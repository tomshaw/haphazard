<?php

class Install_Model_Setup extends Zend_Db_Table_Abstract
{
	protected $_name = 'user';
	
	protected $_schema = 'schema.sql';
	
    public function getColumns()
    {
        return $this->info(self::COLS);
    }
    
    public function getDatabaseSize()
    {
    	$stmt = $this->getAdapter()->getConnection()->prepare('SHOW TABLE STATUS');
    
    	$stmt->execute();
    
    	$data = $stmt->fetchAll();
    
    	$dbsize = 0;
    	foreach($data as $row) {
    		$dbsize += $row["Data_length"] + $row["Index_length"];
    	}
    
    	return Plugin_Function::getFormattedFilesize($dbsize);
    }
    
    public function getServerVersion()
    {
    	return $this->getAdapter()->getServerVersion();
    }
    
    private function getSchema()
    {
    	$fileName = APPLICATION_PATH . '/configs/setup/' . $this->_schemaFile;
    	if(file_exists($fileName)) {
    		return file_get_contents($fileName);
    	}
    	return false;
    }
    
    public function initSetup()
    {
    	$adapter = $this->getAdapter();
    
    	$schemaSql = $this->getSchema();
    
    	if(!$schemaSql) {
    		throw new Exception('Could not open schema file.');
    		return;
    	}
    
    	try {
    		$adapter->getConnection()->exec($schemaSql);
    	} catch(Exception $e) {
    		throw $e;
    	}
    	return;
    }
}