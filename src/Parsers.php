<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parser(string $file): object
{
    if (!file_exists($file)) {
        throw new \Exception("File not found.");
    }

    $formatFile = pathinfo($file, PATHINFO_EXTENSION);
    $data = file_get_contents($file);

    if (empty($data)) {
        throw new \Exception("File empty.");
    }

    if ($formatFile === "yml") {
        return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
    }
    return json_decode($data);
}
