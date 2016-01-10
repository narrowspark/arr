<?php
namespace Narrowspark\Arr\Tests\Traits;

use Narrowspark\Arr\Traits\SplitPathTrait;

class SplitPathTraitTest extends \PHPUnit_Framework_TestCase
{
    use SplitPathTrait;

    public function testSplitPath()
    {
        $this->assertEquals(
            ['a', 'b'],
            $this->splitPath(['a', 'b'])
        );
    }

    public function testSplitDottedPath()
    {
        $this->assertEquals(
            ['a', 'b'],
            $this->splitPath('a.b')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSplitEmptyPath()
    {
        $this->splitPath([]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSplitEmptyDottedPath()
    {
        $this->splitPath('');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSplitInvalidPath()
    {
        $this->splitPath(12);
    }
}
