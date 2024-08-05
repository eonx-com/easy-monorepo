<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

interface BatchItemInterface extends BatchObjectInterface
{
    public function canBeRetried(): bool;

    public function getAttempts(): int;

    public function getBatchId(): int|string;

    public function getDependsOnName(): ?string;

    public function getEncryptionKeyName(): ?string;

    public function getMaxAttempts(): int;

    public function getMessage(): ?object;

    public function isEncrypted(): bool;

    public function isRetried(): bool;

    public function setAttempts(int $attempts): self;

    public function setBatchId(int|string $batchId): self;

    public function setDependsOnName(string $name): self;

    public function setEncrypted(?bool $encrypted = null): self;

    public function setEncryptionKeyName(string $encryptionKeyName): self;

    public function setMaxAttempts(int $maxAttempts): self;

    public function setMessage(object $message): self;
}
