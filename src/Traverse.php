<?php
namespace Narrowspark\Arr;

class Traverse
{
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
     *  The opposite of filter().
     *
     *  @param array    $array
     *  @param callable $cb Function to filter values.
     *
     *  @return array filtered array.
     */
    public function reject(array $array, callable $cb)
    {
        return $this->filter($array, function ($value, $key) use ($cb) {
            return !call_user_func($cb, $value, $key);
        });
    }
}
