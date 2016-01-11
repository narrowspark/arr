<?php
namespace Narrowspark\Arr;

use BadMethodCallException;

class StaticArr
{
    /**
     * @see Arr::__call($name, $arguments)
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([new Arr(), $name], $arguments);
    }
}
