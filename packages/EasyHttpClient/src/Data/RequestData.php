<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Data;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;

final class RequestData implements RequestDataInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var mixed[]
     */
    private $options;

    /**
     * @var \DateTimeInterface
     */
    private $sentAt;

    /**
     * @var string
     */
    private $url;

    /**
     * @param mixed[] $options
     */
    public function __construct(string $method, array $options, \DateTimeInterface $sentAt, string $url)
    {
        $this->method = $method;
        $this->options = $options;
        $this->sentAt = $sentAt;
        $this->url = $url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
