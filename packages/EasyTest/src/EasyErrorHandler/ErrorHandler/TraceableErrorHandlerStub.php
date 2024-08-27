<?php
declare(strict_types=1);

namespace EonX\EasyTest\EasyErrorHandler\ErrorHandler;

use EonX\EasyErrorHandler\Common\ErrorHandler\FormatAwareInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * This stub is used to preserve all errors during container reset.
 */
final class TraceableErrorHandlerStub implements TraceableErrorHandlerInterface, FormatAwareInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response[]
     */
    private static array $allRenderedErrors = [];

    /**
     * @var \Throwable[]
     */
    private static array $allReportedErrors = [];

    public function __construct(
        private readonly TraceableErrorHandlerInterface $decorated,
    ) {
    }

    public static function getAllRenderedErrors(): array
    {
        return self::$allRenderedErrors;
    }

    public static function getAllReportedErrors(): array
    {
        return self::$allReportedErrors;
    }

    public static function reset(): void
    {
        self::$allRenderedErrors = [];
        self::$allReportedErrors = [];
    }

    public function getBuilders(): array
    {
        return $this->decorated->getBuilders();
    }

    public function getRenderedErrorResponses(): array
    {
        return $this->decorated->getRenderedErrorResponses();
    }

    public function getReportedErrors(): array
    {
        return $this->decorated->getReportedErrors();
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

        self::$allRenderedErrors[] = $renderedError;

        return $renderedError;
    }

    public function report(Throwable $throwable): void
    {
        self::$allReportedErrors[] = $throwable;

        $this->decorated->report($throwable);
    }

    public function supportsFormat(Request $request): bool
    {
        if ($this->decorated instanceof FormatAwareInterface) {
            return $this->decorated->supportsFormat($request);
        }

        return false;
    }
}
