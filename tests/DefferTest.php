<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DefferTest extends TestCase
{
    public function testDefferJson(): void
    {
        $this->assertEquals([1, 2], [1 , 2]);
    }
}
