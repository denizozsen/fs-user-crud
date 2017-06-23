<?php

namespace FsTest\User;

use AOrm\Crud;

/**
 * Unit tests for the User model class.
 *
 * @package FsTest\User
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCrudInstance_IsACrud()
    {
        $crud_instance = User::createCrudInstance();
        $this->assertInstanceOf(Crud::class, $crud_instance);
    }
}
