<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\ValueObject;

use DateTimeInterface;

final class RequestData implements RequestDataInterface
{
    public function __construct(
        private readonly string $method,
        private array $options,
        private readonly DateTimeInterface $sentAt,
        private readonly string $url,
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
