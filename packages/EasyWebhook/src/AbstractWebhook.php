<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\WebhookInterface;

abstract class AbstractWebhook implements WebhookInterface
{
    /**
     * @var string[]
     */
    protected static $integers = [
        'current_attempt',
        'max_attempt',
    ];

    /**
     * @var string[]
     */
    protected static $setters = [
        'body' => 'body',
        'current_attempt' => 'currentAttempt',
        'http_options' => 'httpClientOptions',
        'max_attempt' => 'maxAttempt',
        'method' => 'method',
        'status' => 'status',
        'url' => 'url',
    ];

    /**
     * @var null|mixed[]
     */
    private $body;

    /**
     * @var null|int
     */
    private $currentAttempt;

    /**
     * @var null|mixed[]
     */
    private $extra;

    /**
     * @var null|mixed[]
     */
    private $httpClientOptions;

    /**
     * @var null|string
     */
    private $id;

    /**
     * @var null|int
     */
    private $maxAttempt;

    /**
     * @var null|string
     */
    private $method;

    /**
     * @var null|string
     */
    private $secret;

    /**
     * @var null|boolean
     */
    private $sendNow;

    /**
     * @var null|string
     */
    private $status;

    /**
     * @var null|string
     */
    private $url;

    /**
     * @param null|mixed[] $body
     */
    public static function create(string $url, ?array $body = null, ?string $method = null): WebhookInterface
    {
        $webhook = (new static())->url($url)->method($method ?? self::DEFAULT_METHOD);

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

                if (\in_array($name, static::$integers, true)) {
                    $value = (int)$value;
                }

                $webhook->{$setter}($value);
            }
        }

        return $webhook;
    }

    /**
     * @param mixed[] $body
     */
    public function body(array $body): WebhookInterface
    {
        $this->body = $body;

        return $this;
    }

    public function currentAttempt(int $currentAttempt): WebhookInterface
    {
        $this->currentAttempt = $currentAttempt;

        return $this;
    }

    public function extra(array $extra): WebhookInterface
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @return null|mixed[]
     */
    public function getBody(): ?array
    {
        return $this->body;
    }

    public function getCurrentAttempt(): int
    {
        return $this->currentAttempt ?? 0;
    }

    /**
     * @return null|mixed[]
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * @return null|mixed[]
     */
    public function getHttpClientOptions(): ?array
    {
        return $this->httpClientOptions;
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

    public function getStatus(): string
    {
        return $this->status ?? WebhookInterface::STATUS_PENDING;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param mixed[] $options
     */
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

    public function isSendNow(): bool
    {
        return $this->sendNow ?? false;
    }

    public function maxAttempt(int $maxAttempt): WebhookInterface
    {
        $this->maxAttempt = $maxAttempt;

        return $this;
    }

    /**
     * @param mixed[] $extra
     */
    public function mergeExtra(array $extra): WebhookInterface
    {
        $this->extra = \array_merge_recursive($this->extra ?? [], $extra);

        return $this;
    }

    /**
     * @param mixed[] $options
     */
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

    public function secret(string $secret): WebhookInterface
    {
        $this->secret = $secret;

        return $this;
    }

    public function sendNow(?bool $sendNow = null): WebhookInterface
    {
        $this->sendNow = $sendNow ?? true;

        return $this;
    }

    public function status(string $status): WebhookInterface
    {
        $this->status = $status;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'current_attempt' => $this->getCurrentAttempt(),
            'http_options' => $this->getHttpClientOptions(),
            'max_attempt' => $this->getMaxAttempt(),
            'method' => $this->getMethod(),
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
