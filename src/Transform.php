<?php
namespace Narrowspark\Arr;

use Narrowspark\Arr\Traits\SplitPathTrait;
use Narrowspark\Arr\Traits\ValueTrait;

class Transform
{
    /**
     * Dotted array cache.
     *
     * @var array
     */
    protected $dotted = [];

    /**
     * Swap two elements between positions.
     *
     * @param array  $array array to swap
     * @param string $swapA
     * @param string $swapB
     *
     * @return array|null
     */
    public function swap(array $array, $swapA, $swapB)
    {
        list($array[$swapA], $array[$swapB]) = [$array[$swapB], $array[$swapA]];
    }

    /**
     * Indexes an array depending on the values it contains.
     *
     * @param array    $array
     * @param callable $callback  Function to combine values.
     * @param bool     $overwrite Should duplicate keys be overwritten?
     *
     * @return array Indexed values.
     */
    public function combine(array $array, callable $callback, $overwrite = true)
    {
        $combined = [];

        foreach ($array as $key => $value) {
            $combinator = call_user_func($callback, $value, $key);
            $index      = $combinator->key();

            if ($overwrite || !isset($combined[$index])) {
                $combined[$index] = $combinator->current();
            }
        }

        return $combined;
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
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     *
     * @return array[]
     */
    public function divide($array)
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Reindexes a list of values.
     *
     * @param array $array
     * @param array $map          An map of correspondances of the form
     *                            ['currentIndex' => 'newIndex'].
     * @param bool  $keepUnmapped Whether or not to keep keys that are not
     *                            remapped.
     *
     * @return array
     */
    public function reindex(array $array, array $map, $keepUnmapped = true)
    {
        $reindexed = $keepUnmapped
            ? $array
            : [];

        foreach ($map as $from => $to) {
            if (isset($array[$from])) {
                $reindexed[$to] = $array[$from];
            }
        }

        return $reindexed;
    }

    /**
     * Merges two arrays recursively.
     *
     * @param array $first  Original data.
     * @param array $second Data to be merged.
     *
     * @return array
     */
    public function merge(array $first, array $second)
    {
        foreach ($second as $key => $value) {
            $shouldBeMerged = (
                isset($first[$key])
                && is_array($first[$key])
                && is_array($value)
            );

            $first[$key] = $shouldBeMerged
                ? $this->merge($first[$key], $value)
                : $value;
        }

        return $first;
    }

    /**
     * Extend one array with another.
     *
     * @param array $arrays
     *
     * @return array
     */
    public function extend(array $arrays)
    {
        $merged = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && (new Access())->has($merged, $key) && is_array($merged[$key])) {
                    $merged[$key] = $this->extend($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public function dot($array, $prepend = '')
    {
        $cache = serialize(['array' => $array, 'prepend' => $prepend]);

        if (array_key_exists($cache, $this->dotted)) {
            return $this->dotted[$cache];
        }

        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $this->dotted[$cache] = $results;
    }

    /**
     * Flatten a nested array to a separated key.
     *
     * @param array       $array
     * @param string|null $separator
     * @param string      $prepend
     *
     * @return array
     */
    public function flatten(array $array, $separator = null, $prepend = '')
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, $this->flatten($value, $separator, $prepend . $key . $separator));
            } else {
                $flattened[$prepend . $key] = $value;
            }
        }

        return $flattened;
    }
}
