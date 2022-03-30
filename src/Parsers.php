<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parser(string $file): object|array
{
    if (!file_exists($file)) {
        throw new \Exception("File not found.");
    }

    $formatFile = pathinfo($file, PATHINFO_EXTENSION);
    $data = file_get_contents($file);

    if (empty($data)) {
        throw new \Exception("File empty.");
    }

    return match ($formatFile) {
        "json" => json_decode($data),
        "yml" => Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP),
        default => [],
    };
}
