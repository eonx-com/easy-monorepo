<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\ErrorHandler\ErrorHandler;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ErrorHandlerStub implements ErrorHandlerInterface
{
    /**
     * @var \Throwable[]
     */
    private array $reportedThrowables = [];

    public function getBuilders(): array
    {
        return [];
    }

    public function getReportedThrowables(): array
    {
        return $this->reportedThrowables;
    }

    public function getReporters(): array
    {
        return [];
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        return new Response();
    }

    public function report(Throwable $throwable): void
    {
        $this->reportedThrowables[] = $throwable;
    }
}
