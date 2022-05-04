<?php

namespace Differ\Formatters\Json;

function jsonFormatter(array $tree): string
{
    return json_encode($tree, JSON_THROW_ON_ERROR);
}
