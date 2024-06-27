<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Event;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\ValueObject\RequestDataInterface;
use EonX\EasyHttpClient\Common\ValueObject\ResponseDataInterface;
use Throwable;

final class HttpRequestSentEvent
{
    private array $extra;

    public function __construct(
        private RequestDataInterface $requestData,
        private ?ResponseDataInterface $responseData = null,
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
