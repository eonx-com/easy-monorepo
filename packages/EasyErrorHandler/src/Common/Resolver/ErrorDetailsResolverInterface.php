<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Resolver;

use Throwable;

interface ErrorDetailsResolverInterface
{
    public const DEFAULT_MAX_DEPTH = 10;

    public function reset(): void;

    public function resolveExtendedDetails(Throwable $throwable, ?int $maxDepth = null): array;

    public function resolveInternalMessage(Throwable $throwable): string;

    public function resolveSimpleDetails(Throwable $throwable, ?bool $withTrace = null): array;
}
