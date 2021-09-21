<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface AlgorithmsInterface
{
    /**
     * @var string[]
     */
    public const ALL = [
        self::HS256,
        self::RS256,
    ];

    /**
     * @var string
     */
    public const HS256 = 'HS256';

    /**
     * @var string
     */
    public const RS256 = 'RS256';
}
