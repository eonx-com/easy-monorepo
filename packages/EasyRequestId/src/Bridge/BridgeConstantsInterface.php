<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_HTTP_HEADER_CORRELATION_ID = 'easy_request_id.http_header.correlation_id';

    public const PARAM_HTTP_HEADER_REQUEST_ID = 'easy_request_id.http_header.request_id';
}
