<?php

namespace FsTest\User;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UserCliApp
 *
 * @package FsTest\User
 */
class UserCliAppTest extends TestCase
{
    const TEST_USER = [ 'user_id' => 100,  'email' => 'test1.user@mail.com', 'first_name' => 'Test1', 'last_name' => 'User1', 'password' => 'd17f25ecfbcc7857f7bebea469308be0b2580943e96d13a3ad98a13675c4bfc2' ];

    /** @var UserCliApp $fixture */
    private $fixture;
    /** @var \PHPUnit_Framework_MockObject_MockObject $fixture_mock */
    private $fixture_mock;

    /** @var UserCliApp $fixture_with_mocked_getArguments */
    private $fixture_with_mocked_getArguments;
    /** @var \PHPUnit_Framework_MockObject_MockObject $fixture_mock_with_mocked_getArguments */
    private $fixture_mock_with_mocked_getArguments;

    /** @var UserController $fixture */
    private $controller;
    /** @var \PHPUnit_Framework_MockObject_MockObject $fixture_mock */
    private $controller_mock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // Set up mocked version of UserController, with fake versions of all its methods
        $this->controller_mock = $this
            ->getMockBuilder(UserController::class)
            ->setMethods(['getAll', 'save', 'delete'])
            ->getMock();
        $this->controller_mock
            ->expects($this->any())
            ->method('getAll')
            ->willReturn(self::TEST_USER);
        $this->controller = $this->controller_mock;

        // Set up object under test: UserCliApp, with fake version of getController()
        $this->fixture_mock = $this
            ->getMockBuilder(UserCliApp::class)
            ->setMethods([ 'getController' ])
            ->getMock();
        $this->fixture_mock
            ->expects($this->any())
            ->method('getController')
            ->willReturn($this->controller);
        $this->fixture = $this->fixture_mock;

        // Set up alternate object under test: UserCliApp, with fake versions of getArguments() and getController()
        $this->fixture_mock_with_mocked_getArguments = $this
            ->getMockBuilder(UserCliApp::class)
            ->setMethods([ 'getArguments', 'getController' ])
            ->getMock();
        $this->fixture_mock_with_mocked_getArguments
            ->expects($this->any())
            ->method('getController')
            ->willReturn($this->controller);
        $this->fixture_with_mocked_getArguments = $this->fixture_mock_with_mocked_getArguments;

        // Turn on output buffering, to ignore the output of the CLI app
        ob_start();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        // Turn off output buffering, which we turned on in setUp()
        ob_end_clean();
    }

    public function testRun_PrintsUsage()
    {
        $_SERVER['argv'] = [ 'dummy.php' ];

        $this->controller_mock->expects($this->never())->method('getAll');
        $this->controller_mock->expects($this->never())->method('save');
        $this->controller_mock->expects($this->never())->method('delete');

        $this->fixture->start();
    }

    public function testRun_HandlesException()
    {
        $_SERVER['argv'] = [ 'dummy.php' ];

        $this->fixture_mock_with_mocked_getArguments
            ->expects($this->any())
            ->method('getArguments')
            ->willThrowException(new \ErrorException('just a dummy exception'));

        $this->controller_mock->expects($this->never())->method('getAll');
        $this->controller_mock->expects($this->never())->method('save');
        $this->controller_mock->expects($this->never())->method('delete');

        try {
            $this->fixture_mock_with_mocked_getArguments->start();
        } catch (\Exception $e) {
            echo get_class($e) . ': ' . $e->getTraceAsString(); die();
        }
    }

    public function testRun_CallsSaveOnCreate1()
    {
        $this->doTestRun_CallsSaveOnCreate('--create');
    }

    public function testRun_CallsSaveOnCreate2()
    {
        $this->doTestRun_CallsSaveOnCreate('-c');
    }

    private function doTestRun_CallsSaveOnCreate($command)
    {
        $user_data_query_string = 'email=my.mail@mail.com&first_name=My&last_name=Name';
        parse_str($user_data_query_string, $parsed_user_data);

        $_SERVER['argv'] = [ 'dummy.php', $command, $user_data_query_string ];

        $this->controller_mock
            ->expects($this->once())
            ->method('save')
            ->with($parsed_user_data);

        $this->fixture->start();
    }

    public function testRun_CallsSaveOnUpdate1()
    {
        $this->doTestRun_CallsSaveOnUpdate('--update');
    }

    public function testRun_CallsSaveOnUpdate2()
    {
        $this->doTestRun_CallsSaveOnUpdate('-u');
    }

    private function doTestRun_CallsSaveOnUpdate($command)
    {
        $user_data_query_string = 'user_id=123&email=my.mail@mail.com&first_name=My&last_name=Name';
        parse_str($user_data_query_string, $parsed_user_data);

        $_SERVER['argv'] = [ 'dummy.php', $command, $user_data_query_string ];

        $this->controller_mock
            ->expects($this->once())
            ->method('save')
            ->with($parsed_user_data);

        $this->fixture->start();
    }

    public function testRun_ThrowsOnOmittingUserIdInUpdate()
    {
        $_SERVER['argv'] = [ 'dummy.php', '-u', 'email=my.mail@mail.com&first_name=My&last_name=Name' ];
        ob_start();
        $this->fixture->start();
        $output = ob_get_clean();
        $output_contains_error = strpos(strtolower($output), 'error') !== false;
        $this->assertTrue($output_contains_error);
    }

    public function testRun_CallsDelete1()
    {
        $this->doTestRun_CallsDelete('--delete');
    }

    public function testRun_CallsDelete2()
    {
        $this->doTestRun_CallsDelete('-d');
    }

    private function doTestRun_CallsDelete($command)
    {
        $user_id = '3123';
        $_SERVER['argv'] = [ 'dummy.php', $command, $user_id ];

        $this->controller_mock
            ->expects($this->once())
            ->method('delete')
            ->with($user_id);

        $this->fixture->start();
    }

    public function testRun_CallsGetAllWithoutFilter1()
    {
        $this->doTestRun_CallsGetAllWithoutFilter('--retrieve-all');
    }

    public function testRun_CallsGetAllWithoutFilter2()
    {
        $this->doTestRun_CallsGetAllWithoutFilter('-a');
    }

    private function doTestRun_CallsGetAllWithoutFilter($command)
    {
        $_SERVER['argv'] = [ 'dummy.php', $command];

        $this->controller_mock
            ->expects($this->once())
            ->method('getAll')
            ->with([]);

        $this->fixture->start();
    }

    public function testRun_CallsGetAllWithFilter1()
    {
        $this->doTestRun_CallsGetAllWithFilter('--retrieve');
    }

    public function testRun_CallsGetAllWithFilter2()
    {
        $this->doTestRun_CallsGetAllWithFilter('-r');
    }

    private function doTestRun_CallsGetAllWithFilter($command)
    {
        $filter_query_string = 'user_id=123&email=my.mail@mail.com&first_name=My&last_name=Name';
        parse_str($filter_query_string, $parsed_filter);
        $_SERVER['argv'] = [ 'dummy.php', $command, $filter_query_string];

        $this->controller_mock
            ->expects($this->once())
            ->method('getAll')
            ->with($parsed_filter);

        $this->fixture->start();
    }

    public function testGetController()
    {
        $user_cli_app = new UserCliApp();
        $r = new \ReflectionObject($user_cli_app);
        $getController = $r->getMethod('getController');
        $getController->setAccessible(true);
        $returned_controller = $getController->invoke($user_cli_app);
        $this->assertInstanceOf(UserController::class, $returned_controller);
    }
}
