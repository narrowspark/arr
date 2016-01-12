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
     * @dataProvider resetProvider
     */
    public function testReset($expected, $array, $deep)
    {
        $this->assertEquals($expected, $this->access->reset($array, $deep), $deep);
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

    public function testForget()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->forget($array, null);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->forget($array, []);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->forget($array, 'products.desk');
        $this->assertEquals(['products' => []], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->forget($array, 'products.desk.price');
        $this->assertEquals(['products' => ['desk' => []]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = $this->access->forget($array, 'products.final.price');
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['shop' => ['cart' => [150 => 0]]];
        $array = $this->access->forget($array, 'shop.final.cart');
        $this->assertEquals(['shop' => ['cart' => [150 => 0]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        $array = $this->access->forget($array, 'products.desk.price.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50]]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        $array = $this->access->forget($array, 'products.desk.final.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], $array);

        $array = ['products' => ['desk' => ['price' => 50], null => 'something']];
        $array = $this->access->forget($array, ['products.amount.all', 'products.desk.price']);
        $this->assertEquals(['products' => ['desk' => [], null => 'something']], $array);
    }
}
