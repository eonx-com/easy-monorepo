<?php
declare(strict_types=1);

/**
 * Runtime stand-in of the ddtrace extension symbols used by RootSpanHelper.
 * Only require it in tests running in a separate process, as functions cannot be undeclared.
 * PHPStan also discovers these symbols here, as the extension is not loaded during the analysis.
 */

namespace DDTrace;

final class SpanData
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
    static $rootSpan = null;

    return $rootSpan ??= new SpanData();
}
