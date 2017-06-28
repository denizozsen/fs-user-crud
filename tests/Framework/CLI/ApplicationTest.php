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
    /** @var \PHPUnit_Framework_MockObject_MockObject $fixture_mock */
    private $fixture_mock;

    /** @var Application $fixture_without_argument_configuration */
    private $fixture_without_argument_configuration;
    /** @var \PHPUnit_Framework_MockObject_MockObject $fixture_without_argument_configuration_mock*/
    private $fixture_without_argument_configuration_mock;

    /** @var Controller $controller */
    private $controller;
    /** @var \PHPUnit_Framework_MockObject_MockObject $controller_mock */
    private $controller_mock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->fixture_mock = $this
            ->getMockBuilder(Application::class)
            ->setMethods(['run', 'getArgumentConfiguration', 'getController'])
            ->getMockForAbstractClass();
        $this->fixture = $this->fixture_mock;

        $this->fixture_without_argument_configuration_mock = $this
            ->getMockBuilder(Application::class)
            ->setMethods(['run', 'getController'])
            ->getMockForAbstractClass();
        $this->fixture_without_argument_configuration = $this->fixture_without_argument_configuration_mock;

        $this->controller_mock =
            $this->getMockBuilder(Controller::class)
            ->setMethods([self::CONTROLLER_ACTION_WITHOUT_ARGS, self::CONTROLLER_ACTION_WITH_ARGS])
            ->getMock();
        $this->controller = $this->controller_mock;
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure1()
    {
        $this->expectException(\Exception::class);
        $this->start(123);
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure2()
    {
        $this->expectException(\Exception::class);
        $this->start([ ['name' => 'foo'] ]);
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure3()
    {
        $this->expectException(\Exception::class);
        $this->start([ ['type' => 'invalid_type'] ]);
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure4()
    {
        $this->expectException(\Exception::class);
        $this->start([ ['type' => Application::ARG_TYPE_SIMPLE, 'position' => 0] ]);
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure5()
    {
        $this->expectException(\Exception::class);
        $this->start([ ['name' => 'foo', 'type' => Application::ARG_TYPE_SIMPLE] ]);
    }

    public function testArgumentConfiguration_ThrowsOnInvalidStructure6()
    {
        $this->expectException(\Exception::class);
        $this->start([
            ['name' => 'foo', 'type' => Application::ARG_TYPE_SIMPLE, 'position' => 0, 'optional'=>true],
            ['name' => 'bar', 'type' => Application::ARG_TYPE_SIMPLE, 'position' => 1]
        ]);
    }

    public function testArgumentConfiguration_BaseImplementationReturnsEmptyArray()
    {
        $this->startWithoutArgumentConfiguration();

        // Call protected method getArgumentConfiguration reflectively
        $reflection = new \ReflectionClass(get_class($this->fixture_without_argument_configuration));
        $method = $reflection->getMethod('getArgumentConfiguration');
        $method->setAccessible(true);
        $actual = $method->invokeArgs($this->fixture_without_argument_configuration, []);
        $this->assertEquals([], $actual);
    }

    public function testGetArguments_WithoutConfig1()
    {
        $this->setCommandLineArguments([ 'hello', 'world' ]);
        $this->start();
        $this->assertEquals([ 'hello', 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithoutConfig2()
    {
        $this->setCommandLineArguments([ '--foo', 'bar', '-a', 'some_value', 'hello', 'world' ]);
        $this->start();
        $this->assertEquals([ 'foo' => 'bar', 'a' => 'some_value', 'hello', 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithConfig1()
    {
        $this->setCommandLineArguments([ 'hello', 'world' ]);
        $this->start([
            [ 'type' => Application::ARG_TYPE_SWITCH, 'name' => 'irrelevant', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'mandatory1', 'position' => 0 ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'mandatory2', 'position' => 1 ],
        ]);
        $this->assertEquals([ 'mandatory1' => 'hello', 'mandatory2' => 'world' ], $this->fixture->getArguments());
    }

    public function testGetArguments_WithConfig2()
    {
        $this->setCommandLineArguments([ '--foo', 'bar', '-a', '-yyes', 'hello', 'world' ]);
        $this->start([
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'foo' ],
            [ 'type' => Application::ARG_TYPE_SWITCH, 'name' => 'another', 'short_name' => 'a', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'yet-another', 'short_name' => 'y', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'm1', 'position' => 0 ],
            [ 'type' => Application::ARG_TYPE_SIMPLE, 'name' => 'm2', 'position' => 1 ],
        ]);
        $this->assertEquals([ 'foo'=>'bar', 'another'=>'another', 'yet-another'=>'yes', 'm1' => 'hello', 'm2' => 'world' ], $this->fixture->getArguments());
    }

    public function testGetController_ThrowsIfIncorrectClass()
    {
        $this->expectException(\Exception::class);
        $this->start([], new \stdClass());
    }

    private function start($argument_configuration = [], $controller = null)
    {
        $this->fixture_mock
            ->expects($this->any())
            ->method('getArgumentConfiguration')
            ->willReturn($argument_configuration);

        $this->fixture_mock
            ->expects($this->any())
            ->method('getController')
            ->willReturn($controller ?: $this->controller);

        $this->fixture->start();
    }

    private function startWithoutArgumentConfiguration($controller = null)
    {
        $this->fixture_without_argument_configuration_mock
            ->expects($this->any())
            ->method('getController')
            ->willReturn($controller ?: $this->controller);

        $this->fixture_without_argument_configuration->start();
    }

    private function setCommandLineArguments(array $args)
    {
        $_SERVER['argv'] = array_merge([ 'dummy.php' ], $args);
    }
}
