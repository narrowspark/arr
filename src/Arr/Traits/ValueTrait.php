<?php
namespace Narrowspark\Arr\Traits;

use Closure;

trait ValueTrait
{
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
