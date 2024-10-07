<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\DataCollector;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyUtils\Common\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ErrorHandlerDataCollector extends AbstractDataCollector
{
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
     * @return \EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array
    {
        return $this->data['builders'] ?? [];
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
     * @return \EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface[]
     */
    public function getReporters(): array
    {
        return $this->data['reporters'] ?? [];
    }
}
