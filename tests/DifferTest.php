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
     * @dataProvider diffProvider
     */

    public function testDiff(string $file1, string $file2, string $format, string $expectedFileName): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), $format);
        $content = file_get_contents($this->getFilePath($expectedFileName));
        if ($expectedFileName === 'json.txt' && !empty($content)) {
            $expected = str_replace(["-", "\n"], "", $content);
        } else {
            $expected = $content;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider diffDefaultProvider
     */

    public function testDefault(string $file1, string $file2, string $format, string $expectedFileName): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2));
        $content = file_get_contents($this->getFilePath($expectedFileName));
        if ($expectedFileName === 'json.txt' && !empty($content)) {
            $expected = str_replace(["-", "\n"], "", $content);
        } else {
            $expected = $content;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider diffDefaultProvider
     */

    public function testStylish(string $file1, string $file2, string $format, string $expectedFileName): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'stylish');
        $content = file_get_contents($this->getFilePath($expectedFileName));
        if ($expectedFileName === 'json.txt' && !empty($content)) {
            $expected = str_replace(["-", "\n"], "", $content);
        } else {
            $expected = $content;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider diffPlainProvider
     */

    public function testPlain(string $file1, string $file2, string $format, string $expectedFileName): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'plain');
        $content = file_get_contents($this->getFilePath($expectedFileName));
        if ($expectedFileName === 'json.txt' && !empty($content)) {
            $expected = str_replace(["-", "\n"], "", $content);
        } else {
            $expected = $content;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider diffJsonProvider
     */

    public function testJson(string $file1, string $file2, string $format, string $expectedFileName): void
    {
        $actual = genDiff($this->getFilePath($file1), $this->getFilePath($file2), 'json');
        $content = file_get_contents($this->getFilePath($expectedFileName));
        if ($expectedFileName === 'json.txt' && !empty($content)) {
            $expected = str_replace(["-", "\n"], "", $content);
        } else {
            $expected = $content;
        }

        $this->assertEquals($expected, $actual);
    }

    public function diffDefaultProvider(): array
    {
        return [
            ['1.json', '2.json', 'stylish', 'stylish.txt'],
            ['1.yml', '2.yml', 'stylish', 'stylish.txt']
        ];
    }

    public function diffJsonProvider(): array
    {
        return [
            ['1.json', '2.json', 'json', 'json.txt'],
            ['1.yml', '2.yml', 'json', 'json.txt']
        ];
    }

    public function diffPlainProvider(): array
    {
        return [
            ['1.json', '2.json', 'plain', 'plain.txt'],
            ['1.yml', '2.yml', 'plain', 'plain.txt']
        ];
    }

    public function diffProvider(): array
    {
        return [
            ['1.json', '2.json', 'stylish', 'stylish.txt'],
            ['1.yml', '2.yml', 'stylish', 'stylish.txt'],
            ['1.json', '2.json', 'plain', 'plain.txt'],
            ['1.yml', '2.yml', 'plain', 'plain.txt'],
            ['1.json', '2.json', 'json', 'json.txt'],
            ['1.yml', '2.yml', 'json', 'json.txt']
        ];
    }
}
