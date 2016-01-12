<?php
namespace Narrowspark\Arr;

use ArrayAccess;
use Narrowspark\Arr\Traits\ValueTrait;

class Enumerator
{
    use ValueTrait;

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array    $array
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public function first(array $array, callable $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return $this->value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array    $array
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public function last(array $array, callable $callback, $default = null)
    {
        return $this->first(array_reverse($array), $callback, $default);
    }

    /**
     * Get a random element from the array supplied.
     *
     * @param array $array the source array
     *
     * @return mixed
     */
    public function random(array $array)
    {
        if (!count($array)) {
            return;
        }

        $keys = array_rand($array, 1);

        return $this->value($array[$keys]);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param string[] $array
     * @param string[] $keys
     *
     * @return string[]
     */
    public function only(array $array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Split an array in the given amount of pieces.
     *
     * @param array $array
     * @param int   $numberOfPieces
     * @param bool  $preserveKeys
     *
     * @return array
     */
    public function split(array $array, $numberOfPieces = 2, $preserveKeys = false)
    {
        if (count($array) === 0) {
            return [];
        }

        $splitSize = ceil(count($array) / $numberOfPieces);

        return array_chunk($array, $splitSize, $preserveKeys);
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param array|\ArrayAccess $array
     * @param string             $value
     * @param string|null        $key
     *
     * @return array
     */
    public function pluck(array $array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = $this->explodePluckParameters($value, $key);

        // If the key is "null", we will just append the value to the array and keep
        // looping. Otherwise we will key the array using the value of the key we
        // received from the developer. Then we'll return the final array form.
        if (is_null($key)) {
            foreach ($array as $item) {
                $results[] = $this->dataGet($item, $value);
            }
        } else {
            foreach ($array as $item) {
                $results[$this->dataGet($item, $key)] = $this->dataGet($item, $value);
            }
        }

        return $results;
    }

    /**
     * Filter the array using the given Closure.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public function where(array $array, callable $callback)
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     *
     * @return array
     */
    public function prepend(array $array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param string|null  $default
     *
     * @return mixed
     */
    public function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if (!is_array($target) && !$target instanceof ArrayAccess) {
                    return $this->value($default);
                }

                $result = $this->pluck($target, $key);

                return in_array('*', $key, true) ? (new Transform())->collapse($result) : $result;
            }

            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return $this->value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (!isset($target[$segment])) {
                    return $this->value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return $this->value($default);
                }

                $target = $target->{$segment};
            } else {
                return $this->value($default);
            }
        }

        return $target;
    }

    /**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param string $pattern
     * @param array  $replacements
     * @param string $subject
     *
     * @return string
     */
    public function pregReplaceSub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, function ($match) use (&$replacements) {
            return array_shift($replacements);
        }, $subject);
    }

    /**
     * A shorter way to run a match on the array's keys rather than the values.
     *
     * @param string $pattern
     * @param array  $input
     * @param int    $flags
     *
     * @return array
     */
    public function pregGrepKeys($pattern, array $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param string      $value
     * @param string|null $key
     *
     * @return array[]
     */
    protected function explodePluckParameters($value, $key)
    {
        $value = is_array($value) ? $value : explode('.', $key);

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }
}
