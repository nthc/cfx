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
    
    protected function getPDO()
    {
        $config = Application::$config['db'][getenv('CFX_SELECTED_DATABASE')];
        return new PDO("pgsql:host={$config['host']};port={$config['port']};dbname={$config['name']};user={$config['user']};password={$config['password']}");
    }
    
    protected function createSequence($name)
    {
        $pdo = $this->getPDO();
        $pdo->query("CREATE SEQUENCE {$name}");
        $pdo = null;
    }
    
    protected function dropSequence($name)
    {
        $pdo = $this->getPDO();
        $pdo->query("DROP SEQUENCE {$name}");
        $pdo = null;
    }

    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->getPDO());
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet(
            $this->getArrayDataSet()
        );
    }
    
    abstract protected function getArrayDataSet();
}
