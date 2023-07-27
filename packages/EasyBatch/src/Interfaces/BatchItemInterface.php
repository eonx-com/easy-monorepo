<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemInterface extends BatchObjectInterface
{
    public const STATUSES_FOR_DISPATCH = [
        self::STATUS_BATCH_PENDING_APPROVAL,
        self::STATUS_CREATED,
        self::STATUS_FAILED_PENDING_RETRY,
    ];

    public const STATUS_BATCH_PENDING_APPROVAL = 'batch_pending_approval';

    public const STATUS_FAILED_PENDING_RETRY = 'failed_pending_retry';

    public const STATUS_PROCESSING_DEPENDENT_OBJECTS = 'processing_dependent_objects';

    public const TYPE_MESSAGE = 'message';

    public const TYPE_NESTED_BATCH = 'nested_batch';

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
