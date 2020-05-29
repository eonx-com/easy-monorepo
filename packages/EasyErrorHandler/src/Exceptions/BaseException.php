<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use EonX\EasyErrorHandler\Interfaces\LogLevelAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatableExceptionInterface;
use EonX\EasyLogging\Interfaces\LoggerInterface;
use Exception;

abstract class BaseException extends Exception implements
    TranslatableExceptionInterface,
    LogLevelAwareExceptionInterface,
    StatusCodeAwareExceptionInterface,
    SubCodeAwareExceptionInterface
{
    /**
     * @var string
     */
    protected $logLevel = LoggerInterface::LEVEL_ERROR;

    /**
     * @var mixed[]
     */
    protected $messageParams = [];

    /**
     * @var int
     */
    protected $statusCode = 500;

    /**
     * @var int
     */
    protected $subCode = 0;

    /**
     * @var string|null
     */
    protected $userMessage = 'easy-error-handler::messages.default_user_message';

    /**
     * @var mixed[]
     */
    protected $userMessageParams = [];

    /**
     * {@inheritdoc}
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
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
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubCode(): int
    {
        return $this->subCode;
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
     * Sets the log level for an exception.
     */
    public function setLogLevel(string $logLevel): self
    {
        $this->logLevel = $logLevel;

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
     * Sets the HTTP response status code for an exception.
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Sets the sub code for an exception.
     */
    public function setSubCode(int $subCode): self
    {
        $this->subCode = $subCode;

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
