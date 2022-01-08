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

    $objFirstFile = decodeJsonDiff($firstFile);
    $objSecondFile = decodeJsonDiff($secondFile);

    $result = [];
    $result[] = "{";

    foreach ($objFirstFile as $k => $v) {
        if (isset($objSecondFile[$k])) {
            if ($v === $objSecondFile[$k]) {
                $result[] = "    " . $k . ": " . $objSecondFile[$k];
            } else {
                $result[] = "  - " . $k . ": " . $v;
                $result[] = "  + " . $k . ": " . $objSecondFile[$k];
            }
        } else {
            $result[] =  "  - " . $k . ": " . $v;
        }
    }

    foreach ($objSecondFile as $k => $v) {
        $objFirstFileKeys = array_keys($objFirstFile);

        if (!in_array($k, $objFirstFileKeys)) {
            $result[] = "  + " . $k . ": " . $v;
        }
    }

    $result[] = "}";

    return implode(PHP_EOL, $result) . PHP_EOL;
}

function decodeJsonDiff(string $file): array
{
    return json_decode($file, true);
}
