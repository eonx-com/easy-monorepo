<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

final class MessageDecorator
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $dependsOn;

    /**
     * @var int
     */
    private $maxAttempts = 1;

    /**
     * @var object
     */
    private $message;

    /**
     * @var mixed[]
     */
    private $metadata;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $requiresApproval = false;

    public function __construct(object $message)
    {
        $this->message = $message;
    }

    public static function wrap(object $message): self
    {
        return $message instanceof self ? $message : new self($message);
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getDependsOn(): ?string
    {
        return $this->dependsOn;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getMessage(): object
    {
        return $this->message;
    }

    /**
     * @return null|mixed[]
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isApprovalRequired(): bool
    {
        return $this->requiresApproval;
    }

    public function setApprovalRequired(bool $isApprovalRequired): self
    {
        $this->requiresApproval = $isApprovalRequired;

        return $this;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function setDependsOn(string $dependsOn): self
    {
        $this->dependsOn = $dependsOn;

        return $this;
    }

    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * @param mixed[] $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
