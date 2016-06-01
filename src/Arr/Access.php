<?php
namespace Narrowspark\Arr;

use Narrowspark\Arr\Traits\ValueTrait;

class Access
{
    use ValueTrait;

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public function set(array $array, $key, $value)
    {
        if ($key === null) {
            return $value;
        }

        $keys = explode('.', $key);
        $current = &$array;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($current[$key]) || ! is_array($current[$key])) {
                $current[$key] = [];
            }

            $current = &$current[$key];
        }

        $current[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Get an item from an array using "dot" notation.
     * If key dont exist, you get a default value back.
     *
     * @param array                  $array
     * @param string[]|callable|null $key
     * @param mixed                  $default
     *
     * @return mixed
     */
    public function get(array $array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (isset($array[$key])) {
            return $this->value($array[$key]);
        }

        foreach (explode('.', $key) as $segment) {
            if (! array_key_exists($segment, $array)) {
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
    public function add(array $array, $key, $value)
    {
        $target = $this->get($array, $key, []);

        if (! is_array($target)) {
            $target = [$target];
        }

        $target[] = $value;
        $array = $this->set($array, $key, $target);

        return $array;
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

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Updates data at the given path.
     *
     * @param array          $array
     * @param array|string[] $key
     * @param callable       $callback Callback to update the value.
     *
     * @return mixed Updated data.
     */
    public function update(array $array, $key, callable $callback)
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $key) {
            if (! isset($current[$key])) {
                return $array;
            }

            $current = &$current[$key];
        }

        $current = call_user_func($callback, $current);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array        $array
     * @param array|string $keys
     */
    public function forget(array $array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        if (count($keys) === 0) {
            return $original;
        }

        foreach ($keys as $key) {
            $parts = explode('.', $key);
            // clean up before each pass
            $arr = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($arr[$part]) && is_array($arr[$part])) {
                    $arr = &$arr[$part];
                } else {
                    continue 2;
                }
            }

            unset($arr[array_shift($parts)]);
        }

        return $array;
    }
}
