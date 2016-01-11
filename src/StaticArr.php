<?php
namespace Narrowspark\Arr;

use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class StaticArr
 */
class StaticArr
{
    /**
     * A mapping of method names to the numbers of arguments it accepts. Each
     * should be two more than the equivalent Arr method. Necessary as
     * static methods place the optional $encoding as the last parameter.
     *
     * @var string[]
     */
    protected static $methodArgs = null;

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
     * @return Arr
     */
    public static function __callStatic($name, $arguments)
    {
        if (!static::$methodArgs) {
            $arrClass = new ReflectionClass(Arr::class);
            $methods = $arrClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $params = $method->getNumberOfParameters() + 2;
                static::$methodArgs[$method->name] = $params;
            }
        }

        if (!isset(static::$methodArgs[$name])) {
            throw new BadMethodCallException($name . ' is not a valid method');
        }

        $numArgs = count($arguments);
        $str     = ($numArgs) ? $arguments[0] : '';

        if ($numArgs === static::$methodArgs[$name]) {
            $args     = array_slice($arguments, 1, -1);
            $encoding = $arguments[$numArgs - 1];
        } else {
            $args     = array_slice($arguments, 1);
            $encoding = null;
        }

        return call_user_func_array([new Arr(), $name], $args);
    }
}
