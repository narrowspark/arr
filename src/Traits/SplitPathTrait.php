<?php
namespace Narrowspark\Arr\Traits;

use InvalidArgumentException;

trait SplitPathTrait
{
    /**
     *  Splits a path into multiple keys.
     *
     *  @param array|string $key
     *
     *  @return array
     */
    public function splitPath($key)
    {
        $keys = is_string($key)
            ? array_filter(explode('.', $key))
            : $key;

        if (!is_array($keys)) {
            throw new InvalidArgumentException(
                'The path should be either an array or a string.'
            );
        }

        if (empty($keys)) {
            throw new InvalidArgumentException(
                'The path should not be empty.'
            );
        }

        return $keys;
    }
}
