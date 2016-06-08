<?php
namespace Narrowspark\Arr;

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
        $this->access = new Access();
        $this->enumerator = new Enumerator();
    }

    /**
     * Pop value from sub array.
     *
     * @param array  $array
     * @param string $key
     *
     * @return mixed
     */
    public static function pop(array $array, $key)
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (! isset($array[$key])) {
                return;
            }

            $array = $array[$key];
        }

        if (! is_array($array)) {
            return;
        }

        return array_pop($array);
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

            ++$position;
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

            // fix for hhvm #1871 bug
            if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.10.0', '<=')) {
                $combinator->next();
            }

            $index = $combinator->key();

            if ($overwrite || ! isset($combined[$index])) {
                $combined[$index] = $combinator->current();
            }
        }

        return $combined;
    }

    /**
     * Collapse a nested array down to an array of flat key=>value pairs
     */
    public function collapse(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // strip any manually added '.'
                if (preg_match('/\./', $key)) {
                    $key = substr($key, 0, -2);
                }

                $this->recurseCollapse($value, $newArray, (array) $key);
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
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
     * Stripe all empty items.
     *
     * @param array $array
     *
     * @return array
     */
    public function stripEmpty(array $array)
    {
        return array_filter($array, function ($item) {
            if (is_null($item)) {
                return false;
            }

            if (! trim($item)) {
                return false;
            }

            return true;
        });
    }

    /**
     * Remove all instances of $ignore found in $elements (=== is used).
     *
     * @param array $array
     * @param array $ignore
     *
     * @return array
     */
    public function without(array $array, array $ignore)
    {
        foreach ($array as $key => $node) {
            if (in_array($node, $ignore, true)) {
                unset($array[$key]);
            }
        }

        return array_values($array);
    }

    /**
     * Reindexes a list of values.
     *
     * @param array $array
     * @param array $map      An map of correspondances of the form
     *                        ['currentIndex' => 'newIndex'].
     * @param bool  $unmapped Whether or not to keep keys that are not
     *                        remapped.
     *
     * @return array
     */
    public function reindex(array $array, array $map, $unmapped = true)
    {
        $reindexed = $unmapped
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
     * Merges two or more arrays into one recursively.
     *
     * @param array $arrays.
     *
     * @return array
     */
    public function merge(array $arrays)
    {
        $args = func_get_args();
        $array = array_shift($args);

        while (! empty($args)) {
            $next = array_shift($args);

            foreach ($next as $key => $value) {
                if (is_int($key)) {
                    if (isset($array[$key])) {
                        $array[] = $value;
                    } else {
                        $array[$key] = $value;
                    }
                } elseif (is_array($value) && isset($array[$key]) && is_array($array[$key])) {
                    $array[$key] = $this->merge($array[$key], $value);
                } else {
                    $array[$key] = $value;
                }
            }
        }

        return $array;
    }

    /**
     *  Makes every value that is numerically indexed a key, given $default
     *  as value.
     *
     *  @param array $array
     *  @param mixed $default
     *
     *  @return array
     */
    public function normalize(array $array, $default)
    {
        $normalized = [];

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
                $value = $default;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
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
            $segments = explode('.', $key);
            $valueSegment = array_pop($segments);
            $branch = &$hierarchy;

            foreach ($segments as $segment) {
                if (! isset($branch[$segment])) {
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
     * @param array         $array
     * @param callable|null $callback
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

                if (! array_key_exists($key, $buckets)) {
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
     * Expand a dotted array. Acts the opposite way of Arr::dot().
     *
     * @param array $array
     * @param bool  $depth
     *
     * @return array
     */
    public function unDot($array, $depth = INF)
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (count($dottedKeys = explode('.', $key, 2)) > 1) {
                $results[$dottedKeys[0]][$dottedKeys[1]] = $value;
            } else {
                $results[$key] = $value;
            }
        }

        foreach ($results as $key => $value) {
            if (is_array($value) && ! empty($value) && $depth > 1) {
                $results[$key] = $this->unDot($value, $depth - 1);
            }
        }

        return $results;
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
        $merged = [];

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
     * Sort the array using the given callback.
     *
     * @param array    $array
     * @param callable $callback
     * @param int      $options
     * @param bool     $descending
     *
     * @return array
     */
    public function sort(array $array, callable $callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($array as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options)
                    : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $array[$key];
        }

        return $results;
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

    /**
     * Will turn each element in $arr into an array then appending
     * the associated indexs from the other arrays into this array as well.
     *
     * @param array $array
     * @param array $arrays
     *
     * @return array
     */
    public function zip(array $array, array $arrays)
    {
        $args = func_get_args();
        array_shift($args);

        foreach ($array as $key => $value) {
            $array[$key] = [$value];

            foreach ($args as $k => $v) {
                $array[$key][] = current($args[$k]);

                if (next($args[$k]) === false && $args[$k] !== [null]) {
                    $args[$k] = [null];
                }
            }
        }

        return $array;
    }

    /**
     * Recurse through an array, add the leaf items to the $newArray var
     *
     * @param array $subject
     * @param array &$newArray
     * @param array $stack
     *
     * @return string[]|null
     */
    private function recurseCollapse(array $subject, array &$newArray, $stack = [])
    {
        foreach ($subject as $key => $value) {
            $fstack = array_merge($stack, [$key]);

            if (is_array($value)) {
                $this->recurseCollapse($value, $newArray, $fstack);
            } else {
                $top = array_shift($fstack);
                $arrayPart = count($fstack) ? '.' . implode('.', $fstack) : '';
                $newArray[$top . $arrayPart] = $value;
            }
        }
    }
}
