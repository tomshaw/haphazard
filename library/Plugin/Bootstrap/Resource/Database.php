<?php 
/**
 * 
 * @author Tom Shaw
 *
 */
class App_Bootstrap_Resource_Database extends Zend_Application_Resource_ResourceAbstract
{

    public function init()
    {
    	$bootstrap = $this->getBootstrap();
    	if (($container = $bootstrap->getContainer()) && isset($container->db)) {
    		$db = $container->db;
    		$db->getConnection();
        	$db->setFetchMode(Zend_Db::FETCH_OBJ);
        	$db->query("SET NAMES 'utf8'");
        	$db->query("SET CHARACTER SET 'utf8'");
        	return $db;
		}
		return $this;
    }
}