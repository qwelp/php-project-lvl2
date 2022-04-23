<?php

namespace Differ\Formatters\Stylish;

function stylish(array $tree, string $replacer = ' ', int $spaceCount = 4, int $startIndentSize = 2): string
{

    $iter = function ($tree, $indentSize, $innerIter) use (&$iter, $replacer, $spaceCount) {

        if (!is_array($tree)) {
            $value = json_encode($tree) === false ? '' : json_encode($tree);
            return trim($value, "\"");
        }

        $currentIndent = str_repeat($replacer, $indentSize);
        $bracketIndent = str_repeat($replacer, $indentSize - $spaceCount / 2);
        $newIndentSize = $indentSize + $spaceCount;

        if ($innerIter) {
            $data = array_map(
                function ($key, $value) use (&$iter, $currentIndent, $newIndentSize, $innerIter) {
                    $normalizeValue = is_object($value) ? (array) $value : $value;
                    return "{$currentIndent}  {$key}: {$iter($normalizeValue, $newIndentSize, $innerIter)}";
                },
                array_keys($tree),
                $tree
            );
        } else {
            $data = array_map(
                function ($node) use (
                    $iter,
                    $currentIndent,
                    $newIndentSize,
                    $innerIter
                ) {
                    $key = $node['name'];
                    $type = $node['type'];

                    if ($type === 'node') {
                        return "{$currentIndent}  {$key}: {$iter($node['children'], $newIndentSize, $innerIter)}";
                    } else {
                        $value1 = $node['value'];
                        $normalizeBefore = is_object($value1) ? (array) $value1 : $value1;
                        $before = $iter($normalizeBefore, $newIndentSize, true);

                        $value2 = $type === 'changed' ? $node['value2'] : '';
                        $normalizeAfter = is_object($value2) ? (array) $value2 : $value2;
                        $after = $iter($normalizeAfter, $newIndentSize, true);

                        if ($type === 'added') {
                            return "{$currentIndent}+ {$key}: {$before}";
                        } elseif ($type === 'removed') {
                            return "{$currentIndent}- {$key}: {$before}";
                        } elseif ($type === 'changed') {
                            return "{$currentIndent}- {$key}: {$before}\n{$currentIndent}+ {$key}: {$after}";
                        } else {
                            return "{$currentIndent}  {$key}: {$before}";
                        }
                    }
                },
                $tree
            );
        }

        $result = ["{", ...$data, "{$bracketIndent}}"];

        return implode("\n", $result);
    };

    return $iter($tree, $startIndentSize, false);
}
