<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface AlgorithmsInterface
{
    public const ALL = [
        self::HS256,
        self::RS256,
    ];

    public const HS256 = 'HS256';

    public const RS256 = 'RS256';
}
