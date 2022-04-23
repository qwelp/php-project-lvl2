<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\stylish;
use function Differ\Formatters\Plain\plainFormatter;
use function Differ\Formatters\Json\jsonFormatter;

function formatData(array $diff, string $format): string
{
    return match ($format) {
        'stylish' => stylish($diff),
        'plain' => plainFormatter($diff),
        'json' => jsonFormatter($diff),
        default => throw new \Exception("uknown format: '{$format}'!"),
    };
}
