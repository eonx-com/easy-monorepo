<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Event;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use Throwable;

final class HttpRequestSentEvent
{
    private readonly array $extra;

    public function __construct(
        private readonly RequestData $requestData,
        private readonly ?ResponseData $responseData = null,
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

    public function getRequestData(): RequestData
    {
        return $this->requestData;
    }

    public function getResponseData(): ?ResponseData
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
