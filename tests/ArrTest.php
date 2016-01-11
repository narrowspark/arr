<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Arr;

class ArrTest extends \PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        (new Arr())->set($array, 'products.desk.price', 200);

        var_dump($array);

        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testBadMethodCall()
    {
        $result = (new Arr)->invalidMethod('foo');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLessArguments()
    {
        $result = (new Arr)->set('foo');
    }
}
