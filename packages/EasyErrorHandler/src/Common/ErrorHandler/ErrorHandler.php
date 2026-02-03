<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

use EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface;
use EonX\EasyErrorHandler\Common\Provider\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface;
use EonX\EasyErrorHandler\Common\Strategy\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseData;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use SplObjectStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Throwable;

final class ErrorHandler implements ErrorHandlerInterface, FormatAwareInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface[]
     */
    private array $builders;

    /**
     * @var \SplObjectStorage<\Throwable, null>
     */
    private readonly SplObjectStorage $handledExceptions;

    /**
     * @var class-string[]
     */
    private readonly array $ignoredExceptionsForReport;

    private readonly bool $reportRetryableExceptionAttempts;

    /**
     * @var \EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface[]
     */
    private array $reporters;

    private readonly bool $skipReportedExceptions;

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
        ?bool $skipReportedExceptions = null,
    ) {
        $this->setBuilders($builderProviders);
        $this->setReporters($reporterProviders);
        $this->ignoredExceptionsForReport = $ignoredExceptionsForReport ?? [];
        $this->reportRetryableExceptionAttempts = $reportRetryableExceptionAttempts ?? false;
        $this->skipReportedExceptions = $skipReportedExceptions ?? false;
        $this->handledExceptions = new SplObjectStorage();
    }

    /**
     * @return \EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array
    {
        return $this->builders;
    }

    /**
     * @return \EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface[]
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
        if ($this->skipReportedExceptions && $this->handledExceptions->contains($throwable)) {
            return;
        }

        if ($throwable instanceof RetryableException) {
            if ($throwable->willRetry() && $this->reportRetryableExceptionAttempts === false) {
                return;
            }

            $throwable = $throwable->getPrevious();
        }

        if ($throwable instanceof WrappedExceptionsInterface) {
            foreach ($throwable->getWrappedExceptions() as $wrappedException) {
                $this->report($wrappedException);
            }

            return;
        }

        // We especially want to check UnrecoverableMessageHandlingException, not UnrecoverableExceptionInterface
        if (\class_exists(UnrecoverableMessageHandlingException::class)
            && $throwable instanceof UnrecoverableMessageHandlingException
            && $throwable->getPrevious() instanceof Throwable
        ) {
            $throwable = $throwable->getPrevious();
        }

        // Sanity check because getPrevious() signature can return null
        if ($throwable instanceof Throwable === false) {
            return;
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

        $this->handledExceptions->attach($throwable);
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
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     *
     * @return list<TValue>
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
        /** @var \EonX\EasyErrorHandler\Common\Provider\ErrorResponseBuilderProviderInterface[] $providers */
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
        /** @var \EonX\EasyErrorHandler\Common\Provider\ErrorReporterProviderInterface[] $providers */
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

    /**
     * @template T
     *
     * @param T $items
     *
     * @return (T is array ? array : T)
     */
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
