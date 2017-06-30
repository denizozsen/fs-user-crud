<?php

namespace FsTest\Framework\CLI;
use FsTest\Framework\Core\Controller;

/**
 * Base class for CLI applications (aka command line tools)..
 *
 * @package FsTest\Framework
 */
abstract class Application
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

    /**
     * This method must be overridden by the CLI application sub class to implement the program.
     */
    abstract protected function run();

    /**
     * This method must be overridden by the CLI application sub class to provide the CLI application's controller.
     *
     * @return Controller the CLI application's controller
     */
    abstract protected function getController();

    /**
     * This method may be overridden by the CLI application sub class to return the argument configuration for the
     * application, as associative array whose structure is illustrated by the example below.
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
     * @return array $argument_configuration the argument configuration, as associative array
     */
    protected function getArgumentConfiguration()
    {
        return [];
    }

    /**
     * This is the entry point for the CLI application, called by the framework.
     *
     * @throws \ErrorException if the argument configuration returned by getArgumentConfiguration() is invalid
     */
    public function start()
    {
        // Validate and install argument configuration
        $argument_configuration = $this->getArgumentConfiguration();
        $this->validateArgumentConfiguration($argument_configuration);
        $this->useArgumentConfiguration($argument_configuration);

        // Install controller
        $controller = $this->getController();
        if (!is_a($controller, Controller::class)) {
            throw new \ErrorException("Controller must extend " . Controller::class . ", was: " . get_class($controller));
        }
        $this->controller = $controller;

        // Call the run() method implemented by this CLI application sub class
        $this->run();
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
            $args_without_script_name = array_slice($_SERVER['argv'], 1);
            foreach ($args_without_script_name as $arg) {
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
     * Stores the given argument configuration for use by this application.
     *
     * @param array $argument_configuration
     */
    private function useArgumentConfiguration(array $argument_configuration)
    {
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
     * Throws an ErrorException, if the given argument configuration is invalid.
     *
     * @param array $argument_configuration the argument configuration to validate
     * @throws \ErrorException if the given argument configuration is invalid
     */
    private function validateArgumentConfiguration($argument_configuration)
    {
        if (!is_array($argument_configuration)) {
            throw new \ErrorException("Argument configuration must be an array");
        }

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
