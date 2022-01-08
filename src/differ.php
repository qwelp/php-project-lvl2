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

    return "";
}

function decodeJsonDiff(string $file): array
{
    return json_decode($file, true);
}
