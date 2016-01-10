<?php
namespace Narrowspark\Arr\Tests\Traits;

use Narrowspark\Arr\Traits\ValueTrait;

class ValueTraitTest extends \PHPUnit_Framework_TestCase
{
    use ValueTrait;

    public function testValue()
    {
        $int = self::value(function () {
            return 42;
        });

        $this->assertSame(42, $int);
        $this->assertSame('42', self::value('42'));
    }
}
