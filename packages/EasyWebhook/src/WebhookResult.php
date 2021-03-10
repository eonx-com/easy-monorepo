<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WebhookResult implements WebhookResultInterface
{
    /**
     * @var null|string
     */
    private $id;

    /**
     * @var null|\Symfony\Contracts\HttpClient\ResponseInterface
     */
    private $response;

    /**
     * @var null|\Throwable
     */
    private $throwable;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookInterface
     */
    private $webhook;

    public function __construct(
        WebhookInterface $webhook,
        ?ResponseInterface $response = null,
        ?\Throwable $throwable = null
    ) {
        $this->webhook = $webhook;
        $this->response = $response;
        $this->throwable = $throwable;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getWebhook(): WebhookInterface
    {
        return $this->webhook;
    }

    public function isSuccessful(): bool
    {
        return $this->throwable === null;
    }

    public function setId(string $id): WebhookResultInterface
    {
        $this->id = $id;

        return $this;
    }
}
