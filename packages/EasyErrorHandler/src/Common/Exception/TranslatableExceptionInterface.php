<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface TranslatableExceptionInterface
{
    /**
     * Returns the translation domain.
     */
    public function getDomain(): ?string;

    /**
     * Returns the exception message parameters.
     */
    public function getMessageParams(): array;

    /**
     * Returns the user-friendly message.
     */
    public function getUserMessage(): string;

    /**
     * Returns the user-friendly message parameters.
     */
    public function getUserMessageParams(): array;

    public function shouldSkipTranslation(): bool;
}
