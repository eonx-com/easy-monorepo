<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ApiPlatformErrorResponseBuilderInterface;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class AbstractApiPlatformExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ApiPlatformErrorResponseBuilderInterface
{
    public function __construct(
        protected readonly TranslatorInterface $translator,
        private readonly array $keys,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    final public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($this->supports($throwable)) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }

    final public function supports(Throwable $throwable): bool
    {
        return \count($this->buildViolations($throwable)) > 0;
    }

    abstract protected function buildViolations(Throwable $throwable): array;

    protected function getKey(string $name, ?array $keys = null): string
    {
        $keys ??= $this->keys;
        $nameParts = \explode('.', $name);

        if (\count($nameParts) <= 1) {
            return $keys[$name] ?? $name;
        }

        $firstPartOfName = \array_shift($nameParts);

        return $this->getKey(\implode('.', $nameParts), $keys[$firstPartOfName] ?? []);
    }
}
