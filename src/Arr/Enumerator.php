<?php
namespace Narrowspark\Arr;

use Narrowspark\Arr\Traits\ValueTrait;

class Enumerator
{
    use ValueTrait;

    /**
     * Get a random element from the array supplied.
     *
     * @param array $array the source array
     *
     * @return mixed
     */
    public function random(array $array)
    {
        if (! count($array)) {
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
     * Determines if an array is associative.
     *
     * @param array $array
     *
     * @return bool
     */
    public function isAssoc(array $array)
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
    public function split(array $array, $numberOfPieces = 2, $preserveKeys = false)
    {
        if (count($array) === 0) {
            return [];
        }

        $splitSize = ceil(count($array) / $numberOfPieces);

        return array_chunk($array, $splitSize, $preserveKeys);
    }

    /**
     * Check if an array has a numeric index.
     *
     * @param array $array
     *
     * @return bool
     */
    public function isIndexed(array $array)
    {
        if ($array === []) {
            return true;
        }

        return ! $this->isAssoc($array);
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
     * Return the closest found value from array.
     *
     * @param array $array
     * @param sting $value
     *
     * @return mixed
     */
    public function closest(array $array, $value)
    {
        sort($array);
        $closest = $array[0];

        for ($i = 1, $j = count($array), $k = 0; $i < $j; $i++, $k++) {
            $middleValue = ((int) $array[$i] - (int) $array[$k]) / 2 + (int) $array[$k];

            if ($value >= $middleValue) {
                $closest = $array[$i];
            }
        }

        return $this->value($closest);
    }
}
