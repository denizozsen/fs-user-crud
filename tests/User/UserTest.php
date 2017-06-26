<?php

namespace FsTest\User;

use AOrm\Crud;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the User model class.
 *
 * @package FsTest\User
 */
class UserTest extends TestCase
{
    public function testCreateCrudInstance_IsACrud()
    {
        $crud_instance = User::createCrudInstance();
        $this->assertInstanceOf(Crud::class, $crud_instance);
    }
}
