<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DefferTest extends TestCase
{
    public function testDeffer(): void
    {
        $pathFixture = __DIR__ . "/fixtures/";
        $file1 = $pathFixture . "file1.json";
        $file2 = $pathFixture . "file2.json";

        $diff = genDiff($file1, $file2);

        $expected = "{" . PHP_EOL;
        $expected .= "    host: hexlet.io" . PHP_EOL;
        $expected .= "  - timeout: 50" . PHP_EOL;
        $expected .= "  + timeout: 20" . PHP_EOL;
        $expected .= "  - proxy: 123.234.53.22" . PHP_EOL;
        $expected .= "  - follow: " . PHP_EOL;
        $expected .= "  + verbose: 1" . PHP_EOL;
        $expected .= "}" . PHP_EOL;

        $this->assertEquals($expected, $diff);
    }

    public function testExceptionFileNotFound(): void
    {
        $pathFixture = __DIR__ . "/fixtures/";
        $file2 = $pathFixture . "file2.json";
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File not found.');
        genDiff('', $file2);
    }

    public function testExceptionFileEmpty(): void
    {
        $pathFixture = __DIR__ . "/fixtures/";
        $file1 = $pathFixture . "file1.json";
        $fileEmpty = $pathFixture . "fileEmpty.json";
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File empty.');
        genDiff($file1, $fileEmpty);
    }
}
