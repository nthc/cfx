<?php
abstract class BaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        error_reporting(E_ALL ^ E_NOTICE);
        Cache::init('volatile');
        $_SESSION["user_id"] = "1";
        Application::$config['log_level'] = 550;
    }
	
    protected function getSetUpOperation()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::INSERT();
    }
    
    protected function getTearDownOperation()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }
    
    protected function getConnection()
    {
        $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=nthc_test;user=postgres;password=hello');
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet(
            $this->getArrayDataSet()
        );
    }
    
    abstract protected function getArrayDataSet();
}
