<?php

namespace FsTest\Framework\CLI;

use FsTest\Framework\Core\Controller;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the CLI Application class.
 *
 * @package FsTest\Framework\CLI
 */
class ApplicationTest extends TestCase
{
    const CONTROLLER_ACTION_WITHOUT_ARGS = 'withoutArgs';
    const CONTROLLER_ACTION_WITH_ARGS = 'withArgs';
    const NON_EXISTENT_CONTROLLER_ACTION = 'nonExistent';

    /** @var Application $fixture */
    private $fixture;

    /** @var \PHPUnit_Framework_MockObject_MockObject $mock_controller */
    private $mock_controller;

    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Application();
        $this->mock_controller =
            $this->getMockBuilder(Controller::class)
            ->setMethods([self::CONTROLLER_ACTION_WITHOUT_ARGS, self::CONTROLLER_ACTION_WITH_ARGS])
            ->getMock();
    }

    public function testSetArgumentConfiguration_ThrowsOnInvalidStructure1()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setArgumentConfiguration([ ['name' => 'foo'] ]);
    }

    public function testSetArgumentConfiguration_ThrowsOnInvalidStructure2()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setArgumentConfiguration([ ['type' => 'invalid_type'] ]);
    }

    public function testSetArgumentConfiguration_ThrowsOnInvalidStructure3()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setArgumentConfiguration([ ['type' => Application::ARG_TYPE_SIMPLE, 'position' => 0] ]);
    }

    public function testSetArgumentConfiguration_ThrowsOnInvalidStructure4()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setArgumentConfiguration([ ['name' => 'foo', 'type' => Application::ARG_TYPE_SIMPLE] ]);
    }

    public function testSetArgumentConfiguration_ThrowsOnInvalidStructure5()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setArgumentConfiguration([
            ['name' => 'foo', 'type' => Application::ARG_TYPE_SIMPLE, 'position' => 0, 'optional'=>true],
            ['name' => 'bar', 'type' => Application::ARG_TYPE_SIMPLE, 'position' => 1]
        ]);
    }

    public function testGetArguments_WithoutConfig1()
    {
        $_SERVER['argv'] = [ 'hello', 'world' ];
        $this->assertEquals([ 'hello', 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithoutConfig2()
    {
        $_SERVER['argv'] = [ '--foo', 'bar', '-a', 'some_value', 'hello', 'world' ];
        $this->assertEquals([ 'foo' => 'bar', 'a' => 'some_value', 'hello', 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithConfig1()
    {
        $_SERVER['argv'] = [ 'hello', 'world' ];
        $this->fixture->setArgumentConfiguration([
            [ 'type' => Application::ARG_TYPE_SWITCH, 'name' => 'irrelevant', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'mandatory1', 'position' => 0 ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'mandatory2', 'position' => 1 ],
        ]);
        $this->assertEquals([ 'mandatory1' => 'hello', 'mandatory2' => 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithConfig2()
    {
        $_SERVER['argv'] = [ '--foo', 'bar', '-a', '-yyes', 'hello', 'world' ];
        $this->fixture->setArgumentConfiguration([
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'foo' ],
            [ 'type' => Application::ARG_TYPE_SWITCH, 'name' => 'another', 'short_name' => 'a', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'yet-another', 'short_name' => 'y', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'm1', 'position' => 0 ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'm2', 'position' => 1 ],
        ]);
        $this->assertEquals([ 'foo'=>'bar', 'another'=>'another', 'yet-another'=>'yes', 'm1' => 'hello', 'm2' => 'world' ], $this->fixture->getArguments());
    }

    public function testGetController_NullIfNoneSet()
    {
        $this->assertNull($this->fixture->getController());
    }

    public function testGetController_ReturnsSetInstance()
    {
        $this->fixture->setController($this->mock_controller);
        $this->assertSame($this->mock_controller, $this->fixture->getController());
    }

    public function testInvokeAction_ThrowsIfNoControllerSet()
    {
        $this->expectException(\Exception::class);
        $this->fixture->invokeAction('dummyActionName');
    }

    public function testInvokeAction_ThrowsForNonExistentAction()
    {
        $this->expectException(\Exception::class);
        $this->fixture->setController($this->mock_controller);
        $this->fixture->invokeAction(self::NON_EXISTENT_CONTROLLER_ACTION);
    }

    public function testInvokeAction_CallsControllerMethodWithoutArgs()
    {
        $this->mock_controller->expects($this->once())->method(self::CONTROLLER_ACTION_WITHOUT_ARGS);
        $this->fixture->setController($this->mock_controller);
        $this->fixture->invokeAction(self::CONTROLLER_ACTION_WITHOUT_ARGS);
    }

    public function testInvokeAction_CallsControllerMethodWithArgs()
    {
        $arg1_name = 'my_string_arg';
        $arg1_value = 'my_string_value';
        $arg2_name = 'my_int_arg';
        $arg2_value = 123;
        $this->mock_controller->expects($this->once())
            ->method(self::CONTROLLER_ACTION_WITH_ARGS)
            ->with($arg1_value, $arg2_value);
        $this->fixture->setController($this->mock_controller);
        $this->fixture->invokeAction(self::CONTROLLER_ACTION_WITH_ARGS, [$arg1_name=>$arg1_value, $arg2_name=>$arg2_value]);
    }

    public function testInvokeAction_ReturnsControllerReturnValue()
    {
        $arg1_name = 'my_string_arg';
        $arg1_value = 'my_string_value';
        $arg2_name = 'my_int_arg';
        $arg2_value = 123;
        $expected_return_value = 'my return value';
        $this->mock_controller->expects($this->any())
            ->method(self::CONTROLLER_ACTION_WITH_ARGS)
            ->with($arg1_value, $arg2_value)
            ->will($this->returnValue($expected_return_value));
        $this->fixture->setController($this->mock_controller);
        $actual_return_value = $this->fixture->invokeAction(self::CONTROLLER_ACTION_WITH_ARGS, [$arg1_name=>$arg1_value, $arg2_name=>$arg2_value]);
        $this->assertEquals($expected_return_value, $actual_return_value);
    }
}
