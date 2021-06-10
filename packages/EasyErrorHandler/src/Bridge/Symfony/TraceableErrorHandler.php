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
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $decorated;

    /**
     * @var null|\Throwable
     */
    private $reportedException = null;

    public function __construct(ErrorHandlerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function isVerbose(): bool
    {
        return $this->decorated->isVerbose();
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        return $this->decorated->render($request, $throwable);
    }

    public function report(Throwable $throwable): void
    {
        $this->reportedException = $throwable;

        $this->decorated->report($throwable);
    }

    public function getReportedException(): ?Throwable
    {
        return $this->reportedException;
    }
}
