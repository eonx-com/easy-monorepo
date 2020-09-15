<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Response\Data\ErrorResponseData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[]
     */
    private $builders;

    /**
     * @var bool
     */
    private $isVerbose;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface[]
     */
    private $reporters;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @param iterable<mixed> $builderProviders
     * @param iterable<mixed> $reporterProviders
     */
    public function __construct(
        ErrorResponseFactoryInterface $errorResponseFactory,
        iterable $builderProviders,
        iterable $reporterProviders,
        ?bool $isVerbose = null
    ) {
        $this->responseFactory = $errorResponseFactory;
        $this->setBuilders($builderProviders);
        $this->setReporters($reporterProviders);
        $this->isVerbose = $isVerbose ?? false;
    }

    public function isVerbose(): bool
    {
        return $this->isVerbose;
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        foreach ($this->builders as $builder) {
            $data = $builder->buildData($throwable, $data ?? []);
            $headers = $builder->buildHeaders($throwable, $headers ?? null);
            $statusCode = $builder->buildStatusCode($throwable, $statusCode ?? null);
        }

        return $this->responseFactory->create(
            $request,
            ErrorResponseData::create($this->sortRecursive($data ?? []), $statusCode ?? null, $headers ?? null)
        );
    }

    public function report(Throwable $throwable): void
    {
        foreach ($this->reporters as $reporter) {
            // Stop reporting if reporter returns false
            if ($reporter->report($throwable) === false) {
                break;
            }
        }
    }

    /**
     * @param iterable<mixed> $reporters
     */
    private function filterIterable(iterable $items, string $class): array
    {
        $items = $items instanceof \Traversable ? \iterator_to_array($items) : (array)$items;

        $items = \array_filter($items, static function ($reporter) use ($class): bool {
            return $reporter instanceof $class;
        });

        foreach ($items as $item) {
            if ($item instanceof ErrorHandlerAwareInterface) {
                $item->setErrorHandler($this);
            }
        }

        return $items;
    }

    /**
     * @param iterable<mixed> $builderProviders
     */
    private function setBuilders(iterable $builderProviders): void
    {
        /** @var \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface[] $providers */
        $providers = $this->filterIterable($builderProviders, ErrorResponseBuilderProviderInterface::class);
        $builders = [];

        foreach ($providers as $provider) {
            $tmpBuilders = $this->filterIterable($provider->getBuilders(), ErrorResponseBuilderInterface::class);

            foreach ($tmpBuilders as $builder) {
                $builders[] = $builder;
            }
        }

        \usort(
            $builders,
            static function (ErrorResponseBuilderInterface $first, ErrorResponseBuilderInterface $second): int {
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        $this->builders = $builders;
    }

    /**
     * @param iterable<mixed> $reporterProviders
     */
    private function setReporters(iterable $reporterProviders): void
    {
        /** @var \EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface[] $providers */
        $providers = $this->filterIterable($reporterProviders, ErrorResponseBuilderProviderInterface::class);
        $reporters = [];

        foreach ($providers as $provider) {
            $tmpReporters = $this->filterIterable($provider->getReporters(), ErrorReporterInterface::class);

            foreach ($tmpReporters as $reporter) {
                $reporters[] = $reporter;
            }
        }

        \usort(
            $reporters,
            static function (ErrorResponseBuilderInterface $first, ErrorResponseBuilderInterface $second): int {
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        $this->reporters = $reporters;
    }

    /**
     * @param mixed $items
     *
     * @return mixed
     */
    private function sortRecursive($items)
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
