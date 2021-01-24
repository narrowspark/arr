<?php
declare(strict_types=1);
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Arr;

class EnumeratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider splitProvider
     */
    public function testSplit($expectedArray, $array, $splitIntoNumber, $preserveKeys)
    {
        $this->assertSame(
            $expectedArray,
            Arr::split($array, $splitIntoNumber, $preserveKeys)
        );
    }

    public function splitProvider()
    {
        return [
            [
                [['a', 'b'], ['c', 'd']], ['a', 'b', 'c', 'd'], 2, false,
            ],
            [
                [['a', 'b', 'c'], ['d', 'e']], ['a', 'b', 'c', 'd', 'e'], 2, false,
            ],
            [
                [['a', 'b'], ['c', 'd'], ['e']], ['a', 'b', 'c', 'd', 'e'], 3, false,
            ],
            [
                [], [], 2, false,
            ],
            [
                [['a'], ['b']], ['a', 'b'], 2, false,
            ],
            [
                [['a' => 1], ['b' => 2]], ['a' => 1, 'b' => 2], 2, true,
            ],
        ];
    }

    public function testOnly()
    {
        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $array = Arr::only($array, ['name', 'price']);

        $this->assertEquals(['name' => 'Desk', 'price' => 100], $array);
    }

    public function testRandom()
    {
        $this->assertNull(Arr::random([]));

        $testArray = [
            'one'   => 'a',
            'two'   => 'b',
            'three' => 'c',
        ];

        $testArrayValues = array_values($testArray);
        $randomArrayValue = Arr::random($testArray);

        $this->assertContains($randomArrayValue, $testArrayValues);
    }

    /**
     * @dataProvider isAssociativeProvider
     */
    public function testIsAssociative($expected, $array)
    {
        $this->assertEquals($expected, Arr::isAssoc($array));
    }

    public function isAssociativeProvider()
    {
        return [
            [false, [0, '1', 2]],
            [true, [99 => 0, 5 => 1, 2 => 2]],
            [true, ['foo' => 'bar', 1, 2]],
            [true, ['foo' => 'bar', 'bar' => 'baz']],
            [true, []],
        ];
    }

    /**
     * @dataProvider isIndexProvider
     */
    public function isIndexed($expected, $array)
    {
        $this->assertEquals($expected, Arr::isIndexed($array));
    }

    public function isIndexProvider()
    {
        return [
            [false, [1, 2, 3, 'a' => 'foo']],
            [false, [0 => 3, 'a' => 'foo']],
            [true, [1, 2, 3]],
            [true, [0 => 1, '3' => 2]],
            [true, []],
        ];
    }

    public function testIsIndexed()
    {
        $this->assertTrue(Arr::isIndexed([]));
        $this->assertTrue(Arr::isIndexed(['baa', 'foo']));
        $this->assertFalse(Arr::isIndexed(['a' => 'baa', 'foo']));
    }

    public function testPrepend()
    {
        $array = Arr::prepend(['one', 'two', 'three', 'four'], 'zero');
        $this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $array);

        $array = Arr::prepend(['one' => 1, 'two' => 2], 0, 'zero');
        $this->assertEquals(['zero' => 0, 'one' => 1, 'two' => 2], $array);
    }

    public function testClosest()
    {
        $array = ['foobartest', 'bar', 'foo'];

        $this->assertEquals('foobartest', Arr::closest($array, 'test'));
    }

    public function testExcept()
    {
        $array = ['name' => 'Desk', 'price' => 100];
        $array = Arr::except($array, ['price']);

        $this->assertEquals(['name' => 'Desk'], $array);
    }
}
