<?php
namespace Narrowspark\Arr;

class Arr
{
    public function __call($name, $arguments)
    {
        $access     = new Access();
        $enumerator = new Enumerator();
        $transform  = new Transform();

        if (condition) {
            return call_user_func_array([$access, $name], $args);
        } else if (condition) {
            return call_user_func_array([$enumerator, $name], $args);
        } else if (condition) {
            return call_user_func_array([$transform, $name], $args);
        }
    }
}
