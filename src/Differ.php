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

    $data = array_merge($objFile1, $objFile2);

    foreach ($data as $key => $value) {
        if (array_key_exists($key, $objFile1) && array_key_exists($key, $objFile2)) {
            if ($objFile1[$key] === $objFile2[$key]) {
                $value = getStringisBool($objFile1[$key]);
                $result[$key] = "    {$key}: {$value}";
                continue;
            }

            $result[$key] = "  - {$key}: " . getStringisBool($objFile1[$key]);
            $result[$key . 0] = "  + {$key}: " . getStringisBool($objFile2[$key]);
            continue;
        }

        if (array_key_exists($key, $objFile1)) {
            $result[$key] = "  - {$key}: " . getStringisBool($objFile1[$key]);
            continue;
        }
        $result[$key] = "  + {$key}: " . getStringisBool($objFile2[$key]);
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
