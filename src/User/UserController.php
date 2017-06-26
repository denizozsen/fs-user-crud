<?php

namespace FsTest\User;

use FsTest\Framework\Core\Controller;

/**
 * The controller for the User entity.
 *
 * @package FsTest\User
 */
class UserController extends Controller
{
    public function getAll(array $filters = [])
    {
        $users = User::fetchAll($filters);
        array_walk($users, function(User &$user) {
            $user = $user->getData();
        });
        return $users;
    }

    public function save(array $user_data)
    {
        $user_model = null;
        if (
            !empty($user_data['user_id'])
            && $user_data['user_id'] == (int)$user_data['user_id']
            && $user_data['user_id'] > 0
        ) {
            $user_model = User::fetchByPrimaryKey($user_data['user_id']);
        } else {
            unset($user_data['user_id']);
            $user_model = new User();
        }
        $user_model->setData($user_data);
        $user_model->save();
    }

    public function delete($user_id)
    {
        if ( $user_id != (int)$user_id || $user_id <= 0 ) {
            throw new \ErrorException("user_id parameter must be a positive integer");
        }
        $user_model = User::fetchByPrimaryKey($user_id);
        $user_model->delete();
    }
}
