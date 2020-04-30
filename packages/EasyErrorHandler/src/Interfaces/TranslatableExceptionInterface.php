<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface TranslatableExceptionInterface
{
    /**
     * Returns the exception message parameters.
     *
     * @return mixed[]
     */
    public function getMessageParams(): array;

    /**
     * Returns the user-friendly message.
     */
    public function getUserMessage(): ?string;

    /**
     * Returns the user-friendly message parameters.
     *
     * @return mixed[]
     */
    public function getUserMessageParams(): array;
}
