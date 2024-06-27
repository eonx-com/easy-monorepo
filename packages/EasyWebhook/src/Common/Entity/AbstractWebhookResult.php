<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Entity;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

abstract class AbstractWebhookResult implements WebhookResultInterface
{
    private ?string $id = null;

    public function __construct(
        private WebhookInterface $webhook,
        private ?ResponseInterface $response = null,
        private ?Throwable $throwable = null,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    public function getWebhook(): WebhookInterface
    {
        return $this->webhook;
    }

    public function isAttempted(): bool
    {
        return $this->response !== null || $this->throwable !== null;
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
