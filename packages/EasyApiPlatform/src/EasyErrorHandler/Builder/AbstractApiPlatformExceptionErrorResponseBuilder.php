<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use BackedEnum;
use EonX\EasyErrorHandler\Common\Builder\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Throwable;

abstract class AbstractApiPlatformExceptionErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ApiPlatformErrorResponseBuilderInterface
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly RequestStack $requestStack,
        protected readonly TranslatorInterface $translator,
        private readonly array $keys,
        ?int $priority = null,
        protected readonly ?AdvancedNameConverterInterface $nameConverter = null,
        protected readonly int|string|BackedEnum|null $validationErrorCode = null,
    ) {
        parent::__construct($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        $violations = $this->buildViolations($throwable);

        if (\count($violations) > 0) {
            $data[$this->getKey('message')] = $this->translator->trans('exceptions.not_valid', []);
            $data[$this->getKey('violations')] = $violations;

            if ($this->validationErrorCode !== null) {
                $data[$this->getKey('code')] = $this->validationErrorCode instanceof BackedEnum
                    ? $this->validationErrorCode->value
                    : $this->validationErrorCode;
            }
        }

        return parent::buildData($throwable, $data);
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

    protected function normalizePropertyName(string $name, ?string $class = null): string
    {
        if ($class === null) {
            $mainRequest = $this->requestStack->getMainRequest();

            if ($mainRequest !== null) {
                /** @var string|null $apiResourceClass */
                $apiResourceClass = $mainRequest->attributes->get('_api_resource_class');
                $class = $apiResourceClass;
            }
        }

        if ($this->nameConverter !== null && $class !== null) {
            return $this->nameConverter->normalize($name, $class);
        }

        return $name;
    }

    protected function normalizeTypeName(string $class): string
    {
        $typeName = $class;

        if (\class_exists($class) || \interface_exists($class)) {
            try {
                $typeName = $this->iriConverter->getIriFromResource(
                    $class,
                    UrlGeneratorInterface::ABS_PATH,
                    new GetCollection()
                );

                $typeName .= ' IRI';

                if (\str_starts_with($typeName, '/.well-known/genid/')) {
                    $typeName = null;
                }
            } catch (Throwable) {
                // Do nothing
            }

            if ($typeName === null) {
                $classReflection = new ReflectionClass($class);
                $typeName = $classReflection->getShortName();
            }
        }

        return $typeName;
    }
}
