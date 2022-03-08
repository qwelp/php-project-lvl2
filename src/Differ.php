<?php

namespace Differ\Differ;

function genDiff(string $pathToFile1, string $pathToFile2): string
{


    if (!file_exists($pathToFile1) || !file_exists($pathToFile2)) {
        throw new \Exception("File not found.");
    }

    $file1 = file_get_contents($pathToFile1);
    $file2 = file_get_contents($pathToFile2);

    if (empty($file1) || empty($file2)) {
        throw new \Exception("File empty.");
    }

    $firstFile = json_decode($file1, true);
    $secondFile = json_decode($file2, true);
    return render($firstFile, $secondFile);
}

function render(array $firstFile, array $secondFile): string
{
    $result =  [];
    $data = array_merge($firstFile, $secondFile);

    foreach ($data as $key => $value) {
        $firstFileValue = stringToBool($firstFile[$key]);
        $secondFileValue = stringToBool($secondFile[$key]);

        if (array_key_exists($key, $firstFile) && array_key_exists($key, $secondFile)) {
            if ($firstFile[$key] === $secondFile[$key]) {
                $result[$key] = "    {$key}: " . $firstFileValue;
            } else {
                $result[$key] = "  - {$key}: " . $firstFileValue;
                $result[$key . 0] = "  + {$key}: " . $secondFileValue;
            }
            continue;
        }

        if (array_key_exists($key, $firstFile)) {
            $result[$key] = "  - {$key}: " . $firstFileValue;
            continue;
        }
        $result[$key] = "  + {$key}: " . $secondFileValue;
    }
    ksort($result);
    return "{" . PHP_EOL . implode(PHP_EOL, $result) . PHP_EOL . "}" . PHP_EOL;
}

function stringToBool(mixed $value): mixed
{
    if (is_bool($value)) {
        return $value ? "true" : "false";
    }
    return $value;
}
