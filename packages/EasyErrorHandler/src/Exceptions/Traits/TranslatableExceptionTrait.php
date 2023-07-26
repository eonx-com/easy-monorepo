<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;

trait TranslatableExceptionTrait
{
    protected ?string $domain = null;

    /**
     * @var mixed[]
     */
    protected array $messageParams = [];

    protected string $userMessage = TranslatableExceptionInterface::USER_MESSAGE_DEFAULT;

    /**
     * @var mixed[]
     */
    protected array $userMessageParams = [];

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getUserMessageParams(): array
    {
        return $this->userMessageParams;
    }

    /**
     * Sets the translation domain for Symfony bridge.
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets the exception message parameters.
     *
     * @param mixed[] $messageParams
     */
    public function setMessageParams(array $messageParams): self
    {
        $this->messageParams = $messageParams;

        return $this;
    }

    /**
     * Sets the user-friendly message.
     */
    public function setUserMessage(?string $userMessage = null): self
    {
        $this->userMessage = $userMessage ?? TranslatableExceptionInterface::USER_MESSAGE_DEFAULT;

        return $this;
    }

    /**
     * Sets the user-friendly message parameters.
     *
     * @param mixed[] $userMessageParams
     */
    public function setUserMessageParams(array $userMessageParams): self
    {
        $this->userMessageParams = $userMessageParams;

        return $this;
    }
}
