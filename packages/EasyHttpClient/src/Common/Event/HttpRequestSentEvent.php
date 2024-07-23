<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Event;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;
use EonX\EasyHttpClient\Common\ValueObject\ResponseDataInterface;
use Throwable;

final class HttpRequestSentEvent
{
    private readonly array $extra;

    public function __construct(
        private readonly RequestDataInterface $requestData,
        private readonly ?ResponseDataInterface $responseData = null,
        private readonly ?Throwable $throwable = null,
        private readonly ?DateTimeInterface $throwableThrownAt = null,
        ?array $extra = null,
    ) {
        $this->extra = $extra ?? [];
    }

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
