<?php

namespace Php\Package\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Parsers\parse;

class ParserTest extends TestCase
{
    public function testExceptionFileEmpty(): void
    {
        $pathFixture = __DIR__ . "/fixtures/";
        $file1 = $pathFixture . "file1.json22";
        $firstFile = file_get_contents($file1);
        $formatFile = pathinfo($file1, PATHINFO_EXTENSION);


        echo "<pre>";
        print_r($formatFile);
        echo "</pre>";
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid format: json22. Data must be in JSON or YAML format");
        parse(false, $formatFile);
    }
}
