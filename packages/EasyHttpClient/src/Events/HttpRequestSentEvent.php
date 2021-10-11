<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Events;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;

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
     * @var \EonX\EasyHttpClient\Interfaces\ResponseDataInterface
     */
    private $responseData;

    /**
     * @param null|mixed[] $extra
     */
    public function __construct(
        RequestDataInterface $requestData,
        ResponseDataInterface $responseData,
        ?array $extra = null
    ) {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
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

    public function getResponseData(): ResponseDataInterface
    {
        return $this->responseData;
    }
}
