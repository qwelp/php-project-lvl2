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
            $name = $node['name'];
            $type = $node['type'];
            $children = $node['children'];
            if (is_array($children)) {
                $acc["{$node['type']} {$name}"] = $iter($children, $iter);
                return $acc;
            }
            $acc["{$type} {$name}"] = $children;
            return $acc;
        }, []);
    };

    $result = array_reduce($data, function ($acc, $node) use ($iter) {
        $acc["{$node['type']} {$node['name']}"] = $iter($node['children'], $iter);
        return $acc;
    }, []);
    return json_encode($result, JSON_PRETTY_PRINT);
}

function plain(array $data): string
{
    $iter = function (array $data, array $path, &$iter) {
        $result = "";
        $keyUpdate = [];
        foreach ($data as $node) {
            $name = $node['name'];
            $type = $node['type'];
            $children = $node['children'];

            $pathMain = [...$path, ...[$name]];

            if (count($path)) {
                $newPath = implode(".", $path) . "." . $name;
            } else {
                $newPath = $name;
            }

            $update = array_filter($data, function ($item) use ($name) {
                return $item["name"] === $name;
            });
            $update = array_values($update);

            if (count($update) === 2) {
                if (!in_array($newPath, $keyUpdate)) {
                    $value1 = $update[0]['children'];
                    $value2 = $update[1]['children'];

                    $value1 = is_string($value1) ? "'{$value1}'" : stringToBool($value1);
                    $value2 = is_string($value2) ? "'{$value2}'" : stringToBool($value2);

                    $value1 = is_array($value1) ? '[complex value]' : $value1;
                    $value2 = is_array($value2) ? '[complex value]' : $value2;

                    $result .= "Property '{$newPath}' was updated. From {$value1} to {$value2}" . PHP_EOL;
                }
                $keyUpdate[] = $newPath;
            } elseif (is_array($children)) {
                if (isset($children[0]['object'])) {
                    if ($type == "+") {
                        $result .= "Property '{$newPath}' was added with value: [complex value]" . PHP_EOL;
                    } elseif ($type == "-") {
                        $result .= "Property '{$newPath}' was removed" . PHP_EOL;
                    }
                } else {
                    $result .= $iter($children, $pathMain, $iter);
                }
            } else {
                $value = is_string($node['children']) ? "'{$node['children']}'" : stringToBool($node['children']);

                if ($type == "-") {
                    $result .= "Property '{$newPath}' was removed" . PHP_EOL;
                } elseif ($type == "+") {
                    $result .= "Property '{$newPath}' was added with value: {$value}" . PHP_EOL;
                }
            }
        }
        return $result;
    };

    return trim($iter($data, [], $iter), PHP_EOL);
}

function stylish(array $data): string
{
    $iter = function ($data, &$iter, $tabCount) {
        $result = "";
        $strTab = str_repeat(" ", $tabCount + 2);
        $strTab2 = str_repeat(" ", $tabCount + 4);

        foreach ((array) $data as $node) {
            $name = $node['name'];
            $type = $node['type'];
            $children = $node['children'];

            if (is_array($children)) {
                $result .= PHP_EOL . $strTab . "{$type} {$name}: {"
                        . $iter($children, $iter, $tabCount + 4) . PHP_EOL . $strTab2 . "}";
            } else {
                $result .= PHP_EOL . $strTab . "{$type} {$name}: " . stringToBool($children);
            }
        }
        return $result;
    };

    $result = "";
    $result .= "{" . PHP_EOL;
    foreach ($data as $node) {
        $children = $node["children"];
        if (is_array($children)) {
            $result .= "  {$node["type"]} {$node["name"]}: {" . $iter($children, $iter, 4) . PHP_EOL . "    }";
        } else {
            $result .= "  {$node["type"]} {$node["name"]}: " . $children;
        }
        $result .= PHP_EOL;
    }
    $result .= "}";
    return $result;
}

function createData(object $data1, object $data2): array
{
    $iter = function ($keyNode, $data1, $data2, $iterObject, &$iter) {

        $keys = createKeys($data1, $data2);

        return array_reduce($keys, function ($acc, $key) use ($data1, $data2, $keyNode, $iterObject, $iter) {

            $firstData = $data1->$key ?? null;
            $secondData = $data2->$key ?? null;

            if (is_object($firstData) && is_object($secondData)) {
                $acc[] = ["name" => $key, "type" => " ",
                    "children" => $iter($keyNode, $firstData, $secondData, $iterObject, $iter)];
                return $acc;
            }

            if ($firstData && is_object($firstData)) {
                $dataValue1 = $iterObject($firstData, $iterObject);
            } else {
                $dataValue1 = $firstData;
            }

            if ($secondData && is_object($secondData)) {
                $dataValue2 = $iterObject($secondData, $iterObject);
            } else {
                $dataValue2 = $secondData;
            }

            if (property_exists($data1, $key) && property_exists($data2, $key)) {
                if ($data1->$key === $data2->$key) {
                    $acc[] = ["name" => $key, "type" => " ", "children" => $dataValue1];
                    return $acc;
                }

                $acc[] = ["name" => $key, "type" => "-", "children" => $dataValue1];
                $acc[] = ["name" => $key, "type" => "+", "children" => $dataValue2];
                return $acc;
            }

            if (property_exists($data1, $key)) {
                $acc[] = ["name" => $key, "type" => "-", "children" => $dataValue1];
                return $acc;
            }

            $acc[] = ["name" => $key, "type" => "+", "children" => $dataValue2];
            return $acc;
        }, []);
    };

    $iterObject = function ($secondData, &$iterObject) {

        if (!is_object($secondData)) {
            return [
                "name" => key($secondData),
                "type" => " ",
                "object" => "Y",
                "value" => $secondData
            ];
        }

        $result = [];
        foreach ((array) $secondData as $key => $value) {
            if (is_object($secondData->$key)) {
                $result[] = [
                    "name" => $key,
                    "type" => " ",
                    "object" => "Y",
                    "children" => $iterObject($secondData->$key, $iterObject)
                ];
            } else {
                $result[] = [
                    "name" => $key,
                    "type" => " ",
                    "object" => "Y",
                    "children" => $value
                ];
            }
        }
        return $result;
    };

    return array_reduce(createKeys($data1, $data2), function ($acc, $key) use ($data1, $data2, $iter, $iterObject) {
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
                    "children" => $iter($key, $data1->$key, $data2->$key, $iterObject, $iter)];
            }
            return $acc;
        }

        if (is_null($firstData)) {
            if (is_object($secondData)) {
                $secondData = $iterObject($secondData, $iterObject);
            }
            $acc[] = ["name" => $key, "type" => "+", "children" => $secondData];
            return $acc;
        }

        if (is_object($firstData)) {
            $firstData = $iterObject($firstData, $iterObject);
        }

        $acc[] = ["name" => $key, "type" => "-", "children" => $firstData];
        return $acc;
    }, []);
}

function createKeys(object $data1, object $data2): array
{
    $firstKeys = array_keys((array)$data1);
    $secondKeys = array_keys((array)$data2);
    $keys  = array_unique(array_merge($firstKeys, $secondKeys));
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
