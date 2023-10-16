<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler;

use EonX\EasyErrorHandler\Interfaces\IgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\FormatAwareInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Response\Data\ErrorResponseData;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Psr\Log\LoggerInterface;
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

    public function __construct(
        private readonly ErrorResponseFactoryInterface $errorResponseFactory,
        private readonly LoggerInterface $logger,
        private readonly VerboseStrategyInterface $verboseStrategy,
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        private readonly IgnoreExceptionsResolverInterface $ignoreExceptionsResolver,
        iterable $builderProviders,
    ) {
        $this->setBuilders($builderProviders);
    }

    /**
     * @return \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array
    {
        return $this->builders;
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
        if ($this->ignoreExceptionsResolver->shouldIgnore($throwable)) {
            return;
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
            $this->report($throwable->getPrevious());

            return;
        }

        $this->verboseStrategy->setThrowable($throwable);

        $this->logger->log(
            $this->errorLogLevelResolver->getLogLevel($throwable),
            $this->errorDetailsResolver->resolveInternalMessage($throwable),
            ['exception' => $this->errorDetailsResolver->resolveExtendedDetails($throwable)]
        );
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
