<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use BackedEnum;
use EonX\EasyErrorHandler\Common\Builder\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Throwable;

abstract class AbstractApiPlatformExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ApiPlatformErrorResponseBuilderInterface
{
    public function __construct(
        protected readonly TranslatorInterface $translator,
        // @todo Make $nameConverter nullable and change type to `AdvancedNameConverterInterface` in 7.0
        protected readonly MetadataAwareNameConverter $nameConverter,
        private readonly array $keys,
        ?int $priority = null,
        protected readonly int|string|BackedEnum|null $validationErrorCode = null,
    ) {
        parent::__construct($priority);
    }

    public function buildStatusCode(Throwable $throwable, ?HttpStatusCode $statusCode = null): ?HttpStatusCode
    {
        if ($this->supports($throwable)) {
            $statusCode = HttpStatusCode::BadRequest;
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
