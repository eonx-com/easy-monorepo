<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Entity;

use DateTimeInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;

abstract class AbstractWebhook implements WebhookInterface
{
    /**
     * @var string[]
     */
    protected static array $booleans = [WebhookOption::SendNow->value];

    /**
     * @var string[]
     */
    protected static array $integers = [WebhookOption::CurrentAttempt->value, WebhookOption::MaxAttempt->value];

    /**
     * @var string[]
     */
    protected static array $setters = [
        WebhookOption::BodyAsString->value => 'bodyAsString',
        WebhookOption::Body->value => 'body',
        WebhookOption::CurrentAttempt->value => 'currentAttempt',
        WebhookOption::Event->value => 'event',
        WebhookOption::HttpOptions->value => 'httpClientOptions',
        WebhookOption::Id->value => 'id',
        WebhookOption::MaxAttempt->value => 'maxAttempt',
        WebhookOption::Method->value => 'method',
        WebhookOption::Secret->value => 'secret',
        WebhookOption::SendAfter->value => 'sendAfter',
        WebhookOption::SendNow->value => 'sendNow',
        WebhookOption::Status->value => 'status',
        WebhookOption::Url->value => 'url',
    ];

    private ?bool $allowRerun = null;

    private ?array $body = null;

    private ?string $bodyAsString = null;

    private ?bool $bypassSendAfter = null;

    private ?bool $configured = null;

    private ?int $currentAttempt = null;

    private ?string $event = null;

    private ?array $extra = null;

    private ?array $headers = null;

    private ?array $httpClientOptions = null;

    private ?string $id = null;

    private ?int $maxAttempt = null;

    private ?string $method = null;

    private ?array $queries = null;

    private ?string $secret = null;

    private ?DateTimeInterface $sendAfter = null;

    private ?bool $sendNow = null;

    private ?WebhookStatus $status = null;

    private ?string $url = null;

    public static function create(string $url, ?array $body = null, ?string $method = null): WebhookInterface
    {
        $webhook = (new static())->url($url)
            ->method($method ?? self::DEFAULT_METHOD);

        if ($body !== null) {
            $webhook->body($body);
        }

        return $webhook;
    }

    public static function fromArray(array $data): WebhookInterface
    {
        $webhook = new static();

        foreach (static::$setters as $name => $setter) {
            if (($data[$name] ?? null) !== null) {
                $value = $data[$name];

                if (\in_array($name, static::$booleans, true)) {
                    $value = (bool)$value;
                }

                if (\in_array($name, static::$integers, true)) {
                    $value = (int)$value;
                }

                if ($name === WebhookOption::Status->value) {
                    $value = WebhookStatus::from($value);
                }

                $webhook->{$setter}($value);
            }
        }

        return $webhook;
    }

    public function allowRerun(?bool $allowRerun = null): WebhookInterface
    {
        $this->allowRerun = $allowRerun ?? true;

        return $this;
    }

    public function body(array $body): WebhookInterface
    {
        $this->body = $body;

        return $this;
    }

    public function bodyAsString(string $body): WebhookInterface
    {
        $this->bodyAsString = $body;

        return $this;
    }

    public function bypassSendAfter(?bool $bypassSendAfter = null): WebhookInterface
    {
        $this->bypassSendAfter = $bypassSendAfter;

        return $this;
    }

    public function configured(?bool $configured = null): WebhookInterface
    {
        $this->configured = $configured ?? true;

        return $this;
    }

    public function currentAttempt(int $currentAttempt): WebhookInterface
    {
        $this->currentAttempt = $currentAttempt;

        return $this;
    }

    public function event(string $event): WebhookInterface
    {
        $this->event = $event;

        return $this;
    }

    public function extra(array $extra): WebhookInterface
    {
        $this->extra = $extra;

        return $this;
    }

    public function getBody(): ?array
    {
        return $this->body;
    }

    public function getBodyAsString(): ?string
    {
        return $this->bodyAsString;
    }

    public function getCurrentAttempt(): int
    {
        return $this->currentAttempt ?? self::DEFAULT_CURRENT_ATTEMPT;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function getHttpClientOptions(): ?array
    {
        if ($this->headers === null && $this->queries === null && $this->httpClientOptions === null) {
            return null;
        }

        $return = $this->httpClientOptions ?? [];

        if ($this->headers !== null) {
            $return['headers'] = \array_merge($return['headers'] ?? [], $this->headers);
        }

        if ($this->queries !== null) {
            $return['query'] = \array_merge($return['query'] ?? [], $this->queries);
        }

        return $return;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMaxAttempt(): int
    {
        return $this->maxAttempt ?? 1;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getSendAfter(): ?DateTimeInterface
    {
        return $this->sendAfter;
    }

    public function getStatus(): WebhookStatus
    {
        return $this->status ?? WebhookStatus::Pending;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function header(string $name, mixed $value): WebhookInterface
    {
        if ($this->headers === null) {
            $this->headers = [];
        }

        $this->headers[$name] = $value;

        return $this;
    }

    public function headers(array $headers): WebhookInterface
    {
        $this->headers = $headers;

        return $this;
    }

    public function httpClientOptions(array $options): WebhookInterface
    {
        $this->httpClientOptions = $options;

        return $this;
    }

    public function id(string $id): WebhookInterface
    {
        $this->id = $id;

        return $this;
    }

    public function isConfigured(): bool
    {
        return $this->configured ?? false;
    }

    public function isRerunAllowed(): bool
    {
        return $this->allowRerun ?? false;
    }

    public function isSendAfterBypassed(): bool
    {
        return $this->bypassSendAfter ?? false;
    }

    public function isSendNow(): bool
    {
        return $this->sendNow ?? false;
    }

    public function maxAttempt(int $maxAttempt): WebhookInterface
    {
        $this->maxAttempt = $maxAttempt;

        return $this;
    }

    public function mergeExtra(array $extra): WebhookInterface
    {
        $this->extra = \array_merge_recursive($this->extra ?? [], $extra);

        return $this;
    }

    public function mergeHttpClientOptions(array $options): WebhookInterface
    {
        $this->httpClientOptions = \array_merge_recursive($this->httpClientOptions ?? [], $options);

        return $this;
    }

    public function method(string $method): WebhookInterface
    {
        $this->method = $method;

        return $this;
    }

    public function queries(array $queries): WebhookInterface
    {
        $this->queries = $queries;

        return $this;
    }

    public function query(string $name, mixed $value): WebhookInterface
    {
        if ($this->queries === null) {
            $this->queries = [];
        }

        $this->queries[$name] = $value;

        return $this;
    }

    public function secret(string $secret): WebhookInterface
    {
        $this->secret = $secret;

        return $this;
    }

    public function sendAfter(DateTimeInterface $after): WebhookInterface
    {
        $this->sendAfter = $after;

        return $this;
    }

    public function sendNow(?bool $sendNow = null): WebhookInterface
    {
        $this->sendNow = $sendNow ?? true;

        return $this;
    }

    public function status(WebhookStatus $status): WebhookInterface
    {
        $this->status = $status;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'current_attempt' => $this->getCurrentAttempt(),
            'event' => $this->getEvent(),
            'http_options' => $this->getHttpClientOptions(),
            'max_attempt' => $this->getMaxAttempt(),
            'method' => $this->getMethod(),
            'send_after' => $this->getSendAfter(),
            'status' => $this->getStatus(),
            'url' => $this->getUrl(),
        ];
    }

    public function url(string $url): WebhookInterface
    {
        $this->url = $url;

        return $this;
    }
}
