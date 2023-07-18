<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use DateTimeInterface;

interface WebhookInterface
{
    /**
     * @var int
     */
    public const DEFAULT_CURRENT_ATTEMPT = 0;

    /**
     * @var string
     */
    public const DEFAULT_METHOD = 'POST';

    /**
     * @var string
     */
    public const HEADER_EVENT = 'X-Webhook-Event';

    /**
     * @var string
     */
    public const HEADER_ID = 'X-Webhook-Id';

    /**
     * @var string
     */
    public const HEADER_SIGNATURE = 'X-Webhook-Signature';

    /**
     * @var string[]
     */
    public const OPTIONS = [
        self::OPTION_BODY,
        self::OPTION_BODY_AS_STRING,
        self::OPTION_CURRENT_ATTEMPT,
        self::OPTION_EVENT,
        self::OPTION_ID,
        self::OPTION_HTTP_OPTIONS,
        self::OPTION_MAX_ATTEMPT,
        self::OPTION_METHOD,
        self::OPTION_SECRET,
        self::OPTION_SEND_AFTER,
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
    public const OPTION_BODY_AS_STRING = 'body_as_string';

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
    public const OPTION_ID = 'id';

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
    public const OPTION_SECRET = 'secret';

    /**
     * @var string
     */
    public const OPTION_SEND_AFTER = 'send_after';

    /**
     * @var string
     */
    public const OPTION_SEND_NOW = 'send_now';

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

    public function allowRerun(?bool $allowRerun = null): self;

    /**
     * @param mixed[] $body
     */
    public function body(array $body): self;

    public function bodyAsString(string $body): self;

    public function bypassSendAfter(?bool $bypassSendAfter = null): self;

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

    public function getBodyAsString(): ?string;

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

    public function getSendAfter(): ?DateTimeInterface;

    public function getStatus(): string;

    public function getUrl(): ?string;

    public function header(string $name, mixed $value): self;

    /**
     * @param mixed[] $headers
     */
    public function headers(array $headers): self;

    /**
     * @param mixed[] $options
     */
    public function httpClientOptions(array $options): self;

    public function id(string $id): self;

    public function isConfigured(): bool;

    public function isRerunAllowed(): bool;

    public function isSendAfterBypassed(): bool;

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

    /**
     * @param mixed[] $queries
     */
    public function queries(array $queries): self;

    public function query(string $name, mixed $value): self;

    public function secret(string $secret): self;

    public function sendAfter(DateTimeInterface $after): self;

    public function sendNow(?bool $sendNow = null): self;

    public function status(string $status): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;

    public function url(string $url): self;
}
