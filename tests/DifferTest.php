<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private function getFilePath(string $name): string
    {
        return __DIR__ . '/fixtures/' . $name;
    }

    /**
     * @dataProvider formatProvider
     */
    public function testDefault(string $format): void
    {
        $filepath1 = $this->getFilePath("file1.{$format}");
        $filepath2 = $this->getFilePath("file2.{$format}");
        $actual = genDiff($filepath1, $filepath2);
        $expected = file_get_contents($this->getFilePath('diff.stylish'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testStylish(string $format): void
    {
        $filepath1 = $this->getFilePath("file1.{$format}");
        $filepath2 = $this->getFilePath("file2.{$format}");
        $actual = genDiff($filepath1, $filepath2, 'stylish');
        $expected = file_get_contents($this->getFilePath('diff.stylish'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testPlain(string $format): void
    {
        $filepath1 = $this->getFilePath("file1.{$format}");
        $filepath2 = $this->getFilePath("file2.{$format}");
        $actual = genDiff($filepath1, $filepath2, 'plain');
        $expected = file_get_contents($this->getFilePath('diff.plain'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testJson(string $format): void
    {
        $filepath1 = $this->getFilePath("file1.{$format}");
        $filepath2 = $this->getFilePath("file2.{$format}");
        $actual = genDiff($filepath1, $filepath2, 'json');
        $expected = file_get_contents($this->getFilePath('diff.json'));
        $this->assertEquals($expected, $actual);
    }

    public function formatProvider(): array
    {
        return [
            ['json'],
            ['yml']
        ];
    }
}
