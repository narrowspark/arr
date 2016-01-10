<?php
namespace Viserio\Support\Tests;

use Viserio\Support\Arr;

class ArrTest
{
    protected $testData = [
        'php' => [
            'rating'    => 5,
            'stars'     => 5,
            'language'  => 'php',
        ],
        'js' => [
            'rating'    => 5,
            'stars'     => 6,
            'language'  => 'js',
        ],
        'css' => [
            'rating'    => 4,
            'stars'     => 4,
            'language'  => 'css',
        ],
        'scss' => [
            'rating'    => 4,
            'stars'     => 4,
            'language'  => 'scss',
        ],
    ];

    public function testGetIndexedByKeysUnique()
    {
        $keysToIndexBy = [
            'rating',
            'stars',
        ];

        $this->assertEquals(
            [
                5 => [
                    5 => [
                        'rating'    => 5,
                        'stars'     => 5,
                        'language'  => 'php',
                    ],
                    6 => [
                        'rating'    => 5,
                        'stars'     => 6,
                        'language'  => 'js',
                    ],
                ],
                4 => [
                    4 => [
                        'rating'    => 4,
                        'stars'     => 4,
                        'language'  => 'scss',
                    ],
                ],
            ],
            Arr::getIndexedByKeys($this->testData, $keysToIndexBy, true)
        );
    }

    public function getIndexedByKeysNonUnique()
    {
        $keysToIndexBy = [
            'rating',
            'stars',
        ];

        $this->assertEquals(
            [
                5 => [
                    5 => [
                        [
                            'rating'    => 5,
                            'stars'     => 5,
                            'language'  => 'php',
                        ],
                    ],
                    6 => [
                        [
                            'rating'    => 5,
                            'stars'     => 6,
                            'language'  => 'js',
                        ],
                    ],
                ],
                4 => [
                    4 => [
                        [
                            'rating'    => 4,
                            'stars'     => 4,
                            'language'  => 'css',
                        ],
                        [
                            'rating'    => 4,
                            'stars'     => 4,
                            'language'  => 'scss',
                        ],
                    ],
                ],
            ],
            Arr::getIndexedByKeys($this->testData, $keysToIndexBy, false)
        );
    }

    public function testGetIndexedValuesString()
    {
        $this->assertEquals(
            [
                'php'   => 5,
                'js'    => 6,
                'css'   => 4,
                'scss'  => 4,
            ],
            Arr::getIndexedValues($this->testData, 'language', 'stars')
        );
    }

    public function testArrayPrepend()
    {
        $array = Arr::prepend(['one', 'two', 'three', 'four'], 'zero');
        $this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $array);

        $array = Arr::prepend(['one' => 1, 'two' => 2], 0, 'zero');
        $this->assertEquals(['zero' => 0, 'one' => 1, 'two' => 2], $array);
    }

    public function testGetIndexedValuesArray()
    {
        $this->assertEquals(
            [
                'php' => [
                    'rating'    => 5,
                    'stars'     => 5,
                ],
                'js' => [
                    'rating'    => 5,
                    'stars'     => 6,
                ],
                'css' => [
                    'rating'    => 4,
                    'stars'     => 4,
                ],
                'scss' => [
                    'rating'    => 4,
                    'stars'     => 4,
                ],
            ],
            Arr::getIndexedValues($this->testData, 'language', ['stars', 'rating'])
        );
    }

    public function testArrayDot()
    {
        $array = Arr::dot(['name' => 'daniel', 'languages' => ['php' => true]]);
        $this->assertEquals($array, ['name' => 'daniel', 'languages.php' => true]);

        $array = Arr::dot(['name' => 'daniel', 'languages' => ['javascript' => true]]);
        $this->assertEquals($array, ['name' => 'daniel', 'languages.javascript' => true]);
    }

    public function testArrayPluckWithNestedKeys()
    {
        $array = [['user' => ['narrowspark', 'Viserio']], ['user' => ['dayle', 'rees']]];
        $this->assertEquals(['narrowspark', 'dayle'], Arr::pluck($array, 'user.0'));
        $this->assertEquals(['narrowspark', 'dayle'], Arr::pluck($array, ['user', 0]));
        $this->assertEquals(['narrowspark' => 'Viserio', 'dayle' => 'rees'], Arr::pluck($array, 'user.1', 'user.0'));
        $this->assertEquals(['narrowspark' => 'Viserio', 'dayle' => 'rees'], Arr::pluck($array, ['user', 1], ['user', 0]));
    }

    public function testArrayGet()
    {
        $array = ['names' => ['developer' => 'daniel']];
        $this->assertEquals('daniel', Arr::get($array, 'names.developer'));
        $this->assertEquals('david', Arr::get($array, 'names.otherDeveloper', 'david'));
        $this->assertEquals('david', Arr::get($array, 'names.otherDeveloper', function () { return 'david'; }));
    }

    public function testArrayHas()
    {
        $array = ['names' => ['developer' => 'daniel']];
        $this->assertTrue(Arr::has($array, 'names'));
        $this->assertTrue(Arr::has($array, 'names.developer'));
        $this->assertFalse(Arr::has($array, 'foo'));
        $this->assertFalse(Arr::has($array, 'foo.bar'));
    }

    public function testArraySet()
    {
        $array = [];
        Arr::set($array, 'names.developer', 'daniel');
        $this->assertEquals('daniel', $array['names']['developer']);
    }

    public function testForget()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, null);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, []);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.desk');
        $this->assertEquals(['products' => []], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.desk.price');
        $this->assertEquals(['products' => ['desk' => []]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.final.price');
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['shop' => ['cart' => [150 => 0]]];
        Arr::forget($array, 'shop.final.cart');
        $this->assertEquals(['shop' => ['cart' => [150 => 0]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        Arr::forget($array, 'products.desk.price.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50]]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        Arr::forget($array, 'products.desk.final.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], $array);

        $array = ['products' => ['desk' => ['price' => 50], null => 'something']];
        Arr::forget($array, ['products.amount.all', 'products.desk.price']);
        $this->assertEquals(['products' => ['desk' => [], null => 'something']], $array);
    }
}
