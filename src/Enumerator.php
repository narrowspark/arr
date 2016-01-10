<?php
namespace Narrowspark\Arr;

use ArrayAccess;
use Narrowspark\Arr\Traits\SplitPathTrait;
use Narrowspark\Arr\Traits\ValueTrait;

class Enumerator
{
    use SplitPathTrait;
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
    public function first($array, callable $callback, $default = null)
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
    public function last($array, callable $callback, $default = null)
    {
        return $this->first(array_reverse($array), $callback, $default);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param string[] $array
     * @param string[] $keys
     *
     * @return string[]
     */
    public function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array       $array
     * @param string      $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function pull(array &$array, $key, $default = null)
    {
        $value = $this->get($array, $key, $default);

        $this->forget($array, $key);

        return $value;
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
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed        $target
     * @param string|array $key
     * @param string       $default
     *
     * @return mixed
     */
    public function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : $this->splitPath($key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if (!is_array($target) && !$target instanceof ArrayAccess) {
                    return $this->value($default);
                }

                $result = $this->pluck($target, $key);

                return in_array('*', $key, true) ? $this->collapse($result) : $result;
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
     * Collapse an array of arrays into a single array.
     *
     * @param array $array
     *
     * @return array
     */
    public function collapse(array $array)
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
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
     * Index the array by array of keys.
     *
     * @param array     $data
     * @param array     $keys
     * @param bool|true $unique
     *
     * @return array
     */
    public function getIndexedByKeys(array $data, array $keys, $unique = true)
    {
        $result = [];

        foreach ($data as $value) {
            $this->indexByKeys($result, $value, $keys, $unique);
        }

        return $result;
    }

    /**
     * Converts array of arrays to one-dimensional array, where key is $keyName and value is $valueName.
     *
     * @param array        $array
     * @param string       $keyName
     * @param string|array $valueName
     *
     * @return array
     */
    public function getIndexedValues(array $array, $keyName, $valueName)
    {
        array_flip($this->pluck($array, $keyName, $valueName));
    }

    /**
     * @param array     $result
     * @param array     $toSave
     * @param array     $keys
     * @param bool|true $unique
     */
    protected function indexByKeys(array &$result, array $toSave, array $keys, $unique = true)
    {
        foreach ($keys as $key) {
            if (!isset($result[$toSave[$key]])) {
                $result[$toSave[$key]] = [];
            }

            $result = &$result[$toSave[$key]];
        }

        if ($unique) {
            $result = $toSave;
        } else {
            $result[] = $toSave;
        }
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param string      $value
     * @param string|null $key
     *
     * @return array
     */
    protected function explodePluckParameters($value, $key)
    {
        $value = is_array($value) ? $value : $this->splitPath($key);

        $key = is_null($key) || is_array($key) ? $key : $this->splitPath($key);

        return [$value, $key];
    }
}
