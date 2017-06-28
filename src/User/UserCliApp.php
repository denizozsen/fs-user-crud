<?php

namespace FsTest\User;

use FsTest\Framework\CLI\Application;

class UserCliApp extends Application
{
    const ACTION_CREATE       = 'create';
    const ACTION_UPDATE       = 'update';
    const ACTION_DELETE       = 'delete';
    const ACTION_RETRIEVE     = 'retrieve';
    const ACTION_RETRIEVE_ALL = 'retrieve-all';

    /**
     * This method must be overridden by the CLI application sub class to implement the program.
     */
    protected function run()
    {
        try {
            $args = $this->getArguments();
            /** @var UserController $controller */
            $controller = $this->getController();

            switch ($this->getAction()) {

                case self::ACTION_CREATE:
                    $user = $this->convertInputToArray($args['create']);
                    $controller->save($user);
                    echo "The user was successfully created." . PHP_EOL;
                    break;

                case self::ACTION_UPDATE:
                    $user = $this->convertInputToArray($args['update']);
                    $controller->save($user);
                    echo "The user was successfully updated." . PHP_EOL;
                    break;

                case self::ACTION_DELETE:
                    $user_id = $args['delete'];
                    $controller->delete($user_id);
                    echo "User with id {$user_id} successfully deleted." . PHP_EOL;
                    break;

                case self::ACTION_RETRIEVE:
                    $filters = $this->convertInputToArray($args['retrieve']);
                    $output = $this->convertArrayToOutput($controller->getAll($filters));
                    echo PHP_EOL . "The requested user data follows:" . PHP_EOL . PHP_EOL;
                    echo $output;
                    break;

                case self::ACTION_RETRIEVE_ALL:
                    $output = $this->convertArrayToOutput($controller->getAll());
                    echo PHP_EOL . "All users:" . PHP_EOL . PHP_EOL;
                    echo $output;
                    break;

                default:
                    $this->printUsage();
                    break;

            }

        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}";
        }
    }

    private function convertInputToArray($input)
    {
        parse_str($input, $array);
        return $array;
    }

    private function convertArrayToOutput(array $array)
    {
        return print_r($array, true);
    }

    private function getAction()
    {
        $args = $this->getArguments();

        if (isset($args['create'])) {
            return self::ACTION_CREATE;
        } elseif (isset($args['update'])) {
            return self::ACTION_UPDATE;
        } elseif (isset($args['delete'])) {
            return self::ACTION_DELETE;
        } elseif (isset($args['retrieve'])) {
            return self::ACTION_RETRIEVE;
        } elseif (isset($args['retrieve-all'])) {
            return self::ACTION_RETRIEVE_ALL;
        }

        return null;
    }

    private function printUsage()
    {
        echo 'Usage: php cli.php ACTION' . PHP_EOL;
        echo 'Manipulate user data.' . PHP_EOL;
        echo PHP_EOL;
        echo 'ACTIONS' . PHP_EOL;
        echo '    -c "USERDATA", --create "USERDATA"   create a new user' . PHP_EOL;
        echo '    -u "USERDATA", --update "USERDATA"   update an existing user' . PHP_EOL;
        echo '    -d ID,         --delete ID           delete the existing user with the given id' . PHP_EOL;
        echo '    -r "FILTERS",  --retrieve "FILTERS"  retrieve users matching the given filter' . PHP_EOL;
        echo '    -a,            --retrieve-all        retrieve all users' . PHP_EOL;
        echo PHP_EOL;
        echo 'USERDATA is a query-string of user data' . PHP_EOL;
        echo '  e.g. email=someone@mail.com&first_name=Some&last_name=One&password=12345' . PHP_EOL;
        echo '  Note: the create action needs all user fields to be specified, while the update' . PHP_EOL;
        echo '        action needs the user_id and the fields that should be updated.' . PHP_EOL;
        echo 'FILTERS must be in query-string format.' . PHP_EOL;
        echo '  Note: filters work by sub-string match, e.g. -r first_name=Den will find a record where last_name is Deniz' . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    protected function getController()
    {
        return new UserController();
    }

    /**
     * {@inheritdoc}
     */
    protected function getArgumentConfiguration()
    {
        return [
            [ 'type' => Application::ARG_TYPE_NAMED,  'name' => 'create', 'short_name' => 'c', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED,  'name' => 'update', 'short_name' => 'u', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED,  'name' => 'delete', 'short_name' => 'd', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED,  'name' => 'retrieve', 'short_name' => 'r', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_SWITCH, 'name' => 'retrieve-all', 'short_name' => 'a', 'optional' => true ]
        ];
    }
}
