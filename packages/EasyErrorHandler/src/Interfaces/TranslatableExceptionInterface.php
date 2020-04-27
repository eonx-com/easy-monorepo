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
     * Returns the user message parameters.
     *
     * @return mixed[]
     */
    public function getUserMessageParams(): array;

    /**
     * Sets an exception message parameters.
     *
     * @param mixed[] $messageParams
     */
    public function setMessageParams(array $messageParams);

    /**
     * Sets the user-friendly message.
     */
    public function setUserMessage(?string $userMessage = null);

    /**
     * Sets the user-friendly message parameters.
     *
     * @param mixed[] $userMessageParams
     */
    public function setUserMessageParams(array $userMessageParams);
}
