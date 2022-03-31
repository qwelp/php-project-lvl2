<?php

namespace Differ\Differ;

use function Differ\Parsers\parser;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = ""): string
{
    $firstData = parser($pathToFile1);
    $secondData = parser($pathToFile2);
    $data = createData($firstData, $secondData);
    if ($format === "plain") {
        return plain($data);
    }
    if ($format === "json") {
        return json($data);
    }
    return stylish($data);
}

function json(array $data): mixed
{
    $iter = function ($data, &$iter) {
        return array_reduce($data, function ($acc, $node) use ($iter) {
            ["name" => $name, "type" => $type, "children" => $children] = $node;
            if (is_array($children)) {
                $acc["{$node['type']} {$name}"] = $iter($children, $iter);
                return $acc;
            }
            $acc["{$type} {$name}"] = $children;
            return $acc;
        }, []);
    };

    $resultData = [];
    array_map(function ($node) use ($iter, &$resultData) {
        $resultData["{$node['type']} {$node['name']}"] = $iter($node['children'], $iter);
    }, $data);
    return json_encode($resultData, JSON_PRETTY_PRINT);
}

function plainIter(array $data, array $path): string
{
    $keyUpdate = [];
    return array_reduce($data, function ($acc, $node) use ($data, $path, &$keyUpdate) {
        ["name" => $name, "type" => $type, "children" => $children] = $node;
        $pathMain = [...$path, ...[$name]];
        $newPath = count($path) ? implode(".", $path) . "." . $name : $name;
        $update = array_values(array_filter($data, function ($item) use ($name) {
            return $item["name"] === $name;
        }));
        if (count($update) === 2) {
            if (!in_array($newPath, $keyUpdate)) {
                $value1 = $update[0]['children'];
                $value2 = $update[1]['children'];
                $value1 = is_string($value1) ? "'{$value1}'" : stringToBool($value1);
                $value2 = is_string($value2) ? "'{$value2}'" : stringToBool($value2);
                $value1 = is_array($value1) ? '[complex value]' : $value1;
                $value2 = is_array($value2) ? '[complex value]' : $value2;
                $acc .= "Property '{$newPath}' was updated. From {$value1} to {$value2}" . PHP_EOL;
            }
            $keyUpdate[] = $newPath;
            return $acc;
        }
        if (is_array($children)) {
            if (isset($children[0]['object'])) {
                if ($type == "+") {
                    $acc .= "Property '{$newPath}' was added with value: [complex value]" . PHP_EOL;
                } elseif ($type == "-") {
                    $acc .= "Property '{$newPath}' was removed" . PHP_EOL;
                }
            } else {
                $acc .= plainIter($children, $pathMain);
            }
            return $acc;
        }
        $value = is_string($children) ? "'{$children}'" : stringToBool($children);
        if ($type == "-") {
            $acc .= "Property '{$newPath}' was removed" . PHP_EOL;
        } elseif ($type == "+") {
            $acc .= "Property '{$newPath}' was added with value: {$value}" . PHP_EOL;
        }
        return $acc;
    }, "");
}

function plain(array $data): string
{
    return trim(plainIter($data, []), PHP_EOL);
}

function stylishIter(array $data, int $tabCount = 0): string
{
    $result = "";
    $strTab = str_repeat(" ", $tabCount + 2);
    $strTab2 = str_repeat(" ", $tabCount + 4);
    foreach ($data as $node) {
        ["name" => $name, "type" => $type, "children" => $children] = $node;
        if (is_array($children)) {
            $result .= PHP_EOL . $strTab . "{$type} {$name}: {"
                . stylishIter($children, $tabCount + 4) . PHP_EOL . $strTab2 . "}";
        } else {
            $result .= PHP_EOL . $strTab . "{$type} {$name}: " . stringToBool($children);
        }
    }
    return $result;
}

function stylish(array $data): string
{
    $result = "{" . PHP_EOL;
    foreach ($data as $node) {
        $children = $node["children"];
        if (is_array($children)) {
            $result .= "  {$node["type"]} {$node["name"]}: {" . stylishIter($children, 4) . PHP_EOL . "    }";
        } else {
            $result .= "  {$node["type"]} {$node["name"]}: " . $children;
        }
        $result .= PHP_EOL;
    }
    return $result . "}";
}

