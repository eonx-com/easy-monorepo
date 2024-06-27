<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Entity;

use DateTimeInterface;

interface WebhookInterface
{
    public const DEFAULT_CURRENT_ATTEMPT = 0;

    public const DEFAULT_METHOD = 'POST';

    public const HEADER_EVENT = 'X-Webhook-Event';

    public const HEADER_ID = 'X-Webhook-Id';

    public const HEADER_SIGNATURE = 'X-Webhook-Signature';

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

    public const OPTION_BODY = 'body';

    public const OPTION_BODY_AS_STRING = 'body_as_string';

    public const OPTION_CURRENT_ATTEMPT = 'current_attempt';

    public const OPTION_EVENT = 'event';

    public const OPTION_HTTP_OPTIONS = 'http_options';

    public const OPTION_ID = 'id';

    public const OPTION_MAX_ATTEMPT = 'max_attempt';

    public const OPTION_METHOD = 'method';

    public const OPTION_SECRET = 'secret';

    public const OPTION_SEND_AFTER = 'send_after';

    public const OPTION_SEND_NOW = 'send_now';

    public const OPTION_STATUS = 'status';

    public const OPTION_URL = 'url';

    public const STATUSES = [
        self::STATUS_FAILED,
        self::STATUS_FAILED_PENDING_RETRY,
        self::STATUS_PENDING,
        self::STATUS_SUCCESS,
    ];

    public const STATUS_FAILED = 'failed';

    public const STATUS_FAILED_PENDING_RETRY = 'failed_pending_retry';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public static function fromArray(array $data): self;

    public function allowRerun(?bool $allowRerun = null): self;

    public function body(array $body): self;

    public function bodyAsString(string $body): self;

    public function bypassSendAfter(?bool $bypassSendAfter = null): self;

    public function configured(?bool $configured = null): self;

    public function currentAttempt(int $currentAttempt): self;

    public function event(string $event): self;

    public function extra(array $extra): self;

    public function getBody(): ?array;

    public function getBodyAsString(): ?string;

    public function getCurrentAttempt(): int;

    public function getEvent(): ?string;

    public function getExtra(): ?array;

    public function getHttpClientOptions(): ?array;

    public function getId(): ?string;

    public function getMaxAttempt(): int;

    public function getMethod(): ?string;

    public function getSecret(): ?string;

    public function getSendAfter(): ?DateTimeInterface;

    public function getStatus(): string;

    public function getUrl(): ?string;

    public function header(string $name, mixed $value): self;

    public function headers(array $headers): self;

    public function httpClientOptions(array $options): self;

    public function id(string $id): self;

    public function isConfigured(): bool;

    public function isRerunAllowed(): bool;

    public function isSendAfterBypassed(): bool;

    public function isSendNow(): bool;

    public function maxAttempt(int $maxAttempt): self;

    public function mergeExtra(array $extra): self;

    public function mergeHttpClientOptions(array $options): self;

    public function method(string $method): self;

    public function queries(array $queries): self;

    public function query(string $name, mixed $value): self;

    public function secret(string $secret): self;

    public function sendAfter(DateTimeInterface $after): self;

    public function sendNow(?bool $sendNow = null): self;

    public function status(string $status): self;

    public function toArray(): array;

    public function url(string $url): self;
}
