<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Entity;

use DateTimeInterface;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;

interface WebhookInterface
{
    final public const DEFAULT_CURRENT_ATTEMPT = 0;

    final public const DEFAULT_METHOD = 'POST';

    final public const HEADER_EVENT = 'X-Webhook-Event';

    final public const HEADER_ID = 'X-Webhook-Id';

    final public const HEADER_SIGNATURE = 'X-Webhook-Signature';

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

    public function getStatus(): WebhookStatus;

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

    public function status(WebhookStatus $status): self;

    public function toArray(): array;

    public function url(string $url): self;
}
