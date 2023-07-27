<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Provider;

use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationErrorResponseBuilder;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationExceptionErrorResponseBuilder;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;

final class ApiPlatformErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    private readonly array $keys;

    private readonly bool $transformValidationErrors;

    public function __construct(
        private readonly TranslatorInterface $translator,
        ?array $keys = null,
        ?bool $transformValidationErrors = null,
    ) {
        $this->keys = $keys ?? [];
        $this->transformValidationErrors = $transformValidationErrors ?? true;
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        if (\class_exists(ValidationException::class)) {
            yield new ApiPlatformValidationExceptionErrorResponseBuilder($this->translator, $this->keys);

            if ($this->transformValidationErrors) {
                yield new ApiPlatformValidationErrorResponseBuilder($this->translator, $this->keys);
            }
        }
    }
}
