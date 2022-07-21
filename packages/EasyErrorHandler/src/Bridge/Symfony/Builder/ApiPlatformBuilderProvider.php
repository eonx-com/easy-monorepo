<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;

final class ApiPlatformBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @var mixed[]
     */
    private $keys;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\TranslatorInterface
     */
    private $translator;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(TranslatorInterface $translator, ?array $keys = null)
    {
        $this->translator = $translator;
        $this->keys = $keys ?? [];
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        if (\class_exists(ValidationException::class)) {
            yield new ApiPlatformValidationExceptionResponseBuilder($this->translator, $this->keys);
            yield new ApiPlatformValidationErrorResponseBuilder();
        }
    }
}
