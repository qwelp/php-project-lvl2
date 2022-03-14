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

        $expected = "{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            doge: {
              - wow: 
              + wow: so much
            }
            key: value
          + ops: vops
        }
    }
    group1: {
      - baz: bas
      + baz: bars
        foo: bar
      - nest: {
            key: value
        }
      + nest: str
    }
  - group2: {
        abc: 12345
        deep: {
            id: 45
        }
    }
  + group3: {
        deep: {
            id: {
                number: 45
            }
        }
        fee: 100500
    }
}";
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
