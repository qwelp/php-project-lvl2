<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $content, string $format): mixed
{
    if ($format === 'json') {
        return json_decode($content);
    } elseif ($format === 'yaml' || $format === 'yml') {
        return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
    } else {
        throw new \Exception("Invalid format: $format. Data must be in JSON or YAML format");
    }
}
