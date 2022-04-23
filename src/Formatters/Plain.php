<?php

namespace Differ\Formatters\Plain;

function getNormalizeValue(mixed $value): string
{
    if (is_object($value)) {
        return '[complex value]';
    } elseif (is_string($value)) {
        return "'{$value}'";
    } elseif (is_bool($value)) {
        return $value ? 'true' : 'false';
    } elseif (is_null($value)) {
        return "null";
    } else {
        return json_encode($value) ? json_encode($value) : "0";
    }
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
            } else {
                $template = "Property '{$property}' was";

                $value1 = $node['value'];
                $value2 = $type === 'changed' ? $node['value2'] : '';

                $updatedValue1 = getNormalizeValue($value1);
                $updatedValue2 = getNormalizeValue($value2);

                if ($type === 'added') {
                    return "{$template} added with value: {$updatedValue1}";
                } elseif ($type === 'removed') {
                    return "{$template} removed";
                } elseif ($type === 'changed') {
                    return "{$template} updated. From {$updatedValue1} to {$updatedValue2}";
                } else {
                    return '';
                }
            }
        },
        $tree);

        $filtered = array_filter($lines, fn($line) => $line !== '');
        return implode("\n", $filtered);
    };

    return $iter($tree, '');
}
