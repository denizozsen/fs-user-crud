<?php

namespace FsTest\User;

use AOrm\Criteria;
use FsTest\Framework\Core\Controller;

/**
 * The controller for the User entity.
 *
 * @package FsTest\User
 */
class UserController extends Controller
{
    /**
     * Retrieves all user records matching the given filters (all records, if filters argument is omitted).
     *
     * @param array $filters optional associative array with key/value pairs, where the values are used for sub-string matching
     * @return array the array of matching users records, each of which is an associative array representing the fields of a record
     */
    public function getAll(array $filters = [])
    {
        $criteria = Criteria::create();
        foreach ($filters as $field => $value) {
            $criteria->addCondition(User::condition()->like($field, "%{$value}%"));
        }
        $users = User::fetchAll($criteria);
        array_walk($users, function(User &$user) {
            $user = $user->getData();
        });
        return $users;
    }

    /**
     * Saves the given user record to the database. If the record includes a user_id key, the existing record with
     * that user_id is updated, if one exists (otherwise an exception is thrown). Otherwise a new record with
     * the given data is inserted.
     *
     * Note that the value for the password field, if given, is hashed using the SHA-512 algorithm, before the record
     * is saved to the database.
     *
     * @param array $user_data the data of a single user record, to be saved to the database
     * @throws \Exception if something goes wrong
     */
    public function save(array $user_data)
    {
        // If user data includes user_id, assume it's an update, so we attempt to retrieve the existing record
        // If user_id is not included, we create a new record
        $user_model = null;
        if (
            !empty($user_data['user_id'])
            && $user_data['user_id'] == (string)(int)$user_data['user_id']
            && $user_data['user_id'] > 0
        ) {
            $user_model = User::fetchByPrimaryKey($user_data['user_id']);
        } else {
            unset($user_data['user_id']);
            $user_model = new User();
        }

        // If a password is set, hash it, using SHA-512
        if (
            isset($user_data['password'])
            && $user_data['password']
            && ( $user_model->isNew() || $user_model->password != $user_data['password'] )
        ) {
            $user_data['password'] = openssl_digest($user_data['password'], 'sha512');
        }

        // Save the given user data in the database
        foreach ($user_data as $key => $value) {
            $user_model->$key = $value;
        }
        if ($user_model->isNew()) {
            $user_model->insert();
        } else {
            $user_model->save();
        }
    }

    /**
     * Deletes the existing record with the given user_id from the database, if it is found, otherwise an exception
     * is thrown.
     *
     * @param $user_id
     * @throws \AOrm\AOrmException if a user with the given user_id does not exist
     * @throws \ErrorException if the given user_id is not a positive integer
     */
    public function delete($user_id)
    {
        if ( $user_id != (int)$user_id || $user_id <= 0 ) {
            throw new \ErrorException("user_id parameter must be a positive integer");
        }
        $user_model = User::fetchByPrimaryKey($user_id);
        $user_model->delete();
    }
}
