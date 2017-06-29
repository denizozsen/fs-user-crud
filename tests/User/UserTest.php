<?php

namespace FsTest\User;

use AOrm\Criteria;
use AOrm\Crud;
use AOrm\Model;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the User model class.
 *
 * @package FsTest\User
 */
class UserTest extends TestCase
{
    /**
     * {@inheritdoc}
     *
     * Overrides all AOrm crud instances with instances of class UserTestMockCrud, defined at the bottom of this file.
     */
    public static function setUpBeforeClass()
    {
        Model::setCrudOverrideClass(UserTestMockCrud::class);
    }

    /**
     * {@inheritdoc}
     *
     * Cancels the crud override that was set in setUpBeforeClass().
     */
    public static function tearDownAfterClass()
    {
        Model::setCrudOverrideClass(null);
    }

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

/**
 * Mock implementation for faking the Crud to the AOrm user model and testing what the UserController does.
 *
 * @package FsTest\User
 */
class UserTestMockCrud implements Crud
{
    public function __construct($model_class)
    {
        $this->model_class = $model_class;
    }

    public function getModelClass()
    {
        return $this->model_class;
    }

    public function getPrimaryKey()
    {
        return 'user_id';
    }

    public function fetchOne(Criteria $criteria = null)
    {
        return null;
    }

    public function fetchAll(Criteria $criteria = null)
    {
        return [];
    }

    public function getRelatedJoinFragment($relation)
    {
        return null;
    }

    public function save(array $record)
    {
    }

    public function delete($pk_value)
    {
    }
}
