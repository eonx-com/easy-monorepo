<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DataCollector;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\TraceableErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class ErrorHandlerDataCollector extends DataCollector
{
    public const NAME = 'error_handler.error_handler_collector';

    public function __construct(
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (($this->errorHandler instanceof TraceableErrorHandlerInterface) === false) {
            return;
        }

        foreach ($this->errorHandler->getBuilders() as $builder) {
            $class = $builder::class;

            $this->data['builders'][$class] = [
                'class' => $class,
                'priority' => $builder->getPriority(),
            ];
        }

        foreach ($this->errorHandler->getReporters() as $reporter) {
            $class = $reporter::class;

            $this->data['reporters'][$class] = [
                'class' => $class,
                'priority' => $reporter->getPriority(),
            ];
        }

        foreach ($this->errorHandler->getReportedErrors() as $reportedError) {
            $class = $reportedError::class;

            $reportedErrorData = [
                'class' => $class,
                'message' => $reportedError->getMessage(),
            ];

            if ($reportedError instanceof TranslatableExceptionInterface) {
                $reportedErrorData['messageParams'] = $reportedError->getMessageParams();
                $reportedErrorData['userMessage'] = $reportedError->getUserMessage();
                $reportedErrorData['userMessageParams'] = $reportedError->getUserMessageParams();
            }

            $this->data['reported_errors'][$class] = $reportedErrorData;
        }

        $this->data['rendered_error_responses'] = $this->errorHandler->getRenderedErrorResponses();
    }

    /**
     * @return \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array
    {
        return $this->data['builders'] ?? [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response[]
     */
    public function getRenderedErrorResponses(): array
    {
        return $this->data['rendered_error_responses'] ?? [];
    }

    /**
     * @return \Throwable[]
     */
    public function getReportedErrors(): array
    {
        return $this->data['reported_errors'] ?? [];
    }

    /**
     * @return \EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface[]
     */
    public function getReporters(): array
    {
        return $this->data['reporters'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
