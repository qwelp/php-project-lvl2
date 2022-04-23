<?php

namespace Differ\Formatters\Json;

function jsonFormatter(array $tree): string
{
    $iter = function (array $tree) use (&$iter) {
        $lines = array_map(function ($node) use ($iter) {
            $key = $node['name'];
            $type = $node['type'];

            if ($type === 'node') {
                return "\"{$key}\":{$iter($node['children'])}";
            } else {
                $value1 = $node['value'];
                $value2 = $type === 'changed' ? $node['value2'] : '';

                $updatedValue1 = json_encode($value1);
                $updatedValue2 = json_encode($value2);

                if ($type === 'added') {
                    return "\"{$key}\":{\"after\":{$updatedValue1}}";
                } elseif ($type === 'removed') {
                    return "\"{$key}\":{\"before\":{$updatedValue1}}";
                } elseif ($type === 'changed') {
                    return "\"$key\":{\"before\":{$updatedValue1},\"after\":{$updatedValue2}}";
                } else {
                    return "\"$key\":{\"before\":{$updatedValue1},\"after\":{$updatedValue1}}";
                }
            }
        },
        $tree);

        return "{" . implode(',', $lines) . "}";
    };

    return $iter($tree);
}
