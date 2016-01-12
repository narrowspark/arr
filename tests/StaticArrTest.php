<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\StaticArr;

class StaticArrTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = StaticArr::set($array, 'products.desk.price', 200);

        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);
    }

    public function testTwoArgsCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue(StaticArr::has($array, 'products'));
    }

    public function testOneArgsCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue(in_array(StaticArr::random($array), $array));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCall()
    {
        $result = StaticArr::invalidMethod('foo');
    }
}
