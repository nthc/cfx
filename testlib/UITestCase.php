<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

abstract class UITestCase extends BaseTestCase
{
    /**
     *
     * @var WebDriver
     */
    protected $driver;
    private $testId;

    public function setUp()
    {
        parent::setUp();
        $this->testId = uniqid();
        $this->driver = RemoteWebDriver::create(
            'http://localhost:4444/wd/hub', 
            DesiredCapabilities::firefox()
        );
    }
    
    protected function open()
    {
        $this->driver->get(getenv('CFX_TEST_WEB_HOST'));
        $this->driver->manage()->addCookie(
            ['name' => 'CFX_TEST_ID', 'value' => $this->testId]
        );
        $this->navigateTo('');
    }
    
    protected function login()
    {
        $this->driver->findElement(WebDriverBy::id('username'))->sendKeys('dev');
        $this->driver->findElement(WebDriverBy::id('password'))->sendKeys('dev');
        $this->driver->findElement(WebDriverBy::cssSelector('#fapi-submit-area > input'))->click();        
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
        + $this->getUIArrayDataSet();
    }
    
    protected function navigateTo($path)
    {
        $this->driver->navigate()->to(getenv('CFX_TEST_WEB_HOST') . $path);
    }

    protected function find($selector)
    {
        return $this->driver->findElement(WebDriverBy::cssSelector($selector));
    }
    
    public function run(PHPUnit_Framework_TestResult $result = NULL) {
        if($result === NULL) {
            $result = $this->createResult();
        }
        
        parent::run($result);
        
        if($result->getCollectCodeCoverageInformation()) {
            $result->getCodeCoverage()->append(
                unserialize(file_get_contents(getenv('CFX_TEST_WEB_HOST') . "/coverage_data/{$this->testId}")),
                $this
            );
        }
        
        return $result;
    }

    abstract protected function getUIArrayDataSet();
}
