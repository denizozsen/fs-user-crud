<?php

namespace FsTest\User;

use AOrm\Criteria;
use AOrm\Crud;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the User model class.
 *
 * @package FsTest\User
 */
class UserTest extends TestCase
{
    private function newPopulatedUser()
    {
        $user = new User();
        $user->email = 'a.user@mail.com';
        $user->first_name = 'A';
        $user->last_name = 'User';
        $user->password = 'myPassWord123';
        return $user;
    }

    public function testCreateCrudInstance_IsACrud()
    {
        $crud_instance = User::createCrudInstance();
        $this->assertInstanceOf(Crud::class, $crud_instance);
    }

    public function testValidate_Positive1()
    {
        $user = $this->newPopulatedUser();
        $user->validate();
        $this->assertTrue(true);
    }

    public function testValidate_Positive2()
    {
        $user = $this->newPopulatedUser();
        $user->user_id = 223344;
        $user->validate();
        $this->assertTrue(true);
    }

    public function testValidate_Negative1()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        $user->user_id = 'non-numeric';
        $user->validate();
    }

    public function testValidate_Negative2()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        unset($user->email);
        $user->validate();
    }

    public function testValidate_Negative3()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        $user->email = 'invalid.email.com';
        $user->validate();
    }

    public function testValidate_Negative4()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        $user->first_name = '';
        $user->validate();
    }

    public function testValidate_Negative5()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        unset($user->first_name);
        $user->validate();
    }

    public function testValidate_Negative6()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        unset($user->last_name);
        $user->validate();
    }

    public function testValidate_Negative7()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        $user->last_name = '';
        $user->validate();
    }

    public function testValidate_Negative8()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        unset($user->password);
        $user->validate();
    }

    public function testValidate_Negative9()
    {
        $this->expectException(\Exception::class);
        $user = $this->newPopulatedUser();
        $user->password = '';
        $user->validate();
    }
}
