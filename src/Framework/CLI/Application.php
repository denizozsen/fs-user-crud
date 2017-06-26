<?php

namespace FsTest\Framework\CLI;
use FsTest\Framework\Core\Controller;

/**
 * Base class for CLI applications (aka command line tools)..
 *
 * @package FsTest\Framework
 */
class Application
{
    const ARG_TYPE_SIMPLE = 'simple';
    const ARG_TYPE_NAMED  = 'named';
    const ARG_TYPE_SWITCH = 'switch';
    const ARG_TYPES = [ self::ARG_TYPE_SIMPLE, self::ARG_TYPE_NAMED, self::ARG_TYPE_SWITCH ];

    private $argument_configuration = [];
    private $argument_configuration_by_short_name = [];
    private $argument_configuration_by_position = [];
    private $arguments = null;

    private $controller = null;

    public function __construct()
    {
    }

    /**
     * Sets the argument configuration for this application, as associative array whose structure is illistrated
     * by the following example.
     *
     * If we want to allow the following scheme:
     *
     *     my-cli [OPTIONS] MANDATORY_ARG
     *
     *     OPTIONS
     *         --foo=bar     assigns bar to foo
     *         -f bar
     *         -hello        enables the 'hello' switch
     *         -h
     *
     * We return the following configuration array:
     *
     * [
     *     [ 'type' => App::ARG_TYPE_NAMED, 'name' => 'foo', 'short_name' => 'f', 'optional' => true ],
     *     [ 'type' => App::ARG_TYPE_SWITCH, 'name' => 'hello', 'short_name' => 'h', 'optional' => true ],
     *     [ 'type' => App::ARG_TYPE_SIMPLE, 'name' => 'my_mandator_arg', 'position' => 0 ]
     * ]
     *
     * @param array $argument_configuration the argument configuration, as associative array
     * @throws \ErrorException if the given argument configuration is invalid
     */
    public function setArgumentConfiguration($argument_configuration)
    {
        $this->validateArgumentConfiguration($argument_configuration);

        // Index simple entries by name, short_name and position
        foreach ($argument_configuration as $entry) {
            if (isset($entry['name'])) {
                $this->argument_configuration[$entry['name']] = $entry;
            }
            if (isset($entry['short_name'])) {
                $this->argument_configuration_by_short_name[$entry['short_name']] = $entry;
            }
            if (isset($entry['position'])) {
                $this->argument_configuration_by_position[$entry['position']] = $entry;
            }
        }
    }

    /**
     * Returns the parsed command line arguments, e.g. POSIX style arguments are returned as key-value pairs.
     * Examples:
     *     -h world         is returned as 'h' => 'world'
     *     -hworld          is returned as 'h' => 'world'
     *     --hello world    is returned as 'h' => 'world'
     *     --hello=world    is returned as 'h' => 'world'
     *     -a               is returned as 'a' => 'a'
     *     my_value         is returned as 0 => 'my_value'
     *
     * Note: the 'my_value' example above is a simple argument. If the current argument configuration includes a
     * corresponding entry of type App::ARG_TYPE_SIMPLE, then this argument will be returned under the key with the
     * name given in that configuration entry, instead of under a numbered key.
     *
     * @return array the command line argumets, keys are argument names, values are argument values
     */
    public function getArguments()
    {
        if (is_null($this->arguments)) {
            $arguments = [];
            $current_simple_arg_pos = 0;
            $current_arg_name = null;
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, '-') !== false) {
                    $long_name = strpos($arg, '--') !== false;
                    $current_arg_name = $long_name ? substr($arg, 2) :  substr($arg, 1, 1);
                    $config_entry = $long_name
                        ? (isset($this->argument_configuration[$current_arg_name]) ? $this->argument_configuration[$current_arg_name] : null)
                        : (isset($this->argument_configuration_by_short_name[$current_arg_name]) ? $this->argument_configuration_by_short_name[$current_arg_name] : null);
                    if ($config_entry) {
                        $current_arg_name = $config_entry['name'];
                        if ($config_entry['type'] == self::ARG_TYPE_SWITCH) {
                            $arguments[$current_arg_name] = $current_arg_name;
                            $current_arg_name = null;
                        }
                    }
                    if ( !$long_name && (strlen($arg) > 2) ) {
                        $arg_value = substr($arg, 2);
                        $arguments[$current_arg_name] = $arg_value;
                        $current_arg_name = null;
                    }
                } else {
                    if ($current_arg_name) {
                        $arguments[$current_arg_name] = $arg;
                        $current_arg_name = null;
                    } else {
                        if (array_key_exists($current_simple_arg_pos, $this->argument_configuration_by_position)) {
                            $simple_arg_name = $this->argument_configuration_by_position[$current_simple_arg_pos]['name'];
                            $arguments[$simple_arg_name] = $arg;
                            ++$current_simple_arg_pos;
                        } else {
                            $arguments[] = $arg;
                        }
                    }
                }
            }
            $this->arguments = $arguments;
        }

        return $this->arguments;
    }

    /**
     * Sets the controller to be used by the application
     *
     * @param Controller $controller the controller to set
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns the CLI application's controller, or null, if none has been set.
     *
     * @return Controller|null the CLI application's controller, or null, if none has been set
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Invokes the given controller action with the given parameters.
     *
     * @param string $action
     * @param array $arguments
     * @return null|string the data returned by the action, as a terminal-friendly string, or null, if there is no return data
     * @throws \ErrorException if the CLI application's controller was not set or the given action does not exist
     */
    public function invokeAction($action, array $arguments = [])
    {
        if (!$this->controller) {
            throw new \ErrorException("The CLI application's controller is not set");
        }
        if (!is_callable([$this->controller, $action])) {
            throw new \ErrorException("The action {$action} is not defined by controller " . get_class($this->controller));
        }

        return call_user_func_array([$this->controller, $action], $arguments);
    }

    /**
     * Throws an ErrorException, if the given argument configuration is invalid.
     *
     * @param array $argument_configuration the argument configuration to validate
     * @throws \ErrorException if the given argument configuration is invalid
     */
    private function validateArgumentConfiguration($argument_configuration)
    {
        $largest_mandatory_position = PHP_INT_MIN;
        $smallest_optional_position = PHP_INT_MAX;
        foreach ($argument_configuration as $entry) {
            if (!isset($entry['type'])) {
                throw new \ErrorException("Every entry must contain the 'type' key");
            }
            if (!in_array($entry['type'], self::ARG_TYPES)) {
                throw new \ErrorException("Entry type must be one of: " . implode(', ', self::ARG_TYPES));
            }
            if (!isset($entry['name'])) {
                throw new \ErrorException("Every entry must contain the 'name key");
            }
            if ($entry['type'] == self::ARG_TYPE_SIMPLE && !isset($entry['position'])) {
                throw new \ErrorException("Entry of type 'simple' must contain the name position");
            }
            if ($entry['type'] == self::ARG_TYPE_SIMPLE) {
                if (empty($entry['optional'])) {
                    $largest_mandatory_position = max($largest_mandatory_position, $entry['position']);
                } else {
                    $smallest_optional_position = min($smallest_optional_position, $entry['position']);
                }
            }
        }
        if ($smallest_optional_position < $largest_mandatory_position) {
            throw new \ErrorException("Optional 'simple' entries must appear after mandatory 'simple' entries");
        }
    }
}
