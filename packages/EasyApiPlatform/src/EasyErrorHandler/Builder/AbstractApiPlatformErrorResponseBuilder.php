<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Metadata\Metadata;
use BackedEnum;
use EonX\EasyErrorHandler\Common\Builder\AbstractErrorResponseBuilder;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

abstract class AbstractApiPlatformErrorResponseBuilder extends AbstractErrorResponseBuilder implements
    ApiPlatformErrorResponseBuilderInterface
{
    private RequestStack $requestStack;

    public function __construct(
        protected readonly TranslatorInterface $translator,
        protected readonly ?NameConverterInterface $nameConverter,
        private readonly array $keys,
        ?int $priority = null,
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

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
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

    /**
     * @param class-string|null $class
     */
    protected function normalizePropertyName(string $name, ?string $class = null): string
    {
        $class ??= $this->resolveClassFromMainRequest();

        if ($class !== null && $this->nameConverter !== null) {
            return $this->nameConverter->normalize($name, $class);
        }

        return $name;
    }

    /**
     * @return class-string|null
     */
    private function resolveClassFromMainRequest(): ?string
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest === null) {
            return null;
        }

        $operation = $mainRequest->attributes->get('_api_operation');

        if ($operation instanceof Metadata) {
            $input = $operation->getInput();

            /** @var class-string|null $inputClass */
            $inputClass = \is_array($input) ? ($input['class'] ?? null) : null;

            if ($inputClass !== null && \class_exists($inputClass)) {
                return $inputClass;
            }
        }

        /** @var class-string|null $apiResourceClass */
        $apiResourceClass = $mainRequest->attributes->get('_api_resource_class');

        return $apiResourceClass;
    }
}
