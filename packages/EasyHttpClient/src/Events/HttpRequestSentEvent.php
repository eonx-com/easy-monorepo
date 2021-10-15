<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Events;

use DateTimeInterface;
use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;
use Throwable;

final class HttpRequestSentEvent
{
    /**
     * @var mixed
     */
    private $extra;

    /**
     * @var \EonX\EasyHttpClient\Interfaces\RequestDataInterface
     */
    private $requestData;

    /**
     * @var null|\EonX\EasyHttpClient\Interfaces\ResponseDataInterface
     */
    private $responseData;

    /**
     * @var null|\Throwable
     */
    private $throwable;

    /**
     * @var null|\DateTimeInterface
     */
    private $throwableThrownAt;

    /**
     * @param null|mixed[] $extra
     */
    public function __construct(
        RequestDataInterface $requestData,
        ?ResponseDataInterface $responseData = null,
        ?Throwable $throwable = null,
        ?DateTimeInterface $throwableThrownAt = null,
        ?array $extra = null
    ) {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
        $this->throwable = $throwable;
        $this->throwableThrownAt = $throwableThrownAt;
        $this->extra = $extra ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    public function getRequestData(): RequestDataInterface
    {
        return $this->requestData;
    }

    public function getResponseData(): ?ResponseDataInterface
    {
        return $this->responseData;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    public function getThrowableThrownAt(): ?DateTimeInterface
    {
        return $this->throwableThrownAt;
    }
}
