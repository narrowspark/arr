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
        $arr = $this->transform->extend($array1, $array2);

        $this->assertEquals($expected, $arr);
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
}
