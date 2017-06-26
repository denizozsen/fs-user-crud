<?php

namespace FsTest\User;

use FsTest\Framework\CLI\Application;
use FsTest\Framework\Core\Controller;
use TheSeer\Tokenizer\Exception;

class UserCliApp extends Application
{
    /**
     * This method must be overridden by the CLI application sub class to implement the program.
     */
    protected function run()
    {
        $args = $this->getArguments();

        try {
            /** @var UserController $controller */
            $controller = $this->getController();
            if (isset($args['create'])) {
                var_dump('CREATE');
                $user = $this->convertInputToArray($args['create']);
                $controller->save($user);
                echo "The user was successfully created." . PHP_EOL;
            } elseif (isset($args['update'])) {
                var_dump('UPDATE');
                $user = $this->convertInputToArray($args['update']);
                $controller->save($user);
                echo "The user was successfully updated." . PHP_EOL;
            } elseif (isset($args['delete'])) {
                var_dump('DELETE');
                $user_id = $args['delete'];
                $controller->delete($user_id);
                echo "User with id {$user_id} successfully deleted." . PHP_EOL;
            } elseif (isset($args['retrieve'])) {
                var_dump('RETRIEVE');
                $filters = $this->convertInputToArray($args['retrieve']);
                $output = $this->convertArrayToOutput($controller->getAll($filters));
                echo "The requests user data follows:" . PHP_EOL . PHP_EOL;
                echo $output;
            }
        } catch (Exception $e) {
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

    /**
     * This method must be overridden by the CLI application sub class to provide the CLI application's controller.
     *
     * @return Controller the CLI application's controller
     */
    protected function getController()
    {
        return new UserController();
    }

    protected function getArgumentConfiguration()
    {
        return [
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'create', 'short_name' => 'c', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'update', 'short_name' => 'u', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'delete', 'short_name' => 'd', 'optional' => true ],
            [ 'type' => Application::ARG_TYPE_NAMED, 'name' => 'retrieve', 'short_name' => 'r', 'optional' => true ]
        ];
    }
}
