<?php
declare(strict_types=1);

/**
 * PHPStan-only stub of the ddtrace extension symbols used in the packages.
 * It is never executed, it is registered through the "scanFiles" option of phpstan.neon.
 */

namespace DDTrace;

class SpanData
{
    /**
     * @var array<string, string>
     */
    public array $meta = [];

    /**
     * @var array<string, float|int>
     */
    public array $metrics = [];
}

function root_span(): ?SpanData
{
    return null;
}
