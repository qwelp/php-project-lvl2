<?php

namespace Differ\Differ;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $result =  [];

    if (!file_exists($pathToFile1) || !file_exists($pathToFile2)) {
        throw new \Exception("File not found.");
    }

    $file1 = file_get_contents($pathToFile1);
    $file2 = file_get_contents($pathToFile2);

    if (empty($file1) || empty($file2)) {
        throw new \Exception("File empty.");
    }

    $objFile1 = json_decode($file1, true);
    $objFile2 = json_decode($file2, true);

    foreach ($objFile1 as $key => $value) {
        $value = getStringisBool($value);

        if (array_key_exists($key, $objFile2) && $value === $objFile2[$key]) {
            $result[$key] = "    {$key}: {$value}";
        }

        if (array_key_exists($key, $objFile2) && $value !== $objFile2[$key]) {
            $result[$key] = "  - {$key}: {$value}";
            $result[$key . 0] = "  + {$key}: {$objFile2[$key]}";
        }

        if (!array_key_exists($key, $objFile2)) {
            $result[$key] = "  - {$key}: {$value}";
        }
    }

    foreach ($objFile2 as $key => $value) {
        if (!array_key_exists($key, $objFile1)) {
            $value = getStringisBool($value);
            $result[$key] = "  + {$key}: {$value}";
        }
    }

    ksort($result);
    return "{" . PHP_EOL . implode(PHP_EOL, $result) . PHP_EOL . "}" . PHP_EOL;
}

function getStringisBool(mixed $value): mixed
{
    if (is_bool($value)) {
        return $value ? "true" : "false";
    }
    return $value;
}
