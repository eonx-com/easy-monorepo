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
        'body' => 'setBody',
        'current_attempt' => 'setCurrentAttempt',
        'http_options' => 'setHttpClientOptions',
        'max_attempt' => 'setMaxAttempt',
        'method' => 'setMethod',
        'status' => 'setStatus',
        'url' => 'setUrl',
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
        $webhook = (new static())->setUrl($url)->setMethod($method ?? self::DEFAULT_METHOD);

        if ($body !== null) {
            $webhook->setBody($body);
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

    public function isSendNow(): bool
    {
        return $this->sendNow ?? false;
    }

    /**
     * @param mixed[] $options
     */
    public function mergeHttpClientOptions(array $options): WebhookInterface
    {
        $this->httpClientOptions = \array_merge_recursive($this->httpClientOptions ?? [], $options);

        return $this;
    }

    /**
     * @param mixed[] $body
     */
    public function setBody(array $body): WebhookInterface
    {
        $this->body = $body;

        return $this;
    }

    public function setCurrentAttempt(int $currentAttempt): WebhookInterface
    {
        $this->currentAttempt = $currentAttempt;

        return $this;
    }

    public function setExtra(array $extra): WebhookInterface
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @param mixed[] $options
     */
    public function setHttpClientOptions(array $options): WebhookInterface
    {
        $this->httpClientOptions = $options;

        return $this;
    }

    public function setId(string $id): WebhookInterface
    {
        $this->id = $id;

        return $this;
    }

    public function setMaxAttempt(int $maxAttempt): WebhookInterface
    {
        $this->maxAttempt = $maxAttempt;

        return $this;
    }

    public function setMethod(string $method): WebhookInterface
    {
        $this->method = $method;

        return $this;
    }

    public function setSecret(string $secret): WebhookInterface
    {
        $this->secret = $secret;

        return $this;
    }

    public function setSendNow(bool $sendNow): WebhookInterface
    {
        $this->sendNow = $sendNow;

        return $this;
    }

    public function setStatus(string $status): WebhookInterface
    {
        $this->status = $status;

        return $this;
    }

    public function setUrl(string $url): WebhookInterface
    {
        $this->url = $url;

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
}
