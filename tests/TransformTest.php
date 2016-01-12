<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Transform;

class TransformTest extends \PHPUnit_Framework_TestCase
{
    protected $transform;

    public function setUp()
    {
        $this->transform = new Transform();
    }

    /**
     * @dataProvider extendProvider
     */
    public function testExtend($expected, $array1, $array2)
    {
        $this->assertEquals($expected, $this->transform->extend($array1, $array2));
    }

    public function extendProvider()
    {
        return [
            [
                ['foo' => 'bar', 'bar' => 'bar', [1, 2, 3]],
                ['foo' => 'foo', [1, 2, 3]],
                ['foo' => 'bar', 'bar' => 'bar'],
            ],
            [
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar', 'biz' => 'fobo']],
                ['foo' => ['bar' => ['baz' => 'biz']], [1, 'biz' => 'fobo']],
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar']],
            ],
            [
                [0 => 'biz', 1 => 'bar', 'bar' => ['bar' => ['baz' => 'fobo']], 'baz' => ['foo' => 'bar'], 2 => [1, 3]],
                [0 => 'foo', 1 => 'bar', 'baz' => ['foo' => 'bar'], 2 => [2, 3]],
                [0 => 'biz', 'bar' => ['bar' => ['baz' => 'fobo']], 2 => [1]],
            ],
        ];
    }

    /**
     * @dataProvider resetProvider
     */
    public function testReset($expected, $array, $deep)
    {
        $this->assertEquals($expected, $this->transform->reset($array, $deep), $deep);
    }

    public function resetProvider()
    {
        return [
            [
                [0 => 'foo', 'baz' => 'bar', 1 => 'bar'],
                [10 => 'foo', 'baz' => 'bar', '199' => 'bar'],
                false,
            ],
            [
                [0 => [10 => 'foo', 'baz' => 'bar', '199' => 'bar'], 'baz' => 'bar', 1 => 'bar'],
                [10 => [10 => 'foo', 'baz' => 'bar', '199' => 'bar'], 'baz' => 'bar', '199' => 'bar'],
                false,
            ],
            [
                [0 => [0 => 'foo', 'baz' => 'bar', 1 => 'bar'], 'baz' => 'bar', 1 => 'bar'],
                [10 => [10 => 'foo', 'baz' => 'bar', '199' => 'bar'], 'baz' => 'bar', '199' => 'bar'],
                true,
            ],
        ];
    }

    public function testEvery()
    {
        $data = [
            6 => 'a',
            4 => 'b',
            7 => 'c',
            1 => 'd',
            5 => 'e',
            3 => 'f',
        ];

        $this->assertEquals(['a', 'e'], $this->transform->every($data, 4));
        $this->assertEquals(['b', 'f'], $this->transform->every($data, 4, 1));
        $this->assertEquals(['c'], $this->transform->every($data, 4, 2));
        $this->assertEquals(['d'], $this->transform->every($data, 4, 3));
    }

    /**
     * @dataProvider expandProvider
     */
    public function testExpand($expected, $array, $prepend)
    {
        $this->assertEquals($expected, $this->transform->expand($array, $prepend));
    }

    public function expandProvider()
    {
        return [
            [['arr' => 'narrowspark', 'languages' => ['php' => true]], ['arr' => 'narrowspark', 'languages.php' => true], ''],
            [['arr' => 'narrowspark', 'languages' => ['php' => true]], ['foo.arr' => 'narrowspark', 'foo.languages.php' => true], 'foo'],
            [['foo' => ['arr' => 'narrowspark', 'languages' => ['php' => true]]], ['foo.arr' => 'narrowspark', 'foo.languages.php' => true], 'bar'],
        ];
    }

    /**
     * @dataProvider extendDistinctProvider
     */
    public function testExtendDistinct($expected, $array1, $array2)
    {
        $this->assertEquals($expected, $this->transform->extendDistinct($array1, $array2));
    }

    public function extendDistinctProvider()
    {
        return [
            [
                ['foo' => 'bar', 'bar' => 'bar', [1, 2, 3]],
                ['foo' => 'foo', [1, 2, 3]],
                ['foo' => 'bar', 'bar' => 'bar'],
            ],
            [
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar', 'biz' => 'baz']],
                ['foo' => ['bar' => ['baz' => 'biz']], [1, 'biz' => 'baz']],
                ['foo' => ['bar' => 'baz'], [1, 2, 3, 'foo' => 'bar']],
            ],
            [
                [0 => 'biz', 1 => 'bar', 'bar' => ['bar' => ['baz' => 'baz']], 'baz' => ['foo' => 'bar'], 2 => [1]],
                [0 => 'foo', 1 => 'bar', 'baz' => ['foo' => 'bar'], 2 => [2, 3]],
                [0 => 'biz', 'bar' => ['bar' => ['baz' => 'baz']], 2 => [1]],
            ],
            [
                ['foo' => ['bar' => [5]]],
                ['foo' => ['bar' => [1, 2, 3]]],
                ['foo' => ['bar' => [5]]],
            ],
        ];
    }

