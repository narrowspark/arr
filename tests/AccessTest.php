<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Access;

class AccessTest extends \PHPUnit_Framework_TestCase
{
    protected $access;

    public function setUp()
    {
        $this->access = new Access();
    }

    public function testUpdate()
    {
        $data = [
            'a' => 1,
            'b' => [
                'c' => 2,
                'd' => [
                    'e' => 3,
                ],
            ],
        ];

        $expected = [
            'a' => 2,
            'b' => [
                'c' => 2,
                'd' => [
                    'e' => 4,
                ],
            ],
        ];

        $increment = function ($value) {
            return $value + 1;
        };

        $result = $this->access->update($data, 'a', $increment);
        $result = $this->access->update($result, 'z', $increment);
        $result = $this->access->update($result, 'b.d.e', $increment);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider addProvider
     */
    public function testAdd($expected, $array, $key, $value)
    {
        $this->assertEquals($expected, $this->access->add($array, $key, $value));
    }

    public function addProvider()
    {
        return [
            [['list' => [1, 2, 3]], ['list' => [1, 2]], 'list', 3],
            [['value' => [1, 2]], ['value' => 1], 'value', 2],
            [['nested' => ['value' => [1, 2]]], ['nested' => ['value' => 1]], 'nested.value', 2],
            [['nested' => ['value' => [1, 2]]], ['nested' => ['value' => [1]]], 'nested.value', 2],
        ];
    }

    public function testGet()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $value = $this->access->get($array, 'products.desk');
        $this->assertEquals(['price' => 100], $value);

        $value = $this->access->get($array);
        $this->assertEquals($array, $value);

        $value = $this->access->get($array, 'products');
        $this->assertEquals(['desk' => ['price' => 100]], $value);
    }

    public function testHas()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue($this->access->has($array, 'products'));
        $this->assertTrue($this->access->has($array, 'products.desk'));
        $this->assertTrue($this->access->has($array, 'products.desk.price'));
        $this->assertFalse($this->access->has($array, 'products.foo'));
        $this->assertFalse($this->access->has($array, 'products.desk.foo'));
        $this->assertFalse($this->access->has([], null));
    }

    public function testSet()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->set($array, 'products.desk.price', 200);
        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->set($array, null, $array);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);
    }

    /**
     * @dataProvider forgetProvider
     */
    public function testForget($expected, $array, $key)
    {
        $this->assertEquals($expected, $this->access->forget($array, $key));
    }

    public function forgetProvider()
    {
        return [
            [['products' => ['desk' => ['price' => 100]]], ['products' => ['desk' => ['price' => 100]]], null],
            [['products' => ['desk' => ['price' => 100]]], ['products' => ['desk' => ['price' => 100]]], []],
            [['products' => []], ['products' => ['desk' => ['price' => 100]]], 'products.desk'],
            [['products' => ['desk' => []]], ['products' => ['desk' => ['price' => 100]]], 'products.desk.price'],
            [['products' => ['desk' => ['price' => 100]]], ['products' => ['desk' => ['price' => 100]]], 'products.final.price'],
            [['shop' => ['cart' => [150 => 0]]], ['shop' => ['cart' => [150 => 0]]], 'shop.final.cart'],
            [['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], 'products.desk.final.taxes'],
            [['products' => ['desk' => ['price' => ['original' => 50]]]], ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], 'products.desk.price.taxes'],
            [['products' => ['desk' => [], null => 'something']], ['products' => ['desk' => ['price' => 50], null => 'something']], ['products.amount.all', 'products.desk.price']],
        ];
    }
}
