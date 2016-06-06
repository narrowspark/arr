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
        $array = [
            6 => 'a',
            4 => 'b',
            7 => 'c',
            1 => 'd',
            5 => 'e',
            3 => 'f',
        ];

        $this->assertEquals(['a', 'e'], $this->transform->every($array, 4));
        $this->assertEquals(['b', 'f'], $this->transform->every($array, 4, 1));
        $this->assertEquals(['c'], $this->transform->every($array, 4, 2));
        $this->assertEquals(['d'], $this->transform->every($array, 4, 3));
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

    /**
     * @dataProvider unDotProvider
     */
    public function testUnDot($expected, $array, $recursively)
    {
        $this->assertEquals($expected, $this->transform->unDot($array));
    }

    public function unDotProvider()
    {
        return [
            [[], [], false],
            [['foo' => ['bar' => 'baz']], ['foo.bar' => 'baz'], false],
            [['foo' => ['bar' => ['baz' => 'biz']]], ['foo.bar.baz' => 'biz'], false],
            [
                ['foo' => ['bar' => 'baz', 'bar1' => 'baz1'], 'foo2' => 'bar2'],
                ['foo.bar' => 'baz', 'foo.bar1' => 'baz1', 'foo2' => 'bar2'],
                false,
            ],
        ];
    }

    public function testDotCache()
    {
        $this->assertEquals(['foo' => 'bar'], $this->transform->dot(['foo' => 'bar'], ''));
        $this->assertEquals(['foo' => 'bar'], $this->transform->dot(['foo' => 'bar'], ''));
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

    public function testPop()
    {
        $this->assertEquals(
            'banana',
            $this->transform->pop(['orange' => ['banana'], 'apple' => 'raspberry'], 'orange')
        );

        $this->assertEquals(
            null,
            $this->transform->pop(['orange' => 'banana'], 'orange')
        );

        $this->assertEquals(
            null,
            $this->transform->pop(['orange' => ['banana'], 'apple' => 'raspberry'], 'dont')
        );
    }

    /**
     * @dataProvider mergeProvider
     */
    public function testMerge($expected, $array, $array2)
    {
        $this->assertEquals(
            $expected,
            $this->transform->merge($array, $array2)
        );
    }

    public function mergeProvider()
    {
        return [
            [
                [1, 2, 3, 4, 5],
                [1, 2],
                [3, 4, 5],
            ],
            [
                ['a' => 'b', 'c' => 'd', 'e' => 'f'],
                ['a' => 'g', 'c' => 'd'],
                ['a' => 'b', 'e' => 'f'],
            ],
            [
                ['a' => [1, 'b' => 'c', 2, 3, 4, 5]],
                ['a' => [1, 'b' => 'd', 2]],
                ['a' => [3, 4, 'b' => 'c', 5]],
            ],
        ];
    }

    public function testReindex()
    {
        $array = ['foo' => 'bar'];
        $map = ['foo' => 'baz'];

        $expected = [
            'foo' => 'bar',
            'baz' => 'bar',
        ];

        $this->assertEquals(
            $expected,
            $this->transform->reindex($array, $map)
        );

        $expected = ['baz' => 'bar'];
        $this->assertEquals(
            $expected,
            $this->transform->reindex($array, $map, false)
        );
    }

    public function testNormalize()
    {
        $array = [
            'one',
            'two' => 'three',
            'four',
        ];

        $default = 'default';

        $this->assertEquals(
            [
                'one' => $default,
                'two' => 'three',
                'four' => $default,
            ],
            $this->transform->normalize($array, $default)
        );
    }

    public function testCombine()
    {
        $users = [
            ['id' => 1, 'name' => 'a'],
            ['id' => 2, 'name' => 'b'],
            ['id' => 3, 'name' => 'b'],
        ];

        $closure = function ($user) {
            yield $user['name'] => $user['id'];
        };

        // overwriting existing names
        $this->assertEquals(
            [
                'a' => 1,
                'b' => 3,
            ],
            $this->transform->combine($users, $closure)
        );

        // not overwriting existing names
        $this->assertEquals(
            [
                'a' => 1,
                'b' => 2,
            ],
            $this->transform->combine($users, $closure, false)
        );
    }

    public function testWithout()
    {
        $array = [
            'a' => 1,
            'b' => 3,
            'c' => 4,
        ];
        $this->assertEquals(
            [
                1,
                4,
            ],
            $this->transform->without($array, [3])
        );
    }

    public function testCollapse()
    {
        $from = [
            'test' => [
                'a' => 'vala',
                'b' => 'valb',
                'c' => 'valc',
                'd' => [
                    'd0',
                    'd1',
                    ],
                ],
            'top' => 'blah',
        ];

        $this->assertEquals(
            [
                'test.a' => 'vala',
                'test.b' => 'valb',
                'test.c' => 'valc',
                'test.d.0' => 'd0',
                'test.d.1' => 'd1',
                'top' => 'blah',
            ],
            $this->transform->collapse($from)
        );
    }

    public function testComplexCollapse()
    {
        $from = [
            'textbox' => [
                'd' => ['text1'],
                'f' => [
                    'g' => ['val1', 'val2'],
                ],
            ],
            'h.a' => ['text1'],
        ];

        $this->assertEquals(
            [
                'textbox.d.0'   => 'text1',
                'textbox.f.g.0' => 'val1',
                'textbox.f.g.1' => 'val2',
                'h.0'           => 'text1',
            ],
            $this->transform->collapse($from)
        );
    }

    public function testCollapseAndExpand()
    {
        $from = [
            'textbox' => [
                'd' => ['text1'],
                'f' => [
                    'g' => ['val1', 'val2'],
                ],
            ],
            'h.a' => ['text1'],
        ];

        $to = [
            'textbox' => [
                'd' => ['text1'],
                'f' => [
                    'g' => ['val1', 'val2'],
                ],
            ],
            'h' => ['text1'],
        ];

        $this->assertEquals(
            $to,
            $this->transform->expand($this->transform->collapse($from))
        );
    }

    public function testDivide()
    {
        $this->assertEquals(
            [['textbox', 'foo'], [['d' => 'text1'], 'bar']],
            $this->transform->divide([
                'textbox' => ['d' => 'text1'],
                'foo' => 'bar',
            ])
        );
    }

    public function testStripEmpty()
    {
        $this->assertEquals(
            ['textbox' => 'test', 'foo'],
            $this->transform->stripEmpty([
                'textbox' => 'test',
                'foo',
                'a' => '',
                'b' => null,
            ])
        );
    }

    public function testSort()
    {
        $array = [
            ['name' => 'Desk'],
            ['name' => 'Chair'],
        ];

        $array = array_values($this->transform->sort($array, function ($value) {
            return $value['name'];
        }));

        $expected = [
            ['name' => 'Chair'],
            ['name' => 'Desk'],
        ];

        $this->assertEquals($expected, $array);
    }
}
