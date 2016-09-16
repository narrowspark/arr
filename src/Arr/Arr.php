<?php
declare(strict_types=1);
namespace Narrowspark\Arr;

use ArrayAccess;
use Closure;

class Arr
{
    /**
     * Dotted array cache.
     *
     * @var array
     */
    protected static $dotted = [];

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function pull(array &$array, string $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array       $array
     * @param string|null $key
     * @param mixed       $value
     *
     * @return array
     */
    public static function set(array $array, $key, $value): array
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
     * @param array           $array
     * @param string|int|null $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public static function get(array $array, $key = null, $default = null)
    {
        if ($key === null) {
            return $array;
        }

        if (isset($array[$key])) {
            return static::value($array[$key]);
        }

        foreach (explode('.', $key) as $segment) {
            if (! array_key_exists($segment, $array)) {
                return static::value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Add an element to the array at a specific location
     * using the "dot" notation.
     *
     * @param array                  $array
     * @param string[]|callable|null $key
     * @param mixed                  $value
     *
     * @return array
     */
    public static function add(array $array, $key, $value): array
    {
        $target = static::get($array, $key, []);

        if (! is_array($target)) {
            $target = [$target];
        }

        $target[] = $value;
        $array = static::set($array, $key, $target);

        return $array;
    }

    /**
     * Check if any item or items exist in an array using "dot" notation.
     *
     * @param array        $array
     * @param string|array $keys
     *
     * @return bool
     */
    public static function any(array $array, $keys): bool
    {
        foreach ((array) $keys as $key) {
            if (static::has($array, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $key
     *
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array
     * @param string|int         $keys
     *
     * @return bool
     */
    public static function has($array, $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (! $array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', (string) $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
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
    public static function update(array $array, $key, callable $callback)
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
    public static function forget(array &$array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);
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
     * Get a random element from the array supplied.
     *
     * @param array $array the source array
     *
     * @return mixed
     */
    public static function random(array $array)
    {
        if (! count($array)) {
            return;
        }

        $keys = array_rand($array, 1);

        return static::value($array[$keys]);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param string[] $array
     * @param string[] $keys
     *
     * @return string[]
     */
    public static function only(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) !== range(0, count($array) - 1);
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
    public static function split(array $array, int $numberOfPieces = 2, bool $preserveKeys = false): array
    {
        if (count($array) === 0) {
            return [];
        }

        $splitSize = ceil(count($array) / $numberOfPieces);

        return array_chunk($array, (int) $splitSize, $preserveKeys);
    }

    /**
     * Check if an array has a numeric index.
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isIndexed(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return ! static::isAssoc($array);
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
    public static function prepend(array $array, $value, $key = null): array
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Return the closest found value from array.
     *
     * @param array $array
     * @param string $value
     *
     * @return mixed
     */
    public static function closest(array $array, string $value)
    {
        sort($array);
        $closest = $array[0];

        for ($i = 1, $j = count($array), $k = 0; $i < $j; $i++, $k++) {
            $middleValue = ((int) $array[$i] - (int) $array[$k]) / 2 + (int) $array[$k];

            if ($value >= $middleValue) {
                $closest = $array[$i];
            }
        }

        return static::value($closest);
    }

    /**
     * Pop value from sub array.
     *
     * @param array  $array
     * @param string $key
     *
     * @return mixed
     */
    public static function pop(array $array, string $key)
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
     * @param array      $array array to swap
     * @param string|int $swapA
     * @param string|int $swapB
     *
     * @return array|null
     */
    public static function swap(array $array, $swapA, $swapB)
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
    public static function every(array $array, int $step, int $offset = 0): array
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
    public static function combine(array $array, callable $callback, bool $overwrite = true): array
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
     * Collapse a nested array down to an array of flat key=>value pairs.
     *
     * @param array $array
     *
     * @return array
     */
    public static function collapse(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // strip any manually added '.'
                if (preg_match('/\./', $key)) {
                    $key = substr($key, 0, -2);
                }

                self::recurseCollapse($value, $newArray, (array) $key);
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
     * @return array
     */
    public static function divide(array $array): array
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
    public static function stripEmpty(array $array): array
    {
        return array_filter($array, function ($item) {
            if (is_null($item)) {
                return false;
            }

            return (bool) trim($item);
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
    public static function without(array $array, array $ignore): array
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
    public static function reindex(array $array, array $map, bool $unmapped = true): array
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
     * @return array
     */
    public static function merge(): array
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
                    $array[$key] = static::merge($array[$key], $value);
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
    public static function normalize(array $array, $default): array
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
     * @return array
     */
    public static function extend(): array
    {
        $merged = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && static::has($merged, $key) && is_array($merged[$key])) {
                    $merged[$key] = static::extend($merged[$key], $value);
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
    public static function asHierarchy(array $array): array
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
    public static function groupBy(array $array, callable $callback = null): array
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
    public static function dot(array $array, string $prepend = ''): array
    {
        $cache = serialize(['array' => $array, 'prepend' => $prepend]);

        if (array_key_exists($cache, self::$dotted)) {
            return self::$dotted[$cache];
        }

        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return self::$dotted[$cache] = $results;
    }

    /**
     * Expand a dotted array. Acts the opposite way of Arr::dot().
     *
     * @param array     $array
     * @param int|float $depth
     *
     * @return array
     */
    public static function unDot(array $array, $depth = INF): array
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
                $results[$key] = static::unDot($value, $depth - 1);
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
    public static function flatten(array $array, string $separator = null, string $prepend = ''): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, static::flatten($value, $separator, $prepend . $key . $separator));
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
    public static function expand(array $array, string $prepend = ''): array
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

            $results = static::set($results, $key, $value);
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
    public static function reset(array $array, $deep = false)
    {
        $target = [];

        foreach ($array as $key => $value) {
            if ($deep && is_array($value)) {
                $value = static::reset($value);
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
     * @return array
     */
    public static function extendDistinct()
    {
        $merged = [];

        foreach (func_get_args() as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && static::has($merged, $key) && is_array($merged[$key])) {
                    if (static::isAssoc($value) && static::isAssoc($merged[$key])) {
                        $merged[$key] = static::extendDistinct($merged[$key], $value);

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
    public static function sort(
        array $array,
        callable $callback,
        int $options = SORT_REGULAR,
        bool $descending = false
    ): array {
        $results = [];

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($array as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

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
    public static function sortRecursive(array $array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        // sort associative array
        if (static::isAssoc($array)) {
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
     * @return array
     */
    public static function zip()
    {
        $args = func_get_args();
        $originalArr = $args[0];
        array_shift($args);

        foreach ($originalArr as $key => $value) {
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
     * Applies the callback to the elements of the given arrays
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function map(array $array, callable $callback)
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
    public static function filter(array $array, callable $callback)
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
    public static function all(array $array, callable $predicate)
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
    public static function reject(array $array, callable $callback): array
    {
        return static::filter($array, function ($value, $key) use ($callback) {
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
    public static function where(array $array, callable $callback): array
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
    public static function first(array $array, callable $callback, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return static::value($default);
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
    public static function last(array $array, callable $callback, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? static::value($default) : end($array);
        }

        return static::first(array_reverse($array), $callback, $default);
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
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
    private static function recurseCollapse(array $subject, array &$newArray, $stack = [])
    {
        foreach ($subject as $key => $value) {
            $fstack = array_merge($stack, [$key]);

            if (is_array($value)) {
                self::recurseCollapse($value, $newArray, $fstack);
            } else {
                $top = array_shift($fstack);
                $arrayPart = count($fstack) ? '.' . implode('.', $fstack) : '';
                $newArray[$top . $arrayPart] = $value;
            }
        }
    }
}
