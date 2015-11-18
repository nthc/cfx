<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

abstract class UITestCase extends BaseTestCase
{
    
    protected $driver;

    public function setUp()
    {
        parent::setUp();
        $this->driver = RemoteWebDriver::create(
            'http://localhost:4444/wd/hub', 
            DesiredCapabilities::firefox()
        );
    }
    
    public function setBranchId()
    {
        
    }
    
    protected function login()
    {
        $this->driver->get($_ENV['CFX_TEST_WEB_HOST']);
        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('dev');
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('dev');
        $this->driver->findElement(WebDriverBy::cssSelector('#fapi-submit-area > input'))->click();        
    }
    
    protected function getBranchTables()
    {
        return [
            'common.branches' => [
                ['branch_id' => 1, 'branch_name' => 'Adabraka']
            ]
        ];
    }

    protected function getArrayDataSet()
    {
        return [
            'common.roles' => [
                ['role_id' => 1, 'role_name' => 'Super User']
            ],
            'common.users' => [
                [
                    'user_id' => 1,
                    'user_name' => 'dev',
                    'password' => md5('dev'),
                    'role_id' => 1,
                    'first_name' => 'Development',
                    'last_name' => 'User',
                    'email' => 'dev@nthc.com.gh',
                    'user_status' => 1
                ]
            ]
        ] 
        + $this->getBranchTables() 
        + $this->getUIArrayDataSet();
    }
    
    abstract protected function getUIArrayDataSet();
}
