<?php
declare(strict_types=1);
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Arr;

class AccessTest extends \PHPUnit_Framework_TestCase
{
    public function testValue()
    {
        $int = Arr::value(function () {
            return 42;
        });

        $this->assertSame(42, $int);
        $this->assertSame('42', Arr::value('42'));
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

        $result = Arr::update($data, 'a', $increment);
        $result = Arr::update($result, 'z', $increment);
        $result = Arr::update($result, 'b.d.e', $increment);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider addProvider
     */
    public function testAdd($expected, $array, $key, $value)
    {
        $this->assertEquals($expected, Arr::add($array, $key, $value));
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

        $value = Arr::get($array, 'products.desk');
        $this->assertEquals(['price' => 100], $value);

        $value = Arr::get($array);
        $this->assertEquals($array, $value);

        $value = Arr::get($array, 'products');
        $this->assertEquals(['desk' => ['price' => 100]], $value);
    }

    public function testHas()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $this->assertTrue(Arr::has($array, 'products'));
        $this->assertTrue(Arr::has($array, 'products.desk'));
        $this->assertTrue(Arr::has($array, 'products.desk.price'));
        $this->assertFalse(Arr::has($array, 'products.foo'));
        $this->assertFalse(Arr::has($array, 'products.desk.foo'));
        $this->assertFalse(Arr::has([], null));
    }

    public function testAny()
    {
        $array = ['products.desk' => ['price' => 100]];
        $this->assertTrue(Arr::any($array, 'products.desk'));

        $array = ['products' => ['desk' => ['price' => 100], 'foo' => ['price' => 100]]];
        $this->assertTrue(Arr::any($array, ['products.foo', 'products.desk']));
        $this->assertTrue(Arr::any($array, ['products.desk.price', 'products.desk.count']));
        $this->assertFalse(Arr::any($array, ['products.baz', 'products.desk.count']));

        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertTrue(Arr::any($array, ['foo', 'desk']));
        $this->assertTrue(Arr::any($array, ['foo.foo', 'bar.baz']));

        $array = ['foo', 'bar'];
        $this->assertFalse(Arr::any($array, null));

        $this->assertFalse(Arr::any([], null));

        $this->assertFalse(Arr::any([], [null, true]));
    }

    public function testAny()
    {
        $array = ['products.desk' => ['price' => 100]];
        $this->assertTrue($this->access->any($array, 'products.desk'));

        $array = ['products' => ['desk' => ['price' => 100], 'foo' => ['price' => 100]]];
        $this->assertTrue($this->access->any($array, ['products.foo', 'products.desk']));
        $this->assertTrue($this->access->any($array, ['products.desk.price', 'products.desk.count']));
        $this->assertFalse($this->access->any($array, ['products.baz', 'products.desk.count']));

        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertTrue($this->access->any($array, ['foo', 'desk']));
        $this->assertTrue($this->access->any($array, ['foo.foo', 'bar.baz']));

        $array = ['foo', 'bar'];
        $this->assertFalse($this->access->any($array, null));

        $this->assertFalse($this->access->any([], null));

        $this->assertFalse($this->access->any([], [null, true]));
    }

    public function testSet()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = Arr::set($array, 'products.desk.price', 200);
        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        $array = Arr::set($array, null, $array);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);
    }

    /**
     * @dataProvider forgetProvider
     */
    public function testForget($expected, $array, $key)
    {
        Arr::forget($array, $key);

        $this->assertEquals($expected, $array);
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

    /**
     * @dataProvider pullProvider
     */
    public function testPull($expected, $expected2, $array, $key)
    {
        $name = (new Arr())->pull($array, $key);

        $this->assertEquals($expected, $name);
        $this->assertEquals($expected2, $array);
    }

    public function pullProvider()
    {
        return [
            ['Desk', ['price' => 100], ['name' => 'Desk', 'price' => 100], 'name'],
            // Only works on first level keys
            ['Joe', ['jane@localhost' => 'Jane'], ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane'], 'joe@example.com'],
            // Does not work for nested keys
            [null, ['emails' => ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane']], ['emails' => ['joe@example.com' => 'Joe', 'jane@localhost' => 'Jane']], 'emails.joe@example.com'],
        ];
    }
}
