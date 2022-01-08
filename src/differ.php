<?php

namespace Differ\Differ;

function genDiff(string $firstFile, string $secondFile): string
{
    if (!file_exists($firstFile) || !file_exists($secondFile)) {
        throw new \Exception("File not found.");
    }

    $firstFile = file_get_contents($firstFile);
    $secondFile = file_get_contents($secondFile);

    if (!$firstFile || !$secondFile) {
        return '';
    }

    $objFirstFile = decodeJsonFileDiff($firstFile);
    $objSecondFile = decodeJsonFileDiff($secondFile);


    return "123";
}

function decodeJsonFileDiff(string $file): array
{
    return json_decode($file, true);
}
