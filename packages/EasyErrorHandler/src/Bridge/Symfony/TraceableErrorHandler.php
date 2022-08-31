<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\TraceableErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class TraceableErrorHandler implements TraceableErrorHandlerInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response[]
     */
    private array $renderedErrors = [];

    /**
     * @var \Throwable[]
     */
    private array $reportedErrors = [];

    public function __construct(private readonly ErrorHandlerInterface $decorated)
    {
    }

    public function getBuilders(): array
    {
        return $this->decorated->getBuilders();
    }

    public function getRenderedErrorResponses(): array
    {
        return $this->renderedErrors;
    }

    public function getReportedErrors(): array
    {
        return $this->reportedErrors;
    }

    public function getReporters(): array
    {
        return $this->decorated->getReporters();
    }

    public function isVerbose(): bool
    {
        return $this->decorated->isVerbose();
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        $renderedError = $this->decorated->render($request, $throwable);

        $this->renderedErrors[] = $renderedError;

        return $renderedError;
    }

    public function report(Throwable $throwable): void
    {
        $this->reportedErrors[] = $throwable;

        $this->decorated->report($throwable);
    }
}
