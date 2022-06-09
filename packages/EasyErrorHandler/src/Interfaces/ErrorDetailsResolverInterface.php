<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorDetailsResolverInterface
{
    /**
     * @var int
     */
    public const DEFAULT_MAX_DEPTH = 10;

    public function reset(): void;

    /**
     * @return mixed[]
     */
    public function resolveExtendedDetails(\Throwable $throwable, ?int $maxDepth = null): array;

    public function resolveInternalMessage(\Throwable $throwable): string;

    /**
     * @return mixed[]
     */
    public function resolveSimpleDetails(\Throwable $throwable, ?bool $withTrace = null): array;
}
