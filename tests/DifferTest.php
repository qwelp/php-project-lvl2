<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testDiffer(): void
    {
        /*
         * Чтобы избавиться от дублирования, лучше завести функцию, которая в зависимости от имени вернет нам
         * необходимую фикстуру.
         *
         * Чтобы избежать дублирования рекомендую использовать dataprovider
         *
         * ---- Тут все файлы разные, не дублируются! ----
         */
        $pathFixture = __DIR__ . "/fixtures/";
        $fileJson21 = $pathFixture . "file_json_2_1.json";
        $fileJson22 = $pathFixture . "file_json_2_2.json";
        $fileYml11 = $pathFixture . "file_yml_1_1.yml";
        $fileYml12 = $pathFixture . "file_yml_1_2.yml";
        $fileYml21 = $pathFixture . "file_yml_2_1.yml";
        $fileYml22 = $pathFixture . "file_yml_2_2.yml";
        $fileJson11 = $pathFixture . "file_json_1_1.json";
        $fileJson12 = $pathFixture . "file_json_1_2.json";

        $expectedJson1 = file_get_contents("{$pathFixture}jsonExpected1.txt");
        $expectedFormateJson = file_get_contents("{$pathFixture}expectedFormateJson.txt");
        $expectedFormate = file_get_contents("{$pathFixture}expectedFormatePlain.txt");
        $expectedTree = file_get_contents("{$pathFixture}expectedTree.txt");
        $this->assertEquals($expectedJson1, genDiff($fileYml11, $fileYml12));
        $this->assertEquals($expectedJson1, genDiff($fileJson11, $fileJson12));
        $this->assertEquals($expectedTree, genDiff($fileJson21, $fileJson22));
        $this->assertEquals($expectedTree, genDiff($fileYml21, $fileYml22));
        $this->assertEquals($expectedFormate, genDiff($fileJson21, $fileJson22, "plain"));
        $this->assertEquals($expectedFormateJson, genDiff($fileJson21, $fileJson22, "json"));
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
