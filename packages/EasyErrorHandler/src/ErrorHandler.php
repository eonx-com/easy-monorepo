<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler;

use EonX\EasyErrorHandler\Exceptions\RetryableException;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\FormatAwareInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Response\Data\ErrorResponseData;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Throwable;

final class ErrorHandler implements ErrorHandlerInterface, FormatAwareInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[]
     */
    private array $builders;

    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptionsForReport;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface[]
     */
    private array $reporters;

    private bool $reportRetryableExceptionAttempts;

    /**
     * @param class-string[]|null $ignoredExceptionsForReport
     */
    public function __construct(
        private readonly ErrorResponseFactoryInterface $errorResponseFactory,
        iterable $builderProviders,
        iterable $reporterProviders,
        private readonly VerboseStrategyInterface $verboseStrategy,
        ?array $ignoredExceptionsForReport = null,
        ?bool $reportRetryableExceptionAttempts = null,
    ) {
        $this->setBuilders($builderProviders);
        $this->setReporters($reporterProviders);
        $this->ignoredExceptionsForReport = $ignoredExceptionsForReport ?? [];
        $this->reportRetryableExceptionAttempts = $reportRetryableExceptionAttempts ?? false;
    }

    /**
     * @return \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array
    {
        return $this->builders;
    }

    /**
     * @return \EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface[]
     */
    public function getReporters(): array
    {
        return $this->reporters;
    }

    public function isVerbose(): bool
    {
        return $this->verboseStrategy->isVerbose();
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        $this->verboseStrategy->setThrowable($throwable, $request);

        foreach ($this->builders as $builder) {
            $data = $builder->buildData($throwable, $data ?? []);
            $headers = $builder->buildHeaders($throwable, $headers ?? null);
            $statusCode = $builder->buildStatusCode($throwable, $statusCode ?? null);
        }

        return $this->errorResponseFactory->create(
            $request,
            ErrorResponseData::create($this->sortRecursive($data ?? []), $statusCode ?? null, $headers ?? null)
        );
    }

    public function report(Throwable $throwable): void
    {
        if ($throwable instanceof RetryableException) {
            if ($throwable->willRetry() && $this->reportRetryableExceptionAttempts === false) {
                return;
            }

            $throwable = $throwable->getPrevious();
        }

        // Symfony Messenger HandlerFailedException
        if (\class_exists(HandlerFailedException::class) && $throwable instanceof HandlerFailedException) {
            foreach ($throwable->getNestedExceptions() as $nestedThrowable) {
                $this->report($nestedThrowable);
            }

            return;
        }

        // Symfony Messenger UnrecoverableMessageHandlingException
        if (\class_exists(UnrecoverableMessageHandlingException::class)
            && $throwable instanceof UnrecoverableMessageHandlingException
            && $throwable->getPrevious() instanceof Throwable
        ) {
            $throwable = $throwable->getPrevious();
        }

        foreach ($this->ignoredExceptionsForReport as $class) {
            if (\is_a($throwable, $class)) {
                return;
            }
        }

        $this->verboseStrategy->setThrowable($throwable);

        foreach ($this->reporters as $reporter) {
            $reporter->report($throwable);
        }
    }

    public function supportsFormat(Request $request): bool
    {
        // Ultimately the response factory should make the decision
        if ($this->errorResponseFactory instanceof FormatAwareInterface) {
            return $this->errorResponseFactory->supportsFormat($request);
        }

        // Otherwise, assume it supports every format
        return true;
    }

    /**
     * @param class-string $class
     */
    private function filterIterable(iterable $items, string $class): array
    {
        $items = CollectorHelper::filterByClassAsArray($items, $class);

        foreach ($items as $item) {
            if ($item instanceof ErrorHandlerAwareInterface) {
                $item->setErrorHandler($this);
            }
        }

        return $items;
    }

    private function setBuilders(iterable $builderProviders): void
    {
        /** @var \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface[] $providers */
        $providers = $this->filterIterable($builderProviders, ErrorResponseBuilderProviderInterface::class);
        $builders = [];

        foreach ($providers as $provider) {
            $providerBuilders = $this->filterIterable($provider->getBuilders(), ErrorResponseBuilderInterface::class);

            foreach ($providerBuilders as $builder) {
                $builders[] = $builder;
            }
        }

        $this->builders = CollectorHelper::orderLowerPriorityFirstAsArray($builders);
    }

    private function setReporters(iterable $reporterProviders): void
    {
        /** @var \EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface[] $providers */
        $providers = $this->filterIterable($reporterProviders, ErrorReporterProviderInterface::class);
        $reporters = [];

        foreach ($providers as $provider) {
            $providerReporters = $this->filterIterable($provider->getReporters(), ErrorReporterInterface::class);

            foreach ($providerReporters as $reporter) {
                $reporters[] = $reporter;
            }
        }

        $this->reporters = CollectorHelper::orderLowerPriorityFirstAsArray($reporters);
    }

    private function sortRecursive(mixed $items): mixed
    {
        if (\is_array($items) === false) {
            return $items;
        }

        \ksort($items);

        foreach ($items as $key => $value) {
            $items[$key] = $this->sortRecursive($value);
        }

        return $items;
    }
}
