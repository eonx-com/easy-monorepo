<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface ImplementationsInterface
{
    /**
     * @var string[]
     */
    public const IMPLEMENTATIONS = [self::IMPLEMENTATION_DOCTRINE];

    /**
     * @var string
     */
    public const IMPLEMENTATION_DOCTRINE = 'doctrine';
}
