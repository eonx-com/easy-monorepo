<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface ImplementationsInterface
{
    /**
     * @var string[]
     */
    public const IMPLEMENTATIONS = [
        self::IMPLEMENTATION_DOCTRINE,
    ];

    /**
     * @var string
     */
    public const IMPLEMENTATION_DOCTRINE = 'doctrine';
}
