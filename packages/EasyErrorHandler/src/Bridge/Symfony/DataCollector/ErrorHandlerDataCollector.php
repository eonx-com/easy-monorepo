<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DataCollector;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\TraceableErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class ErrorHandlerDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'error_handler.error_handler_collector';

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null): void
    {
        if (($this->errorHandler instanceof TraceableErrorHandlerInterface) === false) {
            return;
        }

        $this->data['exception'] = $this->errorHandler->getReportedException();
    }

    public function getReportedException(): ?Throwable
    {
        return $this->data['exception'] ?? null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
