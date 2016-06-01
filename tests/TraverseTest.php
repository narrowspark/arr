<?php
namespace Narrowspark\Arr\Tests;

use Narrowspark\Arr\Traverse;

class TraverseTest extends \PHPUnit_Framework_TestCase
{
    protected $traverse;

    public function setUp()
    {
        $this->traverse = new Traverse();
    }

    public function testMap()
    {
        $this->assertEquals(
            ['a' => 1, 'b' => 4],
            $this->traverse->map(['a' => 1, 'b' => 2], function ($value, $key) {
                return [$key => $value + 2];
            })
        );
    }

    public function testFirst()
    {
        $array = [100, 200, 300];

        $value = $this->traverse->first($array, function ($key, $value) {
            return $value >= 150;
        });

        $this->assertEquals(200, $value);

        $value = $this->traverse->first($array, function ($key, $value) {
            if ($key === $value) {
                return true;
            }
        }, false);

        $this->assertFalse($value);
    }

    public function testFilter()
    {
        $this->assertEquals(
            [1 => 2, 3 => 2],
            $this->traverse->filter([1, 2, 1, 2], function ($value, $key) {
                if (2 === $value) {
                    return true;
                }
            }, false)
        );
    }

    public function testReject()
    {
        $this->assertEquals(
            [1 => 2, 3 => 2],
            $this->traverse->reject([1, 2, 1, 2], function ($value, $key) {
                if (2 !== $value) {
                    return true;
                }
            }, false)
        );
    }

    public function testLast()
    {
        $array = [100, 200, 300];
        $last = $this->traverse->last($array, function () {
            return true;
        });

        $this->assertEquals(300, $last);
    }

    public function testWhere()
    {
        $array = [100, '200', 300, '400', 500];

        $array = $this->traverse->where($array, function ($key, $value) {
            return is_string($value);
        });

        $this->assertEquals([1 => 200, 3 => 400], $array);
    }

    public function testAll()
    {
        $this->assertTrue(
            $this->traverse->all([2, 4, 6], function ($n) {
                return $n % 2 === 0;
            })
        );

        $this->assertFalse(
            $this->traverse->all([2, 4, 5], function ($n) {
                return $n % 2 === 0;
            })
        );
    }
}
