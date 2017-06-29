<?php

namespace FsTest\User;

use AOrm\DbCrud;
use AOrm\Model;

/**
 * The user model.
 *
 * @package FsTest\User
 *
 * @property int $user_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $password
 */
class User extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function createCrudInstance()
    {
        return new DbCrud(get_class(), 'user', 'user_id');
    }

    /**
     * @throws \ErrorException, if the data in this User is invalid.
     */
    public function validate()
    {
        $errors = [];
        if ( isset($this->user_id) && ((string)(int)$this->user_id != $this->user_id) ) {
            $errors[] = "user_id must be an integer, but was: {$this->user_id}";
        }
        if ( !isset($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL) ) {
            $errors[] = "email must be a valid email address, but was: " . (isset($this->email) ? $this->email : '(not set)');
        }
        if ( !isset($this->first_name) || !is_string($this->first_name) || !strlen($this->first_name) ) {
            $errors[] = "first_name must be a non-empty string, but was: " . (isset($this->first_name) ? $this->first_name : '(not set)');
        }
        if ( !isset($this->last_name) || !is_string($this->last_name) || !strlen($this->last_name) ) {
            $errors[] = "last_name must be a non-empty string, but was: " . (isset($this->last_name) ? $this->last_name : '(not set)');
        }
        if ( !isset($this->password) || !is_string($this->password) || !strlen($this->password) ) {
            $errors[] = "password must be a non-empty string, but was: " . (isset($this->password) ? $this->password : '(not set)');
        }

        if (count($errors)) {
            throw new \ErrorException(implode(PHP_EOL, $errors));
        }
    }

    /**
     * {@inheritdoc}
     *
     * Throws an \ErrorException, if the data in this User is invalid.
     */
    protected function beforeSave()
    {
        parent::beforeSave();
        $this->validate();
    }
}