function iterObject(mixed $secondData): array
{
    if (!is_object($secondData)) {
        return ["name" => key($secondData), "type" => " ", "object" => "Y", "value" => $secondData];
    }
    $data = (array) $secondData;
    return array_reduce(array_keys($data), function ($acc, $key) use ($secondData) {
        if (is_object($secondData->$key)) {
            $acc[] = ["name" => $key, "type" => " ", "object" => "Y", "children" => iterObject($secondData->$key)];
            return $acc;
        }
        $acc[] = ["name" => $key, "type" => " ", "object" => "Y", "children" => $secondData->$key];
        return $acc;
    }, []);
}

function createDataIter(string $keyNode, mixed $data1, mixed $data2): array
{
    return array_reduce(createKeys($data1, $data2), function ($acc, $key) use ($data1, $data2, $keyNode) {
        $firstData = $data1->$key ?? null;
        $secondData = $data2->$key ?? null;
        if (is_object($firstData) && is_object($secondData)) {
            $acc[] = ["name" => $key, "type" => " ", "children" => createDataIter($keyNode, $firstData, $secondData)];
            return $acc;
        }
        $dataValue1 = $firstData && is_object($firstData) ? iterObject($firstData) : $firstData;
        $dataValue2 = $secondData && is_object($secondData) ? iterObject($secondData) : $secondData;
        if (property_exists($data1, $key) && property_exists($data2, $key)) {
            if ($data1->$key === $data2->$key) {
                $acc[] = ["name" => $key, "type" => " ", "children" => $dataValue1];
                return $acc;
            }
            $acc[] = ["name" => $key, "type" => "-", "children" => $dataValue1];
            $acc[] = ["name" => $key, "type" => "+", "children" => $dataValue2];
            return $acc;
        } elseif (property_exists($data1, $key)) {
            $acc[] = ["name" => $key, "type" => "-", "children" => $dataValue1];
            return $acc;
        }
        $acc[] = ["name" => $key, "type" => "+", "children" => $dataValue2];
        return $acc;
    }, []);
}

function createData(mixed $data1, mixed $data2): array
{
    return array_reduce(createKeys($data1, $data2), function ($acc, $key) use ($data1, $data2) {
        $firstData = $data1->$key ?? null;
        $secondData = $data2->$key ?? null;
        if (!is_object($firstData) && !is_object($secondData)) {
            if ($firstData === $secondData) {
                $acc[] = ["name" => $key, "type" => " ", "children" => stringToBool($firstData)];
                return $acc;
            }
            if (isset($firstData) && isset($secondData)) {
                $acc[] = ["name" => $key, "type" => "-", "children" => stringToBool($firstData)];
                $acc[] = ["name" => $key, "type" => "+", "children" => stringToBool($secondData)];
                return $acc;
            }
            if (isset($firstData)) {
                $acc[] = ["name" => $key, "type" => "-", "children" => stringToBool($firstData)];
                return $acc;
            }
            $acc[] = ["name" => $key, "type" => "+", "children" => stringToBool($secondData)];
            return $acc;
        }
        if (!is_null($firstData)  && !is_null($secondData)) {
            if (gettype($firstData) === "object" && gettype($secondData) === "object") {
                $acc[] = ["name" => $key, "type" => " ",
                    "children" => createDataIter($key, $data1->$key, $data2->$key)];
            }
            return $acc;
        }
        if (is_null($firstData)) {
            if (is_object($secondData)) {
                $secondData = iterObject($secondData);
            }
            $acc[] = ["name" => $key, "type" => "+", "children" => $secondData];
            return $acc;
        }
        if (is_object($firstData)) {
            $firstData = iterObject($firstData);
        }
        $acc[] = ["name" => $key, "type" => "-", "children" => $firstData];
        return $acc;
    }, []);
}

function createKeys(mixed $data1, mixed $data2): array
{
    $keys  = array_unique(array_merge(array_keys((array)$data1), array_keys((array)$data2)));
    sort($keys);
    return $keys;
}

function stringToBool(mixed $value): mixed
{
    if (is_bool($value)) {
        return $value ? "true" : "false";
    }
    if (is_null($value)) {
        return "null";
    }
    return $value;
}
