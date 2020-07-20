<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks;

use EonX\EasyWebhooks\Interfaces\WebhookInterface;
use EonX\EasyWebhooks\Interfaces\WebhookResultInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WebhookResult implements WebhookResultInterface
{
    /**
     * @var null|\Symfony\Contracts\HttpClient\ResponseInterface
     */
    private $response;

    /**
     * @var null|\Throwable
     */
    private $throwable;

    /**
     * @var \EonX\EasyWebhooks\Interfaces\WebhookInterface
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
}
