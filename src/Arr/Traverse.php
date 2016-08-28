<?php
namespace Narrowspark\Arr;

use Narrowspark\Arr\Traits\ValueTrait;

class Traverse
{
    use ValueTrait;

    /**
     * Applies the callback to the elements of the given arrays
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public function map(array $array, callable $callback)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            $result = call_user_func($callback, $item, $key);

            $newArray = is_array($result) ?
                array_replace_recursive($array, $result) :
                array_merge_recursive($array, (array) $result);
        }

        return $newArray;
    }

    /**
     * Filters each of the given values through a function.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public function filter(array $array, callable $callback)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            if (call_user_func($callback, $item, $key)) {
                $newArray[$key] = $item;
            }
        }

        return $newArray;
    }

    /**
     * Returns whether every element of the array satisfies the given predicate or not.
     * Works with Iterators too.
     *
     * @param array    $array
     * @param callable $predicate
     *
     * @return bool
     */
    public function all(array $array, callable $predicate)
    {
        foreach ($array as $key => $value) {
            if (! call_user_func($predicate, $value, $key, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     *  The opposite of filter().
     *
     *  @param array    $array
     *  @param callable $callback Function to filter values.
     *
     *  @return array filtered array.
     */
    public function reject(array $array, callable $callback)
    {
        return $this->filter($array, function ($value, $key) use ($callback) {
            return ! call_user_func($callback, $value, $key);
        });
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
        if (is_null($callback)) {
            return empty($array) ? $this->value($default) : reset($array);
        }

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
        if (is_null($callback)) {
            return empty($array) ? $this->value($default) : end($array);
        }

        return $this->first(array_reverse($array), $callback, $default);
    }
}
