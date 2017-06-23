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
}
