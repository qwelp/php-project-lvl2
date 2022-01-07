<?php

namespace Differ\Differ;

function genDiff(string $firstFile, string $secondFile): string
{
    $firstFile = file_get_contents($firstFile);
    $secondFile = file_get_contents($secondFile);

    if (!$firstFile || !$secondFile) {
        return '';
    }

    $objFirstFile = json_decode($firstFile, true);
    $objSecondFile = json_decode($secondFile, true);

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
