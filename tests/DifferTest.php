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
    public function testDefault(string $file1, string $file2): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2));
        $expected = file_get_contents($this->getFilePath('diff.stylish'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testStylish(string $file1, string $file2): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'stylish');
        $expected = file_get_contents($this->getFilePath('diff.stylish'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testPlain(string $file1, string $file2): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'plain');
        $expected = file_get_contents($this->getFilePath('diff.plain'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider formatProvider
     */
    public function testJson(string $file1, string $file2): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'json');
        $expected = file_get_contents($this->getFilePath('diff.json'));
        $this->assertEquals($expected, $actual);
    }

    public function formatProvider(): array
    {
        return [
            ['file1.json', 'file2.json'],
            ['file1.yml', 'file2.yml']
        ];
    }
}
