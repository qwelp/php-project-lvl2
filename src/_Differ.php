<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;

function genDiff(string $file1, string $file2): string
{
    if (!file_exists($file1) || !file_exists($file2)) {
        throw new \Exception("File not found.");
    }

    $firstFile = file_get_contents($file1);
    $secondFile = file_get_contents($file2);

    if (empty($firstFile) || empty($secondFile)) {
        throw new \Exception("File empty.");
    }

    $formatFile = pathinfo($file1, PATHINFO_EXTENSION);

    $objFirstFile = (array) parse($firstFile, $formatFile);
    $objSecondFile = (array) parse($secondFile, $formatFile);

    $objFirstFile = boolToString($objFirstFile);
    $objSecondFile = boolToString($objSecondFile);

    return render($objFirstFile, $objSecondFile);
}

function render(array $firstFile, array $secondFile): string
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

function boolToString(array $array): array
{
    return array_map(function ($value) {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return $value;
    }, $array);
}
