<?php

namespace FsTest\Framework\CLI;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the CLI Application class.
 *
 * @package FsTest\Framework\CLI
 */
class ApplicationTest extends TestCase
{
    /** @var Application $fixture */
    private $fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Application();
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

//    public function testSetController()
//    {
//        $this->fixture->setController(null);
//    }
//
//    public function testInvokeAction()
//    {
//        $this->fixture->invokeAction(null, null);
//    }
}
