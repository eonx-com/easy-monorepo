<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Events;

use EonX\EasyHttpClient\Interfaces\RequestDataInterface;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;

final class HttpRequestSentEvent
{
    /**
     * @var \EonX\EasyHttpClient\Interfaces\RequestDataInterface
     */
    private $requestData;

    /**
     * @var \EonX\EasyHttpClient\Interfaces\ResponseDataInterface
     */
    private $responseData;

    public function __construct(RequestDataInterface $requestData, ResponseDataInterface $responseData)
    {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
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
