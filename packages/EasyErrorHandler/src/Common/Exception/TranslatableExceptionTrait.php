<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

trait TranslatableExceptionTrait
{
    protected ?string $domain = null;

    protected array $messageParams = [];

    protected string $userMessage = 'exceptions.default_user_message';

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
     * Sets the translation domain for Symfony integration.
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets the exception message parameters.
     */
    public function setMessageParams(array $messageParams): self
    {
        $this->messageParams = $messageParams;

        return $this;
    }

    /**
     * Sets the user-friendly message.
     */
    public function setUserMessage(string $userMessage): self
    {
        $this->userMessage = $userMessage;

        return $this;
    }

    /**
     * Sets the user-friendly message parameters.
     */
    public function setUserMessageParams(array $userMessageParams): self
    {
        $this->userMessageParams = $userMessageParams;

        return $this;
    }
}
