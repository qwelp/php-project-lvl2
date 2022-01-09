<?php

namespace Differ\Differ;

function genDiff(string $firstFile, string $secondFile): string
{
    if (!file_exists($firstFile) || !file_exists($secondFile)) {
        throw new \Exception("File not found.");
    }

    $firstFile = file_get_contents($firstFile);
    $secondFile = file_get_contents($secondFile);

    if (empty($firstFile) || empty($secondFile)) {
        throw new \Exception("File empty.");
    }

    $objFirstFile = decodeJsonFileDiff($firstFile);
    $objSecondFile = decodeJsonFileDiff($secondFile);

    return renderDiff($objFirstFile, $objSecondFile);
}

function renderDiff(array $firstFile, array $secondFile): string
{
    $result = [];

    foreach ($firstFile as $k => $v) {
        if (isset($secondFile[$k]) && $v === $secondFile[$k]) {
            $result[] = "    " . $k . ": " . $secondFile[$k];
            continue;
        }

        if (isset($secondFile[$k])) {
            $result[] = "  - " . $k . ": " . $v;
            $result[] = "  + " . $k . ": " . $secondFile[$k];
        } else {
            $result[] = "  - " . $k . ": " . $v;
        }
    }

    foreach ($secondFile as $k => $v) {
        $objFirstFileKeys = array_keys($firstFile);

        if (!in_array($k, $objFirstFileKeys)) {
            $result[] = "  + " . $k . ": " . $v;
        }
    }

    return "{" . PHP_EOL . implode(PHP_EOL, $result) . PHP_EOL . "}" . PHP_EOL;
}

function decodeJsonFileDiff(string $file): array
{
    return json_decode($file, true);
}
