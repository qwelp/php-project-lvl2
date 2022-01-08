<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DefferTest extends TestCase
{
    public function testDeffer(): void
    {
        $path = __DIR__ . "/fixtures/";
        $file1 = $path . "file1.json";
        $file2 = $path . "file2.json";
        $diff = genDiff($file1, $file2);

        $this->assertEquals('123', $diff);
    }
}
