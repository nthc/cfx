<?php
error_reporting(E_ALL ^ E_NOTICE);

Application::$config['log_level'] = 550;

abstract class BaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Cache::init('volatile');
        $_SESSION["user_id"] = "1";
        //$this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::firefox());
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
}