    public function testSwap()
    {
        $this->assertEquals(
            ['foo' => 'boz', 'biz' => 'bar'],
            $this->transform->swap(['foo' => 'bar', 'biz' => 'boz'], 'foo', 'biz')
        );

        $this->assertEquals(['boz', 'bar'], $this->transform->swap(['bar', 'boz'], 0, 1));
    }

    public function testAsHierarchy()
    {
        $this->assertSame(
            [
                'key' => [
                    'sub1' => 'value1',
                    'sub2' => 'value2',
                    'sub3' => [
                        'sub4' => 'value3',
                    ],
                ],
                'answer' => 42,
            ],
            $this->transform->asHierarchy([
                'key.sub1' => 'value1',
                'key.sub2' => 'value2',
                'key.sub3.sub4' => 'value3',
                'answer' => 42,
            ])
        );
    }

    /**
     * @dataProvider dotProvider
     */
    public function testDot($expected, $array, $prepend)
    {
        $this->assertEquals($expected, $this->transform->dot($array));
    }

    public function dotProvider()
    {
        return [
            [['foo' => 'bar'], ['foo' => 'bar'], ''],
            [['foo.bar' => 'baz'], ['foo' => ['bar' => 'baz']], ''],
            [['foo.bar.baz' => 'biz'], ['foo' => ['bar' => ['baz' => 'biz']]], ''],
        ];
    }

    public function testGroupBy()
    {
        $this->assertSame(
            [
                1 => [1],
                2 => [2, 2],
                3 => [3, 3],
                4 => [4],
            ],
            $this->transform->groupBy([1, 2, 2, 3, 3, 4])
        );

        $this->assertSame(
            [
                1 => [1, 3, 5],
                0 => [2, 4, 6],
            ],
            $this->transform->groupBy(
                [1, 2, 3, 4, 5, 6],
                function ($n) {
                    return $n % 2;
                }
            )
        );
    }

    public function testFlatten()
    {
        $this->assertSame(
            [1, 2, 3, 4, 5],
            $this->transform->flatten([1, [2, [3, [4, 5]]]])
        );

        $this->assertSame(
            [0 => 'a', '1-0' => 'b', '1-1-0' => 'c', '1-1-1-0' => 'd', '1-1-1-1' => 'e'],
            $this->transform->flatten(['a', ['b', ['c', ['d', 'e']]]], '-')
        );

        $this->assertSame(
            ['_0' => 'a', '_1-0' => 'b', '_1-1-0' => 'c', '_1-1-1-0' => 'd', '_1-1-1-1' => 'e'],
            $this->transform->flatten(['a', ['b', ['c', ['d', 'e']]]], '-', '_')
        );
    }

    public function testSortRecursive()
    {
        $array = [
            'users' => [
                [
                    // should sort associative arrays by keys
                    'name' => 'joe',
                    'mail' => 'joe@example.com',
                    // should sort deeply nested arrays
                    'numbers' => [2, 1, 0],
                ],
                [
                    'name' => 'jane',
                    'age' => 25,
                ],
            ],
            'repositories' => [
                // should use weird `sort()` behavior on arrays of arrays
                ['id' => 1],
                ['id' => 0],
            ],
            // should sort non-associative arrays by value
            20 => [2, 1, 0],
            30 => [
                // should sort non-incrementing numerical keys by keys
                2 => 'a',
                1 => 'b',
                0 => 'c',
            ],
        ];

        $expect = [
            20 => [0, 1, 2],
            30 => [
                0 => 'c',
                1 => 'b',
                2 => 'a',
            ],
            'repositories' => [
                ['id' => 0],
                ['id' => 1],
            ],
            'users' => [
                [
                    'age' => 25,
                    'name' => 'jane',
                ],
                [
                    'mail' => 'joe@example.com',
                    'name' => 'joe',
                    'numbers' => [0, 1, 2],
                ],
            ],
        ];

        $this->assertEquals($expect, $this->transform->sortRecursive($array));
    }

    /**
     * @dataProvider zipProvider
     */
    public function testZip($expected, $array, $array2, $array3)
    {
        $this->assertEquals($expected, $this->transform->zip($array, $array2, $array3));
    }

    public function zipProvider()
    {
        return [
            [[[1, 4, 7], [2, 5, 8], [3, 6, 9]], [1, 2, 3], [4, 5, 6], [7, 8, 9]],
            [[[1, 4, 7], [2, 5, 8]], [1, 2], [4, 5, 6], [7, 8, 9]],
        ];
    }
}
