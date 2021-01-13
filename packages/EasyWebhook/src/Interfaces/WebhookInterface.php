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
    public const OPTIONS = [
        self::OPTION_BODY,
        self::OPTION_CURRENT_ATTEMPT,
        self::OPTION_EVENT,
        self::OPTION_HTTP_OPTIONS,
        self::OPTION_MAX_ATTEMPT,
        self::OPTION_METHOD,
        self::OPTION_STATUS,
        self::OPTION_URL,
    ];

    /**
     * @var string
     */
    public const OPTION_BODY = 'body';

    /**
     * @var string
     */
    public const OPTION_CURRENT_ATTEMPT = 'current_attempt';

    /**
     * @var string
     */
    public const OPTION_EVENT = 'event';

    /**
     * @var string
     */
    public const OPTION_HTTP_OPTIONS = 'http_options';

    /**
     * @var string
     */
    public const OPTION_MAX_ATTEMPT = 'max_attempt';

    /**
     * @var string
     */
    public const OPTION_METHOD = 'method';

    /**
     * @var string
     */
    public const OPTION_STATUS = 'status';

    /**
     * @var string
     */
    public const OPTION_URL = 'url';

    /**
     * @var string[]
     */
    public const STATUSES = [
        self::STATUS_FAILED,
        self::STATUS_FAILED_PENDING_RETRY,
        self::STATUS_PENDING,
        self::STATUS_SUCCESS,
    ];

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

    /**
     * @param mixed[] $body
     */
    public function body(array $body): self;

    public function configured(?bool $configured = null): self;

    public function currentAttempt(int $currentAttempt): self;

    public function event(string $event): self;

    /**
     * @param mixed[] $extra
     */
    public function extra(array $extra): self;

    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): self;

    /**
     * @return null|mixed[]
     */
    public function getBody(): ?array;

    public function getCurrentAttempt(): int;

    public function getEvent(): ?string;

    /**
     * @return null|mixed[]
     */
    public function getExtra(): ?array;

    /**
     * @return null|mixed[]
     */
    public function getHttpClientOptions(): ?array;

    public function getId(): ?string;

    public function getMaxAttempt(): int;

    public function getMethod(): ?string;

    public function getSecret(): ?string;

    public function getStatus(): string;

    public function getUrl(): ?string;

    /**
     * @param mixed[] $options
     */
    public function httpClientOptions(array $options): self;

    public function id(string $id): self;

    public function isConfigured(): bool;

    public function isSendNow(): bool;

    public function maxAttempt(int $maxAttempt): self;

    /**
     * @param mixed[] $extra
     */
    public function mergeExtra(array $extra): self;

    /**
     * @param mixed[] $options
     */
    public function mergeHttpClientOptions(array $options): self;

    public function method(string $method): self;

    public function secret(string $secret): self;

    public function sendNow(?bool $sendNow = null): self;

    public function status(string $status): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;

    public function url(string $url): self;
}
