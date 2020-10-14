<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions\Traits;

use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;

trait TranslatableExceptionTrait
{
    /**
     * @var mixed[]
     */
    protected $messageParams = [];

    /**
     * @var string|null
     */
    protected $userMessage = TranslatableExceptionInterface::DEFAULT_USER_MESSAGE;

    /**
     * @var mixed[]
     */
    protected $userMessageParams = [];

    /**
     * {@inheritdoc}
     */
    public function getDomain(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserMessage(): ?string
    {
        return $this->userMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserMessageParams(): array
    {
        return $this->userMessageParams;
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
        $this->userMessage = $userMessage;

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
