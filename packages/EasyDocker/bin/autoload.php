<?php

declare(strict_types=1);

$paths = [
    // after split package
    __DIR__ . '/../vendor',
    // dependency
    __DIR__ . '/../../..',
    // monorepo
    __DIR__ . '/../../../vendor',
    __DIR__ . '/../../../../../../vendor',
];

foreach ($paths as $possiblePath) {
    if (\is_file($possiblePath . '/autoload.php')) {
        require_once $possiblePath . '/autoload.php';

        break;
    }
}
