<?php

declare(strict_types=1);

$ext1 = 'no-matter';

(static function (
    int $param1,
    array $param2,
    ?array $param3 = null,
    ?int $param4 = null,
    $param5 = null,
    ?stdClass $class1 = null,
    ?\stdClass $class2 = null
) use ($ext1) {
    // No body needed
})->call(new \stdClass());
