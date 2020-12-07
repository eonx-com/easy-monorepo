<?php

declare(strict_types=1);

$ext1 = 'no-matter';

(static function (int $param1, $param2 = null, ?stdClass $class = null) use ($ext1) {
    // No body needed
})->call(new \stdClass());
