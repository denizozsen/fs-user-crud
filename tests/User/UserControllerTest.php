<?php

namespace FsTest\User;

use AOrm\AOrmException;
use AOrm\Criteria;
use AOrm\Crud;
use AOrm\Model;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UserController
 *
 * @package FsTest\User
 */
class UserControllerTest extends TestCase
{
    // Test user data
    const TEST_USER_1 = [ 'user_id' => 100,  'email' => 'test1.user@mail.com', 'first_name' => 'Test1', 'last_name' => 'User1', 'password' => 'd17f25ecfbcc7857f7bebea469308be0b2580943e96d13a3ad98a13675c4bfc2' ];
    const TEST_USER_2 = [ 'user_id' => 250,  'email' => 'test2.user@mail.com', 'first_name' => 'Test2', 'last_name' => 'User2', 'password' => 'cc399d73903f06ee694032ab0538f05634ff7e1ce5e8e50ac330a871484f34cf5' ];
    const TEST_USER_3 = [ 'user_id' => 3333, 'email' => 'another.person@mail.com', 'first_name' => 'Another', 'last_name' => 'Person', 'password' => 'f05cf0e1b0f53e4962118589d0dea67fcc461280dc7f1fbdc297ba2ec3d1070a' ];
    const TEST_EXISTING_USERS = [ self::TEST_USER_1, self::TEST_USER_2, self::TEST_USER_3 ];
    const TEST_NEW_USER = [ 'email' => 'new.user@mail.com', 'first_name' => 'New', 'last_name' => 'User', 'password' => '1234567' ];

    /** @var UserController $fixture */
    private $fixture;

    /**
     * {@inheritdoc}
     *
     * Overrides all AOrm crud instances with instances of class UserControllerTestMockCrud, defined at the bottom of this file.
     */
    public static function setUpBeforeClass()
    {
        Model::setCrudOverrideClass(UserControllerTestMockCrud::class);
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

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->fixture = new UserController();
        UserControllerTestMockCrud::reset();
    }

    public function testGetAll()
    {
        UserControllerTestMockCrud::$to_return = self::TEST_EXISTING_USERS;
        $actual = $this->fixture->getAll();
        $this->assertTrue(is_array($actual));
        $this->assertEquals(self::TEST_EXISTING_USERS, $actual);
        $this->assertCount(1, UserControllerTestMockCrud::$criterias);
        /** @var Criteria $criteria */
        $criteria = UserControllerTestMockCrud::$criterias[0];
        $this->assertNull($criteria->getCondition());
    }

    public function testGetAll_WithFilter()
    {
        UserControllerTestMockCrud::$to_return = self::TEST_EXISTING_USERS;
        $this->fixture->getAll(['first_name'=>'Test']);
        $this->assertCount(1, UserControllerTestMockCrud::$criterias);
        $this->assertInstanceOf(Criteria::class, UserControllerTestMockCrud::$criterias[0]);
    }

    public function testSave_NonExistentUser()
    {
        $this->fixture->save(self::TEST_NEW_USER);
        $this->assertCount(1, UserControllerTestMockCrud::$saves);
        $expected_user_data = self::TEST_NEW_USER;
        $expected_user_data['password'] = openssl_digest($expected_user_data['password'], 'sha512');
        $this->assertEquals($expected_user_data, UserControllerTestMockCrud::$saves[0]);
    }

    public function testSave_ExistingUser()
    {
        UserControllerTestMockCrud::$to_return = [ self::TEST_USER_1 ];
        $this->fixture->save(self::TEST_USER_1);
        $this->assertCount(1, UserControllerTestMockCrud::$saves);
        $this->assertEquals(self::TEST_USER_1, UserControllerTestMockCrud::$saves[0]);
    }

    public function testDelete_ExceptionOnInvalidId()
    {
        $this->expectException(\Exception::class);
        $this->fixture->delete('asd123');
        $this->assertTrue(true);
    }

    public function testDelete_ExceptionOnNonExistentId()
    {
        $user_id = 999999;
        $this->expectException(AOrmException::class);
        $this->fixture->delete($user_id);
        $this->assertTrue(true);
    }

    public function testDelete_ExistingId()
    {
        UserControllerTestMockCrud::$to_return = self::TEST_EXISTING_USERS;
        $user_id = UserControllerTestMockCrud::$to_return[0]['user_id'];
        $this->fixture->delete($user_id);
        $this->assertCount(1, UserControllerTestMockCrud::$deletes);
        $this->assertEquals($user_id, UserControllerTestMockCrud::$deletes[0]);
    }
}

/**
 * Mock implementation for faking the Crud to the AOrm user model and testing what the UserController does.
 *
 * @package FsTest\User
 */
class UserControllerTestMockCrud implements Crud
{
    public static $to_return = [];

    public static $criterias = [];
    public static $deletes = [];
    public static $saves = [];
    public static $inserts = [];

    private $model_class;

    public static function reset()
    {
        self::$to_return = [];
        self::$criterias = [];
        self::$deletes = [];
        self::$saves = [];
        self::$inserts = [];
    }

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
        switch ($this->model_class) {
            case User::class:
                return 'user_id';
            default:
                throw new \ErrorException("getPrimaryKey: unhandled model class: {$this->model_class}");
        }
    }

    public function fetchOne(Criteria $criteria = null)
    {
        self::$criterias[] = $criteria;
        return array_shift(self::$to_return);
    }

    public function fetchAll(Criteria $criteria = null)
    {
        self::$criterias[] = $criteria;
        $all = self::$to_return;
        self::$to_return = [];
        return $all;
    }

    public function getRelatedJoinFragment($relation)
    {
        return null;
    }

    public function save(array $record)
    {
        self::$saves[] = $record;
    }

    public function insert(array $record)
    {
        self::$inserts[] = $record;
    }

    public function delete($pk_value)
    {
        self::$deletes[] = $pk_value;
    }

}
