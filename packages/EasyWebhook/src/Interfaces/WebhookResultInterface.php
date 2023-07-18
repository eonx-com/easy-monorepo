<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

interface WebhookResultInterface
{
    public function getId(): ?string;

    public function getResponse(): ?ResponseInterface;

    public function getThrowable(): ?Throwable;

    public function getWebhook(): WebhookInterface;

    public function isAttempted(): bool;

    public function isSuccessful(): bool;

    public function setId(string $id): self;
}
