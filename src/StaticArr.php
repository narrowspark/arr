<?php
namespace Narrowspark\Arr;

use BadMethodCallException;

/**
 * Class StaticArr
 */
class StaticArr
{
    /**
     * Creates an instance of Arr and invokes the given method with the
     * rest of the passed arguments.
     * The result is not cast, so the return value may be of type Arr, array,
     * integer, boolean, etc.
     *
     * @param string  $name
     * @param mixed[] $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (!isset(static::$methodArgs[$name])) {
            throw new BadMethodCallException($name . ' is not a valid method');
        }

        $numArgs = count($arguments);

        if ($numArgs === static::$methodArgs[$name]) {
            $args = array_slice($arguments, 1, -1);
        } else {
            $args = array_slice($arguments, 1);
        }

        return call_user_func_array([new Arr(), $name], $args);
    }
}
