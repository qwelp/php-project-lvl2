<?php

namespace Differ\Differ;

use function Differ\Formatters\formatData;
use function Differ\Parsers\getDataParsing;
use function Functional\sort;

function getAbsolutePath(string $path): string
{
    $result = getcwd();
    if ($result === false) {
        throw new \Exception('Unexpected error!');
    }
    return trim($result) . "/{$path}";
}

function isAbsolutePath(string $path): bool
{
    return $path[0] === '/';
}

function getData(string $pathToFile): array
{
    if (!file_exists($pathToFile)) {
        throw new \Exception("invalid path to file!");
    }

    $indexExtension = strrpos($pathToFile, '.');

    if ($indexExtension === false) {
        throw new \Exception("No file extension!");
    }

    $extension = substr($pathToFile, $indexExtension + 1);
    $data = file_get_contents($pathToFile);

    if ($data === false) {
        throw new \Exception("Unexpected error!");
    }

    return ['data' => $data, 'extension' => $extension];
}

function generateAST(object $data1, object $data2): array
{
    $updatedData1 = (array) $data1;
    $updatedData2 = (array) $data2;
    $merged = array_merge($updatedData1, $updatedData2);
    $keys = sort(array_keys($merged), fn ($left, $right) => strcmp($left, $right));

    return array_map(function ($key) use ($updatedData1, $updatedData2) {
        $isKeyExistsData1 = array_key_exists($key, $updatedData1);
        $isKeyExistsData2 = array_key_exists($key, $updatedData2);

        $value1 = $isKeyExistsData1 ? $updatedData1[$key] : '';
        $value2 = $isKeyExistsData2 ? $updatedData2[$key] : '';

        $isObjectValue1 = $isKeyExistsData1 && is_object($value1);
        $isObjectValue2 = $isKeyExistsData2 && is_object($value2);

        if ($isObjectValue1 && $isObjectValue2) {
            return [
                "name" => $key,
                "type" => "node",
                "children" => generateAST($value1, $value2)
            ];
        } else {
            if ($isKeyExistsData1 && !$isKeyExistsData2) {
                return [
                    "name" => $key,
                    "type" => "removed",
                    "value" => $value1
                ];
            } elseif (!$isKeyExistsData1 && $isKeyExistsData2) {
                return [
                    "name" => $key,
                    "type" => "added",
                    "value" => $value2
                ];
            } elseif ($value1 !== $value2) {
                return [
                    "name" => $key,
                    "type" => "changed",
                    "value" => $value1,
                    "value2" => $value2
                ];
            } else {
                return [
                    "name" => $key,
                    "type" => "unchanged",
                    "value" => $value1
                ];
            }
        }
    }, $keys);
}

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    $pathToFile1 = isAbsolutePath($path1) ? $path1 : getAbsolutePath($path1);
    $pathToFile2 = isAbsolutePath($path2) ? $path2 : getAbsolutePath($path2);

    $data1 = getData($pathToFile1);
    $data2 = getData($pathToFile2);

    $data = [getDataParsing($data1['data'], $data1['extension']), getDataParsing($data2['data'], $data2['extension'])];

    $diff = generateAST($data[0], $data[1]);

    return formatData($diff, $format);
}
