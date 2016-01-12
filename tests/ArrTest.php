<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Arr;

class ArrTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = (new Arr())->set($array, 'products.desk.price', 200);

        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);
    }

    public function testTwoArgsCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue((new Arr())->has($array, 'products'));
    }

    public function testOneArgsCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue(in_array((new Arr())->random($array), $array));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCall()
    {
        $result = (new Arr())->invalidMethod('foo');
    }
}
