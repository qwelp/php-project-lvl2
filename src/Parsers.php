<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getDataParsing(string $data, string $extension): object
{
    return match ($extension) {
        'yml', 'yaml' => Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP),
        'json' => json_decode($data),
        default => throw new \Exception("uknown extension: '{$extension}'!"),
    };
}
