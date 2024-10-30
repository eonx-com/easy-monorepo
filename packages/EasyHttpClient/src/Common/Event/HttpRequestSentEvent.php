<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Event;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use Throwable;

final readonly class HttpRequestSentEvent
{
    private array $extra;

    public function __construct(
        private RequestData $requestData,
        private ?ResponseData $responseData = null,
        private ?Throwable $throwable = null,
        private ?DateTimeInterface $throwableThrownAt = null,
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
