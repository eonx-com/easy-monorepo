<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stub\Resolver;

use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use Throwable;

final class ErrorDetailsResolverStub implements ErrorDetailsResolverInterface
{
    private int $resetCount = 0;

    public function getResetCount(): int
    {
        return $this->resetCount;
    }

    public function reset(): void
    {
        $this->resetCount++;
    }

    public function resolveExtendedDetails(Throwable $throwable, ?int $maxDepth = null): array
    {
        return [];
    }

    public function resolveInternalMessage(Throwable $throwable): string
    {
        return $throwable->getMessage();
    }

    public function resolveSimpleDetails(Throwable $throwable, ?bool $withTrace = null): array
    {
        return [];
    }
}
