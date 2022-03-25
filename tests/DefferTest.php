<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DefferTest extends TestCase
{
    public function testDeffer(): void
    {
        $pathFixture = __DIR__ . "/fixtures/";
        $file_json_2_1 = $pathFixture . "file_json_2_1.json";
        $file_json_2_2 = $pathFixture . "file_json_2_2.json";
        $file_yml_1_1 = $pathFixture . "file_yml_1_1.yml";
        $file_yml_1_2 = $pathFixture . "file_yml_1_2.yml";
        $file_yml_2_1 = $pathFixture . "file_yml_2_1.yml";
        $file_yml_2_2 = $pathFixture . "file_yml_2_2.yml";
        $file_json_1_1 = $pathFixture . "file_json_1_1.json";
        $file_json_1_2 = $pathFixture . "file_json_1_2.json";

        $diff_yml_1 = genDiff($file_yml_1_1, $file_yml_1_2);
        $diff_yml_2 = genDiff($file_yml_2_1, $file_yml_2_2);
        $diff_json_1 = genDiff($file_json_1_1, $file_json_1_2);
        $diff_json_2 = genDiff($file_json_2_1, $file_json_2_2);
        $diffFormatePlain = genDiff($file_json_2_1, $file_json_2_2, "plain");
        $diffFormateJson = genDiff($file_json_2_1, $file_json_2_2, "json");

        $expected_1 = '{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}';

        $expectedFormateJson = '{
    "  common": {
        "+ follow": false,
        "  setting1": "Value 1",
        "- setting2": 200,
        "- setting3": true,
        "+ setting3": null,
        "+ setting4": "blah blah",
        "+ setting5": {
            "  key5": "value5"
        },
        "  setting6": {
            "  doge": {
                "- wow": "",
                "+ wow": "so much"
            },
            "  key": "value",
            "+ ops": "vops"
        }
    },
    "  group1": {
        "- baz": "bas",
        "+ baz": "bars",
        "  foo": "bar",
        "- nest": {
            "  key": "value"
        },
        "+ nest": "str"
    },
    "- group2": {
        "  abc": 12345,
        "  deep": {
            "  id": 45
        }
    },
    "+ group3": {
        "  deep": {
            "  id": {
                "  number": 45
            }
        },
        "  fee": 100500
    }
}';
        $expectedFormate = "Property 'common.follow' was added with value: false
Property 'common.setting2' was removed
Property 'common.setting3' was updated. From true to null
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: [complex value]
Property 'common.setting6.doge.wow' was updated. From '' to 'so much'
Property 'common.setting6.ops' was added with value: 'vops'
Property 'group1.baz' was updated. From 'bas' to 'bars'
Property 'group1.nest' was updated. From [complex value] to 'str'
Property 'group2' was removed
Property 'group3' was added with value: [complex value]";
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
        $this->assertEquals($expected_1, $diff_yml_1);
        $this->assertEquals($expected_1, $diff_json_1);
        $this->assertEquals($expected, $diff_json_2);
        $this->assertEquals($expected, $diff_yml_2);
        $this->assertEquals($expectedFormate, $diffFormatePlain);
        $this->assertEquals($expectedFormateJson, $diffFormateJson);
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
