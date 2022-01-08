<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DefferTest extends TestCase
{
    const PATH_FIXTURE = __DIR__ . "/fixtures/";
    const FILE_1 = self::PATH_FIXTURE . "file1.json";
    const FILE_2 = self::PATH_FIXTURE . "file2.json";
    const FILE_EMPTY = self::PATH_FIXTURE . "fileEmpty.json";

    public function testDeffer(): void
    {
        $diff = genDiff(self::FILE_1, self::FILE_2);

        $expected = "{" . PHP_EOL;
        $expected .= "    host: hexlet.io" . PHP_EOL;
        $expected .= "  - timeout: 50" . PHP_EOL;
        $expected .= "  + timeout: 20" . PHP_EOL;
        $expected .= "  - proxy: 123.234.53.22" . PHP_EOL;
        $expected .= "  - follow: " . PHP_EOL;
        $expected .= "  + verbose: 1" . PHP_EOL;
        $expected .= "}". PHP_EOL;

        $this->assertEquals($expected, $diff);
    }

    public function testException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found.');
        genDiff('', self::FILE_2);
    }
}
