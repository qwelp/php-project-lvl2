<?php

namespace Differ\Differ;

use function Differ\Parsers\parser;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    return render(parser($pathToFile1), parser($pathToFile2));
}

function render(array $firstFile, array $secondFile): string
{
    $data = array_merge($firstFile, $secondFile);
    $result = array_reduce(array_keys($data), function ($acc, $key) use ($firstFile, $secondFile) {
        if (array_key_exists($key, $firstFile) && array_key_exists($key, $secondFile)) {
            if ($firstFile[$key] === $secondFile[$key]) {
                $acc[$key] = "    {$key}: " . stringToBool($firstFile[$key]);
            } else {
                $acc[$key] = "  - {$key}: " . stringToBool($firstFile[$key]);
                $acc[$key . 0] = "  + {$key}: " . stringToBool($secondFile[$key]);
            }
            return $acc;
        }

        if (array_key_exists($key, $firstFile)) {
            $acc[$key] = "  - {$key}: " . stringToBool($firstFile[$key]);
            return $acc;
        }
        $acc[$key] = "  + {$key}: " . stringToBool($secondFile[$key]);
        return $acc;
    }, []);

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
