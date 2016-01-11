<?php
namespace Narrowspark\Arr;

use Narrowspark\Arr\Traits\SplitPathTrait;
use Narrowspark\Arr\Traits\ValueTrait;

class Access
{
    use SplitPathTrait;
    use ValueTrait;

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public function set(array &$array, $key, $value)
    {
        var_dump($array);

        if ($key === null) {
            return $array = $value;
        }

        $keys = $this->splitPath($key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array           $array
     * @param string|callable $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public function get(array $array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach ($this->splitPath($key) as $segment) {
            if (!array_key_exists($segment, $array)) {
                return $this->value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Add an element to the array at a specific location
     * using the "dot" notation.
     *
     * @param array $array
     * @param $key
     * @param $value
     *
     * @return array
     */
    public function add(array &$array, $key, $value)
    {
        $target = $this->get($array, $key, []);

        if (!is_array($target)) {
            $target = [$target];
        }

        $target[] = $value;
        $this->set($array, $key, $target);

        return $array;
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
     * Check if an item exists in an array using "dot" notation.
     *
     * @param array  $array
     * @param string $key
     *
     * @return bool
     */
    public function has(array $array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach ($this->splitPath($key) as $segment) {
            if (!array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Updates data at the given path.
     *
     * @param array        $array
     * @param array|string $key
     * @param callable     $cb    Callback to update the value.
     *
     * @return mixed Updated data.
     */
    public function update(array $array, $key, callable $cb)
    {
        $keys    = $this->splitPath($key);
        $current = & $array;

        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return $array;
            }

            $current = & $current[$key];
        }

        $current = call_user_func($cb, $current);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array        $array
     * @param array|string $keys
     */
    public function forget(array &$array, $keys)
    {
        $original = &$array;
        $keys     = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            $parts = $this->splitPath($key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Reset all numerical indexes of an array (start from zero).
     * Non-numerical indexes will stay untouched. Returns a new array.
     *
     * @param array      $array
     * @param bool|false $deep
     *
     * @return array
     */
    public function reset(array $array, $deep = false)
    {
        $target = [];

        foreach ($array as $key => $value) {
            if ($deep && is_array($value)) {
                $value = $this->reset($value);
            }

            if (is_numeric($key)) {
                $target[] = $value;
            } else {
                $target[$key] = $value;
            }
        }

        return $target;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param array    $array
     * @param string[] $keys
     *
     * @return array
     */
    public function except(array $array, $keys)
    {
        $this->forget($array, $keys);

        return $array;
    }
}
