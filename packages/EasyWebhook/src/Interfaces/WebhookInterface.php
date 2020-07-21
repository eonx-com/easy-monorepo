<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookInterface
{
    /**
     * @var string
     */
    public const DEFAULT_METHOD = 'POST';

    /**
     * @var string
     */
    public const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    public const STATUS_FAILED_PENDING_RETRY = 'failed_pending_retry';

    /**
     * @var string
     */
    public const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    public const STATUS_SUCCESS = 'success';

    public static function fromArray(array $data): WebhookInterface;

    public function getBody(): ?array;

    public function getCurrentAttempt(): int;

    public function getExtra(): ?array;

    public function getHttpClientOptions(): ?array;

    public function getId(): ?string;

    public function getMaxAttempt(): int;

    public function getMethod(): ?string;

    public function getRetryAfter(): ?\DateTimeInterface;

    public function getSecret(): ?string;

    public function getStatus(): string;

    public function getUrl(): ?string;

    public function isSendNow(): bool;

    public function mergeHttpClientOptions(array $options): self;

    public function setBody(array $body): self;

    public function setCurrentAttempt(int $currentAttempt): self;

    public function setExtra(array $extra): self;

    public function setHttpClientOptions(array $options): self;

    public function setId(string $id): self;

    public function setMaxAttempt(int $maxAttempt): self;

    public function setMethod(string $method): self;

    /**
     * @param null|string|\DateTimeInterface $retryAfter
     */
    public function setRetryAfter($retryAfter = null): self;

    public function setSecret(string $secret): self;

    public function setSendNow(bool $sendNow): self;

    public function setStatus(string $status): self;

    public function setUrl(string $url): self;

    public function toArray(): array;
}
