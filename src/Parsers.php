<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parser(string $file): mixed
{
    if (!file_exists($file)) {
        throw new \Exception("File not found.");
    }

    $data = file_get_contents($file);

    if (empty($data)) {
        throw new \Exception("File empty.");
    }

    $formatFile = pathinfo($file, PATHINFO_EXTENSION);

    return match ($formatFile) {
        'yml', 'yaml' => Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP),
        'json' => json_decode($data),
        default => throw new \Exception("uknown extension: '{$formatFile}'!"),
    };
}
