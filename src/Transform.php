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
     * A instance of Access
     *
     * @var \Narrowspark\Arr\Access
     */
    protected $access;

    /**
     * A instance of Access
     *
     * @var \Narrowspark\Arr\Enumerator
     */
    protected $enumerator;

    public function __construct()
    {
        $this->access     = new Access();
        $this->enumerator = new Enumerator();
    }

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

        return $array;
    }

    /**
     * Create a new array consisting of every n-th element.
     *
     * @param array $array
     * @param int   $step
     * @param int   $offset
     *
     * @return array
     */
    public static function every($array, $step, $offset = 0)
    {
        $new = [];

        $position = 0;

        foreach ($array as $key => $item) {
            if ($position % $step === $offset) {
                $new[] = $item;
            }

            $position++;
        }

        return $new;
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
                if (is_array($value) && $this->access->has($merged, $key) && is_array($merged[$key])) {
                    $merged[$key] = $this->extend($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * Transforms a 1-dimensional array into a multi-dimensional one,
     * exploding keys according to a separator.
     *
     * @param array $array
     *
     * @return array
     */
    public function asHierarchy(array $array)
    {
        $hierarchy = [];

        foreach ($array as $key => $value) {
            $segments     = explode('.', $key);
            $valueSegment = array_pop($segments);
            $branch       = &$hierarchy;

            foreach ($segments as $segment) {
                if (!isset($branch[$segment])) {
                    $branch[$segment] = [];
                }

                $branch = &$branch[$segment];
            }

            $branch[$valueSegment] = $value;
        }

        return $hierarchy;
    }

    /**
     * Separates elements from an array into groups.
     * The function maps an element to the key that will be used for grouping.
     * If no function is passed, the element itself will be used as key.
     *
     * @param  array         $array
     * @param  callable|null $callback
     *
     * @return array
     */
    public function groupBy(array $array, callable $callback = null)
    {
        $callback = $callback ?: function ($value) {
            return $value;
        };

        return array_reduce(
            $array,
            function ($buckets, $value) use ($callback) {
                $key = call_user_func($callback, $value);

                if (!array_key_exists($key, $buckets)) {
                    $buckets[$key] = [];
                }

                $buckets[$key][] = $value;

                return $buckets;
            },
            []
        );
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

    /**
     * Expand a flattened array with dots to a multi-dimensional associative array.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public function expand(array $array, $prepend = '')
    {
        $results = [];

        if ($prepend) {
            $prepend .= '.';
        }

        foreach ($array as $key => $value) {
            if ($prepend) {
                $pos = strpos($key, $prepend);

                if ($pos === 0) {
                    $key = substr($key, strlen($prepend));
                }
            }

            $results = $this->access->set($results, $key, $value);
        }

        return $results;
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
     * Extend one array with another. Non associative arrays will not be merged
     * but rather replaced.
     *
     * @param array $arrays
     *
     * @return array
     */
    public function extendDistinct(array $arrays)
    {
        $merged     = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && $this->access->has($merged, $key) && is_array($merged[$key])) {
                    if ($this->enumerator->isAssoc($value) && $this->enumerator->isAssoc($merged[$key])) {
                        $merged[$key] = $this->extendDistinct($merged[$key], $value);

                        continue;
                    }
                }

                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     *
     * @return array
     */
    public function sortRecursive(array $array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursive($value);
            }
        }

        // sort associative array
        if ($this->enumerator->isAssoc($array)) {
            ksort($array);
            // sort regular array
        } else {
            sort($array);
        }

        return $array;
    }

    public function zip(array $array, array $arrays)
    {
        $args = func_get_args();
        array_shift($args);

        foreach ($array as $key => $value) {
            $array[$key] = array($value);

            foreach ($args as $k => $v) {
                $array[$key][] = current($args[$k]);

                if (next($args[$k]) === false && $args[$k] !== array(null)) {
                    $args[$k] = array(null);
                }
            }
        }

        return $array;
    }
}
