<?php

class BaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $_SESSION["user_id"] = "1";
    }
	
    protected function getSetUpOperation()
    {
        return $this->getOperations()->CLEAN_INSERT(TRUE);
    }
    
    protected function getConnection()
    {
        $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=test;user=postgres;password=hello');
        return $this->createDefaultDBConnection($pdo);
    }	
}
