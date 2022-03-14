<?php

namespace Differ\Differ;

use function Differ\Parsers\parser;

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $firstData = parser($pathToFile1);
    $secondData = parser($pathToFile2);
    $data = array_merge_recursive($firstData, $secondData);

    $result = array_reduce(array_keys($data), function ($acc, $key) use ($firstData, $secondData) {

        if (array_key_exists($key, $firstData) && array_key_exists($key, $secondData)) {
            $acc["  {$key}"] = iter($key, $firstData, $secondData);
            return $acc;
        }

        if (array_key_exists($key, $firstData)) {
            $acc["- {$key}"] = iter($key, $firstData, $secondData);
            return $acc;
        }

        $acc["+ {$key}"] = iter($key, $firstData, $secondData);
        return $acc;
    }, []);

    $result = json_encode($result, JSON_PRETTY_PRINT);
    $result = str_replace(['"', ','], '', (string) $result);
    return $result;
}

function iter(string $keyNode, array $firstData, array $secondData): mixed
{
    $fullFirstData = $firstData;
    $fullSecondData = $secondData;
    $firstData = $firstData[$keyNode] ?? false;
    $secondData = $secondData[$keyNode] ?? false;

    if (!$firstData) {
        $firstData = [];
    }

    if (!$secondData) {
        $secondData = [];
    }

    if (!is_array($firstData)) {
        return convertToStringValue($firstData);
    }

    if (!is_array($secondData)) {
        return convertToStringValue($secondData);
    }

    $data = array_merge_recursive($firstData, $secondData);
    ksort($data);

    $result = array_reduce(
        array_keys($data),
        function ($acc, $key) use ($firstData, $secondData, $keyNode, $fullFirstData, $fullSecondData) {
            if (array_key_exists($key, $firstData) && array_key_exists($key, $secondData)) {
                if (!is_array($firstData[$key]) && !is_array($secondData[$key])) {
                    if ($firstData[$key] !== $secondData[$key]) {
                        $acc["- {$key}"] = convertToStringValue($firstData[$key]);
                        $acc["+ {$key}"] = convertToStringValue($secondData[$key]);
                    } else {
                        $acc["  {$key}"] = convertToStringValue($firstData[$key]);
                    }
                    return $acc;
                }

                if (!is_array($firstData[$key])) {
                    $acc["- {$key}"] = iter($key, $firstData, $secondData);
                    $acc["+ {$key}"] = convertToStringValue($secondData[$key]);
                    return $acc;
                }

                if (!is_array($secondData[$key])) {
                    $acc["- {$key}"] = convertToStringValue($firstData[$key]);
                    $acc["+ {$key}"] = iter($key, $firstData, $secondData);
                    return $acc;
                }

                $acc[" {$key}"] = iter($key, $firstData, $secondData);
                return $acc;
            }

            if (array_key_exists($key, $firstData)) {
                if (array_key_exists($keyNode, $fullSecondData)) {
                    $acc["- {$key}"] = iter($key, $firstData, $secondData);
                } else {
                    $acc[" {$key}"] = iter($key, $firstData, $secondData);
                }
                return $acc;
            }

            if (array_key_exists($key, $secondData)) {
                if (array_key_exists($keyNode, $fullFirstData)) {
                    $acc["+ {$key}"] = convertToStringValue($secondData[$key]);
                } else {
                    $acc[" {$key}"] = iter($key, $firstData, $secondData);
                }
                return $acc;
            }
            $acc["+ {$key}"] = iter($key, $firstData, $secondData);
            return $acc;
        },
        []
    );
    return $result;
}

function stylish(array $data): string
{
    $iter = function (string|array $children, array $data, &$iter, int $marker = 2): string {
        if (!is_array($children)) {
            return PHP_EOL . str_repeat(" ", $marker) . "{$children}";
        }

        $newMarker = str_repeat(" ", $marker + 4);
        $result = "";

        foreach ($children as $key => $child) {
            if (is_array($child)) {
                $child = $iter($child, $data, $iter, $marker + 4);
                $result .= PHP_EOL . $newMarker . "{$key} : {" . $child . PHP_EOL . str_repeat(" ", $marker + 6) . "}";
            } else {
                $result .= PHP_EOL . $newMarker . "{$key} : " . "  {$child}";
            }
        }
        return $result;
    };

    $result = array_reduce(array_keys($data), function ($acc, $key) use ($data, $iter) {
        $child = $iter($data[$key], $data, $iter);
        $acc .= "    {$key} : {" . $child . PHP_EOL . "    }" . PHP_EOL;
        return $acc;
    }, "");

    return "{" . PHP_EOL . $result . "}";
}

function convertToStringValue(mixed $value): mixed
{
    if (is_bool($value)) {
        return $value ? "true" : "false";
    }
    if (is_null($value)) {
        return "null";
    }
    return $value;
}
