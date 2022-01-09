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

    $objFirstFile = boolTostring($objFirstFile);
    $objSecondFile = boolTostring($objSecondFile);

    return renderDiff($objFirstFile, $objSecondFile);
}

function renderDiff(array $firstFile, array $secondFile): string
{
    $filesKeys = array_keys(array_merge($firstFile, $secondFile));

    $result = array_reduce($filesKeys, function ($acc, $key) use ($firstFile, $secondFile) {
        if (array_key_exists($key, $firstFile) && array_key_exists($key, $secondFile)) {
            if ($firstFile[$key] === $secondFile[$key]) {
                $acc[] = "    " . $key . ": " . $firstFile[$key];
            } else {
                $acc[] = "  - " . $key . ": " . $firstFile[$key];
                $acc[] = "  + " . $key . ": " . $secondFile[$key];
            }
            return $acc;
        }

        if (!array_key_exists($key, $firstFile)) {
            $acc[] = "  + " . $key . ": " . $secondFile[$key];
        } else {
            $acc[] = "  - " . $key . ": " . $firstFile[$key];
        }
        return $acc;
    }, []);

    return "{" . PHP_EOL . implode(PHP_EOL, $result) . PHP_EOL . "}" . PHP_EOL;
}

function boolTostring(array $array): array
{
    return array_map(function ($value) {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }, $array);
}

function decodeJsonFileDiff(string $file): array
{
    return json_decode($file, true);
}
