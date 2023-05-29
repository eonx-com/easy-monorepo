<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

final class MessageDecorator
{
    private ?string $class = null;

    private ?string $dependsOn = null;

    private bool $encrypted = false;

    private ?string $encryptionKeyName = null;

    private int $maxAttempts = 1;

    /**
     * @var mixed[]|null
     */
    private ?array $metadata = null;

    private ?string $name = null;

    private bool $requiresApproval = false;

    public function __construct(
        private readonly object $message
    ) {
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

    public function getEncryptionKeyName(): ?string
    {
        return $this->encryptionKeyName;
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

    public function isEncrypted(): bool
    {
        return $this->encrypted;
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

    public function setEncrypted(?bool $encrypted = null): self
    {
        $this->encrypted = $encrypted ?? true;

        return $this;
    }

    public function setEncryptionKeyName(string $encryptionKeyName): self
    {
        $this->encryptionKeyName = $encryptionKeyName;

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
