<?php

namespace Differ\Formatters\Plain;

function getNormalizeValue(mixed $value): mixed
{
    if (is_object($value)) {
        return '[complex value]';
    }

    if (is_string($value)) {
        return "'{$value}'";
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return "null";
    }

    $json = json_encode($value);
    if (is_string($json)) {
        return json_encode($value);
    }
    return "0";
}

function plainFormatter(array $tree): string
{
    $iter = function (array $tree, string $acc) use (&$iter) {
        $lines = array_map(function ($node) use ($iter, $acc) {

            $key = $node['name'];
            $property = $acc === '' ? "{$key}" : "{$acc}.{$key}";
            $type = $node['type'];

            if ($type === 'node') {
                return $iter($node['children'], $property);
            }

            $template = "Property '{$property}' was";

            $value1 = $node['value'];
            $value2 = $type === 'changed' ? $node['value2'] : '';

            $updatedValue1 = getNormalizeValue($value1);
            $updatedValue2 = getNormalizeValue($value2);

            if ($type === 'added') {
                return "{$template} added with value: {$updatedValue1}";
            }
            if ($type === 'removed') {
                return "{$template} removed";
            }
            if ($type === 'changed') {
                return "{$template} updated. From {$updatedValue1} to {$updatedValue2}";
            }
            return '';
        },
        $tree);

        $filtered = array_filter($lines, fn($line) => $line !== '');
        return implode("\n", $filtered);
    };
    return $iter($tree, '');
}
