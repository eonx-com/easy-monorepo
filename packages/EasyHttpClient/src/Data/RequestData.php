<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Data;

use DateTimeInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataInterface;

final class RequestData implements RequestDataInterface
{
    public function __construct(
        private string $method,
        private array $options,
        private DateTimeInterface $sentAt,
        private string $url,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSentAt(): DateTimeInterface
    {
        return $this->sentAt;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setOptions(array $options): RequestDataInterface
    {
        $this->options = $options;

        return $this;
    }
}
